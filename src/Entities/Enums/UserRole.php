<?php

namespace Unusualify\Modularity\Entities\Enums;

enum UserRole:string {
    case SUPERADMIN = 'Superadmin';
    case ADMIN = 'Admin';
    case PUBLISHER = 'Publisher';
    case VIEWONLY = 'View Only';
}
// if ( version_compare(PHP_VERSION, '8.1.0', '<') ) {
//   use MyCLabs\Enum\Enum;
//   class UserRole extends Enum
//   {
//       const VIEWONLY = 'View only';
//       const PUBLISHER = 'Publisher';
//       const ADMIN = 'Admin';
//   }
// }else {
//   enum UserRole:string {
//     case SUPERADMIN = 'Superadmin';
//     case ADMIN = 'Admin';
//     case PUBLISHER = 'Publisher';
//     case VIEWONLY = 'View Only';
//   }

// }
// if(phpversion())
// use MyCLabs\Enum\Enum;

