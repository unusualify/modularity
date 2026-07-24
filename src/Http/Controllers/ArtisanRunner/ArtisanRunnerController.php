<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\ArtisanRunner;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\CommandNotAllowedException;

final class ArtisanRunnerController extends Controller
{
    public function __construct(
        private readonly ArtisanRunnerInterface $artisanRunner,
    ) {}

    public function commands(Request $request): JsonResponse
    {
        $user = $this->authorizeUser($request);

        return response()->json([
            'commands' => $this->artisanRunner->catalogForUser($user),
        ]);
    }

    public function definition(Request $request, string $name): JsonResponse
    {
        $user = $this->authorizeUser($request);
        $command = rawurldecode($name);

        try {
            return response()->json(
                $this->artisanRunner->definitionForUser($user, $command)
            );
        } catch (CommandNotAllowedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (ArtisanRunnerException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function run(Request $request): StreamedResponse|JsonResponse
    {
        $user = $this->authorizeUser($request);

        $validated = $request->validate([
            'command' => ['required', 'string'],
            'arguments' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
        ]);

        $command = $validated['command'];
        $arguments = $validated['arguments'] ?? [];
        $options = $validated['options'] ?? [];
        $runId = (string) Str::uuid();

        try {
            $this->artisanRunner->assertCommandAllowed($user, $command);
        } catch (CommandNotAllowedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (ArtisanRunnerException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->stream(function () use ($user, $command, $arguments, $options, $runId): void {
            $emit = function (string $event, array $payload): void {
                echo 'event: ' . $event . "\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                @flush();
            };

            // Disable output buffering for live streaming.
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }

            $this->artisanRunner->run(
                $user,
                $command,
                $arguments,
                $options,
                $runId,
                $emit,
            );
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    public function answer(Request $request, string $runId): JsonResponse
    {
        $this->authorizeUser($request);

        $validated = $request->validate([
            'prompt_id' => ['required', 'string'],
            'answer' => ['nullable'],
        ]);

        $this->artisanRunner->answerPrompt(
            $runId,
            $validated['prompt_id'],
            $validated['answer'] ?? null,
        );

        return response()->json(['ok' => true]);
    }

    private function authorizeUser(Request $request): Authenticatable
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        if (! $this->artisanRunner->userCanAccess($user)) {
            abort(403, 'Artisan Runner is disabled or access denied.');
        }

        return $user;
    }
}
