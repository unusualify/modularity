<?php

namespace Modules\SystemNotification\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Unusualify\Modularity\Http\Controllers\BaseController;

class MyNotificationController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'SystemNotification';

    /**
     * @var string
     */
    protected $routeName = 'MyNotification';

    public function show($id, $submoduleId = null)
    {
        $notification = $this->repository->findOrFail($id);

        if ($notification->is_mine) {
            $notification->markAsRead();
        }

        if ($this->request->has('redirector') && $this->request->get('redirector') == true) {
            if ($notification->hasRedirector) {
                return redirect($notification->redirector);
            }
        }

        if (! $notification->is_mine) {
            abort(403, 'Unauthorized action.');
        }

        return parent::show($id, $submoduleId);
    }

    public function index_($parentId = null)
    {
        dd($this->repository->getModel()->get());
    }

    public function markReadMyNotifications()
    {
        $this->repository->getModel()->myNotification()->get()->markAsRead();

        if ($this->request->ajax()) {
            return $this->respondWithSuccess(Lang::get('messages.notifications.mark-read-success'));
        }

        return redirect()->back()->with('success', __('messages.notifications.mark-read-success'));
    }
}
