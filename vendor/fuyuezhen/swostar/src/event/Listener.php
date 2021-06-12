<?php
namespace swostar\event;

/**
 * 事件监听父类
 */
abstract class Listener
{
    /**
     * 事件名称你
     *
     * @var string
     */
    protected $name = "interface";

    /**
     * 监听事件
     *
     * @return void
     */
    public abstract function handler();

    /**
     * 获取事件名称
     *
     * @return void
     */
    public function getName()
    {
        return $this->name;
    }
}