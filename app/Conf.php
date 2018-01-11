<?php

namespace App;

class Conf
{
    const AppId = 'wxf655f522a2d58d73';
    const AppSecret = '47504fe44b6ed2040fe4ca19e310679f';
    const AppToken = '13f1dsa23fd41sa5vcxz13cc23fakjug';
    const payUrl = 'http://app-pay.rekoon.cn/';
    const reflectUrl = 'http://app-pay.rekoon.cn/pay/reflect';
    const aLiImeiCode = '642444bcc1f9460482721d27e97c74bd';
    const aLiImeiHost = 'http://coverage.market.alicloudapi.com';
    const aLiImeiPath = '/apple/coverage';
    const donwLoadAppPage = 'https://fir.im/rekoonag';
    const imgUrl = 'http://ow3i63zts.bkt.clouddn.com/%s';
    const phoneImgUrl = 'http://ow3i63zts.bkt.clouddn.com/%s';
    const weChatCallback = 'https://api.rekoon.cn/wx/callback';

    const orderSubmitCount = 'ld_order_submit_count';//已提交订单个数
    const todayOrderSubmitCount = 'ld_today_order_fail_count';//今天已提交订单个数
    const orderFailCount = 'ld_order_submit_count';//已驳回订单个数
    const todayOrderFailCount = 'ld_today_order_submit_count';//今天已驳回订单个
    const orderPaymentCount = 'ld_order_submit_count';//已支付订单个数
    const todayOrderPaymentCount = 'ld_today_order_submit_count';//今天已支付订单个数
    const orderStoreCount = 'ld_order_submit_count';//今天一次入库订单个数
    const todayOrderStoreCount = 'ld_today_order_submit_count';//今天一次入库订单个数

    public static $defaultImei = 888;

    //微信管家用的
    public static function getGJWxConf()
    {
        return [
            'app_id' => 'wxef8118dbc45119dc',         // AppID
            'secret' => '0f262cb422875c4f42b8906c719ee322',     // AppSecret
            'token' => 'Ondq8HQyFwSMWRnjC6QGk3B2CrQwz1S',          // Token
        ];
    }

    //公司支付用的  商城用的
    public static function getNewWxConf()
    {
        return [
            'app_id' => 'wx44a679dc5a379123',         // AppID
            'secret' => '3ec949b0f2820a8f048596ca97590c48',     // AppSecret
            'token' => 'Ondq8HQyFwSMWRnjC6QGk3B2CrQwz1S',          // Token
        ];
    }

    public static function getWxConf()
    {
        return [
            /**
             * Debug 模式，bool 值：true/false
             *
             * 当值为 false 时，所有的日志都不会记录
             */
            'debug' => true,
            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id' => self::AppId,         // AppID
            'secret' => self::AppSecret,     // AppSecret
            'token' => self::AppToken,          // Token
            'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！
            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
             * file：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'level' => 'debug',
                'permission' => 0777,
                'file' => '/tmp/easywechat.log',
            ],
            /**
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址
             */
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/examples/oauth_callback.php',
            ],
            /**
             * 微信支付
             */
            'payment' => [
                'merchant_id' => 'your-mch-id',
                'key' => 'key-for-signature',
                'cert_path' => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                'key_path' => 'path/to/your/key',      // XXX: 绝对路径！！！！
                // 'device_info'     => '013467007045764',
                // 'sub_app_id'      => '',
                // 'sub_merchant_id' => '',
                // ...
            ],
            /**
             * Guzzle 全局设置
             *
             * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
             */
            'guzzle' => [
                'timeout' => 3.0, // 超时时间（秒）
                //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
            ],
        ];
    }

    public static function getOrderConf()
    {
        return
            [
                'order_verify_fail'=>-3,//审核失败
                'order_user_submit'=>-2,//用户提交
                'order_cleen' => -1,//取消订单
                'order_clerk_pre'=>0,//已下单待提交审核
                'order_will_submit' => 1,//审核中
                'order_submit' => 2,//待付款
                'order_payment' => 3,//待发货
                'order_deliver' => 4,//配送中
                'order_storage_tow' => 5,//二次入库
                'order_will_storage' => 6,//未入库
                'order_review' => 7,//质检中
//            'order_review_fail' => 8,//验货失败
                'order_review_success' => 9,//质检完成
                'order_outgoing_tow' => 10,//二次出库
                'order_storage_one'=>11,//一次入库，
                'order_outgoing_one'=>12,//一次出库，
                'order_finish'=>13,//交易完成
            ];
    }

    public static function getOrderConfName()
    {
        return [
            '-3'=>'被驳回',
            '-2'=>'用户提交',
            '-1' => '订单取消',
            '0'=>'提交订单',
            '1' => '审核中',
            '2' => '待付款',
            '3' => '待发货',
            '4' => '发货中',
            '6' => '未入库',
            '11' => '一次入库',
            '12' => '一次出库',
            '7' => '质检中',
            '8'=>'质检失败',
            '9' => '质检完成',
            '5' => '二次入库',
            '10' => '二次出库',
            '13' => '交易完成',
        ];
    }


    public static function getMinPrice()
    {
        return 5;
    }

    public static function getQuotedClassConf()
    {
        return [
            'color'=>[
                "Jet Black"=>['id'=>60,'rename'=>'yanse'],
                "Rose Gold"=>['id'=>57,'rename'=>'yanse'],
                "Space Gray"=>['id'=>58,'rename'=>'yanse'],
                "Gray"=>['id'=>58,'rename'=>'yanse'],
                "Black"=>['id'=>58,'rename'=>'yanse'],
                "White"=>['id'=>56,'rename'=>'yanse'],
                "Gold"=>['id'=>55,'rename'=>'yanse'],
                "Silver"=>['id'=>56,'rename'=>'yanse'],
                "Blue"=>['id'=>61,'rename'=>'yanse'],
                "Pink"=>['id'=>57,'rename'=>'yanse'],
                "Green"=>['id'=>62,'rename'=>'yanse'],
                "Yellow"=>['id'=>63,'rename'=>'yanse'],
                "Red"=>['id'=>59,'rename'=>'yanse'],
            ],
            'capacity'=>[
                '8GB'=>['id'=>49,'rename'=>'rongliang'],
                '16GB'=>['id'=>50,'rename'=>'rongliang'],
                '32GB'=>['id'=>52,'rename'=>'rongliang'],
                '64GB'=>['id'=>51,'rename'=>'rongliang'],
                '128GB'=>['id'=>53,'rename'=>'rongliang'],
                '256GB'=>['id'=>54,'rename'=>'rongliang'],
            ],
            'daysleft'=>[
                'is_true'=>['id'=>96,'rename'=>'baoxiu'],
                'is_false'=>['id'=>97,'rename'=>'baoxiu'],
            ],

        ];
    }
}
