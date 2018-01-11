<?php
/**
 * Created by PhpStorm.
 * User: 13109
 * Date: 2017/10/17
 * Time: 15:44
 */
namespace App\Library;

class Api_OrderVerify{
    public $orderId;
    public $Api_Sms;
    public $clerkId;
    private $phone = '15911102320';

    public function __construct($orderId,$clerkId,$Api_Sms = [])
    {
        $this->orderId = $orderId;
        $this->clerkId = $clerkId;
        $this->Api_Sms = $Api_Sms;
    }

    //通过订单
    public function success()
    {

        //(new \App\Library\Api_Sms())->send($this->phone,[$this->orderId],'verifyOrder');

        if (!is_null($clientId = \App\Helpers::uidToClientId($this->clerkId))) {
            $msg = [
                'type' => 'managerVerifyOk',
                'data' => [
                    'orderId' => $this->orderId
                ]
            ];

            \App\Helpers::sendMsg($clientId, $msg);
        }
    }

    //驳回订单
    public function fail()
    {
//        dispatch(new \App\Jobs\Order\OrderCount('orderSubmit'));//统计驳回订单个数

        if (!is_null($clientId = \App\Helpers::uidToClientId($this->clerkId))) {
            $msg = [
                'type' => 'managerVerifyFail',
                'data' => [
                    'orderId' => $this->orderId
                ]
            ];

            \App\Helpers::sendMsg($clientId, $msg);

        }
    }
}