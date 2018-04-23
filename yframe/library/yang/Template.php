<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:08
 */

namespace yang;


use yang\exception\FileNotFoundException;

class Template extends template\SimInterface {
    protected $isType = 'array';
    use template\SimFlag;
    private $file_list = [], $cache_file = '', $content, $cache_content, $loadTag = [];
    private static $cache_static;
    private $userclass = '';
    /*
     *  键值为文件绝对路径, 值为输出的缓存文件路径, 做区别处理
     */
    private $update_file_list = [];
    private $tpl_path, $tpl_cache;

    /**
     * @param $file
     * @param $tpl_path
     * @return Template
     * @throws FileNotFoundException
     */
    public static function load($file, $tpl_path)
    {
        self::$cache_static = new self($file, $tpl_path);
        return self::$cache_static;
    }

    /**
     * Template constructor.
     * @param $file
     * @param $tpl_path
     * @throws FileNotFoundException
     */
    public function __construct($file, $tpl_path)
    {
        $this->file_list[] = $file;
        $this->tpl_path = $tpl_path;
        $this->tpl_cache = Env::get('tpl_cache_path');
        $this->cache_file = $this->tpl_cache . md5($file) . '.php';
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
        $content = $this->parseCache($file, $this->cache_file);
        $this->includeCommand($content);
        if (!empty($this->update_file_list)) {
            while (true) {
                if (empty($this->update_file_list)) {
                    break;
                }
                $f = array_keys($this->update_file_list);
                $tfile = array_shift($f);
                $cfile = array_shift($this->update_file_list);
                $this->parseCache($tfile, $cfile);
                $this->includeCommand($content);
            }
        }

        return $this;
    }

    private function parseCache($file, $cache_file) {
        $content = file_get_contents($file);
        if (!file_exists($cache_file) || filectime($file) > filectime($cache_file)) {
            if (!is_dir(dirname($cache_file))) {
                // $this->cache_content = file_get_contents($this->cache_file);
                mkdir(dirname($cache_file), 0755, true);
            }
            $this->convertContent($content);
            $this->addHead($content);
            file_put_contents($cache_file, $content);
            return true;
        }
        $this->includeCommand($content);
    }

    public function render() {
        return $this->cache_file;
    }
    /**
     * 转换模板内容
     */
    private function convertContent(&$content) {
        $content = $this->isCommand($content);
        $content = $this->includeCommand($content);
        $content = $this->loadtagCommand($content);
        $content = $this->showVar($content);
        $content = $this->foreachCommand($content);
        $content = $this->forCommand($content);
        $content = $this->setCommand($content);
        $content = $this->ifCommand($content);
        $content = $this->envCommand($content);
        $content = $this->configCommand($content);
        $content = $this->showFunc($content);
    }

    public function includeCommand($content = '')
    {
        return preg_replace_callback('/@include\s+([\w\W]*?)(?:[\s]*);/i', function ($mathc) {
            // 此处不做结尾逗号的清理
            if ($this->cahce_all[md5($mathc[0])]) {
                return $this->cahce_all[md5($mathc[0])];
            }
            $file = $mathc[1];
            if (strpos($file, ',')) {
                $contents = '<?php' . PHP_EOL;
                $file = explode(',', $file);
                foreach ($file as $f) {
                    $contents .= 'include \'' . $this->convertFile($f) . '\';' . PHP_EOL;
                }
                $contents .= '?>' . PHP_EOL;
                $this->cahce_all[md5($mathc[0])] = $contents;
                return $contents;
            } else {
                $this->cahce_all[md5($mathc[0])] = $contents = '<?php include \'' . $this->convertFile($file) . '\'; ?>' . PHP_EOL;
                return $contents;
            }
        }, $content);
    }

    private function addHead(&$content) {
        $html = '<?php if (!defined(\'YF_TEMP\')) exit; ?>'.PHP_EOL;
        $content = $html . $content;
    }

    private function convertFile($file)
    {
        if (is_array($file)) {
            $file = current($file);
        }
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