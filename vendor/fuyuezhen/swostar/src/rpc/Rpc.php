<?php
namespace swostar\rpc;

use Swoole\Server as SwooleServer;

/**
 * RPC监听
 */
class Rpc
{
    // 监听地址
    protected $host;

    // 监听端口
    protected $port;

    /**
     * 初始化
     * @param [type] $swooleServer
     * @param array $config
     */
    public function __construct(SwooleServer $server, $config) {
        $listen = $server->listen($config['host'], $config['port'], SWOOLE_SOCK_TCP);
        $listen->set($config['swoole']);

        $listen->on('connect', [$this, 'connect']);
        $listen->on('receive', [$this, 'receive']);
        $listen->on('close', [$this, 'close']);

        info("启动TCP监听：" . $config['host'] . ":" . $config['port']);
    }

    /**
     * 有新的连接进入时回调。
     *
     * @param Server $server
     * @param integer $fd          连接的文件描述符，相当于请求连接的ID
     * @return void
     */
    public function connect($server, $fd)
    {
        info("TCP有新的用户连接~");
    }

   /**
     * 接收到数据时回调此函数
     *
     * @param Server $server
     * @param integer $fd          连接的文件描述符，相当于请求连接的ID
     * @param integer $reactorId   TCP 连接所在的 Reactor 线程 ID
     * @param string $data         收到的数据内容，可能是文本或者二进制内容
     * @return void
     */
    public function receive($server, int $fd, int $reactorId, string $data)
    {
        $server->send($fd, "swoole：" .$data);
        info("TCP超管查房，想用户发送信息，并断开他的连接~");
        $server->close($fd);
    }

    /**
     * TCP 客户端连接关闭后回调此函数。
     *
     * @param Server $server
     * @param integer $fd           连接的文件描述符，相当于请求连接的ID
     * @return void
     */
    public function close($server, int $fd)
    {
        info("TCP用户断开连接！");
    }
}