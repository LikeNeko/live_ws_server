<?php
/**
 * Created by PhpStorm.
 * User: Apple
 * Date: 2018/11/1 0001
 * Time: 14:42
 */

namespace App\WebSocket;

use App\Pool\RedisPool;
use co;
use Co\MySQL;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\EasySwooleEvent;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Pool\Manager;
use EasySwoole\Redis\Redis;
use EasySwoole\Socket\AbstractInterface\Controller;
use EasySwoole\Socket\Client\WebSocket;
use Swoole\Coroutine;
use Swoole\Exception;

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class Index extends Controller
{
    public function index()
    {
        $this->response()->setMessage('json error');
    }

    public function ping()
    {
        $this->response()->setMessage(['pong' => "pong"]);
    }

    public function star()
    {
        $user = $this->caller()->getArgs()['user'];
        $type = $this->caller()->getArgs()['type'];
        resp_all(WsPushType::TypeStar,[
            'user' => $user,
            'type' => $type,
        ]);
        $this->response()->setMessage('ok');
    }

    public function send()
    {
        $user = $this->caller()->getArgs()['user'];
        $msg  = $this->caller()->getArgs()['msg'];
        $fd   = $this->caller()->getClient()->getFd();
        /** @var RedisPool $redis_pool */
        $redis_pool = Manager::getInstance()->get(RedisPool::kRedisServer);

        $redis_pool->invoke(function (Redis $redis)
        use ($user, $msg, $fd) {
            $redis->hSet('users', $fd, $user);
            foreach ($redis->hGetAll('users') as $fd => $item) {
                resp((int)$fd, WsPushType::TypeBroadcastMessages, [
                    'user' => $user,
                    'msg'  => $msg,
                    'time' => date('Y-m-d H:i:s')
                ]);
            }
        });
        $this->response()->setMessage('ok');
    }
}