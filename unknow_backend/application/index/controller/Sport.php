<?php
namespace app\index\controller;
use app\index\Base;
use think\Db;
use think\Config;
use think\Session;
use think\Cache;

class Sport extends Base
{

    private function sendHeader()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Pragma: no-cache");
        header('Content-type: text/json; charset=utf-8');
    }

    public function index()
    {       
        \think\Log::write('mymse','info');
    	$uid = Session::get('uid');
    	$user = Db::table('k_user')->where(array('uid'=>$uid))->find();
    	
    	$this->assign('user',$user);
        return $this->fetch('index');
    }
    
    public function home()
    {
    	$uid = Session::get('uid');
    	$user = Db::table('k_user')->where(array('uid'=>$uid))->find();
    	
    	$this->assign('user',$user);
    	return $this->fetch('home');
    }
    
    public function checkcg(){
        $uid = Session("uid");
        $gid = Session('gid');
        $bet_money	=	$_REQUEST['bet_money'];
        header('Content-type: text/json; charset=utf-8');
        include_once CACHE_PATH.'/group_'.$gid.'.php';
        $dz			=	$dz_db["串关"];
        $dc			=	$dc_db["串关"];
        if(!$dz || $dz=="") $dz	=	$dz_db['未定义'];
        if(!$dc || $dc=="") $dc	=	$dc_db['未定义'];
        if($bet_money<=$dz){
            $s_t	=	strftime("%Y-%m-%d",time())." 00:00:00";
            $e_t	=	strftime("%Y-%m-%d",time())." 23:59:59";
            $sql	=	"select sum(bet_money) as s from `k_bet_cg_group` where uid=".$uid." and bet_time>='$s_t' and bet_time<='$e_t' and `status`!=3"; //无效跟平手不当成投注
            $rs	=	Db::query($sql)[0]; //取出串关当天总下注金额
            if(!$rs['s'] || $rs['s']=="null") $rs['s']=0;
            if(($rs['s']+$bet_money)<=$dc){
                $json['result']				=	"ok";
                Session("check_action",'true'); //防软件打水
            }else{
                $json['result']				=	"您本次交易：".$bet_money."\n串关当天限额：".$dc."\n您今天已交易：".$t['s']."\n本次允许交易：".($dc-$t['s']);
            }
        }else{
            if(Session("gid")) $json['result']="您本次交易：".$bet_money."\n串关单注限额：".$dz;
            else $json['result']="wdl"; //用户未登陆
        }
        
        echo json_encode($json);
        exit;
    }
    
    public function checkxe(){
        $ball_sort		=	$_REQUEST['ball_sort'];
        $touzhuxiang	=	$_REQUEST['touzhuxiang'];
        $bet_money		=	$_REQUEST['bet_money'];
        $match_id		=	$_REQUEST['match_id'];
        $uid = Session('uid');
        $gid = Session('gid');
        include_once CACHE_PATH.'/group_'.$gid.'.php';
        if($ball_sort == "冠军" || $ball_sort == "金融"){
            $dz	=	@$dz_db["$ball_sort"];
            $dc	=	@$dc_db["$ball_sort"];
        }else{
            $dz	=	@$dz_db["$ball_sort"]["$touzhuxiang"];
            $dc	=	@$dc_db["$ball_sort"]["$touzhuxiang"];
        }
        if(!$dz || $dz=="") $dz=$dz_db['未定义'];
        if(!$dc || $dc=="") $dc=$dc_db['未定义'];
        if($bet_money<=$dz){
            $s_t	=	strftime("%Y-%m-%d",time())." 00:00:00";
            $e_t	=	strftime("%Y-%m-%d",time())." 23:59:59";
            $sql	=	"select sum(bet_money) as s from `k_bet` where match_id=$match_id and uid=".$uid." and bet_time>='$s_t' and bet_time<='$e_t' and `status` not in(3,8)"; //无效跟平手不当成投注
            $rs	= Db::name('k_bet')->where('match_id','=',$match_id)
            ->where('uid','=',$uid)->where('bet_time','>=',$s_t)
            ->where('bet_time','<=',$e_t) -> whereNotIn('status',[3,8])->sum('bet_money');
            if(!$rs || $rs == "null") $rs = 0;
            if(($rs + $bet_money)<=$dc){
                $json['result']				=	"ok";
                Session("check_action",	'true'); //防软件打水
            }else{
                $json['result']				=	$ball_sort."-".$touzhuxiang."\n您本次交易：".$bet_money."\n单场限额：".$dc."\n您本场已交易：".$t['s']."\n本次允许交易：".($dc-$t['s']);
            }
        }else{
            if($_SESSION["gid"]) $json['result']=$ball_sort."-".$touzhuxiang." 单注限额：".$dz;
            else $json['result']="wdl"; //用户未登陆
        }
        return $json;
    }
    
    public function left(){
        return $this->fetch('left');
    }
    
    public function leftdao()
    {
        $uid		= @$_SESSION["uid"];
        $callback	= @$_GET['callback'];
        $md = date("m-d");
        $uid = Session("uid");
        //sessionNum($uid, 4, $callback);
        $zq = $zq_ds = $zq_gq = $zq_bd = $zq_rqs = $zq_bqc = $zq_jg = 0; // 足球
        $zqzc = $zqzc_ds = $zqzc_bd = $zqzc_rqs = $zqzc_bqc = 0; // 足球早餐
        $lm = $lm_ds = $lm_dj = $lm_gq = $lm_jg = 0; // 篮美
        $lmzc = $lmzc_ds = $lmzc_dj = 0; // 篮美早餐
        $wq = $wq_ds = $wq_bd = $wq_jg = 0; // 网球
        $pq = $pq_ds = $pq_bd = $pq_jg = 0; // 排球
        $bq = $bq_ds = $bq_zdf = $bq_jg = 0; // 棒球
        $gj = $gj_gj = $gj_jg = 0; // 冠军
        $user_money = 0; // 投注
                         
        // 足球-单式
        $db = Db::connect(Config::get('sportdb'));
        $sql = "SELECT count(id) as s FROM bet_match  WHERE Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date='$md' and Match_HalfId is not null";
        $rs =  $db -> query($sql)[0];
        $zq_ds = $rs['s'];
        
        
        // 足球-滚球
        /*include_once (CACHE_PATH. "/zqgq.php");
        if (time() - $lasttime > 3)
            $zq_gq = 0;
        else {
            $zq_gq = count($zqgq);
        }
	*/
        $zqgq = Cache::get('zqgq',[]);
        $zq_gq = count($zqgq);
	
        // 足球-波胆
        $sql = "SELECT count(*) as s FROM bet_match where Match_Type=1 and Match_IsShowbd=1 and  Match_CoverDate>'" . date("Y-m-d H:i:s") . "' and Match_Bd21>0";
        $rs = $db -> query($sql)[0];
        $zq_bd = $rs['s'];
        // 足球-入球数
        $sql = "SELECT count(*) as s FROM bet_match where Match_Type=1 and Match_IsShowt=1 and Match_DsDpl>0 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' ";
        $rs = $db -> query($sql)[0];
        $zq_rqs = $rs['s'];
        
        // 足球-半全场
        $sql = "SELECT count(*) as s FROM bet_match where Match_CoverDate>'" . date("Y-m-d H:i:s") . "' and Match_BqMM>0 and Match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $zq_bqc = $rs['s'];
        
        // 足球-结果
        $sql = "SELECT count(*) as s FROM bet_match where match_Date='$md' and (MB_Inball is not null or MB_Inball_HR is not NULL)";
        $rs = $db -> query($sql)[0];
        $zq_jg = $rs['s'];
        
        $zq = $zq_ds + $zq_gq + $zq_bd + $zq_rqs + $zq_bqc + $zq_jg; // 足球
                                                            
        // 足球早餐-单式
        $sql = "SELECT count(*) as s FROM bet_match WHERE Match_Type=0 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date<>'$md'";
        $rs = $db -> query($sql)[0];
        $zqzc_ds = $rs['s'];
        
        $zqzc = $zqzc_ds + $zqzc_bd + $zqzc_rqs + $zqzc_bqc; // 足球早餐
                                                       
        // 篮美-单式
        $sql = "SELECT count(*) as s FROM lq_match WHERE Match_Type!=3 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $lm_ds = $rs['s'];
        
        // 篮美-结果
        $sql = "SELECT count(*) as s FROM lq_match WHERE MB_Inball_OK is not null and  match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $lm_jg = $rs['s'];
        
        // 篮美-滚球
	/*        
        include_once (CACHE_PATH . "/lqgq.php");
        if (time() - $lasttime > 3)
            $lm_gq = 0;
        else {
            $lm_gq = count($lqgq);
        }
	*/
        $lqgq = Cache::get('lqgq',[]);
        $lm_gq = count($lqgq);
	
        $lm = $lm_ds + $lm_dj + $lm_gq + $lm_jg; // 篮美
                                            
        // 篮美早餐-单式
        $sql = "SELECT count(*) as s FROM lq_match WHERE Match_Type!=3 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date<>'$md'";
        $rs = $db -> query($sql)[0];
        $lmzc_ds = $rs['s'];
        
        $lmzc = $lmzc_ds + $lmzc_dj; // 篮美早餐
                                   
        // 网球-单式
        $sql = "SELECT count(*) as s FROM tennis_match WHERE Match_Type=1 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $wq_ds = $rs['s'];
        // 网球-结果
        $sql = "SELECT count(*) as s FROM tennis_match where MB_Inball is not null and  match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $wq_jg = $rs['s'];
        $wq = $wq_ds + $wq_bd + $wq_jg; // 网球
                                     // 排球-单式
        $sql = "SELECT count(*) as s FROM  volleyball_match WHERE Match_Type=1 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $pq_ds = $rs['s'];
        // 排球-结果
        $sql = "SELECT count(*) as s FROM volleyball_match where MB_Inball is not null and  match_Date='$md' and match_js=1";
        $rs = $db -> query($sql)[0];
        $pq_jg = $rs['s'];
        $pq = $pq_ds + $pq_bd + $pq_jg; // 排球
                                     // 棒球-单式
        $sql = "SELECT count(*) as s FROM baseball_match WHERE Match_Type=1 AND Match_CoverDate>'" . date("Y-m-d H:i:s") . "' AND Match_Date='$md'";
        $rs = $db -> query($sql)[0];
        $bq_ds = $rs['s'];
        // 棒球-结果
        $sql = "SELECT count(*) as s FROM baseball_match WHERE MB_Inball is not null and  match_Date='$md' and match_js=1";
        $rs = $db -> query($sql)[0];
        $bq_jg = $rs['s'];
        $bq = $bq_ds + $bq_zdf + $bq_jg; // 棒球
                                      // 冠军-冠军
        $sql = "SELECT count(*) as s FROM t_guanjun WHERE Match_CoverDate>'" . date("Y-m-d H:i:s") . "' and x_result is null and match_type=1";
        $rs = $db -> query($sql)[0];
        $gj_gj = $rs['s'];
        // 冠军-冠军结果
        $sql = "SELECT count(*) as s FROM t_guanjun where x_result is not null and match_type=1 and match_date='" . date("m-d", time() - 86400) . "'";
        $rs = $db -> query($sql)[0];
        $gj_jg = $rs['s'];
        $gj = $gj_gj + $gj_jg; // 冠军
        $json['zq'] = $zq;
        $json['zq_ds'] = $zq_ds;
        $json['zq_gq'] = $zq_gq;
        $json['zq_bd'] = $zq_bd;
        $json['zq_rqs'] = $zq_rqs;
        $json['zq_bqc'] = $zq_bqc;
        $json['zq_jg'] = $zq_jg;
        $json['zqzc'] = $zqzc;
        $json['zqzc_ds'] = $zqzc_ds;
        $json['zqzc_bd'] = $zqzc_bd;
        $json['zqzc_rqs'] = $zqzc_rqs;
        $json['zqzc_bqc'] = $zqzc_bqc;
        $json['lm'] = $lm;
        $json['lm_ds'] = $lm_ds;
        $json['lm_dj'] = $lm_dj;
        $json['lm_gq'] = $lm_gq;
        $json['lm_jg'] = $lm_jg;
        $json['lmzc'] = $lmzc;
        $json['lmzc_ds'] = $lmzc_ds;
        $json['lmzc_dj'] = $lmzc_dj;
        $json['wq'] = $wq;
        $json['wq_ds'] = $wq_ds;
        $json['wq_bd'] = $wq_bd;
        $json['wq_jg'] = $wq_jg;
        $json['pq'] = $pq;
        $json['pq_ds'] = $pq_ds;
        $json['pq_bd'] = $pq_bd;
        $json['pq_jg'] = $pq_jg;
        $json['bq'] = $bq;
        $json['bq_ds'] = $bq_ds;
        $json['bq_zdf'] = $bq_zdf;
        $json['bq_jg'] = $bq_jg;
        $json['gj'] = $gj;
        $json['gj_gj'] = $gj_gj;
        $json['gj_jg'] = $gj_jg;
        echo $callback . "(" . json_encode($json) . ");";
        //exit();
    }

    public function BK_1_0()
    {
        $this->sendHeader();
        $callback = "";
        $callback = @$_GET['callback'];
        /*
        include CACHE_PATH . 'lqgq.php';
        if (time() - $lasttime > 3) {
            if ($count == 0) { // 没数据
                $json["dh"] = 0;
                $json["fy"]["p_page"] = "0";
                echo $callback . "(" . json_encode($json) . ");";
                exit();
            } else { // 有数据重新采集一次，看是否有数据
                
                include_once (APP_PATH."../common/function_cj1.php");
                if (lqgq_cj()) {
                    include CACHE_PATH . 'lqgq.php'; // 重新载入
                } else {
                    $json["dh"] = 0;
                    $json["fy"]["p_page"] = "0";
                    echo $callback . "(" . json_encode($json) . ");";
                    exit();
                }
            }
        }*/
        $lqgq = Cache::get('lqgq',[]) ??[];
        $lq_gq = count($lqgq);
        $bk = 150; // 每页显示记录总个数
        $this_page = 0; // 当前页
        if (intval($_GET["CurrPage"]) > 0)
            $this_page = $_GET["CurrPage"];
        $this_page ++;
        $start = ($this_page - 1) * $bk; // 本页记录开始位置，数组从0开始
        $end = $this_page * $bk - 1; // 本页记录结束位置，数组从0开始，所以要减1
        
        $match_names = array();
        $info_array = array();
        $rows = array();
        if (isset($lqgq) && ! empty($lqgq)) {
            $zqcount = count($lqgq);
            for ($i = 0; $i < $zqcount; $i ++) {
                $rows[] = $lqgq[$i]; // //保留所有的数据
                $match_names[] = $lqgq[$i]['Match_Name']; // //只保留联赛名
            }
        }
        
        $match_name_array = array_values(array_unique($match_names));
        if (@$_GET["leaguename"] != "") {
            $leaguename = explode("$", urldecode($_GET["leaguename"]));
            $nums_arr = count($rows);
            for ($i = 0; $i < $nums_arr; $i ++) {
                if (in_array($rows[$i]["Match_Name"], $leaguename)) {
                    $info1[] = $rows[$i];
                }
            }
            $rows = $info1;
        }
        
        $count_num = count($rows);
        if ($count_num == "0") {
            $json["dh"] = 0;
            $json["fy"]["p_page"] = 0;
        } else {
            $json["fy"]["p_page"] = ceil($count_num / $bk); // 总页数
            $json["fy"]["page"] = $this_page - 1;
            // 联赛名字
            $i = 0;
            $lsm = '';
            foreach ($match_name_array as $t) {
                $lsm .= urlencode($t) . '|';
                $i ++;
            }
            $json["lsm"] = rtrim($lsm, '|');
            $json["dh"] = ceil($i / 3) * 30; // 窗口高度 px 值
            if ($end > $count_num - 1)
                $end = $count_num - 1;
            $i = 0;
            for ($b = $start; $b <= $end; $b ++) {
                $json["db"][$i]["Match_ID"] = isset($rows[$b]["Match_ID"]) ? $rows[$b]["Match_ID"] : 0;
                $json["db"][$i]["Match_Master"] = isset($rows[$b]["Match_Master"]) ? $rows[$b]["Match_Master"] : 0;
                $json["db"][$i]["Match_Guest"] = isset($rows[$b]["Match_Guest"]) ? $rows[$b]["Match_Guest"] : 0;
                $json["db"][$i]["Match_Name"] = isset($rows[$b]["Match_Name"]) ? $rows[$b]["Match_Name"] : 0;
                $json["db"][$i]["Match_Time"] = isset($rows[$b]["Match_Time"]) ? $rows[$b]["Match_Time"] : 0;
                $json["db"][$i]["Match_Ho"] = isset($rows[$b]["Match_Ho"]) ? $rows[$b]["Match_Ho"] : 0;
                $json["db"][$i]["Match_DxDpl"] = isset($rows[$b]["Match_DxDpl"]) ? $rows[$b]["Match_DxDpl"] : 0;
                $json["db"][$i]["Match_DsDpl"] = isset($rows[$b]["Match_DsDpl"]) ? $rows[$b]["Match_DsDpl"] : 0;
                $json["db"][$i]["Match_Ao"] = isset($rows[$b]["Match_Ao"]) ? $rows[$b]["Match_Ao"] : 0;
                $json["db"][$i]["Match_DxXpl"] = isset($rows[$b]["Match_DxXpl"]) ? $rows[$b]["Match_DxXpl"] : 0;
                $json["db"][$i]["Match_DsSpl"] = isset($rows[$b]["Match_DsSpl"]) ? $rows[$b]["Match_DsSpl"] : 0;
                $json["db"][$i]["Match_RGG"] = isset($rows[$b]["Match_RGG"]) ? $rows[$b]["Match_RGG"] : 0;
                $json["db"][$i]["Match_DxGG1"] = "O" . $rows[$b]["Match_DxGG"];
                $json["db"][$i]["Match_ShowType"] = $rows[$b]["Match_ShowType"];
                $json["db"][$i]["Match_DxGG2"] = "U" . $rows[$b]["Match_DxGG"];
                $i ++;
            }
        }
        echo $callback . "(" . json_encode($json) . ");";
    }

    public function BK_1_1()
    {
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id from lq_match WHERE Match_Type!=3 AND Match_CoverDate>'".date("Y-m-d H:i:s")."' AND Match_Date='".date("m-d")."' ".$sqlwhere.'  order by Match_CoverDate,iPage,iSn,Match_ID,match_name,Match_Master';
        $rs = $db->query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name from lq_match WHERE Match_Type!=3 AND Match_CoverDate>'$now' AND Match_Date='".date("m-d")."' group by match_name";
            $rs = $db->query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG, Match_Name, Match_IsLose, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_ShowType, Match_Type, Match_DsDpl, Match_DsSpl FROM lq_match where id in($id) order by Match_CoverDate,iPage,iSn,Match_ID,match_name,Match_Master";
            //为手机站添加独赢字段Match_BzM,Match_BzG
            //$sql  =   "SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_BzM, Match_BzG,Match_RGG, Match_Name, Match_IsLose, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_ShowType, Match_Type, Match_DsDpl, Match_DsSpl FROM lq_match where id in($id) order by Match_CoverDate,iPage,iSn,Match_ID,match_name,Match_Master";            
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                if($rows["Match_Ho"]==0 /*&& $rows["Match_DxGG1"]==0*/ && $rows["Match_DsDpl"]==0 ){
                    continue;
                }else{
                    $json["db"][$i]["Match_ID"] = $rows["Match_ID"];
                    $json["db"][$i]["Match_Master"] = $rows["Match_Master"];
                    $json["db"][$i]["Match_Guest"] = $rows["Match_Guest"];
                    $json["db"][$i]["Match_Name"] = $rows["Match_Name"];
                    $mdate = $rows["Match_Date"]."<br>".$rows["Match_Time"];
                    if ($rows["Match_IsLose"]==1){
                        $mdate.= "<br><font color='#FF0000'>滚球</font>";
                    }
                    $json["db"][$i]["Match_Date"]		=	$mdate;
                    $json["db"][$i]["Match_Ho"]			=	$rows["Match_Ho"];
                    $json["db"][$i]["Match_DxDpl"]		=	$rows["Match_DxDpl"];
                    $json["db"][$i]["Match_DsDpl"]		=	$rows["Match_DsDpl"];
                    $json["db"][$i]["Match_Ao"]			=	$rows["Match_Ao"];
                    $json["db"][$i]["Match_DxXpl"]		=	$rows["Match_DxXpl"];
                    $json["db"][$i]["Match_DsSpl"]		=	$rows["Match_DsSpl"];
                    $json["db"][$i]["Match_RGG"]		=	$rows["Match_RGG"];
                    $json["db"][$i]["Match_DxGG1"]		=	"O".$rows["Match_DxGG"];
                    $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];
                    $json["db"][$i]["Match_DxGG2"]		=	"U".$rows["Match_DxGG"];
                }
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }

    public function BK_2_1(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id from lq_match WHERE Match_Type!=3 AND Match_CoverDate>'".date("Y-m-d H:i:s")."' AND Match_Date='".date("m-d")."' ".$sqlwhere.'  order by Match_CoverDate,iPage,iSn,Match_ID,match_name,Match_Master';
        $rs = $db->query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name from lq_match WHERE Match_Type!=3 AND Match_CoverDate>'$now' AND Match_Date='".date("m-d")."' group by match_name";
            $rs = $db->query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG, Match_Name, Match_IsLose, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_ShowType, Match_Type, Match_DsDpl, Match_DsSpl FROM lq_match where id in($id) order by Match_CoverDate,iPage,iSn,Match_ID,match_name,Match_Master";
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG, Match_Name, Match_IsLose, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_ShowType, Match_Type, Match_DsDpl, Match_DsSpl FROM lq_match where id in($id) order by Match_CoverDate,iPage,iSn,Match_ID,match_name,Match_Master";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                if($rows["Match_Ho"]==0 && $rows["Match_DxGG"]==0 && $rows["Match_DsDpl"]==0){
                    continue;
                }else{
                    $json["db"][$i]["Match_ID"] = $rows["Match_ID"];
                    $json["db"][$i]["Match_Master"] = $rows["Match_Master"];
                    $json["db"][$i]["Match_Guest"] = $rows["Match_Guest"];
                    $json["db"][$i]["Match_Name"] = $rows["Match_Name"];
                    $mdate = $rows["Match_Date"]."<br>".$rows["Match_Time"];
                    if ($rows["Match_IsLose"]==1){
                        $mdate.= "<br><font color='#FF0000'>滚球</font>";
                    }
                    $json["db"][$i]["Match_Date"]		=	$mdate;
                    $json["db"][$i]["Match_Ho"]			=	$rows["Match_Ho"];
                    $json["db"][$i]["Match_DxDpl"]		=	$rows["Match_DxDpl"];
                    $json["db"][$i]["Match_DsDpl"]		=	$rows["Match_DsDpl"];
                    $json["db"][$i]["Match_Ao"]			=	$rows["Match_Ao"];
                    $json["db"][$i]["Match_DxXpl"]		=	$rows["Match_DxXpl"];
                    $json["db"][$i]["Match_DsSpl"]		=	$rows["Match_DsSpl"];
                    $json["db"][$i]["Match_RGG"]		=	$rows["Match_RGG"];
                    $json["db"][$i]["Match_DxGG1"]		=	"O".$rows["Match_DxGG"];
                    $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];
                    $json["db"][$i]["Match_DxGG2"]		=	"U".$rows["Match_DxGG"];
                }
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }
    
    public function FT_1_0()
    {
        $this->sendHeader();
        $callback="";
        $callback=@$_GET['callback'];

        /*
        include_once(CACHE_PATH."zqgq.php");

        	
        $is_test = '127.0.0.1' == $this->request->server('REMOTE_ADDR');

        if(time()-$lasttime > 3 && !$is_test){ //超时	    			    		
            if($count == 0){ //没数据
                $json["dh"]	=	0;
                $json["fy"]["p_page"] = "0";
                echo $callback."(".json_encode($json).");";
                exit;
            }else{ //有数据重新采集一次，看是否有数据
                include_once (APP_PATH."../common/function_cj1.php");
                if(zqgq_cj()){
                    include(CACHE_PATH."zqgq.php"); //重新载入
                }else{
                    $json["dh"]	=	0;
                    $json["fy"]["p_page"] = "0";
                    echo $callback."(".json_encode($json).");";
                    exit;
                }
            }
        }
        */
        $zqgq = Cache::get('zqgq',[]) ?? [];
        $gq_un = Cache::get('zqgq_un') ?? [];
        if(!$gq_un){
            //include(CACHE_PATH.'zqgq_un.php');
        }
        $count = count($zqgq);
        $bk			=	100; //每页显示记录总个数
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $start		=	($this_page-1)*$bk; //本页记录开始位置，数组从0开始
        $end		=	$this_page*$bk-1; //本页记录结束位置，数组从0开始，所以要减1
        
        //页数统计
        $match_names = array();
        $info_array  = array();
        $rows = array();
        if(isset($zqgq) && !empty($zqgq)){
            $zqcount = count($zqgq);
            for($i=0; $i<$zqcount; $i++){
                if(isset($gq_un[$zqgq[$i]['Match_ID']])){
                    continue;
                }
                $rows[] = $zqgq[$i];      ////保留所有的数据
                $match_names[] = $zqgq[$i]['Match_Name'];    ////只保留联赛名
            }
        }
        
        $match_name_array = array_values(array_unique($match_names));
        if(@$_GET["leaguename"]<>""){
            $match_name	=	explode("$",urldecode($_GET["leaguename"]));
            $nums_arr	=	count($rows);
            for($i=0; $i<$nums_arr; $i++){
                if(in_array($rows[$i]["Match_Name"],$match_name)){
                    $info1[] = $rows[$i];
                }
            }
            $rows = $info1;
        }
        
        $count_num = count($rows);
        if($count_num == "0"){
            $json["dh"]	=	0;
            $json["fy"]["p_page"]	=	0;
            $json['fy']['total'] = $count;
        }else{
            $json["fy"]["p_page"]	=	ceil($count_num/$bk); //总页数
            $json["fy"]["page"] 	=	$this_page-1;
            $json['fy']['total'] = $count;
            //联赛名字
            $i	=	0;
            $lsm=	'';
            foreach($match_name_array as $t){
                $lsm	.=	urlencode($t).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
            if($end > $count_num-1) $end = $count_num-1;
            $i	=	0;
            for($b=$start; $b<=$end; $b++){
                if(isset($gq_un[$rows[$b]["Match_ID"]])){
                    continue;
                }
                $json["db"][$i]["Match_ID"]			=	$rows[$b]["Match_ID"];     		   //  0
                $json["db"][$i]["Match_Master"]		=	$rows[$b]["Match_Master"];         //  1
                $json["db"][$i]["Match_Guest"]		=	$rows[$b]["Match_Guest"];          //  2
                $json["db"][$i]["Match_Name"]		=	$rows[$b]["Match_Name"];     	   //  3
                $json["db"][$i]["Match_Time"]		=	$rows[$b]["Match_Time"];           //  4
                $json["db"][$i]["Match_Ho"]			=	NOnull($rows[$b]["Match_Ho"]);     //  5
                $json["db"][$i]["Match_DxDpl"]		=	NOnull($rows[$b]["Match_DxDpl"]);  //  6
                $json["db"][$i]["Match_BHo"]		=	NOnull($rows[$b]["Match_BHo"]);    //  7
                $json["db"][$i]["Match_Bdpl"]		=	NOnull($rows[$b]["Match_Bdpl"]);   //  8
                $json["db"][$i]["Match_Ao"]			=	NOnull($rows[$b]["Match_Ao"]);     //  9
                $json["db"][$i]["Match_DxXpl"]		=	NOnull($rows[$b]["Match_DxXpl"]);  //  10
                $json["db"][$i]["Match_BAo"]		=	NOnull($rows[$b]["Match_BAo"]);    //  11
                $json["db"][$i]["Match_Bxpl"]		=	NOnull($rows[$b]["Match_Bxpl"]);   //  12
                $json["db"][$i]["Match_RGG"]		=	$rows[$b]["Match_RGG"];    //  13
                $json["db"][$i]["Match_BRpk"]		=	$rows[$b]["Match_BRpk"];    // 14
                $json["db"][$i]["Match_ShowType"]	=	$rows[$b]["Match_ShowType"]; // 15
                $json["db"][$i]["Match_Hr_ShowType"]=	$rows[$b]["Match_Hr_ShowType"]; // 16
                $json["db"][$i]["Match_DxGG"]		=	"O".$rows[$b]["Match_DxGG"];     	// 17
                $json["db"][$i]["Match_Bdxpk"]		=	"O".$rows[$b]["Match_Bdxpk"];      //   18
                $json["db"][$i]["Match_DxGG1"]		=	"U".$rows[$b]["Match_DxGG"];        //  19
                $json["db"][$i]["Match_Bdxpk2"]		=	"U".$rows[$b]["Match_Bdxpk"];     	//  20
                $json["db"][$i]["Match_HRedCard"]	=	NOkong($rows[$b]["Match_HRedCard"]); // 21
                $json["db"][$i]["Match_GRedCard"]	=	NOkong($rows[$b]["Match_GRedCard"]); // 22
                $json["db"][$i]["Match_NowScore"]	=	NOkong($rows[$b]["Match_NowScore"]); // 23
                $json["db"][$i]["Match_BzM"]		=	NOkong($rows[$b]["Match_BzM"]);     //  24
                $json["db"][$i]["Match_BzG"]		=	NOkong($rows[$b]["Match_BzG"]);     //  25
                $json["db"][$i]["Match_BzH"]		=	NOkong($rows[$b]["Match_BzH"]);     //  26
                $json["db"][$i]["Match_Bmdy"]		=	NOkong($rows[$b]["Match_Bmdy"]);    //  27
                $json["db"][$i]["Match_Bgdy"]		=	NOkong($rows[$b]["Match_Bgdy"]);    //  28
                $json["db"][$i]["Match_Bhdy"]		=	NOkong($rows[$b]["Match_Bhdy"]);    //  29
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }

    public function FT_1_1()
    {
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
	$nowdate = date("m-d");
	if('127.0.0.1' == $this->request->server('REMOTE_ADDR')){
	    $now = '2017-08-21 02:53:18';
	    $nowdate = '08-21';
	}
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval(@$_GET["CurrPage"])>0) $this_page	=	@$_GET["CurrPage"];
        $this_page++;
        $bk			=	100; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match WHERE Match_Type=1 AND Match_CoverDate>'".$now."' AND Match_Date='".$nowdate."' and Match_HalfId is not null ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db->query($sql);
        foreach($rs as $row){
            if($i >= $start  && $i <= $end ){
                $id = $row['id'].','.$id;
            }
            $i++;
        }
        
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match WHERE Match_Type=1 AND Match_CoverDate>'$now' AND Match_Date='".$nowdate."' and Match_HalfId is not null group by match_name";
            
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_HalfId, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG, Match_Name, Match_IsLose, Match_BzM, Match_BzG, Match_BzH, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_ShowType, Match_Type,  Match_Bmdy, Match_BHo,  Match_Bdpl, Match_Bgdy, Match_BAo, Match_Bxpl, Match_Bhdy, Match_BRpk, Match_Hr_ShowType, Match_Bdxpk FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db->query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];
                $mdate	=	$rows["Match_Date"]."<br/>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;
                $rows["Match_BzM"]<>""?$a=$rows["Match_BzM"]:$a=0;
                $json["db"][$i]["Match_BzM"]		=	$a;
                double_format($rows["Match_Ho"])<>""?$b=double_format($rows["Match_Ho"]):$b=0;
                $json["db"][$i]["Match_Ho"]			=	$b;
                $rows["Match_DxDpl"]<>""?$c=$rows["Match_DxDpl"]:$c=0;
                $json["db"][$i]["Match_DxDpl"]		=	$c;
                //$rows["Match_DsDpl"]<>""?$d=$rows["Match_DsDpl"]:$d=0;
                //$json["db"][$i]["Match_DsDpl"]		=	$d;
                $rows["Match_BzG"]<>""?$e=$rows["Match_BzG"]:$e=0;
                $json["db"][$i]["Match_BzG"]		=	$e;
                $rows["Match_Ao"]<>""?$f=$rows["Match_Ao"]:$f=0;
                $json["db"][$i]["Match_Ao"]			=	$f;
                $rows["Match_DxXpl"]<>""?$g=$rows["Match_DxXpl"]:$g=0;
                $json["db"][$i]["Match_DxXpl"]		=	$g;
                //$rows["Match_DsSpl"]<>""?$h=$rows["Match_DsSpl"]:$h=0;
                //$json["db"][$i]["Match_DsSpl"]		=	$h;
                $rows["Match_BzH"]<>""?$k=$rows["Match_BzH"]:$k=0;
                $json["db"][$i]["Match_BzH"]		=	$k;
                $rows["Match_RGG"]<>""?$j=$rows["Match_RGG"]:$j=0;
                $json["db"][$i]["Match_RGG"]		=	$j;
                $rows["Match_DxGG"]<>""?$m="O".$rows["Match_DxGG"]:$m=0;
                $json["db"][$i]["Match_DxGG1"]		=	$m;
                $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];
                $rows["Match_DxGG"]<>""?$n="U".$rows["Match_DxGG"]:$n=0;
                $json["db"][$i]["Match_DxGG2"]		=	$n;
                $json["db"][$i]["Match_Bmdy"]		=	$rows["Match_Bmdy"];     ///////////     5
                $json["db"][$i]["Match_BHo"]		=	$rows["Match_BHo"];     ///////////     6
                $json["db"][$i]["Match_Bdpl"]		=	$rows["Match_Bdpl"];     ///////////     7
                $json["db"][$i]["Match_Bgdy1"]		=	$rows["Match_Bgdy"];     ///////////     8
                $json["db"][$i]["Match_Bgdy2"]		=	$rows["Match_Bgdy"];     ///////////     9
                $json["db"][$i]["Match_BAo"]		=	$rows["Match_BAo"];     ///////////     10
                $json["db"][$i]["Match_Bxpl"]		=	$rows["Match_Bxpl"];     ///////////     11
                $json["db"][$i]["Match_Bhdy1"]		=	$rows["Match_Bhdy"];     ///////////     12
                $json["db"][$i]["Match_Bhdy2"]		=	$rows["Match_Bhdy"];     ///////////     13
                $json["db"][$i]["Match_BRpk"]		=	$rows["Match_BRpk"];     ///////////     14
                $json["db"][$i]["Match_Bdxpk1"]		=	"O".$rows["Match_Bdxpk"];     ///////////     15
                $json["db"][$i]["Match_Hr_ShowType"]=	$rows["Match_Hr_ShowType"];     ///////////     16
                $json["db"][$i]["Match_Bdxpk2"]		=	"U".$rows["Match_Bdxpk"];     ///////////     17
                $i++;
            }
        }
        echo $callback."(".json_encode($json).")";
        exit;
    }

    public function FT_1_2()
    {
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	40; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match where Match_Type=1 and Match_IsShowt=1 and Match_DsDpl>0 AND Match_CoverDate>'".date("Y-m-d H:i:s")."'".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db->query($sql);
        foreach ($rs as $row){
            if($i >= $start && $i <= $end){
                $id = $row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match where Match_Type=1 and Match_IsShowt=1 and Match_DsDpl>0 AND Match_CoverDate>'$now' group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach ($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_Name, Match_IsLose, Match_BzM, Match_DsDpl, Match_DsSpl, Match_Total01Pl, Match_Total23Pl, Match_Total46Pl, Match_Total7upPl, Match_BzG, Match_BzH FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];     ///////////  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];     ///////////   1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];     ///////////    2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     ///////////     3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     ///////////               4
                $json["db"][$i]["Match_BzM"]		=	$rows["Match_BzM"];     ///////////  5
                $rows["Match_DsDpl"]<>""?$d=$rows["Match_DsDpl"]:$d=0;
                $json["db"][$i]["Match_DsDpl"]		=	$d;     ///////////8
                $rows["Match_DsSpl"]<>""?$h=$rows["Match_DsSpl"]:$h=0;
                $json["db"][$i]["Match_DsSpl"]		=	$h;     ///////////12
                $json["db"][$i]["Match_Total01Pl"]	=	$rows["Match_Total01Pl"];     ///////////   6
                $json["db"][$i]["Match_Total23Pl"]	=	$rows["Match_Total23Pl"];     ///////////    7
                $json["db"][$i]["Match_Total46Pl"]	=	$rows["Match_Total46Pl"];     ///////////     8
                $json["db"][$i]["Match_Total7upPl"]	=	$rows["Match_Total7upPl"];     ///////////   9
                $json["db"][$i]["Match_BzG"]		=	$rows["Match_BzG"];     ///////////    10
                $json["db"][$i]["Match_BzH"]		=	$rows["Match_BzH"];     ///////////     11
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
        exit;
    }

    public function FT_1_3()
    {
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	100; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match WHERE Match_Date='".date("m-d")."' and Match_CoverDate>'".date("Y-m-d H:i:s")."' and Match_BqMM>0 ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db -> query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match WHERE Match_Date='".date("m-d")."' and Match_CoverDate>'$now' and Match_BqMM>0 group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_Name, Match_IsLose, Match_BqMM, Match_BqMH,  Match_BqMG, Match_BqHM, Match_BqHH, Match_BqHG, Match_BqGM, Match_BqGH, Match_BqGG FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach ( $rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];       //  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];   //  1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];    //  2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     //  3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     //               4
                $json["db"][$i]["Match_BqMM"]		=	NOnull($rows["Match_BqMM"]);  //  5
                $json["db"][$i]["Match_BqMH"]		=	NOnull($rows["Match_BqMH"]);      //  6
                $json["db"][$i]["Match_BqMG"]		=	NOnull($rows["Match_BqMG"]);      //  7
                $json["db"][$i]["Match_BqHM"]		=	NOnull($rows["Match_BqHM"]);      //  8
                $json["db"][$i]["Match_BqHH"]		=	NOnull($rows["Match_BqHH"]);      //  9
                $json["db"][$i]["Match_BqHG"]		=	NOnull($rows["Match_BqHG"]);      //  10
                $json["db"][$i]["Match_BqGM"]		=	NOnull($rows["Match_BqGM"]);      //  11
                $json["db"][$i]["Match_BqGH"]		=	NOnull($rows["Match_BqGH"]);      //  12
                $json["db"][$i]["Match_BqGG"]		=	NOnull($rows["Match_BqGG"]);      //  13
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
        exit();
    }
    
    public function FT_1_4(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	40; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match where Match_Type=1 and Match_IsShowbd=1 and Match_CoverDate>'".date("Y-m-d H:i:s")."' and Match_Bd21>0 ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db -> query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match where Match_Type=1 and Match_IsShowbd=1 and Match_CoverDate>'$now' and Match_Bd21>0 group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_Name, Match_IsLose, Match_Bd10, Match_Bd20, Match_Bd21, Match_Bd30, Match_Bd31, Match_Bd32, Match_Bd40, Match_Bd41, Match_Bd42, Match_Bd43, Match_Bd00, Match_Bd11, Match_Bd22, Match_Bd33, Match_Bd44, Match_Bdup5, Match_Bdg10, Match_Bdg20, Match_Bdg21, Match_Bdg30, Match_Bdg31, Match_Bdg32, Match_Bdg40, Match_Bdg41, Match_Bdg42, Match_Bdg43 FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];     ///////////  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];     ///////////   1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];     ///////////    2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     ///////////     3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     ///////////               4
                $json["db"][$i]["Match_Bd10"]		=	$rows["Match_Bd10"];     ///////////     5
                $json["db"][$i]["Match_Bd20"]		=	$rows["Match_Bd20"];     ///////////     6
                $json["db"][$i]["Match_Bd21"]		=	$rows["Match_Bd21"];     ///////////     7
                $json["db"][$i]["Match_Bd30"]		=	$rows["Match_Bd30"];     ///////////     8
                $json["db"][$i]["Match_Bd31"]		=	$rows["Match_Bd31"];     ///////////     9
                $json["db"][$i]["Match_Bd32"]		=	$rows["Match_Bd32"];     ///////////     10
                $json["db"][$i]["Match_Bd40"]		=	$rows["Match_Bd40"];     ///////////     11
                $json["db"][$i]["Match_Bd41"]		=	$rows["Match_Bd41"];     ///////////     12
                $json["db"][$i]["Match_Bd42"]		=	$rows["Match_Bd42"];     ///////////     13
                $json["db"][$i]["Match_Bd43"]		=	$rows["Match_Bd43"];     ///////////     14
                $json["db"][$i]["Match_Bd00"]		=	$rows["Match_Bd00"];     ///////////     15
                $json["db"][$i]["Match_Bd11"]		=	$rows["Match_Bd11"];     ///////////     16
                $json["db"][$i]["Match_Bd22"]		=	$rows["Match_Bd22"];     ///////////     17
                $json["db"][$i]["Match_Bd33"]		=	$rows["Match_Bd33"];     ///////////     18
                $json["db"][$i]["Match_Bd44"]		=	$rows["Match_Bd44"];     ///////////     19
                $json["db"][$i]["Match_Bdup5"]		=	$rows["Match_Bdup5"];     ///////////     20
                $json["db"][$i]["Match_Bdg10"]		=	$rows["Match_Bdg10"];     ///////////     21
                $json["db"][$i]["Match_Bdg20"]		=	$rows["Match_Bdg20"];     ///////////     22
                $json["db"][$i]["Match_Bdg21"]		=	$rows["Match_Bdg21"];     ///////////     23
                $json["db"][$i]["Match_Bdg30"]		=	$rows["Match_Bdg30"];     ///////////     24
                $json["db"][$i]["Match_Bdg31"]		=	$rows["Match_Bdg31"];     ///////////     25
                $json["db"][$i]["Match_Bdg32"]		=	$rows["Match_Bdg32"];     ///////////     26
                $json["db"][$i]["Match_Bdg40"]		=	$rows["Match_Bdg40"];     ///////////     27
                $json["db"][$i]["Match_Bdg41"]		=	$rows["Match_Bdg41"];     ///////////     28
                $json["db"][$i]["Match_Bdg42"]		=	$rows["Match_Bdg42"];     ///////////     29
                $json["db"][$i]["Match_Bdg43"]		=	$rows["Match_Bdg43"];     ///////////     30
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
        exit();
    }
    
    public function FT_2_1(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match WHERE Match_Type=0 AND Match_CoverDate>'".date("Y-m-d H:i:s")."' and match_date!='".date("m-d")."' ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db->query($sql);
        foreach ($rs as $row){
            if($i >= $start && $i <= $end){
                $id = $row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json['dh'] = 0;
            $json['fy']['p_page'] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
            
            $sql	=	"select match_name FROM bet_match WHERE Match_Type=0 AND Match_CoverDate>'$now' group by match_name";
            $rs = $db -> query($sql);
            $i = 0;
            $lsm = '';
            foreach ($rs as $row){
                $lsm .= urlencode($row['match_name']).'|';
                $i++;
            }
            $json['lsm'] = rtrim($lsm,'|');
            $json['dh']  = ceil($i/3) * 30;
            
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_HalfId, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG, Match_Name, Match_IsLose, Match_BzM, Match_BzG, Match_BzH, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_ShowType, Match_Type,  Match_Bmdy, Match_BHo,  Match_Bdpl, Match_Bgdy, Match_BAo, Match_Bxpl, Match_Bhdy, Match_BRpk, Match_Hr_ShowType, Match_Bdxpk FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i  =	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];     ///////////  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];     ///////////   1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];     ///////////    2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     ///////////     3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     ///////////               4
                $rows["Match_BzM"]<>""?$a=$rows["Match_BzM"]:$a=0;
                $json["db"][$i]["Match_BzM"]		=	$a;     ///////////       5
                double_format($rows["Match_Ho"])<>""?$b=double_format($rows["Match_Ho"]):$b=0;
                $json["db"][$i]["Match_Ho"]			=	$b;     ///////////6
                $rows["Match_DxDpl"]<>""?$c=$rows["Match_DxDpl"]:$c=0;
                $json["db"][$i]["Match_DxDpl"]		=	$c;     ///////////7
                //$rows["Match_DsDpl"]<>""?$d=$rows["Match_DsDpl"]:$d=0;
                //$json["db"][$i]["Match_DsDpl"]		=	$d;     ///////////8
                $rows["Match_BzG"]<>""?$e=$rows["Match_BzG"]:$e=0;
                $json["db"][$i]["Match_BzG"]		=	$e;     ///////////9
                $rows["Match_Ao"]<>""?$f=$rows["Match_Ao"]:$f=0;
                $json["db"][$i]["Match_Ao"]			=	$f;     ///////////10
                $rows["Match_DxXpl"]<>""?$g=$rows["Match_DxXpl"]:$g=0;
                $json["db"][$i]["Match_DxXpl"]		=	$g;     ///////////11
                //$rows["Match_DsSpl"]<>""?$h=$rows["Match_DsSpl"]:$h=0;
                //$json["db"][$i]["Match_DsSpl"]		=	$h;     ///////////12
                $rows["Match_BzH"]<>""?$k=$rows["Match_BzH"]:$k=0;
                $json["db"][$i]["Match_BzH"]		=	$k;     ///////////13
                $rows["Match_RGG"]<>""?$j=$rows["Match_RGG"]:$j=0;
                $json["db"][$i]["Match_RGG"]		=	$j;     ///////////14
                $rows["Match_DxGG"]<>""?$m="O".$rows["Match_DxGG"]:$m=0;
                $json["db"][$i]["Match_DxGG1"]		=	$m;     ///////////15
                $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];/////////16
                $rows["Match_DxGG"]<>""?$n="U".$rows["Match_DxGG"]:$n=0;
                $json["db"][$i]["Match_DxGG2"]		=	$n;     ///////////17
                $json["db"][$i]["Match_Bmdy"]		=	$rows["Match_Bmdy"];     ///////////     5
                $json["db"][$i]["Match_BHo"]		=	$rows["Match_BHo"];     ///////////     6
                $json["db"][$i]["Match_Bdpl"]		=	$rows["Match_Bdpl"];     ///////////     7
                $json["db"][$i]["Match_Bgdy1"]		=	$rows["Match_Bgdy"];     ///////////     8
                $json["db"][$i]["Match_Bgdy2"]		=	$rows["Match_Bgdy"];     ///////////     9
                $json["db"][$i]["Match_BAo"]		=	$rows["Match_BAo"];     ///////////     10
                $json["db"][$i]["Match_Bxpl"]		=	$rows["Match_Bxpl"];     ///////////     11
                $json["db"][$i]["Match_Bhdy1"]		=	$rows["Match_Bhdy"];     ///////////     12
                $json["db"][$i]["Match_Bhdy2"]		=	$rows["Match_Bhdy"];     ///////////     13
                $json["db"][$i]["Match_BRpk"]		=	$rows["Match_BRpk"];     ///////////     14
                $json["db"][$i]["Match_Bdxpk1"]		=	"O".$rows["Match_Bdxpk"];     ///////////     15
                $json["db"][$i]["Match_Hr_ShowType"]=	$rows["Match_Hr_ShowType"];     ///////////     16
                $json["db"][$i]["Match_Bdxpk2"]		=	"U".$rows["Match_Bdxpk"];     ///////////     17
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
        exit;
    }
    
    public function FT_2_2(){
        header('Content-type: text/json; charset=utf-8');
        $now = date("Y-m-d H:i:s");
        $db = Db::connect(Config::get('sportdb'));
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match where Match_Type=0 and Match_IsShowt=1 AND Match_CoverDate>'".date("Y-m-d H:i:s")."' and Match_Total01Pl>0 ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db -> query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match where Match_Type=0 and Match_IsShowt=1 AND Match_CoverDate>'$now' and Match_Total01Pl>0 group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_Name, Match_IsLose, Match_BzM, Match_DsDpl, Match_DsSpl, Match_Total01Pl, Match_Total23Pl, Match_Total46Pl, Match_Total7upPl, Match_BzG, Match_BzH FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];     ///////////  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];     ///////////   1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];     ///////////    2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     ///////////     3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     ///////////               4
                $json["db"][$i]["Match_BzM"]		=	$rows["Match_BzM"];     ///////////  5
                $rows["Match_DsDpl"]<>""?$d=$rows["Match_DsDpl"]:$d=0;
                $json["db"][$i]["Match_DsDpl"]		=	$d;     ///////////8
                $rows["Match_DsSpl"]<>""?$h=$rows["Match_DsSpl"]:$h=0;
                $json["db"][$i]["Match_DsSpl"]		=	$h;     ///////////12
                $json["db"][$i]["Match_Total01Pl"]	=	$rows["Match_Total01Pl"];     ///////////   6
                $json["db"][$i]["Match_Total23Pl"]	=	$rows["Match_Total23Pl"];     ///////////    7
                $json["db"][$i]["Match_Total46Pl"]	=	$rows["Match_Total46Pl"];     ///////////     8
                $json["db"][$i]["Match_Total7upPl"]	=	$rows["Match_Total7upPl"];     ///////////   9
                $json["db"][$i]["Match_BzG"]		=	$rows["Match_BzG"];     ///////////    10
                $json["db"][$i]["Match_BzH"]		=	$rows["Match_BzH"];     ///////////     11
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
        exit;
    }
    
    public function FT_2_3(){
        $db = Db::connect(Config::get('sportdb'));
        header('Content-type: text/json; charset=utf-8');
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match WHERE Match_Date<>'".date("m-d")."' and Match_CoverDate>'".date("Y-m-d H:i:s")."' and Match_BqMM>0 ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db -> query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match WHERE Match_Date<>'".date("m-d")."' and Match_CoverDate>'$now' and Match_BqMM>0 group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach ($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_Name, Match_IsLose, Match_BqMM, Match_BqMH,  Match_BqMG, Match_BqHM, Match_BqHH, Match_BqHG, Match_BqGM, Match_BqGH, Match_BqGG FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];       //  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];   //  1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];    //  2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     //  3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     //               4
                $json["db"][$i]["Match_BqMM"]		=	NOnull($rows["Match_BqMM"]);  //  5
                $json["db"][$i]["Match_BqMH"]		=	NOnull($rows["Match_BqMH"]);      //  6
                $json["db"][$i]["Match_BqMG"]		=	NOnull($rows["Match_BqMG"]);      //  7
                $json["db"][$i]["Match_BqHM"]		=	NOnull($rows["Match_BqHM"]);      //  8
                $json["db"][$i]["Match_BqHH"]		=	NOnull($rows["Match_BqHH"]);      //  9
                $json["db"][$i]["Match_BqHG"]		=	NOnull($rows["Match_BqHG"]);      //  10
                $json["db"][$i]["Match_BqGM"]		=	NOnull($rows["Match_BqGM"]);      //  11
                $json["db"][$i]["Match_BqGH"]		=	NOnull($rows["Match_BqGH"]);      //  12
                $json["db"][$i]["Match_BqGG"]		=	NOnull($rows["Match_BqGG"]);      //  13
                $i++;
            }
        }
        
        echo $callback."(".json_encode($json).");";
    }
    
    public function FT_2_4(){
        $db = Db::connect(Config::get('sportdb'));
        header('Content-type: text/json; charset=utf-8');
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id FROM bet_match where Match_Type=0 and Match_IsShowbd=1 and Match_CoverDate>'".date("Y-m-d H:i:s")."' and Match_Bd21>0 ".$sqlwhere.' order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn';
        $rs = $db ->query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name FROM bet_match where Match_Type=0 and Match_IsShowbd=1 and Match_CoverDate>'$now' and Match_Bd21>0 group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_Name, Match_IsLose, Match_Bd10, Match_Bd20, Match_Bd21, Match_Bd30, Match_Bd31, Match_Bd32, Match_Bd40, Match_Bd41, Match_Bd42, Match_Bd43, Match_Bd00, Match_Bd11, Match_Bd22, Match_Bd33, Match_Bd44, Match_Bdup5, Match_Bdg10, Match_Bdg20, Match_Bdg21, Match_Bdg30, Match_Bdg31, Match_Bdg32, Match_Bdg40, Match_Bdg41, Match_Bdg42, Match_Bdg43 FROM bet_match where id in($id) order by Match_CoverDate,iPage,match_name,Match_Master,Match_ID,iSn";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach ($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];     ///////////  0
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];     ///////////   1
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];     ///////////    2
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];     ///////////     3
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;     ///////////               4
                $json["db"][$i]["Match_Bd10"]		=	$rows["Match_Bd10"];     ///////////     5
                $json["db"][$i]["Match_Bd20"]		=	$rows["Match_Bd20"];     ///////////     6
                $json["db"][$i]["Match_Bd21"]		=	$rows["Match_Bd21"];     ///////////     7
                $json["db"][$i]["Match_Bd30"]		=	$rows["Match_Bd30"];     ///////////     8
                $json["db"][$i]["Match_Bd31"]		=	$rows["Match_Bd31"];     ///////////     9
                $json["db"][$i]["Match_Bd32"]		=	$rows["Match_Bd32"];     ///////////     10
                $json["db"][$i]["Match_Bd40"]		=	$rows["Match_Bd40"];     ///////////     11
                $json["db"][$i]["Match_Bd41"]		=	$rows["Match_Bd41"];     ///////////     12
                $json["db"][$i]["Match_Bd42"]		=	$rows["Match_Bd42"];     ///////////     13
                $json["db"][$i]["Match_Bd43"]		=	$rows["Match_Bd43"];     ///////////     14
                $json["db"][$i]["Match_Bd00"]		=	$rows["Match_Bd00"];     ///////////     15
                $json["db"][$i]["Match_Bd11"]		=	$rows["Match_Bd11"];     ///////////     16
                $json["db"][$i]["Match_Bd22"]		=	$rows["Match_Bd22"];     ///////////     17
                $json["db"][$i]["Match_Bd33"]		=	$rows["Match_Bd33"];     ///////////     18
                $json["db"][$i]["Match_Bd44"]		=	$rows["Match_Bd44"];     ///////////     19
                $json["db"][$i]["Match_Bdup5"]		=	$rows["Match_Bdup5"];     ///////////     20
                $json["db"][$i]["Match_Bdg10"]		=	$rows["Match_Bdg10"];     ///////////     21
                $json["db"][$i]["Match_Bdg20"]		=	$rows["Match_Bdg20"];     ///////////     22
                $json["db"][$i]["Match_Bdg21"]		=	$rows["Match_Bdg21"];     ///////////     23
                $json["db"][$i]["Match_Bdg30"]		=	$rows["Match_Bdg30"];     ///////////     24
                $json["db"][$i]["Match_Bdg31"]		=	$rows["Match_Bdg31"];     ///////////     25
                $json["db"][$i]["Match_Bdg32"]		=	$rows["Match_Bdg32"];     ///////////     26
                $json["db"][$i]["Match_Bdg40"]		=	$rows["Match_Bdg40"];     ///////////     27
                $json["db"][$i]["Match_Bdg41"]		=	$rows["Match_Bdg41"];     ///////////     28
                $json["db"][$i]["Match_Bdg42"]		=	$rows["Match_Bdg42"];     ///////////     29
                $json["db"][$i]["Match_Bdg43"]		=	$rows["Match_Bdg43"];     ///////////     30
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }
    
    public function Tennis(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $now = date('Y-m-d H:i:s');
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id from tennis_match WHERE Match_Type=1 and Match_CoverDate>'".date("Y-m-d H:i:s")."' AND Match_Date='".date("m-d")."' ".$sqlwhere." order by Match_CoverDate,Match_Name";
        $rs = $db -> query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name from tennis_match WHERE Match_Type=1 and Match_CoverDate>'$now' AND Match_Date='".date("m-d")."' group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach ($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"select Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG, Match_Name, Match_IsLose, Match_BzM, Match_BzG,  Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_MasterID, Match_GuestID, Match_Type, Match_DsDpl, Match_DsSpl,Match_ShowType from tennis_match where id in($id) order by Match_CoverDate,match_name";
            $rs = $db ->query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color=red>滾球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;
                $json["db"][$i]["Match_Ho"]			=	$rows["Match_Ho"];
                $json["db"][$i]["Match_DxDpl"]		=	$rows["Match_DxDpl"];
                $json["db"][$i]["Match_DsDpl"]		=	$rows["Match_DsDpl"];
                $json["db"][$i]["Match_Ao"]			=	$rows["Match_Ao"];
                $json["db"][$i]["Match_DxXpl"]		=	$rows["Match_DxXpl"];
                $json["db"][$i]["Match_DsSpl"]		=	$rows["Match_DsSpl"];
                $json["db"][$i]["Match_RGG"]		=	$rows["Match_RGG"];
                $json["db"][$i]["Match_DxGG1"]		=	"O".$rows["Match_DxGG"];
                $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];
                $json["db"][$i]["Match_DxGG2"]		=	"U".$rows["Match_DxGG"];
                $json["db"][$i]["Match_BzM"]		=	$rows["Match_BzM"];
                $json["db"][$i]["Match_BzG"]		=	$rows["Match_BzG"];
                $json["db"][$i]["Match_RGG2"]		=	substr($rows["Match_RGG"],0,1);
                $json["db"][$i]["Match_RGG3"]		=	$rows["Match_RGG"];
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }
    
    public function Volleyball(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id from volleyball_match WHERE Match_Type=1 AND Match_CoverDate>'".date("Y-m-d H:i:s")."' AND Match_Date='".date("m-d")."' ".$sqlwhere.' order by Match_CoverDate,match_name';
        $rows = $db->query($sql);
        foreach ($rows as $row){
            if($i >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name from volleyball_match WHERE Match_Type=1 AND Match_CoverDate>'$now' AND Match_Date='".date("m-d")."' group by match_name";
            $rows = $db->query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rows as $row){
                $lsm .= urlencode($row['match_name']);
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"select Match_ID, Match_Master, Match_Guest, Match_Name, Match_Time, Match_Date, Match_IsLose, Match_Ho, Match_DxDpl,  Match_DsDpl, Match_Ao, Match_DxXpl, Match_DsSpl, Match_RGG, Match_DxGG, Match_ShowType, Match_BzM, Match_BzG from volleyball_match where id in($id) order by Match_CoverDate,match_name";
            $row = $db -> query($sql);
            $i		=	0;
            foreach ($row as $rows ){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];
                $mdate	=	$rows["Match_Date"]."<br>".$rows["Match_Time"];
                if ($rows["Match_IsLose"]==1){
                    $mdate.= "<br><font color='#FF0000'>滚球</font>";
                }
                $json["db"][$i]["Match_Date"]		=	$mdate;
                $json["db"][$i]["Match_Ho"]			=	$rows["Match_Ho"];
                $json["db"][$i]["Match_DxDpl"]		=	$rows["Match_DxDpl"];
                $json["db"][$i]["Match_DsDpl"]		=	$rows["Match_DsDpl"];
                $json["db"][$i]["Match_Ao"]			=	$rows["Match_Ao"];
                $json["db"][$i]["Match_DxXpl"]		=	$rows["Match_DxXpl"];
                $json["db"][$i]["Match_DsSpl"]		=	$rows["Match_DsSpl"];
                $json["db"][$i]["Match_RGG"]		=	$rows["Match_RGG"];
                $json["db"][$i]["Match_DxGG1"]		=	"O".$rows["Match_DxGG"];
                $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];
                $json["db"][$i]["Match_DxGG2"]		=	"U".$rows["Match_DxGG"];
                $json["db"][$i]["Match_BzM"]		=	$rows["Match_BzM"];
                $json["db"][$i]["Match_BzG"]		=	$rows["Match_BzG"];
                $json["db"][$i]["Match_RGG2"]		=	substr($rows["Match_RGG"],0,1);
                $json["db"][$i]["Match_RGG3"]		=	$rows["Match_RGG"];
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }
    
    public function Champion(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	1000; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v x_title='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or x_title='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select x_id from t_guanjun where match_type=1 and match_coverdate>'".date("Y-m-d H:i:s")."' and x_result is null ".$sqlwhere.' order by  match_coverdate,match_name,x_id';
        $rs = $db -> query($sql);
        foreach($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['x_id'].','.$id;
            }
            $i++;
        }
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	=	ceil($i/$bk); //总页数
            $json["fy"]["page"] 	=	$this_page-1;
        
            $sql	=	"select x_title from t_guanjun where match_type=1 and match_coverdate>'$now' and x_result is null group by x_title";
            $rs = $db->query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['x_title']).'|';
                $i++;
            }
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT match_id, x_title, x_id, match_name, match_date, match_time FROM t_guanjun where x_id in($id) order by match_coverdate,match_name,x_id";
            $rs = $db->query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $tid		=	"";
                $team_name	=	"";
                $point		=	"";
                $match_id	=	"";
                $sql		=	"select tid, team_name, point, match_id from t_guanjun_team where xid=".$rows["x_id"]." order by tid desc";
                $rs = $db -> query($sql);
                foreach($rs as $ttrs){
                    $tid		.=	$ttrs["tid"].",";
                    $team_name	.=	$ttrs["team_name"].",";
                    $point		.=	$ttrs["point"].",";
                    $match_id	.=	$ttrs["match_id"].",";
                }
                $json["db"][$i]["Match_ID"]				=	$rows["match_id"];     ///////////  0
                $json["db"][$i]["x_title"]				=	$rows["x_title"];     ///////////   1
                $json["db"][$i]["x_id"]					=	$rows["x_id"];
                $json["db"][$i]["Match_Name"]			=	$rows["match_name"];     ///////////     3
                $json["db"][$i]["Match_Date"]			=	$rows["match_date"]." ".$rows["match_time"];
                $json["db"][$i]["team_name"]			=	$team_name;     ///////////     5
                $json["db"][$i]["point"]				=	$point;
                $json["db"][$i]["tid"]					=	$tid;
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }
    
    public function Baseball(){
        header('Content-type: text/json; charset=utf-8');
        $db = Db::connect(Config::get('sportdb'));
        $now = date("Y-m-d H:i:s");
        $callback="";
        $callback=@$_GET['callback'];
        $this_page	=	0; //当前页
        if(intval($_GET["CurrPage"])>0) $this_page	=	$_GET["CurrPage"];
        $this_page++;
        $bk			=	150; //每页显示多少条记录
        $sqlwhere	=	''; //where 条件
        $id			=	''; //ID字符串
        $i			=	1; //记录总个数
        $start		=	($this_page-1)*$bk+1; //本页记录开始位置
        $end		=	$this_page*$bk; //本页记录结束位置
        //页数统计
        if(@$_GET["leaguename"]<>""){
            $leaguename	 =	explode("$",urldecode($_GET["leaguename"]));
            $v			 =	(count($leaguename)>1 ? 'and (' : 'and' );
            $sqlwhere	.=	" $v match_name='".$leaguename[0]."'";
            for($is=1; $is<count($leaguename)-1; $is++){
                $sqlwhere.=	" or match_name='".$leaguename[$is]."'";
            }
            $sqlwhere	.=	(count($leaguename)>1 ? ')' : '' );
        }
        
        $sql		=	"select id from baseball_match WHERE Match_Type=1 and Match_CoverDate>'".date("Y-m-d H:i:s")."' and Match_Date='".date("m-d")."' ".$sqlwhere.' order by Match_CoverDate,match_name';
        $rs = $db->query($sql);
        foreach ($rs as $row){
            if($i  >= $start && $i <= $end){
                $id	=	$row['id'].','.$id;
            }
            $i++;
        }
        
        if($i == 1){ //未查找到记录
            $json["dh"]	=	0;
            $json["fy"]["p_page"] = 0;
        }else{
            $id			=	rtrim($id,',');
            $json["fy"]["p_page"] 	= ceil($i/$bk); //总页数
            $json["fy"]["page"] 	= $this_page-1;
        
            $sql	=	"select match_name from baseball_match WHERE Match_Type=1 and Match_CoverDate>'$now' and Match_Date='".date("m-d")."' group by match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            $lsm	=	'';
            foreach($rs as $row){
                $lsm	.=	urlencode($row['match_name']).'|';
                $i++;
            }
            
            $json["lsm"]=	rtrim($lsm,'|');
            $json["dh"]	=	ceil($i/3)*30; //窗口高度 px 值
        
            //赛事数据
            $sql	=	"SELECT Match_ID, Match_Date, Match_Time, Match_Master, Match_Guest, Match_RGG,Match_BzM,Match_BzG,Match_BzH, Match_Name, Match_IsLose, Match_DxDpl, Match_DxXpl, Match_DxGG, Match_Ho, Match_Ao, Match_Type, Match_DsDpl, Match_DsSpl, Match_ShowType FROM baseball_match where id in($id) order by Match_CoverDate,match_name";
            $rs = $db -> query($sql);
            $i		=	0;
            foreach($rs as $rows){
                $json["db"][$i]["Match_ID"]			=	$rows["Match_ID"];
                $json["db"][$i]["Match_Name"]		=	$rows["Match_Name"];
                $json["db"][$i]["Match_Date"]		=	$rows["Match_Date"];
                $json["db"][$i]["Match_Time"]		=	$rows["Match_Time"];
                $json["db"][$i]["Match_IsLose"]		=	$rows["Match_IsLose"];
                $json["db"][$i]["Match_Master"]		=	$rows["Match_Master"];
                $json["db"][$i]["Match_Guest"]		=	$rows["Match_Guest"];
                $json["db"][$i]["Match_RGG"]		=	$rows["Match_RGG"];
                $json["db"][$i]["Match_BzM"]		=	$rows["Match_BzM"];
                $json["db"][$i]["Match_BzG"]		=	$rows["Match_BzG"];
                $json["db"][$i]["Match_BzH"]		=	$rows["Match_BzH"];
                $json["db"][$i]["Match_DxXpl"]		=	$rows["Match_DxXpl"];
                $json["db"][$i]["Match_DxDpl"]		=	$rows["Match_DxDpl"];
                $json["db"][$i]["Match_DxGG"]		=	$rows["Match_DxGG"];
                $json["db"][$i]["Match_Ho"]			=	$rows["Match_Ho"];
                $json["db"][$i]["Match_Ao"]			=	$rows["Match_Ao"];
                $json["db"][$i]["Match_Type"]		=	$rows["Match_Type"];
                $json["db"][$i]["Match_DsDpl"]		=	$rows["Match_DsDpl"];
                $json["db"][$i]["Match_DsSpl"]		=	$rows["Match_DsSpl"];
                $json["db"][$i]["Match_ShowType"]	=	$rows["Match_ShowType"];
                $i++;
            }
        }
        echo $callback."(".json_encode($json).");";
    }
    
    public function dialog(){
        $bool	=	false;
        $arr	=	explode('|',urldecode($_GET['lsm']));
        $db = Db::connect(Config::get('sportdb'));
        $now = date('Y-m-d H:i:s');
        $rs = '';
        if($_GET['lsm'] == 'zqzcds'){ //足球早餐单式
            $bool	=	true;
            $sql	=	"select match_name FROM bet_match WHERE Match_Type=0 AND Match_CoverDate>'$now' group by match_name";
        }elseif($_GET['lsm'] == 'zqds'){ //足球单式
            $bool	=	true;
            $sql	=	"select match_name FROM bet_match WHERE Match_Type=1 AND Match_CoverDate>'$now' AND Match_Date='".date("m-d")."' and Match_HalfId is not null group by match_name";
        }elseif($_GET['lsm'] == 'lqds'){ //篮球单式
            $bool	=	true;
            $sql	=	"select match_name from lq_match WHERE Match_Type!=3 AND Match_CoverDate>'$now' AND Match_Date='".date("m-d")."' group by match_name";
        }elseif($_GET['lsm'] == 'gj'){ //冠军
            $bool	=	true;
            $sql	=	"select x_title as match_name from t_guanjun where match_type=1 and match_coverdate>'$now' and x_result is null group by x_title";
        }
        $this->assign('bool',$bool);
        if($bool){
            $rs = $db->query($sql);
        }
        $this->assign('arr',$arr);
        if($rs){
            $this->assign('rs',$rs);
        }
       return $this->fetch('dialog');
    }
    
}