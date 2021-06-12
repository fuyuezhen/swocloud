<?php
namespace swostar\message\http;

use Swoole\Http\Request as SwooleRequest;

/**
 * 请求类
 */
class Request
{
    protected $swooleRequest;
    /**
     * 请求服务信息
     */
    protected $server;

    /**
     * 请求地址
     */
    protected $uriPath;

    /**
     * 请求类型
     */
    protected $method;

    /**
     * 初始化
     * @return void
     */
    public static function init(SwooleRequest $request) {
        $self = app("httpRequest");

        $self->swooleRequest = $request;

        $self->server  = $self->swooleRequest->server;
        $self->uriPath = $self->server['request_uri'] ?? '';
        $self->method  = $self->server['request_method'] ?? '';

        return $self;
    }

    /**
     * 获取请求地址
     * @return void
     */
    public function getUriPath(){
        return $this->uriPath;
    }

    /**
     * 获取请求类型
     * @return void
     */
    public function getMethod()
    {
        return $this->method;
    }
}