<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hanoivip\Game\Services\GameService;
use Hanoivip\Payment\Facades\BalanceFacade;
use Hanoivip\Game\Services\ServerService;
use Hanoivip\Game\Facades\GameHelper;
use Hanoivip\Events\Server\ServerCreated;

class AdminController extends Controller
{
    protected $game;
    
    protected $server;
    
    public function __construct(GameService $game, ServerService $server)
    {
        $this->game = $game;
        $this->server = $server;
    }
    // 
    public function recharge(Request $request)
    {
        $tid = Auth::user()->getAuthIdentifier();
        $servers = $this->server->getAll();
        $packages = $this->game->getRechargePackages();
        $selected = $servers->first();
        if ($request->has('svname'))
            $selected = $request->input('svname');
        $roles = GameHelper::getRoles($selected, $tid);
        $data = [ 'servers' => $servers, 'packs' => $packages,
            'roles' => $roles, 'tid' => $tid, 'selected' => $selected];
        return view('hanoivip::admin.recharge', $data);
    }
    
    public function recharge4other(Request $request)
    {
        $tid = $request->input('tid');
        $servers = $this->server->getAll();
        $packages = $this->game->getRechargePackages();
        $selected = $servers->first();
        if ($request->has('svname'))
            $selected = $request->input('svname');
        $roles = GameHelper::getRoles($selected, $tid);
        $data = [ 'servers' => $servers, 'packs' => $packages,
            'roles' => $roles, 'tid' => $tid, 'selected' => $selected];
        return view('hanoivip::admin.recharge', $data);
    }
    
    public function doRecharge(Request $request)
    {
        $tid = $request->input('tid');
        $server = $request->input('svname');
        $package = $request->input('package');
        $roleid = $request->input('roleid');
        $uid = Auth::user()->getAuthIdentifier();
        $result = GameHelper::recharge($uid, $server, $package, $roleid, $tid);
        if ($result === true)
            return view('hanoivip::admin.recharge-success');
        else
            return view('hanoivip::admin.recharge-fail', ['error' => $result]);
    }
    
    public function serverInfo()
    {
        $all = $this->server->getAll();
        return view('hanoivip::admin.server-info', ['servers' => $all]);
    }
    
    public function addServer(Request $request)
    {
        $message = '';
        $error_message = '';
        try
        {
            $params = $request->all();
            //unset($params['_token']);
            $record = $this->server->addNew($params);
            event(new ServerCreated($record));
            $message = __('hanoivip.admin::admin.user.add-server.success');
        }
        catch (Exception $ex)
        {
            Log::error('Admin add server exception: ' . $ex->getMessage());
            $error_message = __('hanoivip.admin::admin.user.add-server.exception');
        }
        return view('hanoivip::admin.server-result',
            ['message' => $message, 'error_message' => $error_message]);
    }
    
    public function removeServer(Request $request)
    {
        $ident = $request->input('ident');
        $message = '';
        $error_message = '';
        try
        {
            $this->server->removeByIdent($ident);
            $message = __('hanoivip.admin::admin.user.remove-server.success');
        }
        catch (Exception $ex)
        {
            Log::error('Admin remove server exception: ' . $ex->getMessage());
            $error_message = __('hanoivip.admin::admin.user.remove-server.exception');
        }
        return view('hanoivip::admin.server-result',
            ['message' => $message, 'error_message' => $error_message]);
    }
}
