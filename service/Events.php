<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        try {
            //$rsaAuth = new \App\Library\Api_Auth;
            // 向当前client_id发送数据
            $res = Gateway::sendToClient($client_id, json_encode(['type' => 'welcome', 'data' => $client_id]));
            echo $client_id, ' --- ', '推送clientId:', ($res ? '成功' : '失败'), ' -- 当前时间:', date('Y-m-d H:i:s'), "\r\n";
            // 向所有人发送
            // Gateway::sendToAll("$client_id login\n");
        } catch (\Exception $e) {
            \App\Helpers::errLog($e);
        }
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($clientId, $message)
    {

        try {
            $param = json_decode($message);
            echo $message,"\r\n";
            if(!isset($param->type))
                throw new \Exception('提交参数错误',10);
            $db = app('rsaAuth');
            $userId = $db->userId($param->data);
            echo "\r\n";
            echo $userId,"\r\n";
            0 < $userId && \App\Helpers::clientBindUid($userId,$clientId);

        } catch (\Exception $e) {
            \App\Helpers::errLog($e, compact('clientId', 'message'));
        }
        // 向所有人发送
//        echo $client_id, '  to message  = ', $message, "\r\n";
//        Gateway::sendToAll("$client_id said $message");


    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        try {
            echo $client_id,"离开了";
            // 向所有人发送
            // GateWay::sendToAll("$client_id logout");
        } catch (\Exception $e) {
        }
    }
}
