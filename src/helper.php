<?php 
use Hanoivip\Game\Facades\ServerFacade;

if (! function_exists('wizard_roles')) 
{
    function wizard_roles($nextRoute)
    {
        return view('hanoivip::wizard-role', ['next' => $nextRoute]);
    }
}

if (! function_exists('show_user_servers'))
{
    function show_user_servers()
    {
        $servers = ServerFacade::getUserServer();
        return view('hanoivip::serverlist-partial', ['servers' => $servers]);
    }
}
