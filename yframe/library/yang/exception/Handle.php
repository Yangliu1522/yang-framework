<?php

namespace yang\exception;

class Handle
{

    public $data = [
        'message' => '',
        'info' => '',
        'line' => '',
        'trace' => '',
        'file' => '',
        'trace_string' => '',
        'data' => [],
        'file_content' => '',
    ];

    public function __construct($exption)
    {
        if (\yang\Common::$app_debug) {
            $this->data['message'] = $exption->getMessage();
            $this->data['info'] = get_class($exption);
            $this->data['line'] = $exption->getLine();
            // var_dump($this->data['trace']);
            $this->data['file'] = $exption->getFile();
            $this->data['trace_string'] = $this->data['message'] . "\n {$this->data['file']} ({$this->data['line']})\n" . $exption->getTraceAsString();
            $info = [
                'class' => $this->data['info'],
                'args' => [],
                'function' => '',
                'file' => $this->data['file'],
                'line' => $this->data['line']
            ];
            $trace = $exption->getTrace();
            array_unshift($trace, $info);
            $this->data['trace'] = $trace;
            $this->data['data'] = [
                ['name' => "GET DATA", 'data' => $_GET],
                ['name' => "POST DATA", 'data' => $_POST],
                ['name' => "Files", 'data' => $_FILES],
                ['name' => "Cookie", 'data' => $_COOKIE],
                // ['name' => "Session", 'data' => $_SESSION],
                ['name' => "Server/Request Data", 'data' => $_SERVER],
                ['name' => "Environment Variables", 'data' => $_ENV],
            ];
        } else {
            $this->data['message'] = $exption->getMessage();
            $file = $exption->getFile();
            $line = $exption->getLine();
            $this->data['trace_string'] = $this->data['message'] . "\n {$file}({$line}) \n" . $exption->getTraceAsString();
            $this->data['info'] = str_replace(__NAMESPACE__ . '\\', '', get_class($exption));
        }
        return $this;
    }

    public function render()
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        $this->path = \yang\Common::path2url(\yang\Env::get('root_path'));

        $this->show = \yang\Common::$app_debug;
        fastcgi_finish_request();
        include \yang\Env::get('root_path') . "tpl" . DIRECTORY_SEPARATOR . "exception.php";
        exit();
    }

    public function convertCode($file, $line)
    {
        if (file_exists($file)) {
            $temp = '';
            $han = file($file);
            if ($line > 15) {
                for ($i = $line - 10; $i < $line + 10; $i++) {
                    if (isset($han[$i - 1])) {
                        $temp .= $han[$i - 1];
                    }
                }
            } else {
                for ($i = 1; $i <= $line; $i++) {
                    if (isset($han[$i - 1])) {
                        $temp .= $han[$i - 1];
                    }
                }
            }
            return htmlspecialchars($temp);
        }
    }

    public function convertArray($data)
    {
        $data = var_export($data, true);
        return str_replace(PHP_EOL, '<br>', $data);
    }
}