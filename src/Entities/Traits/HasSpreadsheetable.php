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
            if ($model->spreadsheetable) {

                $jsonData = $model->spreadsheetable->first()->content ?? [];
                $model->setAttribute('_spreadsheet', $jsonData);

            } else {

                $model->setAttribute('_spreadsheet', []);
            }
        });

        static::creating(function (Model $model) {});

        static::created(function (Model $model) {

            $model->spreadsheetable()->create([
                'content' => $model->_pendingSpreadsheetData ?? []
            ]);

        });

        static::updating(function (Model $model) {});

        static::updated(function (Model $model) {});

        static::saving(function (Model $model) {

            if (!$model->exists) {
                // For new models, preserve the spreadsheet data for after creation.
                $model->_pendingSpreadsheetData = array_merge($model->_pendingSpreadsheetData, $model->_spreadsheet ?? []);

            } else if($model->_spreadsheet){

                $model->spreadsheetable()->update([
                    'content' => json_encode($model->_spreadsheet)
                ]);
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

}
