<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    /**
     * Max number of recently impersonated users tracked in the session.
     */
    protected const RECENT_IMPERSONATIONS_LIMIT = 5;

    /**
     * Session key holding the recent impersonation id stack (newest first).
     */
    protected const RECENT_IMPERSONATIONS_KEY = 'impersonate_recent';

    public function __construct(protected AuthManager $authManager)
    {
        parent::__construct();
    }

    /**
     * Start impersonating the given user on behalf of the real (impersonator) user.
     */
    public function impersonate(Request $request, int|string $id, UserRepository $users): RedirectResponse|JsonResponse
    {
        $actor = $this->resolveActor($request);

        abort_unless($actor?->can('impersonate'), 403);

        $user = $users->getById($id);
        $actor->setImpersonating($user->id);

        $this->pushRecentImpersonation((int) $user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'impersonating' => true,
                'user_id' => (int) $user->id,
            ]);
        }

        return back();
    }

    /**
     * Stop the active impersonation and return to the real user.
     */
    public function stopImpersonate(Request $request): RedirectResponse|JsonResponse
    {
        $this->resolveActor($request)?->stopImpersonating();

        if ($request->expectsJson()) {
            return response()->json(['impersonating' => false]);
        }

        return back();
    }

    /**
     * The real user behind the request: while impersonating, the guard resolves the
     * impersonated user, so the impersonator stashed by ImpersonateMiddleware wins.
     */
    protected function resolveActor(Request $request): mixed
    {
        return $request->attributes->get('impersonator')
            ?? $this->authManager->guard(Modularous::getAuthGuardName())->user();
    }

    /**
     * Prepend the just-impersonated user id onto the recent stack, dedupe, cap.
     */
    protected function pushRecentImpersonation(int $id): void
    {
        $recent = \array_values(\array_filter(
            (array) Session::get(self::RECENT_IMPERSONATIONS_KEY, []),
            fn ($value) => \is_numeric($value) && (int) $value !== $id
        ));

        \array_unshift($recent, $id);

        Session::put(
            self::RECENT_IMPERSONATIONS_KEY,
            \array_slice($recent, 0, self::RECENT_IMPERSONATIONS_LIMIT)
        );
    }
}
