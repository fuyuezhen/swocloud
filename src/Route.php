<?php
namespace swocloud;

use Swoole\WebSocket\Server as SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use \Redis;

/**
 * 这是一个WebSocket
 * 路由层，用于服务注册与发现
 */
class Route extends Server
{
    /**
     * 服务器选择算法
     *
     * @var string
     */
    protected $arithmetic = "round";

    /**
     * redis set类型 key名称
     *
     * @var string
     */
    protected $serverKey = "im_server";

    /**
     * Redis连接资源对象
     *
     * @var [type]
     */
    protected $redis;

    /**
     * 分发对象实例
     *
     * @var [type]
     */
    protected $dispatcher;

    /**
     * 创建服务
     *
     * @return void
     */
    protected function createServer(){
        $this->swooleServer = new SwooleServer($this->host, $this->port);

        info("启动WebSockte监听：" . $this->host . ":" . $this->port);
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
     * 此事件在 Worker 进程 / Task 进程 启动时发生，这里创建的对象可以在进程生命周期内使用。
     *
     * @param SwooleServer $server
     * @param integer $workerId   Worker 进程 id（非进程的 PID）
     * @return void
     */
    public function onWorkerStart($server, int $workerId)
    {
        $this->redis = new Redis;
        $this->redis->pconnect("192.168.218.30", 6379);
    }

    /**
     * 当 WebSocket 客户端与服务器建立连接并完成握手后会回调此函数。
     *
     * @param SwooleServer $server
     * @param $request 是一个 HTTP 请求对象，包含了客户端发来的握手请求信息
     * @return void
     */
    public function onOpen(SwooleServer $server, $request) {
        info("onOpen");
    }
    
    /**
     * TCP 客户端连接关闭后，在 Worker 进程中回调此函数。
     *
     * @param SwooleServer $server
     * @param integer $fd           连接的文件描述符，相当于请求连接的ID
     * @return void
     */
    public function onClose($server, int $fd, int $reactorId) {
        info("onClose");
    }
    
    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     *
     * @param SwooleServer $server
     * @param $frame 是 Swoole\WebSocket\Frame 对象，包含了客户端发来的数据帧信息
     * @return void
     */
    public function onMessage(SwooleServer $server, $frame) {
        $data = json_decode($frame->data, true);
        $this->getDispatcher()->{$data['method']}($this, $server, $frame->fd, $data);
    }

    /**
     * 在收到一个完整的 HTTP 请求后，会回调此函数
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function onRequest(Request $swooleRequest, Response $swooleResponse) {
        if ($swooleRequest->server['request_uri'] ==  "/favicon.ico") {
            $swooleResponse->end("404");
            return null;
        }
        
        // 设置跨域
        $swooleResponse->header("Access-Control-Allow-Origin", "*");
        $swooleResponse->header("Access-Control-Allow-Methods", 'GET,POST,OPTIONS');

        // 根据方法类型分发处理业务
        $this->getDispatcher()->{$swooleRequest->post['method']}($this, $swooleRequest, $swooleResponse);
    }

    /**
     * 单例分发类
     *
     * @return void
     */
    public function getDispatcher()
    {
        if (empty($this->dispatcher)) {
            $this->dispatcher = new Dispatcher;
        }
        return $this->dispatcher;
    }

    /**
     * 获取服务器选择算法
     *
     * @return void
     */
    public function getArithmetic()
    {
        return $this->arithmetic;
    }

    /**
     * 设置服务器选择算法
     *
     * @return void
     */
    public function setArithmetic($arithmetic)
    {
        $this->arithmetic = $arithmetic;
        return $this;
    }

    /**
     * 获取Redis连接资源对象
     *
     * @return void
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * 获取serverKey
     *
     * @return void
     */
    public function getServerKey()
    {
        return $this->serverKey;
    }

    /**
     * 获取所有的连接服务器集合
     * @return void
     */
    public function getIMServers()
    {
        return $this->getRedis()->smembers($this->getServerKey());
    }
}