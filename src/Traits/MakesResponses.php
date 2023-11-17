<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Unusualify\Modularity\Services\MessageStage;

trait MakesResponses {

    /**
     * @param string|null $back_link
     * @param array $params
     * @return void
     */
    protected function setBackLink($back_link = null, $params = [])
    {
        if (! isset($back_link)) {
            if (($back_link = Session::get($this->getBackLinkSessionKey())) == null) {
                $back_link = $this->request->headers->get('referer') ?? moduleRoute(
                    $this->moduleName,
                    $this->routePrefix,
                    'index',
                    $params
                );
            }
        }

        if (! Session::get($this->moduleName . '_retain')) {
            Session::put($this->getBackLinkSessionKey(), $back_link);
        } else {
            Session::put($this->moduleName . '_retain', false);
        }
    }

    /**
     * @param string|null $fallback
     * @param array $params
     * @return string
     */
    protected function getBackLink($fallback = null, $params = [])
    {
        $back_link = Session::get($this->getBackLinkSessionKey(), $fallback);

        return $back_link ?? moduleRoute($this->moduleName, $this->routePrefix, 'index', $params);
    }

    /**
     * @return string
     */
    protected function getBackLinkSessionKey()
    {
        return $this->moduleName . ($this->isNested ? $this->submoduleParentId ?? '' : '') . '_back_link';
        return $this->moduleName . ($this->submodule ? $this->submoduleParentId ?? '' : '') . '_back_link';
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToForm($id, $params = [])
    {
        Session::put($this->moduleName . '_retain', true);
        dd(
            $this->moduleName,
            $this->routePrefix,
            'edit',
            array_filter($params) + [Str::singular($this->moduleName) => $id],
            debug_backtrace()
        );
        return Redirect::to(moduleRoute(
            $this->moduleName,
            $this->routePrefix,
            'edit',
            array_filter($params) + [Str::singular($this->moduleName) => $id]
        ));
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithSuccess($message)
    {
        return $this->respondWithJson($message, MessageStage::SUCCESS);
    }

    /**
     * @param string $redirectUrl
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithRedirect($redirectUrl)
    {
        return Response::json([
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message)
    {
        return $this->respondWithJson($message, MessageStage::ERROR);
    }

    /**
     * @param string $message
     * @param mixed $variant
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithJson($message, $variant)
    {
        return Response::json([
            'message' => $message,
            'variant' => $variant,
        ]);
    }

}
