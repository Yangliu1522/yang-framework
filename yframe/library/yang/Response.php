<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-14
 * Time: 下午4:46
 */

namespace yang;


class Response {
    private static $inter;
    public static function create($content, $code = '200', $headers = [], $options = []) {
        if (empty(self::$inter)) {
            self::$inter = new static();
        }
        self::$inter->start($content, $code, $headers, $options)
    }

    public function start($content, $code = '200', $headers = [], $options = []) {
        $this->content = $content;
        $this->code    = $code;
        $this->headers = $headers;
        $this->options = $options;
    }

    private function convert($content) {

        switch ($this->gettype($content)) {
            case 'array':
                if (Env::get('use_json')) {
                    return json_encode($content);
                }
            case 'object':
                $content = get_object_vars($content);

        }
    }

    private function gettype($var)
    {
        if (is_array($var)) return "array";
        if (is_bool($var)) return "boolean";
        if (is_float($var)) return "float";
        if (is_int($var)) return "integer";
        if (is_null($var)) return "NULL";
        if (is_numeric($var)) return "numeric";
        if (is_object($var)) return "object";
        if (is_resource($var)) return "resource";
        if (is_string($var)) return "string";
        return "unknown type";
    }


}