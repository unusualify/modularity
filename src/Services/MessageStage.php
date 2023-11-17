<?php

namespace Unusualify\Modularity\Services;

// use MyCLabs\Enum\Enum;

// class MessageStage extends Enum
// {
//     const SUCCESS = 'success';
//     const ERROR = 'error';
//     const WARNING = 'caution';
//     const INFO = 'help';
// }

enum MessageStage:string {
  case SUCCESS = 'success';
  case ERROR = 'error';
  case WARNING = 'warning';
  case INFO = 'info';
}
