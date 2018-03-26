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
    private $content = '',
    $code = 200,$is_json = false,
    $headers = [],
    $options = [];
    public static function create($content, $code = '200', $headers = [], $options = []) {
        if (empty(self::$inter)) {
            self::$inter = new static();
        }
        self::$inter->start($content, $code, $headers, $options);
        return self::$inter;
    }

    public function start($content, $code = '200', $headers = [], $options = []) {
        $this->content = $this->convert($content);
        $this->code    = $code;
        $this->headers = $headers;
        $this->options = $options;
    }

    public function send() {
        Common::http_response_code($this->code);
        if (!empty($this->headers)) {
            foreach ($this->headers as $key => $val) {
                header($key . ":" . $val);
            }
        }

        echo $this->content;
        Common::fastcgi_finish_request();
    }

    public function headers(array $header = array()){
        $this->header = array_merge($this->headers,$header);
        return $this;
    }

    public function contentType($type,$charset = ''){
        if ($this->is_json){
            $type = 'text/json';
        }
        $this->headers['Content-Type'] = $type;
        if (!empty($charset)) {
            $this->headers['Content-Type'] .= '; charset=' . $charset;
        }
        return $this;
    }

    private function convert($content) {

        switch ($this->gettype($content)) {
            case 'array':
                if (Env::get('use_json')) {
                    $this->contentType('text/json');
                    return json_encode($content);
                }
                return var_export($content, true);
            case 'object':
                $content = get_object_vars($content);
                return json_encode($content);
            case 'string':
            default:
                return $content;

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