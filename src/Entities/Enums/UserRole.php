<?php

namespace Unusual\CRM\Base\Entities\Enums;

use MyCLabs\Enum\Enum;

class UserRole extends Enum
{
    const VIEWONLY = 'View only';
    const PUBLISHER = 'Publisher';
    const ADMIN = 'Admin';
}
