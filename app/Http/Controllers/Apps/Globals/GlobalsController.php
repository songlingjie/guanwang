<?php

namespace App\Http\Controllers\Apps\Globals;

use App\Http\Controllers\Controller;

class GlobalsController extends Controller
{

    /**
     * @apiUrl [post] /g/upToken
     * 七牛上传图片获取token
     * @return array
     */
    public function upToken()
    {
        $qiniu = new \App\Library\Api_QiNiu;
        $qiniu->Bucket_Name = 'images';
        $token = $qiniu->getToken();
        $domain = $qiniu::imgUrl;
        $time = time();
        $res = compact('token','domain','time');
        return $this->respon($res);
    }
}
