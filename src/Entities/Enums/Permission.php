<?php

namespace Unusualify\Modularity\Entities\Enums;

enum Permission: string
{
    //case DASHBOARD = 'dashboard';
    case CREATE = 'create';
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case FORCEDELETE = 'forceDelete';
    case RESTORE = 'restore';
    case DUPLICATE = 'duplicate';
    case REORDER = 'reorder';
    case BULK = 'bulk';
    case BULKDELETE = 'bulkDelete';
    case BULKFORCEDELETE = 'bulkForceDelete';
    case BULKRESTORE = 'bulkRestore';

    public static function get($caseName)
    {
        foreach (self::cases() as $case) {
            if ($case->name == $caseName) {
                return $case->value;
            }
        }

        return null;
    }
}
