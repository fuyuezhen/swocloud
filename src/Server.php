<?php
namespace swocloud;

use Swoole\Server as SwooleServer;

/**
 * 服务公共抽象父类
 */
abstract class Server
{
    /**
     * 保存swoole对象
     * @var SwooleServer
     */
    protected $swooleServer;

    /**
     * 监听地址
     * @var string
     */
    public $host = "0.0.0.0";

    /**
     * 监听端口
     * @var string
     */
    public $port = 9000;

    protected $event = [
        // 服务事件
        'server' => [
            'Start'          =>  'onStart',
            'Shutdown'       =>  'onShutdown',
            'WorkerStart'    =>  'onWorkerStart',
            'WorkerStop'     =>  'onWorkerStop',
            'WorkerExit'     =>  'onWorkerExit',
            'Connect'        =>  'onConnect',
            'Receive'        =>  'onReceive',
            'Packet'         =>  'onPacket',
            'Close'          =>  'onClose',
            'Task'           =>  'onTask',
            'Finish'         =>  'onFinish',
            'PipeMessage'    =>  'onPipeMessage',
            'WorkerError'    =>  'onWorkerError',
            'ManagerStart'   =>  'onManagerStart',
            'ManagerStop'    =>  'onManagerStop',
            'BeforeReload'   =>  'onBeforeReload',
            'AfterReload'    =>  'onAfterReload',
        ],
        
        // 额外事件
        'exp' => [],
        // 子类事件
        'sub' => [],
    ];

    /**
     * 这是swoole服务的配置
     * @var [type]
     */
    protected $config = [
        'task_worker_num' => 0,
    ];

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 1. 设置子类事件
        $this->setSubEvent();
        // 2. 创建对象
        $this->createServer();
        // 3. 注册swoole回调事件
        $this->initSwooleEvent();
    }

    /**
     * 设置子类事件
     */
    abstract protected function setSubEvent();

    /**
     * 创建服务对象
     */
    abstract protected function createServer();

    /**
     * 初始化swoole事件
     *
     * @return void
     */
    public function initSwooleEvent()
    {
        foreach ($this->event as $key => $events) {
            foreach ($events as $event => $func) {
                $this->swooleServer->on($event, [$this, $func]);
            }
        }
    }

    /**
     * Swoole服务启动事件
     * @return void
     */
    public function start()
    {
        if(empty($this->swooleServer)) {
            return "error";
        }

        // 设置swoole的配置
        $this->swooleServer->set($this->config);

        // 4. 启动服务
        $this->swooleServer->start();
    }

    /**
     * 启动后在主进程（master）的主线程回调此函数
     *
     * @param SwooleServer $server
     * @return void
     */
    public function onStart(SwooleServer $server)
    {
        
    }

    /**
     * 此事件在 Server 正常结束时发生
     *
     * @param SwooleServer $server
     * @return void
     */
    public function onShutdown(SwooleServer $server)
    {

    }

    /**
     * 此事件在 Worker 进程 / Task 进程 启动时发生，这里创建的对象可以在进程生命周期内使用。
     *
     * @param SwooleServer $server
     * @param integer $workerId   Worker 进程 id（非进程的 PID）
     * @return void
     */
    public function onWorkerStart(SwooleServer $server, int $workerId)
    {

    }

    /**
     * 此事件在 Worker 进程终止时发生。在此函数中可以回收 Worker 进程申请的各类资源。
     *
     * @param SwooleServer $server
     * @param integer $workerId   Worker 进程 id（非进程的 PID）
     * @return void
     */
    public function onWorkerStop(SwooleServer $server, int $workerId)
    {

    }

    /**
     * 仅在开启 reload_async 特性后有效。
     * 当旧的 Worker 即将退出时，会触发 onWorkerExit 事件，在此事件回调函数中，应用层可以尝试清理某些长连接 Socket，
     * 直到事件循环中没有 fd 或者达到了 max_wait_time 退出进程。
     *
     * @param SwooleServer $server
     * @param integer $workerId   Worker 进程 id（非进程的 PID）
     * @return void
     */
    public function onWorkerExit(SwooleServer $server, int $workerId)
    {

    }

    /**
     * 有新的连接进入时，在 worker 进程中回调。
     *
     * @param SwooleServer $server
     * @param integer $fd          连接的文件描述符，相当于请求连接的ID
     * @param integer $reactorId   连接所在的 Reactor 线程 ID
     * @return void
     */
    public function onConnect(SwooleServer $server, int $fd, int $reactorId)
    {

    }

    /**
     * 接收到数据时回调此函数，发生在 worker 进程中。
     *
     * @param SwooleServer $server
     * @param integer $fd          连接的文件描述符，相当于请求连接的ID
     * @param integer $reactorId   TCP 连接所在的 Reactor 线程 ID
     * @param string $data         收到的数据内容，可能是文本或者二进制内容
     * @return void
     */
    public function onReceive(SwooleServer $server, int $fd, int $reactorId, string $data)
    {

    }

    /**
     * 接收到 UDP 数据包时回调此函数，发生在 worker 进程中。
     *
     * @param SwooleServer $server
     * @param string $data         收到的数据内容，可能是文本或者二进制内容
     * @param array $clientInfo    客户端信息包括 address/port/server_socket 等多项客户端信息数据，参考 UDP 服务器
     * @return void
     */
    public function onPacket(SwooleServer $server, string $data, array $clientInfo)
    {

    }
    
    /**
     * TCP 客户端连接关闭后，在 Worker 进程中回调此函数。
     *
     * @param SwooleServer $server
     * @param integer $fd           连接的文件描述符，相当于请求连接的ID
     * @param integer $reactorId    来自哪个 reactor 线程，主动 close 关闭时为负数
     * @return void
     */
    public function onClose($server, int $fd, int $reactorId)
    {

    }

    /**
     * worker 进程可以使用 task 函数向 task_worker 进程投递新的任务
     * 在 task 进程内被调用。
     * 当前的 Task 进程在调用 onTask 回调函数时会将进程状态切换为忙碌，这时将不再接收新的 Task，当 onTask 函数返回时会将进程状态切换为空闲然后继续接收新的 Task。
     *
     * @param SwooleServer $server
     * @param integer $task_id        执行任务的 task 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param integer $src_worker_id  投递任务的 worker 进程 id
     * @param mixed $data             任务的数据内容
     * @return void
     */
    function onTask(SwooleServer $server, int $task_id, int $src_worker_id, mixed $data)
    {

    }

    /**
     * 此回调函数在 worker 进程被调用，当 worker 进程投递的任务在 task 进程中完成时， task 进程会通过 Swoole\Server->finish() 方法将任务处理的结果发送给 worker 进程。
     *
     * @param SwooleServer $server
     * @param integer $task_id    执行任务的 task 进程 id
     * @param mixed $data         任务处理的结果内容
     * @return void
     */
    public function onFinish(SwooleServer $server, int $task_id, mixed $data)
    {

    }

    /**
     * 当工作进程收到由 $server->sendMessage() 发送的 unixSocket 消息时会触发 onPipeMessage 事件。worker/task 进程都可能会触发 onPipeMessage 事件
     *
     * @param Swoole\Server $server
     * @param integer $src_worker_id 消息来自哪个 Worker 进程
     * @param mixed $message         消息内容，可以是任意 PHP 类型
     * @return void
     */
    public function onPipeMessage(SwooleServer $server, int $src_worker_id, mixed $message)
    {

    }

    /**
     * 当 Worker/Task 进程发生异常后会在 Manager 进程内回调此函数。
     *
     * @param Swoole\Server $server
     * @param integer $worker_id   异常 worker 进程的 id
     * @param integer $worker_pid  异常 worker 进程的 pid
     * @param integer $exit_code   退出的状态码，范围是 0～255
     * @param integer $signal      进程退出的信号
     * @return void
     */
    public function onWorkerError(SwooleServer $server, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {

    }

    /**
     * 当管理进程启动时触发此事件
     *
     * @param Swoole\Server $server
     * @return void
     */
    public function onManagerStart(SwooleServer $server)
    {

    }

    /**
     * 当管理进程结束时触发
     *
     * @param Swoole\Server $server
     * @return void
     */
    public function onManagerStop(SwooleServer $server)
    {

    }

    /**
     * Worker 进程 Reload 之前触发此事件，在 Manager 进程中回调
     *
     * @param Swoole\Server $server
     * @return void
     */
    public function onBeforeReload(SwooleServer $server)
    {

    }

    /**
     * Worker 进程 Reload 之后触发此事件，在 Manager 进程中回调
     *
     * @param Swoole\Server $server
     * @return void
     */
    public function onAfterReload(SwooleServer $server)
    {

    }
}