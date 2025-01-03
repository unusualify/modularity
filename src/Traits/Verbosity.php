<?php

namespace Unusualify\Modularity\Traits;

use Symfony\Component\Console\Output\OutputInterface;

trait Verbosity
{
    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    public function setVerbosity($verbosity)
    {
        if (isset($this->verbosityMap[$verbosity])) {
            $verbosity = $this->verbosityMap[$verbosity];
        } elseif (! is_int($verbosity)) {
            $verbosity = $this->verbosity;
        } elseif (is_int($verbosity)) {
            $verbosity = $verbosity;
        }

        $this->verbosity = $verbosity;

        return $this;
    }

    public function getVerbosity()
    {
        return $this->verbosity;
    }

    public function isQuiet()
    {
        return $this->verbosity === OutputInterface::VERBOSITY_QUIET;
    }

    public function isVerbose()
    {
        return $this->verbosity >= OutputInterface::VERBOSITY_VERBOSE;
    }

    public function isVeryVerbose()
    {
        return $this->verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    public function isDebug()
    {
        return $this->verbosity >= OutputInterface::VERBOSITY_DEBUG;
    }
}
