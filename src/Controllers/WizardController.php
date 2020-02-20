<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;

class WizardController extends Controller
{
    public function chooseRole(Request $request)
    {
        $next = $request->get('next');
        return view('hanoivip::wizard-role', ['next' => $next]);
    }
    
    public function continue(Request $request)
    {
        $svname = $request->get('svname');
        $roleId = $request->get('role');
        $next = $request->get('next');
        return redirect()->route($next, ['svname' => $svname, 'role' => $roleId]);
        //return redirect($nextUrl)->withInput();
    }
}
