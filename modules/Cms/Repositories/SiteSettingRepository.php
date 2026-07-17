<?php

declare(strict_types=1);

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\SiteSetting;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Repositories\Traits\RepeatersTrait;

class SiteSettingRepository extends Repository
{
    use ImagesTrait, RepeatersTrait;

    public function __construct(SiteSetting $model)
    {
        $this->model = $model;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @param  array<string, mixed>  $fields
     */
    public function afterSave($object, $fields): void
    {
        parent::afterSave($object, $fields);

        if (app()->bound('cms.settings')) {
            app('cms.settings')->forgetCache();
        }

        if (app()->bound('site.settings')) {
            app('site.settings')->forgetCache();
        }
    }
}
