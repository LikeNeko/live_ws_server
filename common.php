<?php

use App\Pool\RedisPool;
use App\WebSocket\WsPushType;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Pool\Manager;
use EasySwoole\Redis\Redis;

function go_redis(Closure $func, $timer = 0){
    go(function ()use ($func,$timer){
        try {
            $redis_pool = Manager::getInstance()->get(RedisPool::kRedisServer);
            $redis_pool->invoke($func,$timer);
        } catch (Exception $exception) {
        }
    });
}

function resp($fd, $type = WsPushType::TypePing, $data = [])
{
    $server = ServerManager::getInstance()->getSwooleServer();
    $server->push($fd, json_encode(['type' => $type, 'data' => $data], JSON_UNESCAPED_UNICODE));
}

function resp_all($type = WsPushType::TypePing, $data = []){
    /** @var RedisPool $redis_pool */
    $redis_pool = Manager::getInstance()->get(RedisPool::kRedisServer);
    $redis_pool->invoke(function (Redis $redis)use ($data,$type){
        foreach ($redis->hGetAll('users') as $fd => $item) {
            resp((int)$fd, $type,$data);
        }
    });
}