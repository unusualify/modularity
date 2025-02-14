<?php

namespace Unusualify\Modularity\Entities\Traits;

use Oobook\Database\Eloquent\Concerns\ManageEloquent;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Unusualify\Modularity\Traits\ManageModuleRoute;

trait ModelHelpers
{
    use ManageEloquent, ManageModuleRoute, HasScopes, LogsActivity;

    public $oldTranslations = [];

    /**
     * Boot the trait.
     *
     * Sets up event listeners for model creation, updating, retrieval, and deletion.
     *
     * @return void
     */
    public static function bootModelHelpers()
    {
        static::retrieved(function ($model) {});

        static::saving(function ($model) {
            if (method_exists($model, 'isTranslatable') && $model->isTranslatable()) {
                // Store original translation values before save
                $model->translations->each(function ($translation) use ($model) {
                    // dd($translation->getDirty(), $translation);
                    if ($translation->isDirty()) {
                        foreach ($translation->getDirty() as $key => $value) {
                            if (in_array($key, $model->getTranslatedAttributes())) {
                                $prev = data_get($model->oldTranslations, $translation->locale, []);
                                data_set($prev, $key , $translation->getOriginal($key));
                                data_set($model->oldTranslations, $translation->locale, $prev);
                                // $model->setAttribute("original_{$key}", $translation->getOriginal($key));
                            }
                        }
                    }
                });
            }
        });

        static::saved(function ($model) {
            // dd($model->oldTranslations);
            try {
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
            if (method_exists($model, 'isTranslatable') && $model->isTranslatable()) {
                $changes = [];
                $oldValues = [];
                $model->translations->each(function ($translation) use (&$changes, &$oldValues, $model) {
                    if ($translation->isDirty()) {
                        foreach ($translation->getDirty() as $key => $value) {
                            if (in_array($key, $model->getTranslatedAttributes())) {
                                $changes[$translation->locale][$key] = $value;
                                $oldValues[$translation->locale][$key] = data_get($model->oldTranslations, $translation->locale, [])[$key];
                            }
                        }
                    }
                });

                if (!empty($changes)) {
                    $translatedAttributes = $model->getTranslatedAttributes();
                    $foreignKey = $model->getForeignKey();
                    $batchUuid = LogBatch::getUuid(); // save batch id to retrieve activities later

                    $properties = [
                        'attributes' => $changes,
                        'old' => $oldValues,
                    ];

                    if($batchUuid) {
                        $batchActivityModel = Activity::forBatch($batchUuid)
                            ->where('subject_type', static::class)
                            ->where('subject_id', $model->id)
                            ->first();

                        // dd($attributesToBeLogged);
                        if($batchActivityModel) {
                            $batchActivityModel->update(
                                [
                                    'properties' => array_merge_recursive_preserve($batchActivityModel->properties, $properties)
                                ]
                            );
                        }else{
                            activity()
                                ->event('saved')
                                ->performedOn($model)
                                ->withProperties($properties)
                                ->log('updated');
                        }
                    }

                }
            }
        });
    }


    /**
     * Checks if this model is soft deletable.
     *
     * @param array|string|null $columns Optionally limit the check to a set of columns.
     */
    public function isSoftDeletable(): bool
    {
        // Model must have the trait
        if (! classHasTrait($this, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            return false;
        }

        return true;
    }

    public function getTitleValue()
    {
        return $this->{$this->getRouteTitleColumnKey()};
    }

    public function getShowFormat()
    {
        return $this->{$this->getRouteTitleColumnKey()};
    }

    public function setRelationsShowFormat()
    {
        foreach ($this->getRelations() as $relationName => $relation) {

            if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                // dd($this, $relationName, $this->getRelations());
                $this->{$relationName} = $relation->map(function ($related) {

                    if (method_exists($related, 'setRelationsShowFormat')) {
                        $related->setRelationsShowFormat();
                    }

                    return $related;
                });

                $this["{$relationName}_show"] ??= $this->{$relationName}->map(fn ($model) => modelShowFormat($model))->implode(', ');

            } elseif ($relation) {

                if (method_exists($relation, 'setRelationsShowFormat')) {
                    $relation->setRelationsShowFormat();
                }

                // $this->{$relationName} = $relation;

                $this["{$relationName}_show"] ??= modelShowFormat($relation);

            }
        }
    }

    public function setStateablePreview($state)
    {
        return "<v-chip variant='text' color='{$state->color}' prepend-icon='{$state->icon}'>{$state->translatedAttribute('name')[app()->getLocale()]}</v-chip>";
    }

    public function setStateablePreviewNull()
    {
        return "<v-chip  color='' prepend-icon=''>" . __('No Status') . '</v-chip>';
    }

    public function getActivitylogOptions(): LogOptions
    {

        if(isset($this->baseModuleModel)) {
            $baseModuleClass = new $this->baseModuleModel;

            if($baseModuleClass && $baseModuleClass->isTranslatable()) {
                $this->disableLogging();
                return LogOptions::defaults()->dontSubmitEmptyLogs();

                if(false){
                    $translatedAttributes = $baseModuleClass->translatedAttributes;
                    $parentForeignKey = $baseModuleClass->getForeignKey();
                    $batchUuid = LogBatch::getUuid(); // save batch id to retrieve activities later

                    if($batchUuid) {
                        $batchActivity = Activity::forBatch($batchUuid)
                            ->where('subject_type', $this->baseModuleModel)
                            ->where('subject_id', $this->{$parentForeignKey})
                            ->first();

                        $dirty = $this->getDirty();
                        $previousAttributes = $this->getOriginal();
                        $attributesToBeLogged = [];
                        foreach($dirty as $key => $value) {
                            if(in_array($key, $translatedAttributes)) {
                                $attributesToBeLogged[$key] = $previousAttributes[$key];
                            }
                        }
                        // dd($attributesToBeLogged);
                        if($batchActivity) {
                            $batchActivity->update(
                                [
                                    'properties' => array_merge_recursive_preserve($batchActivity->properties, $this->attributesToBeLogged())
                                ]
                            );
                        }else{

                            app(ActivityLogger::class)
                                ->useLog('default')
                                ->performedOn($this->baseModuleClass::find($this->{$parentForeignKey}))
                                ->withProperties($this->attributesToBeLogged());
                            // $batchActivity = Activity::create([
                            //     'event' => $this->getCurrentEvent(),
                            //     'subject_type' => $this->baseModuleModel,
                            //     'subject_id' => $this->{$parentForeignKey},
                            // ]);
                        }
                    }
                }
            }


        }

        // For the main model, get translatable attributes and log them
        $attributesToLog = $this->fillable;

        if (method_exists($this, 'isTranslatable') && $this->isTranslatable()) {
            $attributesToLog = array_merge(
                $attributesToLog,
                $this->getTranslatedAttributes()
            );
        }

        // dd($attributesToLog, $this->isDirty(), $this->getDirty());
        return LogOptions::defaults()
            // ->logAll()
            ->logOnly($attributesToLog)
            // ->logOnly(['preferences->notifications->status', 'preferences->hero_url']) // log json attributes
            // ->logOnly(['user.name']) // log relationships
            // ->dontLogIfAttributesChangedOnly(['text'])
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ;
    }

    public function lastActivities() : \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->activities()
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->limit(10);
    }

    // public function tapActivity(\Spatie\Activitylog\Contracts\Activity $activity, string $eventName)
    // {
    //     $activity->description = "activity.logs.message.{$eventName}";
    // }

}
