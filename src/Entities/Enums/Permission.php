<?php

namespace Unusualify\Modularous\Entities\Enums;

use Illuminate\Support\Str;

enum Permission: string
{
    // case DASHBOARD = 'dashboard';
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
    case REVISION_APPROVE = 'revisionApprove';
    case REVISION_REJECT = 'revisionReject';
    case REVISION_RESTORE = 'revisionRestore';

    case ACTIVITY = 'activity';
    case SHOW = 'show';

    public static function get($caseName)
    {
        foreach (self::cases() as $case) {
            if ($case->name == $caseName) {
                return $case->value;
            }
        }

        return null;
    }

    public static function generatePermissionName($permission, $routeName)
    {
        return Str::kebab($routeName) . '_' . self::get($permission);
    }

    public static function generatePermissionMiddlewareDefinition($permission, $routeName)
    {
        return 'can:' . self::generatePermissionName($permission, $routeName);
    }
}
