<?php
namespace app\common\model;
use think\Model;
class AuthGroup extends Base{
	protected $table = 'gygy_auth_group';
        //----------------后台------------------

    public function admingroup()
    {   
         return $this->hasMany('AdminGroup','group_id');
    }

    public static function getList(){
      $params = request()->param();
      $query = self::order('group_id');
      $data = $query->paginate();
     	return $data;
    }
}
