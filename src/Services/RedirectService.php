<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

final class RedirectService
{
	public const SESSION_KEY = 'modularity.redirect_url';
	public const CACHE_KEY = 'modularity.redirect_url';

	public function set(string $url, ?int $ttlSeconds = null, bool $useCache = false): void
	{
		if ($useCache) {
			$ttl = $ttlSeconds ?? 600; // default 10 minutes
			Cache::put(self::CACHE_KEY, $url, $ttl);
			return;
		}

		Session::put(self::SESSION_KEY, $url);
	}

	public function get(): ?string
	{
		$url = Session::get(self::SESSION_KEY);
		if (is_string($url) && $url !== '') {
			return $url;
		}

		$url = Cache::get(self::CACHE_KEY);
		return is_string($url) && $url !== '' ? $url : null;
	}

	public function clear(): void
	{
		Session::forget(self::SESSION_KEY);
		Cache::forget(self::CACHE_KEY);
	}

	public function pull(): ?string
	{
		$url = $this->get();

        if ($url !== null) {
			$this->clear();
		}

        return $url;
	}
}
