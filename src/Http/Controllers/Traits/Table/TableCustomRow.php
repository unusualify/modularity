<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Table;

use Unusualify\Modularous\Entities\Model;

trait TableCustomRow
{
    /**
     * Get the custom row data
     *
     * @param Model $item
     * @return array
     */
    protected function getCustomRowData($item)
    {
        $customRows = $this->getTableAttribute('customRow') ?? [];
        $customRowFillable = [];

        foreach ($customRows as $customRow) {
            if (isset($customRow['itemAttributes']) && is_array($customRow['itemAttributes'])) {
                $customRowFillable = array_unique(array_merge($customRowFillable, $customRow['itemAttributes']));
            }
        }

        $customRowData = [];

        foreach ($this->getCustomRowAppendData($item) as $key) {
            $itemTitle = $key;
            $itemValue = $key;
            preg_match('/(.*) as (.*)/', $key, $matches);
            if ($matches) {
                $itemTitle = $matches[2];
                $itemValue = $matches[1];
            }

            if (! isset($customRowData[$itemTitle])) {
                $customRowData[$itemTitle] = $item->{$itemValue};
            }
        }

        foreach ($customRowFillable as $fillable) {
            if (! isset($customRowData[$fillable])) {
                $customRowData[$fillable] = $item->{$fillable};
            }
        }

        // foreach ( $this->addIndexWithsCustomRowData() as $with ) {
        //     $customRowData[$with] = $item->getRelation($with);
        // }

        return $customRowData;
    }

    /**
     * Get the custom row append data
     *
     * @return array
     */
    protected function getCustomRowAppendData()
    {
        $customRows = $this->getTableAttribute('customRow') ?? [];
        $customRowAppend = [];

        foreach ($customRows as $customRow) {
            $customRowAppend = array_unique(array_merge($customRowAppend, $customRow['append'] ?? []));
        }

        return $customRowAppend;
    }

    /**
     * Get the custom row with data
     *
     * @return array
     */
    protected function addIndexWithsCustomRowData()
    {
        $customRows = $this->getTableAttribute('customRow') ?? [];
        $customRowWith = [];

        foreach ($customRows as $customRow) {
            $customRowWith = array_unique(array_merge($customRowWith, $customRow['with'] ?? []));
        }

        return $customRowWith;
    }
}
