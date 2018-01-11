<?php
namespace  App\Models\User;

use App\Library\Api_Sms;
use App\Models\BaseModels;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class User extends BaseModels{

    public function getUserMoney()
    {
        $res = [];
        $db = app('db');
        $data = $db->table('shop_member')
            ->where('id',$this->userId)->select(['av_amount','freeze_amount','weChatName','user_name'])->first();
        $res['userName'] = $data->user_name;
        $res['weChatName'] = $data->weChatName;
        $res['available'] = $data->av_amount;
        $res['frozen'] = $data->freeze_amount;
        $res['total'] = $data->av_amount + $data->freeze_amount;
        return $res;
    }

    public function getMoneyList(Request $request)
    {
        $pageSize = $request->get('pageSize',self::pageSize);
        $data = app('db')->table('pd_log')->where('lg_member_id',$this->userId)
            ->select('lg_av_amount','lg_freeze_amount','created_at','lg_desc')
            ->where('lg_av_amount','<>',0)
            ->orderBy('created_at','desc')->paginate($pageSize);
        $list = [];
        foreach ($data as $k=>$v){
            $v->month = date("Y-m",strtotime($v->created_at));
            if(!isset($list[$v->month])) $list[$v->month] = ['lists'=>[]];
            $list[$v->month]['time'] = $v->month;
            array_push($list[$v->month]['lists'],[
                'created_at'=>$v->created_at,
                'desc'=>$v->lg_desc,
                'day'=>date("d",strtotime($v->created_at)),
                'price'=>abs(\App\Helpers::priceFormat($v->lg_av_amount)),
                'type'=>$v->lg_av_amount >0 ? 'add' : 'del'
            ]);
        }
        $return = [];
        $return['lists'] = array_values($list);
        $return['total'] = $data->total();
        $return['page'] = $data->currentPage();
        $return['pageSize'] = $data->perPage();
        return $return;
    }

    public function setUserValue($data)
    {
       if (!$this->userId)
            throw new \Exception('参数错误',-11);
        $db = app('db');
        return $db->table('shop_member')->where('id', $this->userId)->update($data);
    }

    public function userInfo()
    {

        $db = app('db');
        $data = $db->table('shop_member')->where('id',$this->userId)
            ->select(['shop_id','id','shop_name','user_name','phone','weChatName','img_url','groupId'])->first();

        if(is_null($data))
            throw new \Exception('用户不存在',-11);

        $res = [];
        $res['userName'] = $data->user_name;
        $res['phone'] = $data->phone;
        $res['userId'] = $data->id;
        $res['imgUrl'] = \App\Library\Api_QiNiu::imgUrl."/".$data->img_url;
        $res['weChatName'] = $data->weChatName;
        $res['groupId'] = $data->groupId;


        if($data->groupId == 2)
        {
            $shop = $db->table('shop')->where('id',$data->shop_id)
                ->select(['detailed','manager_id'])->first();
            $area = array_values(array_filter(explode(' ',$shop->detailed)));

            $res['manage']['shopArea'] = $area[0];
            $res['manage']['shopDetailed'] = $area[1];

            $manage = $db->table('shop_member')->where('id',$shop->manager_id)
                ->select(['user_name','phone'])->first();
            $res['manage']['name'] = $manage->user_name;
            $res['manage']['phone'] = $manage->phone;
            $res['manage']['shopName'] = $data->shop_name;
        }
        return $res;
    }

    public function  getMessageInfo($m_id)
    {
        $db = app('db');
        $info = $db->table('message')->where('id',$m_id)
            ->select(['title','content','state','created_at'])->first();
        if($info->state == 0)
            $db->table('message')->where('id',$m_id)
                ->update(['state'=>1,'updated_at'=>date("Y-m-d H:i:s")]);
        return $info;
    }

    public function getMessageList(Request $request)
    {
        $db = app('db');
        $pageSize = $request->get('pageSize',self::pageSize);
        $list = $db->table('message')->where('to_member_id',$this->userId)
            ->select(['id','title','created_at','type','orderId','content','state'])->paginate($pageSize);
        $data = [];
        if($list->isEmpty() ===false){
            foreach ($list as $v)
                array_push($data, [
                    'id'=>$v->id,
                    'title' => $v->title,
                    'type' => $v->type,
                    'desc' => $v->content,
                    'orderId' => $v->orderId,
                    'state' => $v->state,
                    'created_at' => $v->created_at,
                ]);
        }

        $return = [];
        $return['lists'] = $data;
        $return['total'] = $list->total();
        $return['page'] = $list->currentPage();
        $return['pageSize'] = $list->perPage();
        return $return;
    }

    public function center()
    {
        $db = app('db');
        $user = $db->table('shop_member')->where('id',$this->userId)
            ->select(['av_amount','freeze_amount','user_name','groupId'])->first();
        $conf = \App\Conf::getOrderConf();
        $data['money'] = \App\Helpers::priceFormat($user->av_amount + $user->freeze_amount);
        $data['userName'] = $user->user_name;
        $data['hasMessage'] = $db->table('message')->where('to_member_id',$this->userId)
            ->where('state',0)->first() === null ? 0 : 1;

        if($user->groupId == 1){
            $shop_ids = $db->table('shop')->select('id')->where('manager_id',$this->userId)
                ->where('status',1)->get()->toArray();

            $shop_ids = array_map('reset',$shop_ids);

            $data['stassCount'] = $db->table('shop_member')->where('status',1)
                ->whereIn('shop_id',$shop_ids)->where('groupId',2)->count();

            $data['delicerOrderCount'] = $db->table('order')
                ->where('state',$conf['order_payment'])->whereIn('shop_id',$shop_ids)
                ->count();
            $data['orderAmount'] = $db->table('order')
                ->where('state','>',$conf['order_submit'])->whereIn('shop_id',$shop_ids)
                ->sum('user_price');
        }else{
            $data['delicerOrderCount'] = $db->table('order')
                ->where('state',$conf['order_payment'])->where('clerk_id',$this->userId)
                ->count();
            $data['phoneCount'] = $db->table('order')
                ->where('state','>',$conf['order_submit'])->where('clerk_id',$this->userId)
                ->count();

            $data['orderAmount'] = $db->table('order')
                ->where('state','>',$conf['order_submit'])->where('clerk_id',$this->userId)
                ->sum('user_price');
        }


        return $data;
    }

    public function readMsg()
    {
        return app('db')->table('message')->where('to_member_id',$this->userId)->update(['state'=>1]);
    }

    public function cash(Request $request)
    {
        if(!$request->has('money'))
            throw new \Exception('参数错误',-101);

        $db = app('db');
        $user =$db->table('shop_member')->select(['av_amount','weChatName','phone','shop_id','groupId','id'])
            ->where('status',1)->where('id',$this->userId)->first();

        $this->isTest($user);

        if($user->weChatName == '')
            throw new \Exception('请先绑定微信',-13);

        if(!is_numeric($request->money))
            throw new \Exception('提现金额不合法',-13);

        if(($user->av_amount*100) < ($request->money*100))
            throw new \Exception('可用余额不足',-14);

        $api_Sms = new api_Sms();
        if (false === $api_Sms->checkVerifyCode($user->phone, $request->code))
            throw new \Exception('验证码不正确', -12);

        $pd_id = $db->table('pd_cash')->insertGetId([
            'pdc_member_id'=>$this->userId,
            'pdc_member_name'=> $db->table('shop_member')->where('id',$this->userId)->value('user_name'),
            'pdc_amount'=>$request->money,
            'created_at'=>date("Y-m-d H:i:s"),
        ]);
        if($pd_id === false)
            throw new \Exception('网络出现异常了',-101);

        \App\Helpers::putCashPostDistinct($this->userId);

        $data = $this->createPayData($pd_id,$this->userId,$request->money);
        $result['code']= 0;
        $result = \App\Helpers::getHttpResponsePOST(\App\Conf::reflectUrl,$data);
        $result = json_decode($result,true);
        if($result['code'] == 0) {

            $db->beginTransaction();

            $state = $db->table('pd_cash')->where('pdc_id',$pd_id)->update([
                'pdc_payment_state'=>'1',
                'pdc_payment_time'=>time(),
                'updated_at'=>date("Y-m-d H:i:s"),
            ]);
//            dd($pd_id,$state);
            $db->table('pd_log')->insert([
                'lg_member_id'=>$this->userId,
                'lg_member_name'=>$db->table('shop_member')->where('id',$this->userId)->value('user_name'),
                'lg_type'=>'cash_pay',
                'lg_av_amount'=>-$request->money,
                'lg_desc'=>'提现',
                'created_at'=>date("Y-m-d H:i:s"),
            ]);

            $db->table('shop_member')->where('id',$this->userId)->decrement('av_amount',$request->money);
            $db->table('shop_member')->where('id',$this->userId)->increment('all_cash_amount',$request->money);

            $db->commit();
            return true;
        }else{
            throw new \Exception($result['errMsg'], $result['code']);
        }
    }

    protected function isTest($user){
        if(is_null($user))
            throw new \Exception('参数错误');

        $db = app('db');
        $state = null;
        if($user->groupId == 1)//店主

            $state = $db->table('shop')->where('manager_id',$user->id)->where('isTest',1)->first();
        else
            $state = $db->table('shop')->where('id',$user->shop_id)->where('isTest',1)->first();


        if($state !== null)
            throw new \Exception('测试账户不允许操作提现','-15');
    }

    protected  function updateCashState($pd_id,$money)
    {

        $db = app('db');

    }
    protected  function createPayData($pd_id,$userId,$amount)
    {
        $time = time();
        $openId = app('db')->table('wechat')->where('user_id',$userId)->value('oopenId');
        $data['openId'] = $openId;
        $data['amount']	= $amount * 100;
        //$data['amount']	= 1 * 100;
        $data['orderId'] = 	$pd_id;
        $data['salt'] =$time;
        $para = \App\Helpers::buildRequestPara($data);
        $token = md5($para.$time);
        $data['token'] = $token;
//        dd($para,$time,$data);
        return $data;
    }

    public function bindWechat(Request $request)
    {
        if(!$request->has('openId'))
            throw new \Exception('参数错误',-11);

        $db = app('db');
        $data = $db->table('wechat_user')->where('uniqueId',$request->openId)
            ->select(['openId','oopenId','nickname','sex','language','city','province','country','headimgurl'])
            ->first();

        if(is_null($data))
            throw new \Exception('绑定微信失败',-12);

        $userId = $db->table('wechat')->where('openId',$data->openId)->value('user_id');

        if($userId !== null && $userId != $this->userId)
        {
            $name = $db->table('shop_member')->where('id',$userId)->value('user_name');
            throw new \Exception('该微信已绑定'.$name,-12);
        }

        $res = $db->table('shop_member')->where('id',$this->userId)->update([ 'weChatName' => trim($data->nickname)]);
        if($res === false)
        {
            throw new \Exception('操作失败',-12);
        }else{
            $db->table('wechat_log')->insert([
                'user_id'=>$this->userId,
                'wechat_log'=>\GuzzleHttp\json_encode($data)
            ]);
            $data->user_id = $this->userId;
            if($db->table('wechat')->where('user_id',$this->userId)->first() !== null)
                $db->table('wechat')->where('user_id',$this->userId)->update((array)$data);
            else
                $db->table('wechat')->insert((array)$data);

            return ['nickname'=>$data->nickname,'headimgurl'=>$data->headimgurl];
        }
    }

    public function getWechatInfo()
    {
       return app('db')->table('wechat')->where('user_id',$this->userId)
           ->first();
    }
}