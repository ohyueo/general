<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/6/4
 * Time: 18:56
 */

namespace app\api\controller;
use app\models\GeneralUserLogin;
use app\models\GeneralYuyueClass;
use app\models\GeneralYuyueList;
use app\models\GeneralYuyueImg;
use app\models\GeneralYuyueTime;
use app\models\GeneralYuyueOrder;
use app\BaseController;
use think\Request;
use app\validate\api\yue;
use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;
use app\handler\YuyueListmsgHandler;
use app\models\GeneralYuyueSeat;
use app\models\GeneralYuyuePersonnel;
use app\models\GeneralYuyueYuyuespec;
use app\models\GeneralNotiList;

class Yuyue extends BaseController
{
    public function yuelist(){
        $where=[];
        $id=input("param.id/d");
        $title='';
        $classlist=[];
        if($id){
            $where=array('classid'=>$id);
            $title=GeneralYuyueClass::where('id',$id)->value('title');
            $classlist=GeneralYuyueClass::where('status',1)->select();
        }else{
            $classlist=GeneralYuyueClass::where('status',1)->select()->toArray();
            if($classlist){
                $id=$classlist[0]['id'];
                $where=array('classid'=>$id);
            }
        }
        $list=GeneralYuyueList::where('status',1)
            ->where($where)
            ->order('paiid desc')
            ->field(['id','title','bao','mobile','img','address','lat','lng','is_info','recommended'])
            ->select();
        if($list){
            for($i=0;$i<count($list);$i++){
                $list[$i]['val']=0;
                $id=$list[$i]['id'];
                $is_info=$list[$i]['is_info'];
                $opst=0;//默认
                if($is_info==1){
                    $opst=1;//显示详情页
                }else if($is_info==2){
                    $ist=GeneralYuyueTime::where('list_id',$id)->where('status',1)->count();
                    if($ist){
                        //判断有无人员  有则打开人员和时间
                        $res=GeneralYuyuePersonnel::where('list_id',$id)->where('status',1)->count();
                        if($res){
                            $opst=5;//打开人员
                        }else{
                            $opst=2;//打开时间
                        }
                    }else{
                        //判断有无座位
                        $isz=GeneralYuyueSeat::where('list_id',$id)->where('status',1)->count();
                        if($isz){
                            $opst=4;//打开座位
                        }else{
                            $opst=3;//打开订单
                        }
                    }
                }
                $list[$i]['opst']=$opst;
            }
        }
        $arr['list']=$list;
        $arr['title']=$title;
        $arr['classlist']=$classlist;
        return $this->message('请求成功', $arr);
    }
    //获取分类和产品数据
    public function getclass(Request $request){
        $params = $request->get();
        $where=[];
        if($params['id']){
            $where=['class'=>$params['id']];
        }
        $list=GeneralYuyueList::where('status',1)->where($where)->field(['id','img','title'])->select();
        $class=GeneralYuyueClass::select();
        $arr['list']=$list;
        $arr['class']=$class;
        return $this->message('请求成功', $arr);
    }
    //预约详情
    public function yuyue_info(Request $request){
        $id=input("param.id");
        if(!$id){
            return $this->message('id不能为空', [],0);
        }
        $lunbo=GeneralYuyueImg::where('acid',$id)->select()->toArray();

        $data=GeneralYuyueList::where('id',$id)->where('status',1)->find();
        $data->inc('pv');
        $data->save();
        $ordno=GeneralYuyueOrder::where('list_id',$id)->where('status','<',4)->count();
        //查询报名时间是否已经结束了
        $start=0;
        //是否报名已满了
        if($data['zno'] && $ordno>=$data['zno']){
            $start=2;//报名已满
        }
        $data['start']=$start;

        //$data['bao']=$ordno;//实际报名数量
        if(!$data['money']){
            $data['money']=0;
        }
        $data['ordno']=$ordno;
        $classarr=[];//预约类型
        $classarr=GeneralYuyueYuyuespec::where('list_id',$id)->order('paiid desc,id asc')->select()->toArray();
        if($classarr){
            //查询单个规格是否已经满了
            foreach ($classarr as $key=>&$value){
                $sid=$value['id'];
                $no=$value['number'];
                //该规格已经报名数量
                $bno=GeneralYuyueOrder::where('list_id',$id)
                    ->where('specid',$sid)
                    ->where('status','<',4)->count();
                //如果已经报名的数量大于等于可以预约的数量
                if($no && $bno>=$no){
                    $value['number']=0;
                }
                if(!$no){
                    $value['number']=999;
                }
                if($bno>0 && $no>0){
                    $value['number']=$no-$bno;
                }
            }
        }

        //查询是否有这个预约的设置时间  如果有则选择时间
        $opst=0;
        $ist=GeneralYuyueTime::where('list_id',$id)->where('status',1)->count();
        if($ist){
            $opst=2;//打开时间
            //判断有无人员  有则打开人员和时间
            $res=GeneralYuyuePersonnel::where('list_id',$id)->where('status',1)->count();
            if($res){
                $opst=5;//打开人员
            }
            //判断有无规格  如果有规格则弹出规格
            $isz=GeneralYuyueYuyuespec::where('list_id',$id)->where('status',1)->count();
            if($isz){
                $opst=6;//打开规格
            }
        }else{
            //判断有无座位
            $isz=GeneralYuyueSeat::where('list_id',$id)->where('status',1)->count();
            if($isz){
                $opst=4;//打开座位
            }else{
                $opst=3;//打开订单
            }
        }
        //是否显示详情标题
        $isinfoshow=0;
        $data = [
            'lunbo' => $lunbo,
            'list' => $data,
            'opst' => $opst,
            'isinfoshow' => $isinfoshow,
            'classarr' => $classarr
        ];
        return $this->message('请求成功', $data);
    }
    //查询日期和星期
    public function getydate(Request $request){
        $params = $request->get();
        if(!$params['id']){
            return $this->message('请求错误', [], 0);
        }
        $dayno=config('-timesite.time_yuedayno')?:7;//可预约天数
        $res=GeneralYuyueList::where('id',$params['id'])->where('status',1)->field(['dayno','startingday'])->find();
        if($res){
            if($res['dayno']){
                $dayno=$res['dayno'];
            }
        }
        
        //查询设置的  预约起始时间
        $startingday=$res['startingday']?:0;
        
        $newarr=[];
        //查询多少天的日期
        $date=week($dayno);
        for($i=0;$i<count($date);$i++){
            //查询今天是否可预约
            $riqi=$date[$i]['riqi'];
            $date[$i]['day']=date('m月d号',strtotime($riqi));
            $wek=date("w",strtotime($riqi));
            if($wek==0){
                $wek=7;
            }
            //可以预约的 时间戳
            $star=strtotime(date("Y-m-d",strtotime("+".$startingday." day")));
            if(strtotime($riqi) < $star){
                continue;
            }

            //先查询当前日期是否有  为了兼容  做的比较复杂  这里查询的是多条数据
            $res=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$params['id'])->where('p_val','like','%'.$riqi.'%')->where('status',1)->order('paiid desc,id desc')->count();
            if(!$res){
                //如果没有单独设置日期  那则根据星期来查询
                $res=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$params['id'])->where('p_val','like','%'.$wek.'%')->where('status',1)->order('paiid desc,id desc')->count();
            }
            //如果没有设置当时间则不显示
            if($res){
                //添加数组
                array_push($newarr,$date[$i]);
            }
        }
        $arr['date']=$newarr;
        //查询是否选择座位
        $opst=0;
        $isz=GeneralYuyueSeat::where('list_id',$params['id'])->where('status',1)->count();
        if($isz){
            $opst=4;//打开座位
        }else{
            $opst=3;//打开订单
        }
        $arr['opst']=$opst;
        return $this->message('请求成功', $arr);
    }
    //查询时间和是否可以预约
    public function getytime(Request $request){
        $uid=0;
        if(input('param.token')){
            $user = $this->user();
            if(!$user){
                return $this->message('重新登录', [], 201);
            }
            $uid=$user->id;
        }
        $params = $request->post();
        if(!$params['id']){
            return $this->message('请求错误', [], 0);
        }
        if(!$params['riqi']){
            return $this->message('请求时间错误', [], 0);
        }
        $list_id=intval($params['id']);
        $riqi=$params['riqi'];
        $wek=date("w",strtotime($riqi));
        if($wek==0){
            $wek=7;
        }
        //先查询当前日期是否有  为了兼容  做的比较复杂  这里查询的是多条数据
        $res=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$list_id)->where('p_val','like','%'.$riqi.'%')->where('status',1)->where('closed',1)->order('paiid desc,id desc')->select()->toArray();
        if(!$res){
            //如果没有单独设置日期  那则根据星期来查询
            $res=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$list_id)->where('p_val','like','%'.$wek.'%')->where('status',1)->where('closed',1)->order('paiid desc,id desc')->select()->toArray();
        }
        $nowtime=time();

        $isting=1;//是否可以预约
        $startingday=0;//可预约几天后的订单
        if($startingday){
            //可以预约的 时间戳
            $star=strtotime(date("Y-m-d",strtotime("+".$startingday." day")));
            if(strtotime($riqi) < $star){
                $isting=0;//不可以预约
            }
        }

        $cid=input('param.cid/d')??0;//规格id

        //查询时间
        if($res){
            for($i=0;$i<count($res);$i++){
                $t_val=$res[$i]['t_val'];//时间的值
                $tval=explode("|",$t_val);
                $number=$res[$i]['number'];//可预约人数
                //如果设置了规格id则查询该规格的可预约人数
                if($cid){
                    $spec=GeneralYuyueYuyuespec::where('id',$cid)->where('status',1)->find();
                    if($spec){
                        $number=$spec['number'];
                    }
                }
                $closed=$res[$i]['closed'];//2关闭  1开放
                //遍历时间
                $yval=[];
                if($tval){
                    for($y=0;$y<count($tval);$y++){
                        $yval[$y]['time']=$tval[$y];
                        if(!$isting){
                            $yval[$y]['title']='关闭';
                            $yval[$y]['status']=2;
                            $yval[$y]['sno']=0;
                            continue;
                        }
                        if($closed==2){ //如果闭店
                            $yval[$y]['title']='关闭';
                            $yval[$y]['status']=2;
                            $yval[$y]['sno']=0;
                            continue;
                        }else{ //不闭店
                            $yval[$y]['title']=$tval[$y];
                            $yval[$y]['time']=$tval[$y];

                            //查询是否有闭馆的时间规则
                            //先查询自定义日期的 今天的日期
                            $isbi=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$list_id)->where('p_val','like','%'.$riqi.'%')->where('status',1)
                            ->where('closed',2)->where('t_val','like','%'.$tval[$y].'%')->count();
                            if($isbi){
                                $yval[$y]['title']='关闭';
                                $yval[$y]['status']=2;
                                $yval[$y]['sno']=0;
                                continue;
                            }
                            //查询星期
                            if(!$isbi){
                                $isbi=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$list_id)->where('p_val','like','%'.$wek.'%')->where('status',1)
                                ->where('closed',2)->where('t_val','like','%'.$tval[$y].'%')->count();
                                if($isbi){
                                    $yval[$y]['title']='关闭';
                                    $yval[$y]['status']=2;
                                    $yval[$y]['sno']=0;
                                    continue;
                                }
                            }

                            //查询预约当前时间的数量
                            $uno=Db::name('general_yuyue_order')->where('list_id',$list_id)->where('y_data',$riqi)->where('y_time',$tval[$y])->where('status','<',4)->sum('number');
                            if($uno>=$number){
                                $yval[$y]['time']=$tval[$y];
                                $yval[$y]['title']='约满';
                                $yval[$y]['status']=2;
                                $yval[$y]['sno']=0;
                            }else{
                                $new=explode("-",$tval[$y]);
                                if(count($new)>1){
                                    $isguo=config('-venuessite.yue_guoqitime')?:0;
                                    if($isguo==1){
                                        $xzt=strtotime(date($riqi.' '.$new[0]));
                                    }
                                    else{
                                        $xzt=strtotime(date($riqi.' '.$new[1]));
                                    }
                                }else{
                                    $xzt=strtotime(date($riqi.' '.$tval[$y]));
                                }
                                if($nowtime>=$xzt){
                                    $yval[$y]['time']=$tval[$y];
                                    $yval[$y]['title']='过期';
                                    $yval[$y]['status']=3;
                                    $yval[$y]['sno']=0;
                                    continue;
                                }

                                //查询每人可预约单个时间段的次数
                                $yueno=Db::name('general_yuyue_list')->where('id',$list_id)->value('yueno');
                                if($yueno){
                                    //查询该时间段已经约了几次了
                                    $ssnoz=Db::name('general_yuyue_order')->where('uid',$uid)->where('status','<',4)
                                        ->where('list_id',$list_id)
                                        ->where('y_data',$riqi)->where('y_time',$tval[$y])
                                        ->sum('number');
                                    if($ssnoz>=$yueno){
                                        $yval[$y]['time']=$tval[$y];
                                        $yval[$y]['title']='不可预约';
                                        $yval[$y]['status']=4;
                                        $yval[$y]['sno']=0;
                                        continue;
                                    }
                                }

                                $yval[$y]['time']=$tval[$y];
                                $yval[$y]['status']=1;
                                $yval[$y]['sno']=$number-$uno;
                                $yval[$y]['title']='余:'.$yval[$y]['sno'];

                                //查询是否在场地设置了预约天数 $dayno
                                $dayno=0;
                                if($dayno>0){
                                    $showtime=date("Y-m-d");
                                    $todata=date('Y-m-d',strtotime($showtime . '+'.$dayno.' day'));
                                    if($riqi>$todata){
                                        $yval[$y]['title']=config('-venuessite.yue_closedtit')?:'已闭馆';
                                        $yval[$y]['status']=2;
                                        $yval[$y]['sno']=0;
                                    }
                                }
                                //查询是否有闭馆的单独时间段

                            }
                        }
                    }
                }
                $res[$i]['list']=$yval;
            }
        }
        return $this->message('请求成功', $res);
    }
    //预约信息
    public function getyuyue(Request $request){
        $params = $request->get();
        if(!$params['id']){
            return $this->message('请求错误', [], 0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        $timeid=input('param.timeid');
        $id=$params['id'];
        $money=0;
        $dayno=config('-timesite.time_yuedayno')?:7;//可预约天数
        //查询本用户是否预约过当前id项目
        $arr=GeneralYuyueList::where('id',$params['id'])->where('status',1)->field(['id','title','money','img','dayno'])->find();
        if(!$arr){
            return $this->message('没有数据', [], 0);
        }
        if($arr['dayno']){
            $dayno=$arr['dayno'];
        }
        $money=$arr['money'];
        $data['arr']=$arr;
        $list=Db::name('general_yuyue_form')->where('list_id',$id)->order('paiid desc,id desc')->select()->toArray();
        if($list){
            for($i=0;$i<count($list);$i++){
                $list[$i]['value']='';
                $list[$i]['imageList']=[];
                $arr=[];
                //如果是单选或多选 则需要改成数组
                if($list[$i]['type']==3 || $list[$i]['type']==4 || $list[$i]['type']==6){
                    $arr=explode('|',$list[$i]['val']);
                    //如果是6 单选下拉框 则默认值为第一个
                    if($list[$i]['type']==6){
                        $list[$i]['value']=$arr[0];
                    }
                }
                $list[$i]['arr']=$arr;
            }
        }
        $data['form']=$list;
        //是否开启共享地址
        $gxadd=config('-systemsite.wxgxadd');
        $data['isgx']=$gxadd;
        $syno=0;//剩余数量
        //查询规格
        $cid=input('param.cid');
        $spec=$speclist=[];
        //查询规格
        $speclist=GeneralYuyueYuyuespec::where('list_id',$id)->where('status',1)->order('paiid desc,id asc')->select()->toArray();
        if($speclist){
            for($i=0;$i<count($speclist);$i++){
                $speclist[$i]['syno']=0;
                $specno=$speclist[$i]['number'];
                //查询该规格已经报名数量
                $bno=GeneralYuyueOrder::where('list_id',$id)
                    ->where('specid',$speclist[$i]['id'])
                    ->where('status','<',4)->count();
                $speclist[$i]['syno']=$specno-$bno;
            }
        }
        if($cid){
            $spec=GeneralYuyueYuyuespec::where('id',$cid)->where('status',1)->find();
            if($spec){
                //$money=$spec['money'];
                //根据规格id来查询数量 以及剩余数量
                $specno=$spec['number'];
                //查询该规格已经报名数量
                $bno=GeneralYuyueOrder::where('list_id',$id)
                    ->where('specid',$cid)
                    ->where('status','<',4)->count();
                $syno=$specno-$bno;
            }
        }else{
            if($speclist && count($speclist)>0){
                $spec=$speclist[0];
                //$money=$spec['money'];
                $specno=$spec['number'];
                $cid=$spec['id'];
                $syno=$spec['syno'];
            }
        }
        $data['cid']=$cid;
        $data['speclist']=$speclist;
        $data['syno']=$syno;
        $data['money']=$money;
        $data['spec']=$spec;
        //查询模板
        $template=Db::name('general_system_diy')->where('name','template')->value('val')?:1;
        $data['template']=$template;
        //查询模板
        $newdate=[];
        $template=Db::name('general_system_diy')->where('name','template')->value('val')?:1;
        if(!$timeid){
            //查询多少天的日期
            $date=week($dayno);
            for($i=0;$i<count($date);$i++){
                $date[$i]['day']=date('m月d号',strtotime($date[$i]['riqi']));
                //查询有没有该时间
                $riqi=$date[$i]['riqi'];
                $wek=$date[$i]['wek'];
                if($wek==0){
                    $wek=7;
                }
                //先查询当前日期是否有  为了兼容  做的比较复杂  这里查询的是多条数据
                $res=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$params['id'])->where('p_val','like','%'.$riqi.'%')->where('status',1)->order('paiid desc,id desc')->count();
                if(!$res){
                    //如果没有单独设置日期  那则根据星期来查询
                    $res=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$params['id'])->where('p_val','like','%'.$wek.'%')->where('status',1)->order('paiid desc,id desc')->count();
                }
                if($res){
                    $date[$i]['num']=1;
                    array_push($newdate,$date[$i]);
                }
            }
        }
        $data['date']=$newdate;
        //订票须知
        $dingmsg=config('-systemsite.sys_dingpiaomsg');
        $data['dingmsg']=$dingmsg;
        //滚动提示
        $gundong='温馨提示（最晚出岛时间20:45）';
        $data['gundong']=$gundong;
        //公告
        //查询公告
        $notilist=GeneralNotiList::field(['id','title','text'])->order('id desc')->select()->toArray();
        $data['noti']=$notilist;
        return $this->message('请求成功', $data);
    }
    //添加预约
    public function add_yuyue(Request $request){


        // 获取当前时间  控制并发
        $time = time();
        // 生成一个基于当前时间的键
        $key = "visits:{$time}";
        // 获取键的值
        $visits = Cache::get($key, 0);
        if ($visits >= 2) { //并发 1秒2个
            // 如果键的值大于或等于100，就提示用户稍后再试
            return $this->message('系统繁忙，请稍后再试', [], 0);
        } else {
            // 如果键的值小于100，就将键的值加1，并设置键的过期时间为1秒
            Cache::set($key, $visits + 1, 1);
        }
        

        $data = $request->post();

        //查询该时间段是否可以预约
        $stime=config('-timesite.yue_strtime');
        if($stime){
            $arrx=explode('-',$stime);
            $ytit='未到预约时间，请'.$stime.'时间段内预约';
            $r=get_curr_time_section($arrx[0],$arrx[1]);
            if(!$r){
                return $this->message($ytit, [], 0);
            }
        }

        

        // 检验数据
        $id=$data['id'];//预约产品id
        $timeid=$data['timeid'];//时间id
        $ydate=$data['riqi'];//预约日期
        $ytime=$data['time'];//预约时间
        $list=urldecode($data['list']);
        $list=json_decode(strip_tags(trim($list)),true);
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;

        //获取请求头  记录请求头
        $info = $request->header();
        Log::info('用户ID='.$uid);
        Log::info(json_encode($info));
        Log::info(json_encode($data));
        $ip = getip();
        Log::info('ip='.$ip);
        

        //防止作弊的  判断一个ip请求的频率 太高则限制
        $ip_cache = cache($ip . '_yue_cache');
        if($ip_cache){
            if($ip_cache>10){
                return $this->message('请求过于频繁，请稍后再试', [], 0);
            }
        }
        $ip_cache=$ip_cache+1;
        cache($ip . '_yue_cache', $ip_cache, 60);

        //根据ip判断 请求频率  10秒内只能请求一次
        $ip_cache2 = cache($ip . '_yue_cache2');
        if($ip_cache2){
            if($ip_cache2>=1){
                return $this->message('请求过于频繁，请稍后再试', [], 0);
            }
        }
        $ip_cache2=$ip_cache2+1;
        cache($ip . '_yue_cache2', $ip_cache2, 10);

        //根据uid判断 请求频率  10秒内只能请求一次
        $uid_cache = cache($uid . '_yue_cache3');
        if($uid_cache){
            if($uid_cache>=1){
                return $this->message('请求过于频繁，请稍后再试', [], 0);
            }
        }
        $uid_cache=$uid_cache+1;
        cache($uid . '_yue_cache3', $uid_cache, 10);


        $biao=strip_tags(trim($data['biao']));
        $biaoid=intval($data['biaoid']);
        $biaotit=strip_tags(trim($data['biaotit']));

        $pid=intval($data['pid']);//人员id


        //2024-1-11增加预约规格和预约人数
        $numberx=intval($data['number'])?:1;//预约人数
        $cid=intval($data['cid']);//规格id
        $zmo=$data['zmo'];//支付总金额
        //如果规格id存在则查询该规格剩余数量
        // if($cid){
        //     $spec=GeneralYuyueYuyuespec::where('id',$cid)->where('status',1)->find();
        //     if($spec){
        //         $numberss=$spec['number'];
        //         $money=$spec['money'];
        //         //根据规格id来查询数量 以及剩余数量
        //         $specno=$spec['number'];
        //         //查询该规格已经报名数量
        //         $bno=GeneralYuyueOrder::where('list_id',$id)
        //             ->where('specid',$cid)
        //             ->where('status','<',4)->count();
        //         $syno=$specno-$bno;
        //         if($numberss > 0 && $syno<$numberx){
        //             return $this->message('该规格剩余数量不足', [], 0);
        //         }
        //         $newzmo=$money*$numberx;
        //         if($newzmo!=$zmo){
        //             return $this->message($newzmo.'支付金额不对'.$zmo, [], 0);
        //         }
        //     }
        // }

        //预约时间是否已经过了
        if($ydate && $ytime && time()>strtotime($ydate.' '.$ytime)){
            return $this->message('预约时间已经过了', [], 0);
        }

        //查询后台设置的单人可预约时间段
        $dayno=config('-timesite.yue_dayno');
        //判断 已经预约了多少个订单
        $yyno=Db::name('general_yuyue_order')->where('uid',$uid)->where('status','<',4)
            ->whereDay('addtime')
            ->count();
        if($dayno>0 && $yyno>=$dayno){
            return $this->message('每人每天仅可预约'.$dayno.'个订单！', [], 0);
        }

        $prominx=config('-timesite.prominxno')??0;
        if($prominx){
            //同一个日期的同一个时间段只能预约一个项目
            $res=Db::name('general_yuyue_order')->where('uid',$uid)->where('status','<',4)
                ->where('y_data',$ydate)->where('y_time',$ytime)
                ->count();
            if($res){
                return $this->message('同一个时间段只能预约一个项目！', [], 0);
            }
        }

        //查询设置的  预约起始时间
        $isting=1;//是否可以预约
        $startingday=Db::name('general_yuyue_list')->where('id',$id)->value('startingday')?:0;
        if($startingday){
            //可以预约的 时间戳
            $star=strtotime(date("Y-m-d",strtotime("+".$startingday." day")));
            if(strtotime($ydate) < $star){
                $isting=0;//不可以预约
                return $this->message('未到预约时间不可预约！', [], 0);
            }
        }

        //查询每人可预约单个时间段的次数
        $yueno=Db::name('general_yuyue_list')->where('id',$id)->value('yueno');
        if($yueno){
            //查询该时间段已经约了几次了
            $ssnoz=Db::name('general_yuyue_order')->where('uid',$uid)->where('status','<',4)
                ->where('list_id',$id)
                ->where('y_data',$ydate)->where('y_time',$ytime)
                ->count();
            if($ssnoz>=$yueno){
                return $this->message($ytime.'一个人只能约'.$yueno.'次！', [], 0);
            }
        }

        //获取数字型星期几
        $number_wk=date("w",strtotime($ydate));
        if($number_wk==0){
            $number_wk=7;
        }
        $szno=0;//是否设置了预约数量限制，默认没有
        if($timeid){
            $res=Db::name('general_yuyue_time')->find($timeid);
            if($res && $res['status']==2){
                return $this->message('该时间不允许预约', [], 0);
            }
            if($res && $res['closed']==2){
                return $this->message('该时间已关闭预约', [], 0);
            }
            $number=$res['number'];
            $szno=1;//预约时间段里面有设置数量
        }else{
            //如果设置了时间 则提示选择时间
            $res=Db::name('general_yuyue_time')->where('list_id',$id)->where('closed',1)->where('status',1)->order('paiid desc,id desc')->find();
            if($res){
                return $this->message('请选择预约时间', [], 0);
            }
            $zno=Db::name('general_yuyue_list')->where('id',$id)->value('zno');
            if($zno){
                $number=$zno;
                $szno=1;//预约里面有设置总共预约数量
            }
        }

        //如果预约日期或星期 和 时间段已经关闭了 则不允许预约
        $isbi=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$id)->where('p_val','like','%'.$ydate.'%')->where('status',1)
            ->where('closed',2)->where('t_val','like','%'.$ytime.'%')->count();
        if($isbi){
            return $this->message('该时间不允许预约', [], 0);
        }
        //查询星期
        if(!$isbi){
            $isbi=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$id)->where('p_val','like','%'.$number_wk.'%')->where('status',1)
                ->where('closed',2)->where('t_val','like','%'.$ytime.'%')->count();
            if($isbi){
                return $this->message('该时间不允许预约', [], 0);
            }
        }

        $seat=Db::name('general_yuyue_seat')->where('id',$biaoid)->value('status');
        if($seat==2){
            return $this->message('该座位不允许预约', [], 0);
        }


        //先判断是否都上传了
        if($list){
            for($x=0;$x<count($list);$x++){
                if($list[$x]['mandatory'] && !$list[$x]['value']){
                    return $this->message($list[$x]['title'], [], 0);exit;
                }
                if($list[$x]['only']){
                    //判断是否唯一
                    $only=Db::name('general_yuyue_ord')->where('list_id',$id)->where('val','like','%'.$list[$x]['value'].'%')->count();
                    if($only){
                        return $this->message($list[$x]['name'].'不能重复', [], 0);exit;
                    }
                }
                if($list[$x]['validate']==1){
                    $isy=isAllChinese($list[$x]['value']);
                    if(!$isy){
                        return $this->message($list[$x]['name'].'格式不对,需要中文', [], 0);exit;
                    }
                }
                if($list[$x]['validate']==2){
                    $isy=is_mobile($list[$x]['value']);
                    if(!$isy){
                        return $this->message($list[$x]['name'].'格式不对', [], 0);exit;
                    }
                }
                if($list[$x]['validate']==3){
                    $isy=isCreditNo($list[$x]['value']);
                    if(!$isy){
                        return $this->message($list[$x]['name'].'格式不对', [], 0);exit;
                    }
                }
                //如果没有name 那么就查询
                if(!$list[$x]['name']){
                    $formname=Db::name('general_yuyue_form')->where('id',$list[$x]['id'])->value('name');
                    $list[$x]['name']=$formname;
                }
            }
        }
        //查询需付款金额
        $money=GeneralYuyueList::where('id',$id)->value('money');
        // if($zmo){
        //     $money=$zmo;
        // }

        //规格改成了多规格
        $spemo=0;
        $cid=0;
        $cidarr=[];
        if(isset($data['speclist'])){
            $speclist=urldecode($data['speclist']);
            $speclist=json_decode(strip_tags(trim($speclist)),true);
            if($speclist){
                for($x=0;$x<count($speclist);$x++){
                    if($speclist[$x]['xuan']==1){
                        $cidarr[]=$speclist[$x]['id'];
                        $spemo+=$speclist[$x]['money'];
                    }
                }
            }
            $money=($money+$spemo)*$numberx;
        }
        if($zmo!=$money){
            return $this->message($zmo.'异常错误'.$money, [], 0); exit;
        }
        if($cidarr){
            $cid=implode(',',$cidarr);
        }


        //违约次数
        $weino=config('-timesite.time_weiyueno');
        //禁止预约天数
        $weiday=config('-timesite.time_weiyueday');
        if($weino){
            $weino=Db::name('general_default_defaultlist')->where('uid',$uid)->count();
            if($weino>=$weino){
                if(!$weiday){
                    return $this->message('违约次数过多，不允许预约', [], 500);
                }
                //违约次数够了  查询最后一次违约时间
                $weitime=Db::name('general_default_defaultlist')->where('uid',$uid)->order('id desc')->value('addtime');
                $nowtime=time();
                if($nowtime-strtotime($weitime)<$weiday*24*3600){
                    return $this->message('违约次数过多，'.$weiday.'天内不允许预约', [], 500);
                }
            }
        }

        $title=GeneralYuyueList::where('id',$id)->value('title');
        //开启事务  查询是否已满  是否已经报名过了
        $neiwtime=time();
        Db::startTrans();
        try {
            //查询他是否有未支付订单
            $res=Db::name('general_yuyue_order')->where('uid',$uid)
                ->lock(true)->order('id desc')->find();
            if($res && $neiwtime-strtotime($res['addtime'])<20){
                Db::commit();
                return $this->message('提交过快请稍后再试', [], 0);
            }
            if($szno){
                //查询实际预约数量
                if($pid){
                    if($ydate){
                        $ordno=Db::name('general_yuyue_order')->where('list_id',$id)
                            ->where('y_data',$ydate)
                            ->where('personid',$pid)
                            ->where('y_time',$ytime)
                            ->where('status','<',4)->sum('number');
                    }
                    else{
                        $ordno=Db::name('general_yuyue_order')->where('list_id',$id)
                            ->where('personid',$pid)
                            ->where('status','<',4)->sum('number');
                    }
                }else{
                    if($ydate){
                        $ordno=Db::name('general_yuyue_order')->where('list_id',$id)
                            ->where('y_data',$ydate)
                            ->where('y_time',$ytime)
                            ->where('status','<',4)->sum('number');
                    }
                    else{
                        $ordno=Db::name('general_yuyue_order')->where('list_id',$id)
                            ->where('status','<',4)->sum('number');
                    }
                }
                if($ordno+1 > $number){
                    // 提交事务
                    Db::commit();
                    return $this->message('预约超过可预约人数'.$number, [], 0);
                }
            }

            //判断座位是否预定过了
            if($biaoid && $biao && !$ydate && !$ytime){
                $isseat=Db::name('general_yuyue_seatmsg')
                    ->where('status',1)
                    ->where('seatid',$biaoid)->where('biao',$biao)->count();
                if($isseat){
                    Db::commit();
                    return $this->message('该座位已经被预约了', [], 0);
                }
            }else{
                $isseat=Db::name('general_yuyue_seatmsg')->where('seatid',$biaoid)->where('biao',$biao)
                    ->where('status',1)
                    ->where('y_data',$ydate)->where('y_time',$ytime)
                    ->count();
                if($isseat){
                    Db::commit();
                    return $this->message('该座位已经被预约了', [], 0);
                }
            }
            if($money>0){
                $status=1;//支付
                $code=200;
                $ordmsg='下单待完善支付';
            }else{
                $status=2;//直接待核销
                $ordmsg='下单待核销';
                $code=300;
            }
            $arr=[
                'list_id' => $id,
                'uid' => $uid,
                'money' =>$money,
                'paymo' =>0,
                'addtime' => gettime(),
                'status' => $status,
                'y_time' => $ytime,
                'personid' => $pid,
                'specid' => $cid,
                'number' => $numberx,
                'y_timeid' => $timeid
            ];
            if($ydate){
                $arr['y_data']=$ydate;
            }
            $reid=Db::name('general_yuyue_order')->insertGetId($arr);
            $msg='预约成功';
            $rearr['id']=$reid;
            $rearr['money']=$money;

            //存入子订单
            
            //将时间段使用-分割存入
            $y_start_time=$y_end_time='';
            if($ytime){
                $new=explode('-',$ytime);
                if(count($new)>1){
                    $y_start_time=$new[0];
                    $y_end_time=$new[1];
                }else{
                    $y_start_time=$ytime;
                    $y_end_time=$ytime;
                }
            }
            $muarr=[
                'list_id' => $id,
                'order_id' => $reid,
                'y_time' => $ytime,
                'y_data' => $ydate,
                'status' => $status,
                'number' => $numberx,
                'uid' => $uid,
                'y_start_time' => $y_start_time,
                'y_end_time' => $y_end_time
            ];
            Db::name('general_yuyue_ordermultiple')->insert($muarr);

            $no=Db::name('general_yuyue_order')->whereTime('y_data',$ydate)->count();
            $rearr['no']=$no;

            if($list){
                for($i=0;$i<count($list);$i++){
                    $arrx=array(
                        'list_id' => $id,
                        'ord_id' => $reid,
                        'form_id' => $list[$i]['id'],
                        'type' => $list[$i]['type'],
                        'val' => strip_tags($list[$i]['value']),
                        'uid' => $uid,
                        'paiid' => $list[$i]['paiid'],
                        'validate' => $list[$i]['validate'],
                        'formname' => $list[$i]['name']
                    );
                    Db::name('general_yuyue_ord')->insert($arrx);
                }
            }
            //座位订单存入
            if($biaoid && $biao){
                $biaoarr=[
                    'orderid' => $reid,
                    'list_id' => $id,
                    'seatid' => $biaoid,
                    'title' => $biaotit,
                    'biao' => $biao,
                    'y_data' => $ydate,
                    'y_time' => $ytime
                ];
                Db::name('general_yuyue_seatmsg')->insert($biaoarr);
            }
            //报名数量+1
            Db::name('general_yuyue_list')->where('id',$id)->inc('bao')->update();
            if($status==2){
                $data=['uid'=>$uid,'id'=>$reid,'title'=>$title];
                event('SendYuyueMessage', ['data'=>$data,'type'=>1]);//通知预约成功
                event('SendYuyueMessage', ['data'=>['listid'=>$id],'type'=>5]);//通知新订单
            }
            //添加预约
            YuyueListmsgHandler::add('用户预约下单',$reid,$status,$ordmsg);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $code=0;
            $rearr=[];
            $msg=$e->getMessage();
            Db::rollback();
        }
        return $this->message($msg, $rearr, $code);
    }
    public function quxiao_yuyue(Request $request){
        $params = $request->post();
        if(!$params['id']){
            return $this->message('参数错误', [], 0);
        }
        $id=intval($params['id']);
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        //查询是否是本人
        $d=GeneralYuyueOrder::find($params['id']);
        if($d->uid!=$uid){
            return $this->message('权限不足', [], 0);
        }
        if($d->status!=1 && $d->status!=2){
            return $this->message('只有预约成功可取消', [], 0);
        }
        
        //如果预约的时间小于当前时间减去自动核销时间则不能签到
        $res=Db::name('general_yuyue_ordermultiple')->where('order_id',$id)->order('id asc')->field(['id','y_data','y_start_time'])->find();
        //$starttime=Db::name('general_yuyue_ordermultiple')->where('order_id',$id)->order('id asc')->value('y_start_time');
        if(strtotime($res['y_data'].$res['y_start_time'])<time()){
            return $this->message('预约时间已开始，不可取消', [],0);
        }

        $d->status=4;//取消预约
        $d->save();
        YuyueListmsgHandler::add('用户主动取消',$params['id'],4,'用户主动取消订单');

        //查询是否有座位  有座位则取消座位
        Db::name('general_yuyue_seatmsg')->where('orderid',$id)->update(['status'=>2]);

        $yid=$d['list_id'];
        $y_data=$d['y_data'];
        $title=GeneralYuyueList::where('id',$yid)->value('title');
        $data=['uid'=>$uid,'id'=>$params['id'],'title'=>$title,'y_data'=>$y_data,'tit1'=>'用户主动取消','tit2'=>'用户主动取消订单'];
        event('SendYuyueMessage', ['data'=>$data,'type'=>2]);//通知预约成功

        return $this->message('取消成功', [], 200);
    }
    //查询规格
    public function get_spec(Request $request){
        $params = $request->post();
        if(!$params['id']){
            return $this->message('请求错误', [], 0);
        }
        $id=intval($params['id']);
        //日期
        $riqi=$params['riqi'];
        $spec=GeneralYuyueYuyuespec::where('list_id',$id)->where('yueriqi','like','%'.$riqi.'%')->where('status',1)->order('paiid desc,id asc')->select()->toArray();
        if($spec){
            for($i=0;$i<count($spec);$i++){
                $spec[$i]['xuan']=0;
            }
        }
        return $this->message('请求成功', $spec);
    }
}