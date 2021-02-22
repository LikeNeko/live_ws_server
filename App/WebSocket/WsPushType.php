<?php

namespace App\WebSocket;

use EasySwoole\EasySwoole\ServerManager;

class  WsPushType
{
    const TypeBroadcastMessages = 'broadcast_messages';
    const TypeStar = 'star';
    const TypePing = 'ping';
    const Log ="log";
}


