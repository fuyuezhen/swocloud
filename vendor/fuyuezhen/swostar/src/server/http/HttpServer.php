<?php
namespace swostar\server\http;

use swostar\server\Server;
use swostar\rpc\Rpc;
use swostar\message\http\Request as HttpRequest;
use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * http服务类
 */
class HttpServer extends Server
{
    
    /**
     * 创建服务
     *
     * @return void
     */
    protected function createServer(){
        $this->swooleServer = new SwooleServer($this->host, $this->port);

        info("启动HTTP监听：" . $this->host . ":" . $this->port);
    }

    /**
     * 设置子类回调事件
     * @return void
     */
    protected function setSubEvent()
    {
        $this->event['sub'] = [
            'request' => 'onRequest'
        ];
    }

    /**
     * 开启RPC
     */
    public function addRPC()
    {
        $config = app('config');
        if (!empty($this->tcpable)) {
            new Rpc($this->swooleServer, $config->get('server.http.rpc'));
        }
    }

    /**
     * 初始化设置
     * @return void
     */
    protected function initSetting()
    {
        $config     = app('config');
        $this->tcpable = $config->get('server.http.tcpable');
        $this->host = $config->get('server.http.host');
        $this->port = $config->get('server.http.port');
        $this->confing = $config->get('server.http.swoole');
    }

    /**
     * 在收到一个完整的 HTTP 请求后，会回调此函数
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function onRequest(Request $swooleRequest, Response $swooleResponse) {

        if($swooleRequest->server['request_uri'] == '/favicon.ico'){
            $swooleResponse->end('404');
            return null;
        }

        info("有HTTP新的请求进入~");

        $request = HttpRequest::init($swooleRequest);
        $swooleResponse->end( app('route')->setFlag('http')->setMethod($request->getMethod())->match($request->getUriPath()));
    }


}