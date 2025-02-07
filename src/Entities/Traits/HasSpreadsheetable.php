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
        static::retrieved(function (Model $model) {});

        static::creating(function (Model $model) {});

        static::created(function (Model $model) {});

        static::updating(function (Model $model) {});

        static::updated(function (Model $model) {});

        static::saving(function (Model $model) {
            // dd($model);
            if (!$model->exists) {
                // For new models, preserve the spreadsheet data for after creation.
                $model->_pendingSpreadData = array_merge($model->_pendingSpreadsheetData, $model->_spreadsheet);

            } else if($model->_spread){
                $model->spreadsheetable()->update([
                    'content' => json_encode($model->_spreadsheet)
                ]);
            }
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

    public function spreadsheetable(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(\Unusualify\Modularity\Entities\Spreadsheet::class, 'spreadsheetable');
    }

}
