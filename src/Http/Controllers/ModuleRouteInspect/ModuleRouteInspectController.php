<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\ModuleRouteInspect;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectHealer;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectRemedyMapper;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;

final class ModuleRouteInspectController extends Controller
{
    public function __construct(
        private readonly ModuleRouteInspector $inspector,
        private readonly ModuleRouteInspectRemedyMapper $remedyMapper,
        private readonly ModuleRouteInspectHealer $healer,
    ) {
    }

    public function inspect(Request $request): JsonResponse
    {
        $this->authorizeUser($request);

        $validated = $request->validate([
            'module' => ['nullable', 'string'],
            'route' => ['nullable', 'string'],
            'findings_only' => ['nullable', 'boolean'],
            'feature' => ['nullable', 'string'],
        ]);

        $featureKeys = [];
        if (! empty($validated['feature'])) {
            $featureKeys = array_values(array_filter(array_map(
                static fn (string $part): string => strtolower(trim($part)),
                explode(',', (string) $validated['feature'])
            )));
        }

        try {
            $report = $this->inspector->inspect(
                $validated['module'] ?? null,
                $validated['route'] ?? null,
            )->filter(
                findingsOnly: (bool) ($validated['findings_only'] ?? false),
                featureKeys: $featureKeys,
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'entries' => $this->remedyMapper->attachToReport($report),
            'finding_count' => $report->findingCount(),
            'feature_keys' => $this->inspector->featureKeys(),
            'highlight_feature_keys' => $this->inspector->highlightFeatureKeys(),
            'allow_heal' => $this->inspector->canHeal(),
        ]);
    }

    public function setStatus(Request $request): JsonResponse
    {
        $this->authorizeUser($request);

        if (! $this->inspector->canToggleStatus()) {
            return response()->json(['message' => 'Status toggle is disabled.'], 403);
        }

        $validated = $request->validate([
            'module' => ['required', 'string'],
            'route' => ['required', 'string'],
            'enabled' => ['required', 'boolean'],
        ]);

        try {
            $this->inspector->setRouteEnabled(
                $validated['module'],
                $validated['route'],
                (bool) $validated['enabled'],
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $report = $this->inspector->inspect($validated['module'], $validated['route']);
        $entry = $report->entries[0] ?? null;

        return response()->json([
            'ok' => true,
            'entry' => $entry ? $this->remedyMapper->attachToEntry($entry) : null,
        ]);
    }

    public function heal(Request $request): JsonResponse
    {
        $this->authorizeUser($request);

        if (! $this->inspector->canHeal()) {
            return response()->json(['message' => 'Heal is disabled.'], 403);
        }

        $validated = $request->validate([
            'module' => ['required', 'string'],
            'route' => ['required', 'string'],
            'code' => ['required', 'string'],
            'feature' => ['nullable', 'string'],
            'dry_run' => ['nullable', 'boolean'],
            'force' => ['nullable', 'boolean'],
        ]);

        $dryRun = array_key_exists('dry_run', $validated)
            ? (bool) $validated['dry_run']
            : true;

        try {
            $result = $this->healer->healFinding(
                $validated['module'],
                $validated['route'],
                $validated['code'],
                $validated['feature'] ?? null,
                dryRun: $dryRun,
                allowUnsafe: (bool) ($validated['force'] ?? false),
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $report = $this->inspector->inspect($validated['module'], $validated['route']);
        $entry = $report->entries[0] ?? null;

        return response()->json([
            'ok' => $result['ok'],
            'dry_run' => $result['dry_run'],
            'exit_code' => $result['exit_code'],
            'output' => $result['output'],
            'remedy' => $result['remedy'],
            'entry' => $entry ? $this->remedyMapper->attachToEntry($entry) : null,
        ], $result['ok'] ? 200 : 422);
    }

    private function authorizeUser(Request $request): Authenticatable
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        if (! $this->inspector->userCanAccess($user)) {
            abort(403, 'Module Route Inspect is disabled or access denied.');
        }

        return $user;
    }
}
