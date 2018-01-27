<?php

if (!function_exists('fastcgi_finish_request')) {
    function fastcgi_finish_request()
    {
        \yang\Common::fastcgi_finish_request();
    }
}

if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL)
    {
        return \yang\Common::http_response_code($code);
    }
}

if (!function_exists('dump')) {
    function dump() {
        call_user_func_array('\yang\Common::dump', func_get_args());
    }
}