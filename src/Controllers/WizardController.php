<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hanoivip\Game\Services\GameService;

class WizardController extends Controller
{
    protected $service;
    
    public function __construct(GameService $service)
    {
        $this->service = $service;
    }
    /**
     * Display selected role, if any
     * 
     */
    public function chooseRole(Request $request)
    {
        $userId = Auth::user()->getAuthIdentifier();
        $current = $this->service->getUserDefaultRole($userId);
        $next = $request->get('next');
        return view('hanoivip::wizard-role', ['next' => $next, 'current' => $current]);
    }
    
    public function saveDefaultRole(Request $request)
    {
        $userId = Auth::user()->getAuthIdentifier();
        $svname = $request->get('svname');
        $roleId = $request->get('role');
        $result = $this->service->saveUserDefaultRole($userId, $svname, $roleId);
        return view('hanoivip::wizard-save-role',['result' => $result]);
    }
    /**
     * @deprecated
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function continue(Request $request)
    {
        $useSaved = $request->get('use-saved-role');
        if (empty($useSaved))
        {
            $svname = $request->get('svname');
            $roleId = $request->get('role');
        }
        else
        {
            $userId = Auth::user()->getAuthIdentifier();
            $current = $this->service->getUserDefaultRole($userId);
            $svname = $current->server;
            $roleId = $current->role;
        }
        $next = $request->get('next');
        return redirect()->route($next, ['svname' => $svname, 'role' => $roleId]);
    }
}
