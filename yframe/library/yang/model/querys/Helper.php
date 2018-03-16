<?php
/**
 * Created by PhpStorm.
 * User: superyang
 * Date: 18-2-3
 * Time: 下午2:46
 */

namespace yang\model\querys;


trait Helper
{

    /**
     * 转换简写的符号
     * @param $sql
     * @return mixed
     */
    private function parseConditioncode($sql) {
        return str_replace(['neq', 'eq',  'lte', 'lt', 'gte',  'gt' ], ['!=', '=', '<=', '<', '>=', '>'], $sql);
    }

    /**
     * 转换字符串
     * @param $value
     * @return string
     */
    private function parseQuot($value) {
        $value = trim($value);
        if (empty($value)) {
            return $value;
        }

        if (strpos($value, ':') === 0) {
            if (strpos($value, ' ') !== false){
                $value = explode(' ', $value)[0];
            }
            return $value;
        }

        $value = addcslashes($value, '"\'`()');

        return '"' . $value . '"';
    }

    /**
     * 转换字段名
     * @param $value
     * @return null|string|string[]
     */
    private function parseField($value) {
        if (empty($value) || trim($value) == '*') {
            return $value;
        }

        $value = trim(addcslashes($value, '"\'`'));

        if (strpos($value, '.') === false) {
            if (strpos($value, '(') !== false) {
                return preg_replace('/(?:[(\s]+)([\w_]+?)(?:[(\s]+)/i', '`\\1`', $value);
            }
            return "`{$value}`";
        } else {
            if (strpos($value, '(') !== false) {
                return preg_replace_callback('/(?:[(\s]+)[a-zA-Z_](?>\w*)(?:[\.][0-9a-zA-Z_](?>\w*))/i', function ($m) {
                    if (strpos($m[0], '.') === false) {
                        return '`' . $m[0] . '`';
                    }
                    $m[1] = explode('.', $m[0]);
                    $m[1][1] = '`' . $m[1][1] . '`';
                    return implode('.', $m[1]);
                }, $value);
            }
            return preg_replace_callback('/[a-zA-Z_](?>\w*)(?:[\.][0-9a-zA-Z_](?>\w*))/i', function ($m) {
                if (strpos($m[0], '.') === false) {
                    return '`' . $m[0] . '`';
                }
                $m[1] = explode('.', $m[0]);
                $m[1][1] = '`' . $m[1][1] . '`';
                return implode('.', $m[1]);
            }, $value);
        }
    }

    /**
     * 转换多条field
     * @param $value
     * @return array
     */
    private function parseFieldStr($value) {
        if (!is_array($value)) {
            if (strpos($value, ',') === false) {
                return [$this->parseField($value)];

            }
            $value = explode(',',$value);
        }

        foreach ($value as &$field) {
            $field = $this->parseField($field);
        }

        return $value;
    }

    private function humpToLine($str){
        $str = lcfirst($str);
        if (preg_match('/([A-Z]{1})/',$str)){
            $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                return '_'.strtolower($matches[0]);
            },$str);
        }
        return $str;
    }

    private function method_table($class)
    {
        $table = explode('\\', $class);
        $table = $this->humpToLine(end($table));
        if (false !== ($config = \Env::get("db_prifx"))) {
            $table = $config . $table;
        }
        return $table;
    }

    private function db_method()
    {
        switch (strtolower(\Env::get('db_connection'))) {
            case 'we7':
                return new \yang\model\connection\We7Connection();
                break;
            case 'pdo':
                return new \yang\model\connection\PdoConnection();
                break;
            default:
                $dbmethod = "\\yang\\models\\connection\\" . ucfirst(\Env::get('db_connection')) . "Connection";
                return new $dbmethod();
        }
    }

    private function all_field()
    {
        $filed = \yang\Cache::get('db.' . $this->sqlcom['basetable'] . '_filed');
        if (empty($filed) || $filed === false) {
            $filed = $this->db->getAllField($this->sqlcom['table']);
            \yang\Cache::set('db.' . $this->sqlcom['basetable'] . '_filed', $filed);
        }
        $this->cacheFiled = $filed;
    }

    protected function auto_func()
    {
        foreach (get_class_methods($this) as $v) {
            if (preg_match('/get([\w]*)Attr/', $v, $match)) {
                $this->getFunc[] = $match[1];
            }
            if (preg_match('/set([\w]*)Attr/', $v, $match)) {
                $this->setFunc[] = $match[1];
            }
        }
    }

    private function autotime(&$data)
    {
        if ($this->auto_time) {
            if ($this->is_insert && $this->is_update === false && !empty($this->auto_time_c)) {
                $data[$this->auto_time_c] = time();
            } elseif ($this->is_insert && $this->is_update && !empty($this->auto_time_u)) {
                $data[$this->auto_time_u] = time();
            }
        }
    }

    private function auto_type(&$value, $in = false)
    {
        if (is_array($value)) {
            foreach ($value as $key => &$val) {
                if (isset($this->type[$key])) {
                    if (strpos($val, ':') === false) {
                        if ($in) {
                            $val = $this->auto_type_insert_switch($this->type[$key], $val);
                        } else {
                            $val = $this->auto_type_switch($this->type[$key], $val);
                        }
                    }
                } else {
                    if ($this->is_insert || $this->is_update) {
                        $val = $this->parseQuot($val);
                    }
                }
            }
        }
    }

    private function auto_type_insert_switch($type, $value = '')
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'boolean':
                return (boolean)$value;
            case 'object':
                return json_encode($value);
            case 'json':
                return json_encode($value);
            case 'timestamp':
                return strtotime($value);
            case 'datetime':
                return ctype_digit($value) ? date('Y-m-d H:i:s', $value) : $value;
        }
    }

    private function run_auto(&$data, $type = 'get')
    {
        if (!empty($this->type)) {
            $this->auto_type($data);
        }

        if ($type == 'get') {
            if (!empty($this->getFunc)) {
                foreach ($this->getFunc as $val) {
                    $k = $this->humpToLine($val);
                    $v = isset($data[$k]) ? $data[$k] : '';
                    $data[$k] = call_user_func([$this, "get" . $val . "Attr"], $v, $data);
                }
            }
        } else {
            if (!empty($this->setFunc)) {
                foreach ($this->setFunc as $val) {
                    $k = $this->humpToLine($val);
                    $v = isset($data[$k]) ? $data[$k] : '';
                    $data[$k] = call_user_func([$this, "set" . $val . "Attr"], $v, $data);
                }
            }
        }
    }

    private function auto_type_switch($type, $value = '')
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'boolean':
                return (boolean)$value;
            case 'object':
                return json_decode($value);
            case 'json':
                return json_decode($value, true);
            case 'timestamp':
                return date($this->dateFormat, $value);
            case 'datetime':
                return date('Y-m-d H:i:s', $value);
        }
    }

    private function cached($data) {
        if ($this->is_cache) {
            \yang\Cache::set('db.' . $this->sqlcom['basetable'] . '_' . $this->sqlcom['cache_flag'], $data);
        }
    }

    private function delcache() {
        \yang\Cache::del('db.' . $this->sqlcom['basetable'] . '_' . $this->sqlcom['cache_flag']);
    }

    protected function getLastsql() {
        return $this->db->getLastsql();
    }

    private function clear() {
        $this->sqlcom = [
            'where' => '',
            'limit' => '',
            'order' => '',
            'field' => '*',
            'basetable' => $this->sqlcom['basetable'],
            'table' => $this->sqlcom['table'],
            'cache_flag' => '',
            'insert_type' => ' VALUES ',
            'convert' => [],
        ];
    }
}