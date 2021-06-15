<?php
namespace swocloud;

use Swoole\Server as SwooleServer;
use \Redis;
use Swoole\Coroutine\Http\Client;
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
        // info($token);
        $swooleResponse->end(json_encode(['token' => $token, 'url' => $url]));
    }

    /**
     * 服务注册
     *
     * @param Route $route
     * @param SwooleServer $server
     * @param [type] $fd
     * @param [type] $data
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
     * 路由向所有服务器进行广播 =》 服务器再向自己的客户端进行信息发送
     *
     * @param Route $route
     * @param SwooleServer $server
     * @param [type] $fd
     * @param [type] $data
     * @return void
     */
    public function routeBroadcast(Route $route, SwooleServer $server, $fd, $data)
    {
        // 从redis中读取所有服务器信息
        $ims = $route->getIMServers();
        foreach ($ims as $key => $im) {
            $imInfo = json_decode($im, true);
            // 这里需要注意，因为我们的server实际上是有jwt的认证，因此route也需要生成jwt的token并发送
            $this->send($route, $imInfo['ip'], $imInfo['port'], [
                'data' => [
                    'msg' => $data['msg']
                ]
            ]);
        }
    }

    /**
     * route服务器发送信息给其他服务器
     *
     * @param Route $route
     * @param [type] $ip
     * @param [type] $port
     * @param [type] $data
     * @return void
     */
    protected function send(Route $route, $ip, $port, $data)
    {
        $token = $this->getJwtToken(0, 0, $ip . ":" . $port);
        $client = new Client($ip, $port);
        $client->setHeaders(['sec-websocket-protocol' => $token]);
        $ret = $client->upgrade("/"); // 升级为 WebSocket 连接。
        if ($ret) {
            $data = [
                'method'      => 'routeBroadcast',
            ];
            $client->push(json_encode($data));
        }
    }

    /**
     * 根据算法获取连接服务
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
            'iss' => "http://192.168.218.30", // 可选参数
            'aud' => "http://192.168.218.30", // 可选参数
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
     * 没有找到方法
     *
     * @return void
     */
    public function __call($method, $param = [])
    {
        info("没有找到方法");
    }

}