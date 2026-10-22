<?php

namespace Unusualify\Modularous\Entities\Traits;

use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers as CoreModelHelpers;

/**
 * @deprecated since version 0.0.0, will be removed in version 1.0.0
 * Use \Unusualify\Modularous\Entities\Traits\Core\ModelHelpers instead
 *
 * This trait has been moved to Core\ModelHelpers for better organization.
 * Please update your code to use the new location:
 *
 * OLD: use \Unusualify\Modularous\Entities\Traits\ModelHelpers;
 * NEW: use \Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;
 * @see CoreModelHelpers
 *
 * @psalm-suppress DeprecatedTrait
 *
 * @phpstan-ignore-next-line
 */
trait ModelHelpers
{
    use CoreModelHelpers;
}
