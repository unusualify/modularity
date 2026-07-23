<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner\Streaming;

use Illuminate\Support\Facades\Cache;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;

final class InteractiveQuestionBroker
{
    public function __construct(
        private readonly int $promptTimeoutSeconds = 60,
    ) {}

    public function answerKey(string $runId, string $promptId): string
    {
        return "artisan-runner:{$runId}:answer:{$promptId}";
    }

    public function publishAnswer(string $runId, string $promptId, mixed $answer): void
    {
        Cache::put(
            $this->answerKey($runId, $promptId),
            ['answer' => $answer],
            now()->addSeconds($this->promptTimeoutSeconds + 30),
        );
    }

    /**
     * Block until an answer is published or timeout elapses.
     *
     * @throws ArtisanRunnerException
     */
    public function waitForAnswer(string $runId, string $promptId): mixed
    {
        $key = $this->answerKey($runId, $promptId);
        $deadline = microtime(true) + $this->promptTimeoutSeconds;

        while (microtime(true) < $deadline) {
            $payload = Cache::pull($key);

            if (is_array($payload) && array_key_exists('answer', $payload)) {
                return $payload['answer'];
            }

            usleep(150_000);
        }

        throw new ArtisanRunnerException("Timed out waiting for interactive prompt answer ({$promptId}).");
    }

    public function forget(string $runId, string $promptId): void
    {
        Cache::forget($this->answerKey($runId, $promptId));
    }
}
