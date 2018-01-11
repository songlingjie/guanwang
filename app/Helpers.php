<?php
/**
 * Created by PhpStorm.
 * User: 13109
 * Date: 2017/9/16
 * Time: 14:59
 */

namespace App;

use GatewayWorker\Lib\Gateway;
use Illuminate\Http\Request;
use Workerman\Lib\Timer;

class Helpers
{
    const CacheUserOrderDistinct = 'ld_user_order_distinct_%d_info';//用户订单提交去重
    const CacheUserCashDistinct = 'ld_user_cash_distinct_%d_info';//用户提现提交去重
    const CacheUserFiveCode = 'ld_user_cash_five_%d_code';//订单五位码
    const CacheTestInfoList = 'ld_test_list_%d_info';//
    const RdbUserToClientId = 'rdb_userId_to_clientId';//用户id对应的clientId
    const CacheAuCode = 'ld_cache_au_code';//店员授权码
    const payApiUrl = 'http://app-pay.rekoon.cn/%s';
    const QuotedHash = 'ld_quoted_hasn';
    const QoutedClassInfo = 'ld_qouted_class_info_hash';
    const WeChatOpenIdUrlCache = 'ld_wechat_opid_cache';
    const QoutedImgHash = 'ld_qouted_img_hash';

    /**
     * 用户提交订单去重
     */
    public static function putOrderPostDistinct($userId)
    {
        $cache = app('rdb');
        return $cache->set(sprintf(self::CacheUserOrderDistinct, $userId), $userId, 30);
    }

    public static function getOrderPostDistinct($userId)
    {
        $cache = app('rdb');

        return $cache->exists(sprintf(self::CacheUserOrderDistinct, $userId));
    }

    /**
     * 缓存查询商品图片
     */
    public static function QoutedImg($id)
    {
        $rdb = app('rdb');
        if($img = $rdb->hGet(self::QoutedImgHash,$id))
            return $img;
        $db = app('db');
        if(!is_null($quoted = $db->table('quoted')->where(compact('id'))->first()) && !empty($quoted->image)){
            $rdb->hSet(self::QoutedImgHash,$id,$quoted->image);
            return $quoted->image;
        }
    }

    /**
     * 用户提现提交去重
     */
    public static function putCashPostDistinct($userId)
    {
        $cache = app('rdb');
        $key = sprintf(self::CacheUserCashDistinct, $userId);
        return $cache->set($key, $userId, 30);
    }

    public static function getCashPostDistinct($userId)
    {
        $cache = app('rdb');
        return $cache->exists(sprintf(self::CacheUserCashDistinct, $userId));
    }


    /**
     * 价格格式化
     */
    public static function priceFormat($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * 生成订单号
     * 更具payId生成
     */
    public static function getOrderSn($pay_id)
    {
        return (date('y', time()) % 9 + 1) . sprintf('%013d', $pay_id) . sprintf('%02d', 1);
    }

    /**
     * 生成五位码
     */
    public static function getFiveCode($user_id)
    {
        $code = rand(10000, 99999);
        $cache = app('rdb');

        $data = $cache->smembers(sprintf(self::CacheUserFiveCode, $user_id));

        while (in_array($code, $data)) {
            $code = rand(10000, 99999);
        }

        $cache->sadd($code, $data);
        return $code;
    }

    /**
     * 删除五位码
     */

    public static function delFiveCode($user_id, $code)
    {
        $cache = app('rdb');
        $cache->srem(sprintf(self::CacheUserFiveCode, $user_id), $code);
        return true;
    }

    /**
     * 将客户端检测信息缓存存入缓存
     */

    public static function putTestInfoForCache($data)
    {
        $code = rand(1, 99999);
        $cache = app('cache');

        $key = sprintf(self::CacheTestInfoList, $code);

        while ($cache->has($key)) {
            $code = rand(1, 99999);
            $key = sprintf(self::CacheTestInfoList, $code);
        }
        $cache->add($key, $data, 30);
        return $code;
    }


    /**
     * 根据imei返回不需要检测的数据
     */
    public static function imeiFilter($info, $type)
    {
        if ($type != 'Apple')
            return [];

        $data = [];
        $conf = \App\Conf::getQuotedClassConf();

        if (isset($info->color))
            $data[$conf['color'][$info->color]['rename']] = $conf['color'][$info->color]['id'];

        if (isset($info->daysleft)) {
            if ($info->daysleft > 30)
                $data[$conf['daysleft']['is_true']['rename']] = $conf['daysleft']['is_true']['id'];
            else
                $data[$conf['daysleft']['is_false']['rename']] = $conf['daysleft']['is_false']['id'];
        }

        if (isset($info->capacity))
            $data[$conf['capacity'][$info->capacity]['rename']] = $conf['capacity'][$info->capacity]['id'];

        return $data;
    }

    /**
     * 根据imei接口返回imei数据
     */
    public static function imeiApi($imei, $type)
    {

        if (trim($type) != 'Apple')
            return [];

        $imei = self::trim($imei);

        if (!is_numeric($imei) || strlen($imei) != 15)
            throw new \Exception('imei不正确', -12);

        $headers = [sprintf('Authorization:APPCODE %s', \App\Conf::aLiImeiCode)];

        $url = sprintf('%s%s?sn=%s', \App\Conf::aLiImeiHost, \App\Conf::aLiImeiPath, $imei);

        $res = self::getHttpResponseGET($url, $headers, \App\Conf::aLiImeiHost);
        if ($res) {
            $info = json_decode($res);
            return $info->code == 0 ? $info : null;
        } else {
            return null;
        }
    }

    /**
     * 去掉文本内容的所有的空格
     */
    public static function trim($str)
    {
        return str_replace(" ", "", $str);
    }

    /**
     * 获取用户信息
     */
    public static function userCache($id)
    {
        $db = app('db');
        if (is_null($user = $db->table('shop_member')->where(compact('id'))->first()))
            throw new \Exception('用户不存在' . $id, -10);
        return $user;
    }

    /**
     * 生成检测码
     */
    public static function createTestNumber($userId, $type)
    {
        //$code = rand(100000,999999);
        $cache = app('rdb');
        do {
            $code = rand(10000000, 99999999);
        } while ($cache->hGet(self::CacheAuCode, $code));
        $cache->hSet(self::CacheAuCode, $code, json_encode(['userId' => $userId, 'type' => $type]));
        return $code;
    }

    /**
     * 开始算价
     */
    public static function math($data)
    {
        $res = json_decode($data['testInfo']);

        throw new \Exception(json_encode($res));
    }

    public static function payApi($path, $data)
    {
        if ('phone/math' === $path && isset($data['type']) && 'test' === $data['type'])
            return self::math($data);
        $url = sprintf(self::payApiUrl, $path);
        unset($data['created_at']);
        $data['salt'] = time();
        $para = self::buildRequestPara($data);
        $token = md5($para . $data['salt']);
        $data['token'] = $token;
        $query = http_build_query($data);
        $options['http'] = array(
            'timeout' => 60,
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $query
        );
        $context = stream_context_create($options);
        if (is_null($res = json_decode(file_get_contents($url, false, $context))))
            throw new \Exception('远程接口出错', -10);


        $db = app('db');

        $db->table('browse_payapi_log')->insert([
            'params' => json_encode($data),
            'result' => json_encode($res),
            'path' => $path,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if (0 > $res->code)
            throw new \Exception(isset($res->errMsg) ? $res->errMsg : '未知错误', $res->code);

        return 0 == $res->code ? $res->data : $res;

    }

    /**
     * #延迟发送消息
     * @param int $time
     * @param string $clientId
     * @param array $msg
     * @param bool $persistent
     */
    public static function addTimes($time, $clientId, $msg, $persistent = false)
    {
        return Timer::add($time, ['\GatewayWorker\Lib\Gateway', 'sendToClient'], [$clientId, json_encode($msg)], $persistent);
    }

    /**
     * 使用检测码
     */
    public static function useTestNumber($number)
    {
        $rdb = app('rdb');

        if (is_null($auCode = json_decode($rdb->hGet(self::CacheAuCode, $number))))
            throw new \Exception('检测码错误', -10);
        //$rdb->hDel(self::CacheAuCode,$number);
        return $auCode;


//        $data = \GuzzleHttp\json_decode($rdb->hget(self::CacheAuCode,$number));
//        $data['state'] = 2;
//        $data['startTime'] = time();
//        $rdb->hSet(self::CacheAuCode,$number,json_encode($data));
    }

    /**
     * 判断检测码是否可否可用
     */
    public static function isTestNumber($number)
    {
        $rdb = app('rdb');
        $data = \GuzzleHttp\json_decode($rdb->hget(self::CacheAuCode, $number));
        if ($data['state'] == 1)
            return true;
        else
            return false;
    }

    /**
     * 删除检测码
     */

    public static function delTestNumber($number)
    {
        app('rdb')->hDel(self::CacheAuCode, $number);
    }

    public static function getTestNumber($number)
    {
        return app('rdb')->hGet(self::CacheAuCode, $number);
    }

    /**
     * 读取客户端检测信息缓存
     */
    public static function getTestInfoForCache($id)
    {
        return app('cache')->get(sprintf(self::CacheTestInfoList, $id));
    }

    /**
     * 删除客户端检测信息缓存
     */
    public static function delTestInfoForCache($id)
    {
        return app('cache')->pull(sprintf(self::CacheTestInfoList, $id));
    }

    /**
     * 是否是手机号
     */
    public static function isMobile($mobile)
    {
        return is_numeric($mobile) && 11 === mb_strlen($mobile) && preg_match("/^1[34578]{1}\d{9}$/", $mobile);
    }

    /**
     *  发送消息通知用户端
     */
    public static function sendMsg($clientId, array $msg)
    {
        Gateway::sendToClient($clientId, json_encode($msg));
    }

    //根据uid获取用户clientId
    public static function uidToClientId($uid)
    {
        $rdb = app('rdb');
        $clientId = $rdb->hGet(self::RdbUserToClientId, $uid);
        return $clientId && Gateway::isOnline($clientId) ? $clientId : null;
    }

    //客户端id绑定uid
    public static function clientBindUid($uid, $clientId)
    {
        $rdb = app('rdb');
        $rdb->hSet(self::RdbUserToClientId, $uid, $clientId);
    }

    /**
     * 记录错误日志
     */

    public static function errLog(\Exception $e, $request = null)
    {

        if ($request instanceof Request) {
            if ('OPTIONS' == $request->method())
                return false;

            $path = $request->path();
            $param = json_encode($request->all());
            $method = $request->method();
            $uid = $request->user();

        } else {

            $param = json_encode($request);

        }

        $errMsg = $e->getMessage();
        $errCode = $e->getCode();
        $errLine = $e->getLine();
        $errFile = $e->getFile();
        $data = json_encode($e);
        $created_at = date('Y-m-d H:i:s');

        app('db')->table('errLog')->insert(compact('param', 'errMsg', 'errCode', 'errLine', 'errFile', 'path', 'method', 'uid', 'created_at', 'data'));
    }

    /**
     * 获取图片地址
     */
    public static function qiNiuImagePath()
    {
    }

    /**
     * 生成支付单号
     * 两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)
     */
    public static function getOrderPaySn()
    {
        return sprintf('%s', date('YmdHi') . self::getIncrId());
    }

    public static function getIncrId()
    {
        $rdb = app('rdb');
        //$rdb->select(8);
        $keys = sprintf('zizengId_%s', date('i'));
        if (!$rdb->exists($keys)) {
            $rdb->set($keys, rand(100000, 500000), 60);
            return $rdb->incr($keys);
        }
        return $rdb->incr($keys);
    }


    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    public static function argSort($para)
    {
        $para = array_filter($para);
        ksort($para);
        reset($para);
        return $para;
    }


    public static function buildRequestPara($para_temp)
    {
        $para_sort = self::argSort($para_temp);

        $arg = "";
        while (list ($key, $val) = each($para_sort)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    public static function getHttpResponsePOST($url, $para)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para);
        $responseText = curl_exec($curl);
//			dd( curl_error($curl) );
        return $responseText;
    }

    public static function getHttpResponseGET($url, $headers = false, $host)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_URL, $url);
        if($headers){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $responseText = curl_exec($curl);
//			dd( curl_error($curl) );
        return $responseText;
    }

    /**
     * 生成随机密码
     * @param 密码长度
     * return 随机密码
     */
    public static function getRandPassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $password;
    }

    public static function getCommisdion($user, $manager, $price)
    {
        return [0, 0];
        $db = app('db');
        $commisdion = $db->table('setting')->where('name', 'commisdion')->value('value');
        $commisdion = \GuzzleHttp\json_decode($commisdion, true);
        $commisdion_user = $user->commisdion > 0 ?
            self::priceFormat($price * $user->commisdion / 1000) :
            self::priceFormat($price * $commisdion['member'] / 100);


        $commisdion_manger = $manager->commisdion > 0 ?
            self::priceFormat($price * $manager->commisdion / 1000) :
            self::priceFormat($price * $commisdion['manager'] / 100);

        return [$commisdion_user, $commisdion_manger];
    }

    public static function imgOcr($path)
    {
        //$path = base_path($path);
        //$path = sprintf('/data/www/app-v1.rekoon.cn/public/%s',$path);
        $str = file_get_contents('http://127.0.0.1:8000/opencv?path=' . $path);

        return $str;
        if (is_null($arr = json_decode($str)))
            return null;

        $arr = explode("\n", $arr->result);
        foreach ($arr as $num) {
            if ($num && 15 === strlen($num))
                return $num;
            if (false !== strpos($num, 'MUD:')) {
                return str_replace('MUD:', '', $num);
            }
        }
        return null;
    }

    public static function checkOrderState($orderId)
    {
        if ($enable = app('db')->table('order')->where('order_id', $orderId)->value('is_enable') == 0)
            throw new \Exception('此订单不可进行该操作', -10);
    }

    /**
     * 获取订单检测信息选中颜色
     */
    public static function getOrderColor($obj)
    {
        foreach ($obj as $v)
            if ($v->id == 12)
                foreach ($v->lists as $vv)
                    if ($vv->id == $v->cur)
                        return $vv->name;
        return '无';
    }

    /**
     * 获取报价单中常用信息
     * 报价单id
     */
    public static function getQuotedHash($id)
    {
        $rdb = app('rdb');
        if (($info = $rdb->hGet(self::QuotedHash, $id))) {
            return json_decode($info);
        } else {
            $info = app('db')->table('quoted')->where('id', $id)
                ->select(['name', 'name1', 'name2', 'edition', 'model', '4G', '3G', 'ram', 'image'])->first();
            $rdb->hSet(self::QuotedHash, $id, json_encode($info));
        }
    }

    /**
     * 设置订单加密token
     */
    public static function setOrderToken($data)
    {
        if (!isset($data['orderId']) || !isset($data['sign']))
            throw new \Exception('参数错误', -13);

        if (is_null($addtime = app('db')->table('order')->where('order_id', $data['orderId'])->where('sign', $data['sign'])->value('add_time')))
            throw new \Exception('网络错误', -14);

        $data = self::buildRequestPara($data);
        return md5($data . $addtime);
    }


    public static function checkOrderToken($data)
    {
        $selfToken = $data['token'];
        unset($data['token']);
        $token = self::setOrderToken($data);
        if ($selfToken == $token)
            return true;
        else
            return false;
    }

    /**
     * 获取url参数；
     */
    public static function getUrlParams($url)
    {
        $url = substr($url, strpos($url, '?') + 1);
        $arr = array();
        if (!empty($url)) {
            $paramsArr = explode('&', $url);

            foreach ($paramsArr as $k => $v) {
                $a = explode('=', $v);
                $arr[$a[0]] = $a[1];
            }
        }
        return $arr;
    }

    /**
     * 加密
     */
    public static function decrypt($data)
    {
        return decrypt($data);
    }

    public static function getImeiColor($color)
    {
        return str_replace(array("Jet Black", "Rose Gold", "Space Gray", "Gray", "Black", "White", "Gold", "Silver", "Blue", "Pink", "Green", "Yellow", "Red"),
            array("亮黑", "粉色/玫瑰金", "灰/黑", "灰/黑", "灰/黑", "银/白", "金", "银/白", "蓝色", "粉色/玫瑰金", "绿色", "黄色", "红"), $color);
    }

    public static function getZoushi($price)
    {
        $months = ['-11' => '1月', '-10' => '2月', '-9' => '3月', '-8' => '4月', '-7' => '5月', '-6' => '6月', '-5' => '7月',
            '-4' => '8月', '-3' => '9月', '-2' => '10月', '-1' => '11月', '0' => '12月',
            '1' => '1月', '2' => '2月', '3' => '3月', '4' => '4月', '5' => '5月', '6' => '6月',
            '7' => '7月', '8' => '8月', '9' => '9月', '10' => '10月', '11' => '11月', '12' => '12月','13'=>'1月'];
        $month = date('m');
        $base = intval($price * 15 / 100);
        $zoushi = [
            [
                $months[$month - 3],
                $months[$month - 2],
                $months[$month - 1],
                sprintf('%d月', $month),
                $months[$month + 1],
            ],
            [
                $price + $base * 3,
                $price + $base * 2,
                $price + $base,
                $price,
                $price - $base,
            ],
            [
                ['xAxis' => $months[$month - 3], 'yAxis' => $price + $base * 3],
                ['xAxis' => $months[$month - 2], 'yAxis' => $price + $base * 2],
                ['xAxis' => $months[$month - 1], 'yAxis' => $price + $base],
                ['xAxis' => sprintf('%d月', $month), 'yAxis' => $price],
                ['xAxis' => $months[$month + 1], 'yAxis' => $price - $base],
            ],
            $base,
        ];

        return $zoushi;
    }

    public static function getTestInfoValue($obj, $id)
    {
        foreach ($obj as $v)
            if ($v->id == $id)
                foreach ($v->lists as $vv)
                    if ($vv->id == $v->cur)
                        return $vv->name;
        return '无';
    }

    public static function getManagerCommis($price)
    {
        if (50 < $price && $price < 100) {
            return 5;
        }
        if ($price > 99) {
            return 10;
        }

        return 0;
    }

    public static function getMemberCommis($price)
    {
        if (50 < $price && $price <= 99)
            return 5;

        if (99 < $price && $price <= 499)
            return 15;

        if (499 < $price && $price <= 999)
            return 25;

        if (999 < $price)
            return 35;

        return 0;
    }
}