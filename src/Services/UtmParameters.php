<?php

namespace Unusualify\Modularous\Services;

use Illuminate\Http\Request;
use Unusualify\Payable\Services\Paypal\Str;

class UtmParameters
{
    /**
     * Enable UTM parameters service
     */
    protected readonly bool $isEnabled;

    /**
     * Keep data between requests
     */
    protected readonly bool $persisted;

    /**
     * Request instance
     */
    protected readonly Request $request;

    /**
     * Should handle request on
     */
    protected readonly bool $shouldHandleRequest;

    /**
     * Request handled
     */
    private bool $requestHandled = false;

    protected array $parameters = [
        'utm_source' => null,
        'utm_medium' => null,
        'utm_campaign' => null,
        'utm_term' => null,
        'utm_content' => null,
    ];

    protected static array $arguments = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    public function __construct(Request $request)
    {
        $this->isEnabled = ! (bool) env('MODULAROUS_UTM_DISABLED', false);

        $this->persisted = ! (bool) env('MODULAROUS_UTM_TEMPORARY', false);

        $this->shouldHandleRequest = (bool) env('MODULAROUS_UTM_HANDLE_REQUEST', false);

        $this->request = $request;

        if (! $this->persisted) {
            $this->resetParameters();
        }

        if ($this->shouldHandleRequest) {
            $this->handleRequest();
        }

        if (! $this->isRequestHandled()) {
            $this->loadParameters();
        }
    }

    /**
     * Check if UTM parameters service is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Check if UTM parameters are persisted
     *
     * @return bool
     */
    public function isPersisted()
    {
        return $this->persisted;
    }

    /**
     * Set UTM parameters
     *
     * @param array $data
     * @return void
     */
    public function setParameters($data = [])
    {
        if (! $this->isEnabled()) {
            return;
        }

        session()->put('utm_parameters', array_merge([
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_term' => null,
            'utm_content' => null,
        ], $data));

        $this->loadParameters();
    }

    public function handleRequest()
    {
        if (! $this->isEnabled() || ! $this->shouldHandleRequest || $this->requestHandled) {
            return;
        }

        $parameters = $this->request->all();

        $parameters = array_filter($parameters, function ($value, $key) {
            return in_array($key, self::$arguments);
        }, ARRAY_FILTER_USE_BOTH);

        if (! empty($parameters)) {
            if ($this->isPersisted()) {
                $this->mergeParameters($parameters);
            } else {
                $this->setParameters($parameters);
            }

            $this->loadParameters();

            $this->requestHandled = true;
        }
    }

    /**
     * Get UTM parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Reset UTM parameters
     *
     * @return void
     */
    public function resetParameters()
    {
        session()->forget('utm_parameters');

        $this->loadParameters();
    }

    public function isRequestHandled()
    {
        return $this->requestHandled;
    }

    /**
     * Merge UTM parameters
     *
     * @param array $data
     */
    public function mergeParameters($data = [])
    {
        if (! $this->isEnabled()) {
            return;
        }

        foreach (self::$arguments as $argument) {
            if (isset($data[$argument])) {
                session()->put('utm_parameters.' . $argument, $data[$argument]);
            }
        }

        $this->loadParameters();

        return $this;
    }

    /**
     * Load UTM parameters from session
     *
     * @return void
     */
    protected function loadParameters()
    {
        foreach (self::$arguments as $argument) {
            $this->parameters[$argument] = session()->get('utm_parameters.' . $argument, null);
        }
    }

    public function __get($name)
    {
        if (in_array($name, self::$arguments)) {
            return $this->parameters[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        if (in_array($name, self::$arguments)) {
            $this->parameters[$name] = $value;
        }
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/^get(.*)Parameter$/', $name, $matches)) {
            $argument = Str::snake($matches[1]);
            if (in_array($argument, self::$arguments)) {
                return $this->parameters[$argument];
            }
        }

        return null;
    }

    /**
     * Convert UTM parameters to array
     *
     * @return array
     */
    public function __serialize()
    {
        return $this->parameters;
    }

    /**
     * Convert UTM parameters to string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->__toArray());
    }
}
