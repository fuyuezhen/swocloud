<?php
namespace swocloud\support;

/**
 * 负载均衡算法
 */
class Arithmetic
{
    // 轮询的下一个读取，这样会错开
    protected static $roundLastIndex = 0;

    /**
     * 轮询算法
     *
     * @param array $list
     * @return void
     */
    public static function round(array $list) 
    {
        $currentIndex = self::$roundLastIndex; // 当前index
        $url          = $list[$currentIndex];
        // 是否已经到最后一个
        if ($currentIndex + 1 > count($list) - 1) {
            self::$roundLastIndex = 0;
        } else {
            self::$roundLastIndex++;
        }
        return $url;
    }

    /**
     * 随机算法
     *
     * @return void
     */
    public static function random() 
    {

    }

    // ... 其他算法
}