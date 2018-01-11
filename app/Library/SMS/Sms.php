<?php

namespace App\Library\SMS;

include __DIR__.'/Ucpaas.class.php';

class Sms {
    private $options = [
        'accountsid' => '6a2c4918257a9aca5b1705ca3d5474af',
        'token' => 'dbee0ebc7fdc27476cf2d38d3730301e'
    ];
    private $appId = '843e50704b9d45529ecb002ff35534e2';
    private $messageTemplates = [
        'verifyCode'=>'179131',
        'verifyOrder'=>'179132',
        'verifyReg'=>'181215',
        'sendDeliverOrderForDay'=>'238456',
        'sendDeliverOrderForMonth'=>'238454',
    ];
    
    public $to;
    public $messageType;
    public $params;
    public function __construct($to,$messageType,$params) {
        $this->to = $to;
        $this->messageType = $messageType;
        $this->params = $params;
    }
    public function sendMessage() {
        try {
            $ucpass = new \Ucpaas($this->options);
            $result = $ucpass->templateSMS($this->appId, $this->to, $this->messageTemplates[$this->messageType], $this->params);
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

}
