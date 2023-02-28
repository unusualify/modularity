<?php

namespace Unusual\CRM\Base\Repositories\Traits;


trait TreeviewTrait
{


    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateTreeviewTrait($fields)
    {
        return $this->prepareFieldsBeforeSaveTreeviewTrait(null, $fields);
    }

    /**
     * @param \Unusual\CRM\Base\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveTreeviewTrait($object, $fields)
    {
        dd(
            $object,
            $fields
        );
        foreach ($this->model->getDates() as $f) {
            if (isset($fields[$f])) {
                if (!empty($fields[$f])) {
                    $fields = $this->prepareTreeviewField($fields, $f);
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
    public function prepareTreeviewField($fields, $f)
    {
        if ($date = Carbon::parse($fields[$f])) {
            $fields[$f] = $date->format("Y-m-d H:i:s");
        } else {
            $fields[$f] = null;
        }

        return $fields;
    }
}
