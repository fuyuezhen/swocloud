<?php
namespace swostar;

use swostar\route\Route;
use swostar\config\Config;
use swostar\message\http\Request;

/**
 * 容器类
 */
class Container
{
    /**
     * 单例
     */
    protected static $instance;

    // 容器绑定标识
    protected $bind = [
        'route'  => Route::class,
        'config' => Config::class,
        'httpRequest' => Request::class,
    ];

    /**
     * 绑定标识到容器
     * @return void
     */
    protected function bind($abstract)
    {
        $this->bind = array_merge($this->bind, $abstract);
    }
    
    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];

    /**
     * 从容器中解析实例对象或者闭包
     * @param  string $abstract   标识
     * @param  array  $parameters 传递的参数
     * @return object             是一个闭包或者对象
     */
    public function make($abstract, $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }


    public function resolve($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if (!$this->has($abstract)) {
            // 如果不存在自行
            // 选择返回, 可以抛出一个异常
            throw new Exception('没有找到这个容器对象'.$abstract, 500);
        }

        $object = $this->bind[$abstract];
        // 在这个容器中是否存在
        // 1. 判断是否一个为对象
        // 2. 闭包的方式
        if ($object instanceof Closure) {
            return $object();
        }

        // 3. 类对象的字符串 (类的地址)
        return $this->instances[$abstract] = (is_object($object)) ? $object :  new $object(...$parameters) ;
    }

    // 判断是否在容器中
    // 1. 容器很多便于扩展
    // 2. 可能在其他场景中会用到
    public function has($abstract)
    {
        return isset($this->bind[$abstract]);
    }


    /**
     * 单例设置方法
     * @return void
     */
    public static function setInstance($instance = null)
    {
        static::$instance = $instance;
    }
    /**
     * 单例获取方法
     * @return void
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
}