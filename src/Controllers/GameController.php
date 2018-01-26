<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
		if (empty($server))
		    abort(500, "Game in maintainance");
		$url = $this->games->enter($server, Auth::user());
		if (empty($url))
		    abort(500, "Server in maintainance");
		if (strpos($url, 'message=') !== false)
		    return view('hanoivip::playfail', substr($url, strlen('message=')));
		if (strpos($url, 'uri=') !== false)
		    return redirect(substr($url, strlen('uri=')));
		
		abort(500);
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
		if (Auth::guard('token')->check())
		{
		    $params['recents'] = $this->logs->getRecentEnter(Auth::guard('token')->user()['id']);
		}
		return view('hanoivip::serverlist', $params);
	}
	
	/**
	 * Vào 1 máy chủ của 1 game. 
	 * @param string $name Server Name
	 */
	public function play($svname)
	{
	    $server = $this->servers->getServerByName($svname);
	    if (empty($server))
		    abort(500, "Server bogus");
		$url = $this->games->enter($server, Auth::user());
		if (empty($url))
		    abort(500, "Server in maintainance");
	    if (strpos($url, 'message=') !== false)
	        return view('hanoivip::playfail', substr($url, strlen('message=')));
        if (strpos($url, 'uri=') !== false)
            return redirect(substr($url, strlen('uri=')));
            
        abort(500);
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
		$uid = Auth::guard('token')->user()['id'];
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
	    $svname = $request->input('svname');
	    $package = $request->input('package');
	    
	    $server = $this->servers->getServerByName($svname);
	    $uid = Auth::user()['id'];
	    if ($this->games->recharge($server, $uid, $package))
	    {
	        $viewData = $this->getRechargeViewData($uid);
	        $viewData['message'] = "Chuyển xu thành công!";//TODO: use trans lang
	        return view('hanoivip::recharge', $viewData);
	        //return view('hanoivip::recharge_success');
	    }
	    else 
	    {
	        $viewData = $this->getRechargeViewData($uid);
	        $viewData['message'] = "Chuyển xu thất bại!";
	        return view('hanoivip::recharge', $viewData);
	        //return view('hanoivip::recharge_fail');
	    }
	}
}
