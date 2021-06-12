<?php
// 助手函数
use \swostar\Application;

if (!function_exists('app')) {
    /**
     * 容器方法
     * @param string $abstract
     * @return void
     */
    function app($abstract = null)
    {
        if (empty($abstract)) {
            return Application::getInstance();
        }
        return Application::getInstance()->make($abstract);
    }
}

if (!function_exists('info')) {
    /**
     * 打印
     */
    function info($string = '')
    {
        echo "====>>>> " . $string . "\n";
    }
}