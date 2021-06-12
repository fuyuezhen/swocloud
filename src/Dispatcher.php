<?php
namespace swocloud;

use Swoole\Server as SwooleServer;
use \Redis;
use Swoole\Http\Request;
use Swoole\Http\Response;
use swocloud\support\Arithmetic;

/**
 * 分发事件处理类
 */
class Dispatcher
{
    /**
     * 用户登陆
     *
     * @param Route $route
     * @param Request $swooleRequest
     * @param Response $swooleResponse
     * @return void
     */
    public function login(Route $route, Request $swooleRequest, Response $swooleResponse)
    {
        $data = $swooleRequest->post;
        $data['id'] = 1;
        // 用户名和密码校验
        // ... code

        // 获取连接的服务器
        $server = json_decode($this->getIMServer($route), true);

        $url = $server['ip'] . ":" . $server['port'];

        // 生成 token
        $token = $this->getJwtToken($server['ip'], $data['id'], $url);
        info($token);
        $swooleResponse->end(json_encode(['token' => $token, 'url' => $url]));
    }

    /**
     * 获取Token
     *
     * @param [type] $sid 服务器的fd
     * @param [type] $uid 用户ID
     * @param [type] $url 连接的地址
     * @return void
     */
    protected function getJwtToken($sid, $uid, $url)
    {
        // iss：jwt签发者
        // aud：接受jwt的一方
        // sub：jwt所面向的用户
        // iat：签发时间
        // nbf：生效时间
        // exp：jwt的过期时间
        // jti：jwt的唯一身份标识，主要用来作为一次性token，从而回避重放攻击

        $key   = "swocloud";
        $time  = time();
        $token = [
            'iss' => "http://192.168.218.20", // 可选参数
            'aud' => "http://192.168.218.20", // 可选参数
            'iat' => $time, // 签发时间
            'nbf' => $time, // 生效时间
            'exp' => $time + 7200, // 过期时间
            'data' => [
                'uid'  => $uid,
                'name'  => "client" . $time . $sid, // 用户名
                'service_url' => $url,
            ],
        ];
        return \Firebase\JWT\JWT::encode($token, $key);
    }

    /**
     * 根据算法获取连接服务
     *
     * @param Route $route
     * @return void
     */
    protected function getIMServer(Route $route)
    {
        // 从redis中获取列表
        $list = $route->getRedis()->smembers($route->getServerKey());

        if (!empty($list)) {
            return Arithmetic::{$route->getArithmetic()}($list);
        }
        return false;
    }

    /**
     * 服务注册
     *
     * @return void
     */
    public function register(Route $route, SwooleServer $server, $fd, $data)
    {
        $serverKey = $route->getServerKey();
        // 把服务器信息保存到redis中
        $redis     = $route->getRedis();
        $value     = json_encode([
            'ip'   => $data['ip'],
            'port' => $data['port'],
        ]);
        $redis->sadd($serverKey, $value);
        // 这里需要触发定时器判断，不用heartbeat_check_interval，因为我们需要有后续炒作，比如清空redis数据
        $server->tick(3000, function($timer_id, Redis $redis, $server, $serverKey, $fd, $value){
            if (!$server->exist($fd)) {
                $redis->srem($serverKey, $value);
                $server->clearTimer($timer_id);

                info("im server 宕机，主动清空");
            }
        }, $redis, $server, $serverKey, $fd, $value);

    }

    /**
     * 没有找到方法
     *
     * @return void
     */
    public function __call($method, $param = [])
    {
        info("没有找到方法");
    }
}