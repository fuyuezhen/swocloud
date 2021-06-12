<?php
namespace swostar\event;
/**
 * 事件类
 */
class Event
{
    /**
     * 事件的记录
     * @var array
     */
    protected $events = [];

    /**
     * 注册事件
     *
     * @param [type] $event
     * @param [type] $callback
     * @return void
     */
    public function register($event, $callback)
    {
        $event = strtolower($event); // 不区分大小写
        $this->events[$event] = ["callback" => $callback];
    }

    /**
     * 触发事件
     *
     * @param [type] $event
     * @param array $param
     * @return void
     */
    public function trigger($event, $param = [])
    {
        $event = strtolower($event);
        if (isset($this->events[$event])) {
            ($this->events[$event]['callback'])(...$param);
            return true;
        }
        return false;
    }

    /**
     * 获取事件
     *
     * @param [type] $event
     * @return void
     */
    public function getEvents($event = null)
    {
        return empty($event) ? $this->events : $this->events[$event];
    }
}