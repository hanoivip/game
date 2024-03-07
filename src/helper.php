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


if (! function_exists('show_all_servers'))
{
    function show_all_servers()
    {
        $servers = ServerFacade::getAll();
        return view('hanoivip::serverlist-partial', ['servers' => $servers]);
    }
}
