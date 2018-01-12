<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:08
 */

namespace yang;


class Template extends template\SimInterface {
    use template\SimFlag;
    private $file_list = [], $cache_file = '', $content, $cache_content;
    private static $cache_static;
    /*
     *  键值为文件绝对路径, 值为输出的缓存文件路径, 做区别处理
     */
    private $update_file_list = [];
    private $tpl_path, $tpl_cache;

    public static function load($file, $tpl_path)
    {
        self::$cache_static = new self($file, $tpl_path);
        return self::$cache_static;
    }

    public function __construct($file, $tpl_path)
    {
        $this->file_list[] = $file;
        $this->tpl_path = $tpl_path;
        $this->tpl_cache = Env::get('tpl_cache_path');
        $this->cache_file = $this->tpl_cache . md5($file) . '.php';

        $content = $this->parseCache($file, $this->cache_file);
        $this->includeCommand($content);
        if (!empty($this->update_file_list)) {
            foreach ($this->update_file_list as $tfile => $cfile ) {
                $this->parseCache($tfile, $cfile);
            }
        }

        return $this;
    }

    private function parseCache($file, $cache_file) {
        if (!file_exists($cache_file) || filectime($file) > filectime($cache_file)) {
            $content = file_get_contents($file);
            if (!is_dir(dirname($cache_file))) {
                // $this->cache_content = file_get_contents($this->cache_file);
                mkdir(dirname($cache_file), 0755, true);
            }
            $content = $this->includeCommand($content);
            $this->convertContent($content);
            $this->addHead();
            file_put_contents($cache_file, $content);
        }
    }

    public function render() {
        Fastload::includeFile($this->cache_file);
    }
    /**
     * 转换模板内容
     */
    private function convertContent(&$content) {
        $content = $this->showVar($content);
        $content = $this->foreachCommand($content);
        $content = $this->fallCallback($content);
    }

    public function getContent() {
        return $this->content;
    }

    public function includeCommand($content = '')
    {
        return preg_replace_callback('/@include\s+([\w\W]*?)(?:[\s]*);/i', function ($mathc) {
            // 此处不做结尾逗号的清理
            $file = $mathc[1];
            if (strpos($file, ',')) {
                $contents = '<?php' . PHP_EOL;
                $file = explode(',', $file);
                foreach ($file as $f) {
                    $contents .= 'include \'' . $this->convertFile($f) . '\';' . PHP_EOL;
                }
                return $contents . '?>' . PHP_EOL;
            } else {
                return '<?php include \'' . $this->convertFile($file) . '\'; ?>' . PHP_EOL;
            }
        }, $content);
    }

    private function addHead() {
        $html = '<?php if (defined(\'YY_TEMP\')) exit; ?>'.PHP_EOL;
        $this->content = $html . $this->content;
    }

    private function convertFile($file)
    {
        $file = str_replace(PHP_EOL, '', $file);
        $f = trim($file);
        $tpl = $this->tpl_path . $f . '.html';
        $cache = $this->tpl_cache . md5($tpl) . '.php';
        if (!file_exists($tpl)) {
            throw new \RuntimeException($tpl . ' Not Found');
        }

        if (!file_exists($cache) || filectime($tpl) > filectime($cache)) {
            $this->update_file_list[$tpl] = $cache;
        }

        return $cache;
    }
};