<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午7:05
 */

return [

    /** 漂亮的注释, 公共配置文件 **/
    'env' => [
        'app_debug' => true,
        'app_name' => 'app',
        'module_name' => 'index',
        'controller_name' => 'index',
        'action_name' => 'index',
        'cache_life'  => 600,
        'use_json'    => true,
    ],
    'config' => [
        'url_type'    => 'query',
        'url_parse'   => [
            'query'   => 'do',
            'url_esp' => '.'
        ]
    ]
];