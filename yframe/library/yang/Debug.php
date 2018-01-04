<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午7:14
 */

namespace yang;


class Debug
{
    public $starTime, $starMem;
    public static function create($name, $value) {
        return new static($name, $value);
    }

    public function __construct($name, $value)
    {
        Log::recore($name, $value, 'debug');
        Log::recore('Url', $this->getNowUrl(), 'debug');
        Log::recore('UseMemory', $this->getUseMem() . ' kb', 'debug');
        Log::recore('UseTime', $this->getUseTime() . ' s', 'debug');
        Log::recore('IncludeCount', $this->getIncludeFileNum(), 'debug');
        Log::recore('Requests', $this->getThroughputRate() . ' req/s', 'debug');
    }

    /**
     * 当前URL
     * @return mixed
     */
    private function getNowUrl()
    {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * 使用掉的内存
     * @return string
     */
    private function getUseMem()
    {
        if (empty($this->starMem)) {
            $this->starMem = Env::get('yf_start_mem');
        }
        return number_format((memory_get_usage() - $this->starMem) / 1024, 2);
    }

    /**
     * 引入的文件数量
     * @return int
     */
    private function getIncludeFileNum()
    {
        return count(get_included_files());
    }

    /**
     * 执行的时间
     * @return string
     */
    private function getUseTime()
    {
        return number_format(round(microtime(true) - static::getAppStartTime(), 10), 6);
    }

    /**
     * 程序开始的时间
     * @return mixed|string
     */
    private function getAppStartTime()
    {
        if (empty($this->starTime)) {
            $this->starTime = Env::get('yf_start_time');
        }
        return $this->starTime;
    }

    /**
     * 获取当前访问的吞吐率情况
     * @return string
     */
    private function getThroughputRate()
    {
        return number_format(1 / $this->getUseTime(), 2);
    }
}