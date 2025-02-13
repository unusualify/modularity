<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasSpreadsheetable
{


    protected $_pendingSpreadsheetData = [];

    /**
     * Perform any actions when booting the trait
     *
     * @return void
     */
    public static function bootHasSpreadsheetable(): void
    {
        static::retrieved(function (Model $model) {
            // Assuming you have one spreadsheet per locale
            $spreadsheet = $model->spreadsheetable()->first();
            if ($spreadsheet) {
                // Load the spreadsheet's translation for the current locale
                $translatedSpreadsheet = $spreadsheet->translateOrDefault(app()->getLocale());
                $model->setAttribute('_spreadsheet', $translatedSpreadsheet->content);
            } else {
                $model->setAttribute('_spreadsheet', []);
            }
        });

        static::creating(function (Model $model) {});

        static::created(function (Model $model) {
            $model->spreadsheetable()->create([
                'content' => $model->_pendingSpreadsheetData ?? [],
                'locale'  => app()->getLocale(),
            ]);
        });

        static::updating(function (Model $model) {});

        static::updated(function (Model $model) {});

        static::saving(function (Model $model) {
            if (!$model->exists) {
                $model->_pendingSpreadsheetData = array_merge($model->_pendingSpreadsheetData, $model->_spreadsheet ?? []);
            } else if ($model->_spreadsheet) {
                $locale = app()->getLocale();
                // Update or create the spreadsheet translation via the relationship
                $model->spreadsheetable()->updateOrCreate(
                    ['locale' => $locale],
                    ['content' => $model->_spreadsheet]
                );
            }
            $model->offsetUnset('_spreadsheet');
        });

        static::saved(function (Model $model) {});

        static::restoring(function (Model $model) {});

        static::restored(function (Model $model) {});

        static::replicating(function (Model $model) {});

        static::deleting(function (Model $model) {});

        static::deleted(function (Model $model) {});

        static::forceDeleting(function (Model $model) {});

        static::forceDeleted(function (Model $model) {});
    }

    /**
     * Laravel hook to initialize the trait
     *
     * @return void
     */
    public function initializeHasSpreadsheetable(): void
    {
        $spreadsheetableFields = ['_spreadsheet'];
        $this->mergeFillable($spreadsheetableFields);
    }

    public function spreadsheetable(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Unusualify\Modularity\Entities\Spreadsheet::class, 'spreadsheetable');
    }

    // protected function handleTranslatedSpreadsheet($data){

    // }

}
