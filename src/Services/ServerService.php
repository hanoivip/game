<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Server;
use Exception;

class ServerService
{
    /**
     * Lấy 1 server khuyến cáo, có thể:
     * + Server mới nhất
     * + Server đông nhất
     * + Server có điểm KC cao nhất
     */
    public function getRecommendServer()
    {
        $server = Server::where('is_recommend', true)
        ->orderBy('id', 'desc')
        ->first();
        return $server;
    }
    /**
     * Lấy danh sách máy chủ dành cho người dùng
     * - Máy chủ hoạt động
     * - Máy chủ hiển thị
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getUserServer()
    {
        $servers = Server::where('is_show', true)
        ->orderBy('id', 'desc')
        ->get();
        return $servers;
    }
    
    public function getAll()
    {
        $servers = Server::orderBy('id', 'desc')->get();
        return $servers;
    }
    
    public function getServerByName($svname)
    {
        $server = Server::where('name', $svname)->first();
        return $server;
    }
    
    public function addNew($params)
    {
        $server = new Server();
        $server->name = $params['name'];
        $server->ident = $params['ident'];
        $server->title = $params['title'];
        $server->description = $params['description'];
        $server->login_uri = $params['login_uri'];
        $server->recharge_uri = $params['recharge_uri'];
        $server->operate_uri = $params['operate_uri'];
        $server->save();    
    }
    
    public function removeByIdent($ident)
    {
        $server = Server::where('ident', $ident)->get();
        if (!$server->isEmpty())
        {
            $server->first()->delete();
            return true;
        }
        throw new Exception('Server ident ' . $ident . ' not found!');
    }
    
    public function scheduleNew()
    {
        
    }
}