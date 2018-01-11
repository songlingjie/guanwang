<?php

namespace App\Library;
/**
 * Class Api_Rsa
 * @package App\Library
 */
class Api_Rsa
{

    //$rsa->encrypt($str);//加密字符串
    //$rsa->decrypt($token);//解密字符串
    private $public_key;
    private $private_key;

    private function initPublicKey()
    {
        $pub = file_get_contents(__DIR__ . ('/rsa/public_key.pem'));
        $this->public_key = openssl_get_publickey($pub);

    }

    private function initPrivateKey()
    {
        $pri = file_get_contents(__DIR__ . ('/rsa/private_key.pem'));
        $this->private_key = openssl_get_privatekey($pri, 'Lhw19810616');
    }

    public function setParam($request)
    {
        $this->request = $request;
    }

    public function encrypt_data($data)
    {
        empty($this->public_key) && $this->initPublicKey();
        if (openssl_public_encrypt($data, $encrypted, $this->public_key))
            $data = $this->base_encode($encrypted);
        else
            throw new \Exception('Unable to encrypt data. Perhaps it is bigger than the key size?');
        return $data;
    }

    public function decrypt_data($data)
    {
        empty($this->private_key) && $this->initPrivateKey();
        if (openssl_private_decrypt($this->base_decode($data), $decrypted, $this->private_key))
            $data = $decrypted;
        else
            $data = '';
        return $data;
    }

    public function encryptToMd5($param)
    {
        unset($param['sign']);
        $prestr = md5($this->createLinkstring($param));
        $param['sign'] = $this->encrypt($prestr);
        return $param;
    }

    public function base_encode($str) {
        if(empty($str))
            return;
        $src  = array("/","+","=");
        $dist = array("_a","_b","_c");
        $old  = base64_encode($str);
        $new  = str_replace($src,$dist,$old);
        return $new;
    }

    public function base_decode($str) {
        if(empty($str))
            return;
        $src = array("_a","_b","_c");
        $dist  = array("/","+","=");
        $old  = str_replace($src,$dist,$str);
        $new = base64_decode($old);
        return $new;
    }

    public function encrypt($str)
    {
        $crypt_res = "";
        for ($i = 0; $i < ((strlen($str) - strlen($str) % 117) / 117 + 1); $i++) {
            $crypt_res = $crypt_res . ($this->encrypt_data(mb_strcut($str, $i * 117, 117, 'utf-8')));
        }
        return $crypt_res;
    }

    public function decryptToMd5($param)
    {
        $str = self::createLinkstring($param);
        return md5($str) === $this->decrypt($param['sign']);
    }

    public function decrypt($sign)
    {
        $decrypt_res = "";
        $signs = explode('=', $sign);
        foreach ($signs as $value)
            $decrypt_res = $decrypt_res . $this->decrypt_data($value);
        return $decrypt_res;
    }

    /**
     * @Desc 生成要请求的参数数组
     * @param {array} $params 请求前的参数数组
     * @return {array} 要请求的参数数组

     * @param $params
     * @return array 排序前的数组
     */
    public function buildRequestPara(array $params)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = self::createLinkstring($params);
        //签名结果与签名方式加入请求提交参数组中
        $params['sign'] = self::encrypt($prestr);
        return $params;
    }


    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $para 需要拼接的数组
     * @return array 拼接完成以后的字符串
     */
    private function createLinkstring($param)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = self::paraFilter($param);
        //对待签名参数数组排序
        $para_sort = self::argSort($para_filter);

        $arg = "";
        while (list ($key, $val) = each($para_sort))
            $arg .= $key . "=" . $val . "&";

        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc())
            $arg = stripslashes($arg);

        return $arg;
    }

    /**
     * @Desc 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param array $para 需要拼接的数组
     * @return array 拼接完成以后的字符串
     */
    private function createLinkstringUrlencode(array $param)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = self::paraFilter($param);
        //对待签名参数数组排序
        $para_sort = self::argSort($para_filter);

        $arg = "";
        while (list ($key, $val) = each($para_sort))
            $arg .= $key . "=" . urlencode($val) . "&";

        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc())
            $arg = stripslashes($arg);

        return $arg;
    }

    /**
     * @Desc 除去数组中的空值和签名参数
     * @param array $para 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter(array $para)
    {
        return array_filter($para,function($v,$k){
            return $k !== 'sign' && $v != '';
        },ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @Desc 对数组进行排序
     * @param array $para
     * @return array
     */
    private function argSort(array $para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
}