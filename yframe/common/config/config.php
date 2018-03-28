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
        'db_connection' => 'pdo',
        'db_prifx' => 'yf_'
    ],
    'config' => [
        'url_type'    => 'pathinfo',
        'url_parse'   => [
            'query'   => 'do',
            'url_esp' => '.'
        ],
        'database' => [
            'db_type' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_user' => 'root',
            'db_pwd'  => 'aa1522',
            'db_name' => 'chat',
            'db_charset' => 'utf8',
            'db_port'  => 3306,
        ]
    ]
];