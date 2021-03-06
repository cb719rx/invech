<?php
namespace app\admin\controller;
use app\admin\Login;
use app\common\model\BackWater as BackWaterModel;
class Backwater extends Login{
    
    public function index(){
    	$this->view->page_header = '返水列表';
    	$request   =   request();
    	$list      =   BackWaterModel::getList($request);
        $this->assign('list',$list);
        // 统计数据
        $stat_fields = [
            'ifnull(sum(amount), 0.00) as sum_amount',
        ];
        $statData = BackWaterModel::getStatData($stat_fields);
        $this->assign('statData', $statData);
        return $this->fetch();
    }
   
}