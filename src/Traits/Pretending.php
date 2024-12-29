<?php

namespace Unusualify\Modularity\Traits;

trait Pretending
{
    /**
     * Indicates if the connection is in a "dry run".
     *
     * @var bool
     */
    public $pretending = false;

    /**
     * Get the pretending status.
     *
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending ?? false;
    }

    /**
     * Set the pretending status.
     *
     * @param bool $pretending
     * @return void
     */
    public function setPretending($pretending)
    {
        $this->pretending = $pretending;
    }
}
