<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 18-1-4
 * Time: 下午5:04
 */

namespace yang\caches;

class File implements CacheServer
{
    private $cached = [];
    private $cachedName = [];

    private function createName($name)
    {
        $copy = $name = trim($name, '.');
        if ($this->cachedName[$name]) {
            return $this->cachedName[$name];
        }
        if (strpos($name, '.')) {
            $name = explode('.', $name);
            if (count($name) > 3) {
                $name = array_splice($name, 0, 3);
            }
            $filename = md5(array_pop($name));
            $name = '/' . implode('/', $name) . '/' . $filename;
        } else {
            $name = md5($name);
        }
        $name = \Env::get('cache_path') . $name . '.php';
        $this->cachedName[$copy] = $name;
        return $name;
    }


    /**
     * @param $filename
     * @param $value
     * @throws \yang\exception\Premission
     */
    private function save($filename, $value) {
        $content = "<?php \n\n return ";
        $content .= var_export($value, true) . ';';

        if (!is_dir(dirname($filename))) {
            if (!mkdir(dirname($filename), 0755, true)) {
                throw new \yang\exception\Premission();
            }
        }
        file_put_contents($filename, $content);
    }

    /**
     * @param string $name
     * @param $value
     * @throws \yang\exception\Premission
     */
    public function add($name, $value)
    {
        $filename = $this->createName($name);
        $this->save($filename, $value);
    }

    public function get($name)
    {
        if ($this->cached[$name]) {
            return $this->cached[$name];
        }
        $filename = $this->createName($name);
        if (!file_exists($filename) || filectime($filename) + \Env::get('cache_life') < time()) {
            $this->del($name);
            return null;
        }

        $data = \yang\Fastload::getContentOfFile($filename);
        $this->cached[$name] = $data;
        return $data;
    }

    public function clear($name = '')
    {
        // TODO: Implement clear() method.
        //
        if (empty($name)) {
            $this->delPath(\Env::get('cache_path'));
            return true;
        }
        $name = str_replace('.', DIRECTORY_SEPARATOR, $name);
        $this->delPath(\Env::get('cache_path') . $name);
    }

    private function delPath($path) {
        if (is_dir($path)) {
            $open = opendir($path);

            while (false !== ($readdir = readdir($open))) {
                if ($readdir == '.' || $readdir == '..') continue;
                $file = $path . DIRECTORY_SEPARATOR . $readdir;
                if (is_dir($file)) {
                    $this->delPath($file);
                } else {
                    @unlink($file);
                }

                if(count(scandir($file))==2){
                    rmdirs($file);
                }
            }
        }
    }

    public function del($name)
    {
        $filename = $this->createName($name);
        if (file_exists($filename)) {
            @unlink($filename);
            unset($this->cached[$name]);
        }
    }

    public static function init() {}
}