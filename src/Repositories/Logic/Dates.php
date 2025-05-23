<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Carbon\Carbon;

trait Dates
{
    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateDates($fields)
    {
        return $this->prepareFieldsBeforeSaveDates(null, $fields);
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveDates($object, $fields)
    {
        foreach ($this->model->getDates() as $f) {
            if (isset($fields[$f])) {
                if (! empty($fields[$f])) {
                    $fields = $this->prepareDatesField($fields, $f);
                } else {
                    $fields[$f] = null;
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param string $f
     * @return array
     */
    public function prepareDatesField($fields, $f)
    {
        if ($date = Carbon::parse($fields[$f])) {
            $fields[$f] = $date->format('Y-m-d H:i:s');
        } else {
            $fields[$f] = null;
        }

        return $fields;
    }
}
