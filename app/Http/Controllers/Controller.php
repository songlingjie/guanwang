<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //

    protected $userId;

    public function __construct(Request $request)
    {
        $this->userId = $request->user();
    }

    protected function respon($data,$code = 0,$sign = 0)
    {


        //响应jsonp
        //\Dingo\Api\Http\Response::addFormatter('json', new \Dingo\Api\Http\Response\Format\Jsonp);
        //重写Paginator返回数据格式
        if($data instanceof Paginator){
            $total = $data->total();
            $list = $data->items();
            $page = $data->currentPage();
            $pageSize = $data->perPage();
            $data = compact('total','list','page','pageSize');
        }

        if($sign){
            $data = (array) $data;
            $data['dateline'] = time();
            $data = Api_Rsa::i()->encryptToMd5($data);
        }
        $array = compact('code','data');

        if(0 > $code){
            $array['errMsg'] = $data;
            unset($array['data']);
        }

        return response($array);
    }

    //是否注册过
    protected function isReg($mobile)
    {
        return !is_null(app('db')->table('shop_member')->where('status',1)
            ->where('phone',$mobile)->first());
    }
}
