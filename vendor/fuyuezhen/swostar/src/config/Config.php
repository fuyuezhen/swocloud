<?php
namespace swostar\Config;
/**
 * 配置类
 */
class Config
{
    /**
     * 配置项
     * @var array
     */
    protected $itmes = [];

    /**
     * 配置地址
     * @var string
     */
    protected $configPath = '';

    /**
     * 初始化
     * @return void
     */
    public function load($base_path = '')
    {
        $this->configPath = $base_path.'/config'; 

        // 读取配置
        $this->itmes = $this->phpParser($this->configPath);
    }
    /**
     * 读取PHP文件类型的配置文件
     * @return [type] [description]
     */
    protected function phpParser($path = '')
    {
        // 1. 找到文件
        // 此处跳过多级的情况
        $files = scandir($path);
        $data = null;
        // 2. 读取文件信息
        foreach ($files as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if(is_dir($path."/".$file)){
                $result = $this->phpParser($path."/".$file);
                if(!empty($result)) {
                    $data[$file] = $result;
                }
                continue;
            }
            // 2.1 获取文件名
            $filename = \stristr($file, ".php", true);
            // 2.2 读取文件信息
            $data[$filename] = include $path."/".$file;
        }

        // 3. 返回
        return $data;
    }


    // key.key2.key3
    public function get($keys = '')
    {
        $data = $this->itmes;
        if(!empty($keys)) {
            foreach (\explode('.', $keys) as $key => $value) {
                if(!isset($data[$value])){
                    return $data;
                }
                $data = $data[$value];
            }
        }
        return $data;
    }
}
