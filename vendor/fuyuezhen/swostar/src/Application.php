<?php
namespace swostar;

use swostar\server\http\HttpServer;
use swostar\server\websocket\WebSocketServer;
use swostar\Container;
use swostar\event\Event;

/**
 * 应用类
 */
class Application extends Container
{

    /**
     * 启动欢迎
     */
    protected const SWOSTAR_WELCOME = "
      _____                     _____     ___
     /  __/             ____   /  __/  __/  /__   ___ __    __  __
     \__ \  | | /| / / / __ \  \__ \  /_   ___/  /  _`  |  |  \/ /
     __/ /  | |/ |/ / / /_/ /  __/ /   /  /_    |  (_|  |  |   _/
    /___/   |__/\__/  \____/  /___/    \___/     \___/\_|  |__|
    ";

    /**
     * 根目录地址
     * @var string
     */
    protected $basePath = '';

    /**
     * 构造方法
     * @param string $path
     */
    public function __construct($path = null)
    {
        if(!empty($path)) {
            $this->setBasePath($path);
        }

        $this->init();

        echo self::SWOSTAR_WELCOME."\n";
    }

    /**
     * 运行
     *
     * @return void
     */
    public function run($argv)
    {
        $server = null;
        switch ($argv[1]) {
            case 'http:start':
                $server = new HttpServer($this);
                break;
            case 'ws:start':
                $server = new WebSocketServer($this);
                break;
        }

        // 启动
        $server->start();
    }

    /**
     * 初始化
     * @return void
     */
    private function init() {
        self::setInstance($this);
        
        // 初始化配置
        app('config')->load($this->basePath);
        // 初始化路由
        app('route')->load($this->basePath);
        // 注册事件
        $this->bind(['event' => $this->registerEvent()]);

        // 加载注册基础类到容器
        $this->registerBaseBindings();
    }

    /**
     * 注册事件
     *
     * @return void
     */
    private function registerEvent()
    {
        $event = new Event;

        $files = scandir($this->basePath . "/app/listener");
        foreach ($files as $key => $file) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $class = "\app\\listener\\" . explode(".", $file)[0];
            if (class_exists($class)) {
                $listener = new $class($this);
                $event->register($listener->getName(), [$listener, 'handler']);
            }
        }

        return $event;
    }

    /**
     * 注册基础类到容器
     * @return void
     */
    private function registerBaseBindings()
    {
        // 注册服务的容器对象实例
        if (is_file($this->basePath . '/app/provider.php')) {
            $provider = include $this->basePath . '/app/provider.php';
            if (is_array($provider)) {
                $this->bind($provider);
            }
        }
    }

    /**
     * 设置根目录地址
     *
     * @param [type] $path
     * @return void
     */
    public function setBasePath($path)
    {
        $this->basePath = \rtrim($path, '\/');
    }
    /**
     * 设置地址
     *
     * @param [type] $path
     * @return void
     */
    public function setHost($host)
    {
        $this->host = $host;
        return  $this;
    }
    /**
     * 获取地址
     * @return void
     */
    public function getHost()
    {
        return $this->host;
    }
    /**
     * 获取根目录地址
     * @return void
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
}