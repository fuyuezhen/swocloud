<?php
namespace swostar\route;

/**
 * 路由类
 */
class Route
{

    /**
     * 路由目录地址
     * @var string
     */
    protected $routePath = '';

    /**
     * 解析后的路由
     * @var array
     */
    protected $routes = [];

    /**
     * 默认加载的路由
     * @var string
     */
    protected $flag = 'http';

    /**
     * 定义访问类型
     * @var array
     */
    protected $verbs = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct()
    {
        // echo "初始化route\n";
    }

    /**
     * get方法
     * @param [type] $uri
     * @param [type] $action
     * @return void
     */
    public function get($uri, $action) {
        $this->addRoute(['GET'], $uri, $action);
    }

    /**
     * post方法
     * @param [type] $uri
     * @param [type] $action
     * @return void
     */
    public function post($uri, $action) {
        $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * 所有定义的访问类型
     * @param [type] $uri
     * @param [type] $action
     * @return void
     */
    public function any($uri, $action) {
        $this->addRoute($this->verbs, $uri, $action);
    }

    /**
     * WebSocket的控制器
     * @return void
     */
    public function ws($uri, $controller)
    {
        $actions = ['open', 'message', 'close'];
        foreach ($actions as $action) {
            $this->addRoute([$action], $uri, $controller.'@'.$action);
        }
    }

    /**
     * 添加到路由
     * @return void
     */
    public function addRoute($methods, $uri, $action)
    {
        foreach ($methods as $method) {
            $this->routes[$this->flag][$method][$uri] = $action;
        }
        return $this;
    }

    /**
     * 路由校验
     * @return void
     */
    public function match($path, $params = []){
        /*
        本质就是一个字符串的比对
        1. 获取请求的uripath
        2. 根据类型获取路由
        3. 根据请求的uri 匹配 相应的路由；并返回action
        4. 判断执行的方法的类型是控制器还是闭包
           4.1 执行闭包
           4.2 执行控制器
        */
        $action = null;
        if (isset($this->routes[$this->flag][$this->method])) {
            foreach ($this->routes[$this->flag][$this->method] as $uri => $value) {
                $uri = ($uri && substr($uri,0,1)!='/') ? "/".$uri : $uri;

                if ($path === $uri) {
                    $action = $value;
                    break;
                }
            }
        }

        if (!empty($action)) {
            return $this->runAction($action, $params);
        }

        info("没有找到方法：".$action);

        return "404";
    }

    /**
     * 运行方法
     * @param [type] $action
     * @return void
     */
    protected function runAction($action, $params = [])
    {
        if ($action instanceof \Closure) {
            return $action(...$params);
        } else {
            $namespace  = "\app\\" . $this->flag . "\controller\\";
            $string     = explode('@', $action);
            $controller = $namespace . $string[0];
            $class      = new $controller();
            return $class->{$string[1]}(...$params);
        }
    }

    /**
     * 用于加载路由规则
     * @return void
     */
    public function load($base_path = '') {
        $this->routePath = $base_path.'/route'; 
        // 读取配置
        $this->phpParser($this->routePath);
    }

    
    /**
     * 读取PHP文件类型的配置文件
     * @return [type] [description]
     */
    protected function phpParser($path = '')
    {
        // 1. 找到文件
        // 此处跳过多级的情况
        $files = scandir($path);
        // 2. 读取文件信息
        foreach ($files as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if(is_dir($path."/".$file)){
                $result = $this->phpParser($path."/".$file);
                continue;
            }
            // 2.1 获取文件名
            $filename = \stristr($file, ".php", true);

            $this->flag = $filename;
            // 2.2 读取文件信息
            require_once $path."/".$file;
        }
    }

    /**
     * 设置请求类型
     * @param string $method
     * @return void
     */
    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }

    /**
     * 设置路由标记
     * @param string $flag
     * @return void
     */
    public function setFlag($flag) {
        $this->flag = $flag;
        return $this;
    }

    /**
     * 获取路由
     * @return void
     */
    public function getRoutes()
    {
        return $this->routes[$this->flag];
    }
}