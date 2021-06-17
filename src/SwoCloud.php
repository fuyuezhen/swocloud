<?php
namespace swocloud;

use swocloud\server\Route;

/**
 * 入口文件
 */
class SwoCloud
{
    protected $basePath;

    /**
     * 初始化
     * @param [type] $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * 运行
     * @return void
     */
    public function run()
    {
        $routeServer = new Route;
        $routeServer->start();
    }
}