<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\Recharge;
use Hanoivip\Game\Services\GameService;
use Hanoivip\Game\Services\ScheduleService;
use Hanoivip\Game\Services\ServerService;
use Hanoivip\Game\Services\UserLogService;
use Hanoivip\PaymentClient\BalanceUtil;

class GameController extends Controller
{
    protected $games;
    
    protected $servers;
    
    protected $schedule;
    
    protected $logs;
    
    protected $balance;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GameService $games, 
        ServerService $servers, ScheduleService $schedule,
        UserLogService $logs, BalanceUtil $balance)
    {
        $this->games = $games;
        $this->servers = $servers;
        $this->schedule = $schedule;
        $this->logs = $logs;
        $this->balance = $balance;
    }

    /**
     * Vào nhanh 1 game. 
     * Server sẽ tự động được chọn là server mới nhất.
     *  
     */
	public function quickplay()
	{
		$server = $this->servers->getRecommendServer();
		return $this->play($server->name);
	}
	
	/**
	 * Hiển thị danh sách server của game tương ứng.
	 */
	public function serverlist()
	{
	    $params = [];
		$params['servers'] = $this->servers->getAll();
		$params['schedules'] = $this->schedule->getAll();
		$params['ranks'] = $this->games->rank();
		if (Auth::check())
		{
		    $params['recents'] = $this->logs->getRecentEnter(Auth::user()->getAuthIdentifier());
		}
		return view('hanoivip::serverlist', $params);
	}
	
	/**
	 * Vào 1 máy chủ của 1 game. 
	 * @param string $name Server Name
	 */
	public function play($svname)
	{
	    try 
	    {
    	    $server = $this->servers->getServerByName($svname);
    	    if (empty($server))
    	        throw new Exception("Cụm máy chủ không tồn tại.");
    		$url = $this->games->enter($server, Auth::user());
    		if (empty($url))
    		    throw new Exception("Cụm máy chủ đang bảo trì.");
    	    if (strpos($url, 'message=') !== false)
    	        return view('hanoivip::playfail', [ 'message' => substr($url, strlen('message=')) ]);
            if (strpos($url, 'uri=') !== false)
            {
                return view('hanoivip::play', [ 'playuri' => substr($url, strlen('uri=')) ]);
            }
	    }
	    catch (Exception $ex)
	    {
	        Log::error('Game play game exception. Msg:' . $ex->getMessage());
	        return view('hanoivip::playfail', [ 'error_message' => $ex->getMessage()]);
	    }
	}
	
	private function getRechargeViewData($uid)
	{
	    $all = $this->servers->getAll();
	    $servers = [];
	    foreach ($all as $s)
	        $servers[$s->name] = $s->title;
	        
        $packages = Recharge::all();
        $packs = [];
        foreach ($packages as $p)
            $packs[$p->code] = $p->title;
        
        $recents = $this->logs->getRecentEnter($uid);
        $balanceInfo = $this->balance->getInfo($uid);
        
        return [ 'servers' => $servers, 'packs' => $packs,
            'recents' => $recents, 'balances' => $balanceInfo];
	}
	
	/**
	 * Vào trang cho phép mua tiền tệ trong game.
	 */
	public function recharge()
	{
	    $uid = Auth::user()->getAuthIdentifier();
		$viewData = $this->getRechargeViewData($uid);
		return view('hanoivip::recharge', $viewData);
	}
	
	/**
	 * https://laravelcollective.com/docs/5.4/html
	 * From laravel 5.0, Html Helper is not default included.
	 * 
	 * @param string $svname
	 * @param string $package
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function doRecharge(Request $request)
	{
	    $params = $request->all();
	    //Log::debug('Post keys:' . print_r($params, true));
	    $svname = $request->input('svname');
	    $package = $request->input('package');
	    
	    $server = $this->servers->getServerByName($svname);
	    $user = Auth::user();
	    $viewData = $this->getRechargeViewData($user->getAuthIdentifier());
	    
	    try 
	    {
        	    if ($this->games->recharge($server, $user, $package, $params))
        	    {
        	        $viewData['message'] = "Chuyển xu thành công!";//TODO: use trans lang
        	        return view('hanoivip::recharge', $viewData);
        	    }
        	    else 
        	    {
        	        $viewData['error_message'] = "Chuyển xu thất bại!";
        	        return view('hanoivip::recharge', $viewData);
        	    }
	    }
	    catch (Exception $ex)
	    {
	        $viewData['error_message'] = $ex->getMessage();
	        return view('hanoivip::recharge', $viewData);
	    }
	}
}
