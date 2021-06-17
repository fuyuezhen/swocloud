<?php
namespace swocloud\traits;

use Co;
use Swoole\Coroutine\Http\Client;
use Swoole\Table;

trait AckTraits
{
    /**
     * 共享内存
     *
     * @var [type]
     */
    protected $table;
    
    /**
     * 创建共享内存空间
     *
     * @return void
     */
    protected function createTable()
    {
        $this->table = new Table(1024);
        // 设置ack确认码
        $this->table->column('ack', Table::TYPE_INT, 1);
        // 尝试次数
        $this->table->column('num', Table::TYPE_INT, 1);

        $this->table->create();
    }

    /**
     * 确认消息的发送，采用协程的方式
     *
     * @param [type] $uniqid  任务唯一ID
     * @param [type] $data    请求动作
     * @param Client $client  发送方的客户端
     * @return void
     */
    protected function confirmGo($uniqid, $data, Client $client)
    {
        go(function() use($uniqid, $data, $client){
            // 确认消息监听
            while (true) {
                Co::sleep(1); // 延迟1秒
                $ackData = $client->recv(0.2); // 接收消息。只为 WebSocket 使用，需要配合 upgrade() 使用；$timeout 超时时间
                $ack = json_decode($ackData->data, true);
                // 判断是否确认
                if (isset($ack['method']) && $ack['method'] == 'ack') {
                    // 确认则修改
                    $this->table->incr($ack['msg_id'], 'ack');
                    info("收到已确认信息，" . $ack['msg_id']);
                }
                // 查询任务的状态
                $task = $this->table->get($uniqid);

                // 如果任务已经被确认了，或者重试超过了3次之后就会清空任务
                if ($task['ack'] > 0 && $task['num'] >= 0) {
                    info("清空任务，" . $uniqid);
                    // 清空任务
                    $this->table->del($uniqid);
                    // 关闭客户端
                    $client->close();
                    break; // 退出循环监听
                } else {
                    $client->push(json_decode($data));
                }
                // 尝试次数加1
                $this->table->incr($uniqid, 'num');
                info("尝试一次，" . $uniqid);
            }
        });
    }
}