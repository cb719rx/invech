<?php
namespace app\common\model;
use think\Model;

use bong\service\UserAgent;

class PaySet extends Base{

    protected $table = 'gygy_pay_set';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $autoWriteTimestamp = 'datetime';

   CONST PAY_SET_TYPE = [
        'wechat' ,
        'tenpay',
        'bank' ,
        'qqpay',
        'diankapay' ,
        'jdpay',
        'app',
        'baipay' ,
    ];
    //----------------后台------------------

    //ios和安卓和wap一样的支付接口;仅区分pc和wap即可;
    public function scopeClient($query){

        $query->where('status',0); 

        $pc = UserAgent::isPc();
        if($pc){
            return $query->where('is_wap',0);
        }else{
            return $query->where('is_wap',1);
        }        
    }   
    //----------------后台------------------
    public static function getList(){
        $params = request()->param();
        $query = self::order('id desc');
        return $query->paginate();
    }


    public static function getAll(){
        $params = cache('gygy_pay_set');
        if(!$params){
            $params = self::All();
            $params_map = [];
            foreach($params as $k=>$v){
                $params_map[$v['id']] = $v;
            }   
            $params = $params_map;
            cache('gygy_pay_set',$params);
        }
        return $params;
    }  

    //------------------API-------------------------
    public static function code(){
        return self::client()->with('payWays.payChannels')
        ->order('sort')->field('id,name,type,img')->select();
    }

    //------------------关联-------------------
    public function payWays()
    {
        return $this->hasMany('PayWay','set_id')->field('id,set_id,name,img,code as local_code');
    }        
}
