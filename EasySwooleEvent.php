<?php


namespace EasySwoole\EasySwoole;
require_once "./common.php";

use App\Pool\RedisPool;
use App\WebSocket\WebSocketParser;
use App\WebSocket\WsPushType;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Pool\Manager;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\Socket\Dispatcher;
use EasySwoole\Socket\Exception\Exception;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        ini_set('default_socket_timeout', -1);

        go(function () {
            $config      = new \EasySwoole\Pool\Config();
            $redisConfig = new RedisConfig(Config::getInstance()->getConf('REDIS'));
            $redis_pool  = new RedisPool($config, $redisConfig);

            Manager::getInstance()->register($redis_pool, RedisPool::kRedisServer);


        });

        try {
            // 创建一个 Dispatcher 配置
            $conf = new \EasySwoole\Socket\Config();
            // 设置 Dispatcher 为 WebSocket 模式
            $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);

            // 设置解析器对象
            $conf->setParser(new WebSocketParser());
            // 创建 Dispatcher 对象 并注入 config 对象
            $dispatch = new Dispatcher($conf);

        } catch (Exception $e) {
            Logger::getInstance()->error($e);
        } catch (\Exception $e) {
            Logger::getInstance()->error($e);
        }
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });
        // 监听链接事件
        $register->set(EventRegister::onOpen, function (\swoole_websocket_server $server, \swoole_http_request $req) {
            resp($req->fd,WsPushType::Log,['content'=>'服务器连接成功']);

            /** @var RedisPool $redis_pool */
            $redis_pool = Manager::getInstance()->get(RedisPool::kRedisServer);
            $redis_pool->invoke(function (Redis $redis)use ($req){
                $redis->hset('users',$req->fd,'游客');
                foreach ($redis->hGetAll('users') as $fd=>$item) {
                    resp((int)$fd,WsPushType::TypeBroadcastMessages,['user'=>$item,'msg'=>date("Y-m-d",time()).'加入']);
                }
            });

        });

        // 监听关闭事件
        $register->set(EventRegister::onClose, function (\swoole_websocket_server $server, $fd) {
            /** @var RedisPool $redis_pool */
            $redis_pool = Manager::getInstance()->get(RedisPool::kRedisServer);
            $redis_pool->invoke(function ($redis)use ($fd){

                $redis->hDel('users',$fd);
                foreach ($redis->hGetAll('users') as $fd=>$item) {
                    resp((int)$fd,WsPushType::TypeBroadcastMessages,['user'=>$item,'msg'=>date("Y-m-d",time()).'已离开']);
                }
            });
        });

    }
}