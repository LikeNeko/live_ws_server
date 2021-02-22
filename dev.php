<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SOCKET_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP| SWOOLE_SSL,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 2,// 服务器是几核建议是几
            'reload_async' => true,// 热重载
            'max_wait_time'=>3,
            'ssl_cert_file'         => './log_nekopoi_cn.pem',
            'ssl_key_file'          => './log_nekopoi_cn.key',
        ],
        'TASK'=>[
            'workerNum'=>4,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'REDIS'      => [
//        'host' => "192.168.0.101",
        'host' => "172.18.0.14",
        'auth' => '',
        'port' => '6379'
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null
];
