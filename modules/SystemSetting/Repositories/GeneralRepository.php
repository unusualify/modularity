<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Modules\SystemSetting\Entities\General;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Repositories\Traits\RepeatersTrait;

class GeneralRepository extends Repository
{
    use ImagesTrait, RepeatersTrait;

    public function __construct(General $model)
    {
        $this->model = $model;
    }

    public function prepareFieldsBeforeCreate($fields)
    {
        $fields = $this->encryptSmtpPassword($fields);

        return parent::prepareFieldsBeforeCreate($fields);
    }

    /**
     * @param Model $object
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    public function prepareFieldsBeforeSave($object, $fields)
    {
        $fields = $this->encryptSmtpPassword($fields, $object);

        return parent::prepareFieldsBeforeSave($object, $fields);
    }

    /**
     * @param Model $object
     * @param array<string, mixed> $fields
     */
    public function afterSave($object, $fields): void
    {
        parent::afterSave($object, $fields);

        if (app()->bound('system.settings')) {
            app('system.settings')->forgetCache();
        }

        if (app()->bound('site.settings')) {
            app('site.settings')->forgetCache();
        }
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    protected function encryptSmtpPassword(array $fields, mixed $object = null): array
    {
        $password = Arr::get($fields, 'smtp.password');

        if ($password === null) {
            return $fields;
        }

        if ($password === '') {
            if ($object !== null) {
                $existing = is_array($object->smtp ?? null) ? $object->smtp : [];
                Arr::set($fields, 'smtp.password', $existing['password'] ?? null);
            }

            return $fields;
        }

        if (is_string($password) && ! $this->alreadyEncrypted($password)) {
            Arr::set($fields, 'smtp.password', encrypt($password, false));
        }

        return $fields;
    }

    protected function alreadyEncrypted(string $value): bool
    {
        try {
            decrypt($value, false);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
