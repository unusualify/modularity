<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\BaseController;

class UserController extends BaseController
{
    /**
     * @var string
     */
    protected $namespace = 'Modules\SystemUser';

    /**
     * @var string
     */
    protected $moduleName = 'SystemUser';

    /**
     * @var string
     */
    protected $routeName = 'User';

    protected $titleColumnKey = 'name';

    // protected $perPage = 2;

    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = 'User';

    public function __construct(\Illuminate\Foundation\Application $app, Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }

    public function store($parentId = null)
    {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        $input = $this->validateFormRequest()->all();

        $optionalParent = $this->nestedParentScopes();

        $item = $this->repository->create($input + $optionalParent, $this->getPreviousRouteSchema());

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = Password::broker(Modularity::getAuthProviderName())->sendResetLink(
            // $this->request->only('email'),
            ['email' => $item->email],
            function ($user, $token) {
                // dd($user, $token, Password::RESET_LINK_SENT);

                $user->sendGeneratePasswordNotification($token);

                return Password::RESET_LINK_SENT;
            }
        );

        activity()->performedOn($item)->log('created');

        Session::put($this->routeName . '_retain', true);

        if (isset($input['cmsSaveType']) && Str::endsWith($input['cmsSaveType'], '-close')) {
            return $this->respondWithRedirect($this->getBackLink());
        }

        if (isset($input['cmsSaveType']) && Str::endsWith($input['cmsSaveType'], '-new')) {
            return $this->respondWithRedirect(moduleRoute($this->routeName,
                $this->routePrefix,
                'create'
            ));
        }

        return $this->request->ajax()
            ? $this->respondWithSuccess(___('messages.save-success'))
            : $this->respondWithRedirect(moduleRoute($this->routeName,
                $this->routePrefix,
                'edit',
                [Str::singular(last(explode('.', $this->moduleName))) => $this->getItemIdentifier($item)]
            ));

    }

    public function destroy($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params);

        $item = $this->repository->getById($id);

        if ($item->isSuperAdmin()) {
            return $this->respondWithError(___('listing.delete-superadmin-error'));
        }

        if ($this->repository->delete($id)) {
            // $this->fireEvent();
            activity()->performedOn($item)->log('deleted');

            return $this->respondWithSuccess(___('listing.delete.success', ['modelTitle' => $this->modelTitle]));
            // return $this->respondWithSuccess(___("$this->baseKey::lang.listing.delete.success", ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___('listing.delete.error', ['modelTitle' => $this->modelTitle]));
        // return $this->respondWithError(modularityTrans("$this->baseKey::lang.listing.delete.error", ['modelTitle' => $this->modelTitle]));
    }
}
