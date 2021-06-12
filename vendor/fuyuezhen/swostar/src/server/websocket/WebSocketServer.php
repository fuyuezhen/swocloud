<?php
namespace swostar\server\websocket;

use swostar\server\Server;
use swostar\server\http\HttpServer;
use Swoole\WebSocket\Server as SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * websocket服务类
 */
class WebSocketServer extends HttpServer
{
    
    /**
     * 创建服务
     *
     * @return void
     */
    protected function createServer(){
        $this->swooleServer = new SwooleServer($this->host, $this->port);

        info("启动WebSocket监听：" . $this->host . ":" . $this->port);
    }

    /**
     * 设置子类回调事件
     * @return void
     */
    protected function setSubEvent()
    {
        $this->event['sub'] = [
            'request' => 'onRequest',
            'open'    => 'onOpen',
            'message' => 'onMessage',
            'close'   => 'onClose',
        ];
    }

    /**
     * 初始化设置
     * @return void
     */
    protected function initSetting()
    {
        $config     = app('config');
        $this->tcpable = $config->get('server.ws.tcpable');
        $this->host = $config->get('server.ws.host');
        $this->port = $config->get('server.ws.port');
        $this->confing = $config->get('server.ws.swoole');
    }

    /**
     * 当 WebSocket 客户端与服务器建立连接并完成握手后会回调此函数。
     *
     * @param SwooleServer $server
     * @param $request 是一个 HTTP 请求对象，包含了客户端发来的握手请求信息
     * @return void
     */
    public function onOpen(SwooleServer $server, $request) {
        app("route")->setFlag('web_socket')->setMethod("open")->match($request->server['path_info'], [$server, $request]);
        
        Connections::init($request->fd, $request->server['path_info']);
    }
    
    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     *
     * @param SwooleServer $server
     * @param $frame 是 Swoole\WebSocket\Frame 对象，包含了客户端发来的数据帧信息
     * @return void
     */
    public function onMessage(SwooleServer $server, $frame) {
        app("route")->setFlag('web_socket')->setMethod("message")->match(Connections::get($frame->fd), [$server, $frame]);
    }
    
    /**
     * TCP 客户端连接关闭后，在 Worker 进程中回调此函数。
     *
     * @param SwooleServer $server
     * @param integer $fd           连接的文件描述符，相当于请求连接的ID
     * @return void
     */
    public function onClose($server, int $fd, int $reactorId) {
        app("route")->setFlag('web_socket')->setMethod("close")->match(Connections::get($fd), [$server, $fd, $reactorId]);
        Connections::del($fd);
    }

}