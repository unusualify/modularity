<?php

namespace OoBook\CRM\Base\Services;

use MyCLabs\Enum\Enum;

class MessageStage extends Enum
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'caution';
    const INFO = 'help';
}
