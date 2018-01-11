<?php

namespace App\Library;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Api_QiNiu
{

    //https://oli8t0ygp.qnssl.com/tag/icon/80001_0.png
    //https://omuiwwdiz.qnssl.com/tag/icon/80002_0.png
    const Access_Key = 'mCwGqdgVxjlsZvpzkeZzBnHqm-1NR3BFMHvLujrh';//七牛设置access_key
    const Secret_Key = '77BQrUqyVkz35Dr-yQIFBz1TyD2lU2edZUsujBUL';//七牛设置secret_key
    const scheme = 'http';
    //const imgUrl = 'ok5xn9z1n.bkt.clouddn.com';//静态文件的url地址
    public $Bucket_Name = 'images';//七牛设置bucket_name
    const imgUrl = 'http://ow3i63zts.bkt.clouddn.com';


    //获取七牛的文件列表
    private function filelist()
    {
        $bucketMgr = self::BucketManager();
        return $bucketMgr->listFiles($this->Bucket_Name);
    }


    public function BucketManager()
    {
        return new BucketManager(self::QNToken());
    }

    private function UploadManager()
    {
        return new UploadManager();
    }

    //保存文件到七牛服务器
    public function saveqiniu($newName, $filePath, $type = 0)
    {
        //return true;
        $token = self::uploadToken();
        $uploadMgr = self::UploadManager();
        list($ret, $err) = 1 === $type ? $uploadMgr->putFile($token, $newName, $filePath) : $uploadMgr->put($token, $newName, $filePath);//凭证 、 新的文件名、上传文件的路径
        if ($err === null) {
            return $newName;
        }
        return null;
    }

    //在七牛服务器上删除旧的数据
    public function delFile($key)
    {
        $bucketMgr = $this->BucketManager();
        $err = $bucketMgr->delete($this->Bucket_Name, $key);

        return $err !== null ? $err : 'Success';
    }

    public function getToken()
    {
        return self::uploadToken();
    }

    private function QNToken()
    {
        return (new Auth(self::Access_Key, self::Secret_Key));
    }

    //获取七牛上传token
    private function uploadToken()
    {
        return self::QNToken()->uploadToken($this->Bucket_Name);
    }
}
