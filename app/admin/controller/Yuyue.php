<?php
/**
 *  * 系统-受国家计算机软件著作权保护 - !
 * =========================================================
 * Copy right 2018-2025 成都海之心科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.ohyu.cn
 * 这不是一个自由软件！在未得到官方有效许可的前提下禁止对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * User: ohyueo
 * Date: 2021/5/22
 * Time: 17:56
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use app\api\controller\Seat;

class Yuyue extends Common
{
    use AdminAuth;
    //座位预订详情
    public function seatlist(){
        $id=input("param.id");
        
        return View::fetch();
    }
    public function yuyuepersonnel(){
        $permis = $this->getPermissions('Yuyue/yuyuepersonnel');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_personnel')
                ->where($where)
                ->count();
            $data=Db::name('general_yuyue_personnel')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            if($data){
                for ($i=0;$i<count($data);$i++){
                    $listid=$data[$i]['id'];
                    $tit=Db::name('general_yuyue_list')->where('id',$listid)->value('title');
                    $data[$i]['tit']=$tit;
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function addperson(){
        if(request()->isPost()){
            $data = array('status' => 0, 'msg' => '未知错误');
            $title = input('param.title');
            if (!$title){
                $data['msg'] = '请填写地区名称';
                return json($data);
                exit;
            }
            $status = input('param.status');
            $list_id = input('param.list_id');
            $Intro = input('param.Intro');
            $img = input('param.img');
            $arr=array('title'=>$title,'Intro'=>$Intro,'list_id'=>$list_id,'img'=>$img,'status'=>$status);
            $id=Db::name('general_yuyue_personnel')->insertGetId($arr);
            $text = '添加了预约人员-' . $title.'id='.$id;
            $this->writeActionLog($text);
            $data['status'] = 1;
            return json($data);
        }
        $list=Db::name('general_yuyue_list')->order('paiid desc,id desc')->select();
        View::assign('list', $list);
        return View::fetch('editperson');
    }
    public function editperson(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $title = input('param.title');
            if (!$title){
                $data['msg'] = '请填写地区名称';
                return json($data);
                exit;
            }
            $status = input('param.status');
            $list_id = input('param.list_id');
            $Intro = input('param.Intro');
            $img = input('param.img');
            $arr=array('title'=>$title,'Intro'=>$Intro,'list_id'=>$list_id,'img'=>$img,'status'=>$status);
            $id = input('param.id');
            if ($id){
                Db::name('general_yuyue_personnel')->where('id',$id)->update($arr);
                $text = '修改了预约人员-' . $title.'id='.$id;
                $this->writeActionLog($text);
                $data['status'] = 1;
            }
            return json($data);
        }
        $list=Db::name('general_yuyue_list')->order('paiid desc,id desc')->select();
        View::assign('list', $list);
        return View::fetch();
    }
    public function delperson(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_personnel')->where('id',$arr[$i])->delete();
                $text = '删除了人员id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function yuyueseat(){
        $id=input('get.id');
        if(!$id){
            $id='';
        }
        View::assign('id', $id);
        $permis = $this->getPermissions('Yuyue/yuyueseat');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['list_id','=',$id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_seat')
                ->where($where)
                ->count();
            $data=Db::name('general_yuyue_seat')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            if($data){
                for ($i=0;$i<count($data);$i++){
                    $id=$data[$i]['list_id'];
                    $name=Db::name('general_yuyue_list')->where('id',$id)->value('title');
                    $data[$i]['name']=$name?$name:'无';

                    $lxid=$data[$i]['id'];
                    //使用app\api\controller Seat.php
                    $res=new Seat(app());
                    $rx=$res->seatlist($lxid);
                    $res=$rx->getData();
                    $syno=$res['data']['syno'];
                    $data[$i]['syno']=$syno;

                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }

    public function editeseat(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            $row=input('param.row');
            if(!$row){
                $data['msg']='行不能为空';
                return json($data);
            }
            if($row>10){
                $data['msg']='行最大10';
                //return json($data);
            }
            $column=trim(input('param.column'));
            if(!$column){
                $data['msg']='列不能为空';
                return json($data);
            }
            if($column>10){
                $data['msg']='列最大10';
                //return json($data);
            }
            $list_id=input('param.list_id');
            $title=input('param.title');
            $status=input('param.status');
            $closed=input('param.closed');
            $arr=array(
                'column' => $column,
                'row' => $row,
                'title' => $title,
                'closed' => $closed,
                'list_id' => $list_id,
                'status' => $status
            );
            if($id){//修改
                Db::name('general_yuyue_seat')->where('id',$id)->update($arr);
                $data['msg']='修改成功';
                $data['status']=1;
                $text = '修改了座位 id='.$id;
                $this->writeActionLog($text);
            }else{//添加
                $vid=Db::name('general_yuyue_seat')->insertGetId($arr);
                $text = '添加了座位 id='.$vid;
                $this->writeActionLog($text);
                $data['msg']='添加成功';
                $data['status']=1;
            }
            return json($data);
        }
        //查询预约
        $list=Db::name('general_yuyue_list')->field(['id','title'])->order('paiid desc,id desc')->select();
        View::assign('list', $list);
        return View::fetch('edit_seat');
    }
    public function addeseat(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            $row=input('param.row');
            if(!$row){
                $data['msg']='行不能为空';
                return json($data);
            }
            if($row>10){
                $data['msg']='行最大10';
                //return json($data);
            }
            $column=trim(input('param.column'));
            if(!$column){
                $data['msg']='列不能为空';
                return json($data);
            }
            if($column>10){
                $data['msg']='列最大10';
                //return json($data);
            }
            $list_id=input('param.list_id');
            $title=input('param.title');
            $status=input('param.status');
            $closed=input('param.closed');
            $arr=array(
                'column' => $column,
                'row' => $row,
                'title' => $title,
                'closed' => $closed,
                'list_id' => $list_id,
                'status' => $status
            );
            $vid=Db::name('general_yuyue_seat')->insertGetId($arr);
            $text = '添加了座位 id='.$vid;
            $this->writeActionLog($text);
            $data['msg']='添加成功';
            $data['status']=1;
            return json($data);
        }
        //查询预约
        $list=Db::name('general_yuyue_list')->field(['id','title'])->order('paiid desc,id desc')->select();
        View::assign('list', $list);
        return View::fetch('edit_seat');
    }
    public function deleseat(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_seat')->where('id',$arr[$i])->delete();
                $text = '删除了座位id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function yuyueclass(){
        $id=input('get.id');
        if(!$id){
            $id='';
        }
        View::assign('id', $id);
        $permis = $this->getPermissions('Yuyue/yuyueclass');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()) {
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_class')
                ->where($where)
                ->count();
            $data=Db::name('general_yuyue_class')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            if($data){
                for ($i=0;$i<count($data);$i++){

                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function editclass(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $title = input('param.title');
            if (!$title){
                $data['msg'] = '请填写场地名称';
                return json($data);
                exit;
            }
            $status = input('param.status')?:1;
            $arr=array('title'=>$title,'status'=>$status);
            $id = input('param.id');
            if ($id){
                Db::name('general_yuyue_class')->where('id',$id)->update($arr);
                $text = '修改了预约分类-' . $title.'id='.$id;
                $this->writeActionLog($text);
                $data['status'] = 1;
            }
            return json($data);
        }
        return View::fetch('class_edit');
    }
    public function addclass(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $title = input('param.title');
            if (!$title){
                $data['msg'] = '请填写场地名称';
                return json($data);
                exit;
            }
            $status = input('param.status')?:1;
            $arr=array('title'=>$title,'status'=>$status);
            $id = input('param.id');
            Db::name('general_yuyue_class')->insert($arr);
            $text = '添加了预约分类-' . $title.'id='.$id;
            $this->writeActionLog($text);
            $data['status'] = 1;
            return json($data);
        }
        return View::fetch('class_edit');
    }

    public function yuyuelist(){
        $id=input('get.id');
        if(!$id){
            $id='';
        }
        View::assign('id', $id);
        $permis = $this->getPermissions('Yuyue/yuyuelist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_list')->where($where)->count();
            $data=Db::name('general_yuyue_list')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    //查询表单信息数量
                    $id=$data[$i]['id'];
                    $no=Db::name('general_yuyue_form')->where('list_id',$id)->count();
                    $data[$i]['no']=$no;
                    //查询活动订单数量
                    $yno=Db::name('general_yuyue_order')->where('list_id',$id)->count();
                    $data[$i]['yno']=$yno;
                    $classid=$data[$i]['classid'];
                    $tit='无';
                    if($classid){
                        $tit=Db::name('general_yuyue_class')->where('id',$classid)->value('title');
                    }
                    $data[$i]['tit']=$tit;
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function edit(){
        $list=Db::name('general_yuyue_class')->where('status',1)->select()->toArray();
        View::assign('list', $list);
        $id=input('param.id');
        View::assign('id', $id);
        //查询
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            $title=input('param.title');
            if(!$title){
                $data['msg']='活动标题不能为空';
                return json($data);
            }
            $img=input('param.img');
            if(!$img){
                $data['msg']='活动图片不能为空';
                return json($data);
            }
            $intro=input('param.intro');
            $money=input('param.money')?:0;
            $pv=input('param.pv')?:0;
            $bao=input('param.bao')?:0;
            $status=input('param.status')?:1;
            $address=input('param.address');
            //$texter=$_POST['texter'];//input('param.texter');
            $texter=input('param.texter','','remove_xss');
            if(!$texter){
                $data['msg']='报名详情不能为空';
                return json($data);
            }
            $mobile=input('param.mobile');
            $name=input('param.name');
            $lng=input('param.lng');
            $lat=input('param.lat');
            $paiid=input('param.paiid');
            $istui=input('param.istui');
            $zno=input('param.zno');
            $yueno=input('param.yueno');
            $classid=input('param.classid');
            $is_info=input('param.is_info');
            $recommended=input('param.recommended');
            $heno=input('param.heno');
            $startingday=input('param.startingday');
            $arr=array(
                'title' => $title,
                'img' => $img,
                'money' => $money,
                'pv' => $pv,
                'classid' => $classid,
                'bao' => $bao,
                'address' => $address,
                'intro' => $intro,
                'mobile' => $mobile,
                'name' => $name,
                'zno' => $zno,
                'yueno' => $yueno,
                'lng' => $lng,
                'lat' => $lat,
                'texter' => $texter,
                'status' => $status,
                'recommended' => $recommended,
                'paiid' => $paiid,
                'istui' => $istui,
                'is_info' => $is_info,
                'startingday' => $startingday,
                'heno' => $heno
            );
            if($id){//修改
                Db::name('general_yuyue_list')->where('id',$id)->update($arr);
                $data['msg']='修改成功';
                $data['status']=1;
                $text = '修改了预约列表 id='.$id;
                $this->writeActionLog($text);
            }
            return json($data);
        }
        $id=input("param.id");
        View::assign('id', $id);
        //查询详情轮播图
        $lunimg=Db::name('general_yuyue_img')->where('acid',$id)->select();
        View::assign('lunimg', $lunimg);
        return View::fetch('edit_list');
    }
    public function add(){
        $list=Db::name('general_yuyue_class')->where('status',1)->select()->toArray();
        View::assign('list', $list);
        $id=input('param.id');
        View::assign('id', $id);
        //查询
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            $title=input('param.title');
            if(!$title){
                $data['msg']='活动标题不能为空';
                return json($data);
            }
            $img=input('param.img');
            if(!$img){
                $data['msg']='活动图片不能为空';
                return json($data);
            }
            $intro=input('param.intro');
            $money=input('param.money')?:0;
            $pv=input('param.pv')?:0;
            $bao=input('param.bao')?:0;
            $status=input('param.status')?:1;
            $address=input('param.address');
            if(!$address){
                $data['msg']='地址不能为空';
                return json($data);
            }
            //$texter=$_POST['texter'];//input('param.texter');
            $texter=input('param.texter','','remove_xss');
            if(!$texter){
                $data['msg']='报名详情不能为空';
                return json($data);
            }
            $mobile=input('param.mobile');
            $name=input('param.name');
            $lng=input('param.lng');
            $lat=input('param.lat');
            $paiid=input('param.paiid');
            $istui=input('param.istui');
            $zno=input('param.zno');
            $classid=input('param.classid');
            $is_info=input('param.is_info');
            $recommended=input('param.recommended');
            $heno=input('param.heno');
            $startingday=input('param.startingday');
            $arr=array(
                'title' => $title,
                'img' => $img,
                'money' => $money,
                'pv' => $pv,
                'classid' => $classid,
                'bao' => $bao,
                'address' => $address,
                'intro' => $intro,
                'mobile' => $mobile,
                'name' => $name,
                'zno' => $zno,
                'lng' => $lng,
                'lat' => $lat,
                'texter' => $texter,
                'status' => $status,
                'recommended' => $recommended,
                'paiid' => $paiid,
                'istui' => $istui,
                'is_info' => $is_info,
                'startingday' => $startingday,
                'heno' => $heno
            );
            if($id){//修改
                Db::name('general_yuyue_list')->where('id',$id)->update($arr);
                $data['msg']='修改成功';
                $data['status']=1;
                $text = '修改了预约列表 id='.$id;
                $this->writeActionLog($text);
            }else{//添加
                $arr['addtime']=date('Y-m-d H:i:s');
                $xid=Db::name('general_yuyue_list')->insertGetId($arr);
                $text = '添加了预约 标题='.$title.'id='.$xid;
                $this->writeActionLog($text);
                $data['msg']='添加成功';
                $data['status']=1;
            }
            return json($data);
        }
        $id=input("param.id");
        View::assign('id', $id);
        //查询详情轮播图
        $lunimg=Db::name('general_yuyue_img')->where('acid',$id)->select();
        View::assign('lunimg', $lunimg);
        return View::fetch('edit_list');
    }

    public function del(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_list')->where('id',$arr[$i])->delete();
                $text = '删除了预约列表id='.$arr[$i].'的数据yuyue_list';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function deltime(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_time')->where('id',$arr[$i])->delete();
                $text = '删除了预约时间id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    //删除预约轮播图
    public function delyuyueimg(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_img')->where('id',$arr[$i])->delete();
                $text = '删除了预约图片id='.$arr[$i].'的数据yuyue_img';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }

    public function delclass(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_class')->where('id',$arr[$i])->delete();
                $text = '删除了预约分类id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    //预约时间
    public function yuyuetime(){
        $permis = $this->getPermissions('Yuyue/yuyuetime');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.val");
            if($name){
                $where[]=['c.val','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['list_id','=',$id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_time')
                ->where($where)
                ->count();
            $data=Db::name('general_yuyue_time')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $id=$data[$i]['list_id'];
                    $name=Db::name('general_yuyue_list')->where('id',$id)->value('title');
                    $data[$i]['name']=$name?$name:'无';
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function addtime(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            $pid=input('param.pid');
            if(!$pid){
                $data['msg']='日期类型不能为空';
                return json($data);
            }
            $p_val=trim(input('param.p_val'));
            if(!$p_val){
                $data['msg']='日期的值不能为空';
                return json($data);
            }
            $t_val=trim(input('param.t_val'));
            if(!$t_val){
                $data['msg']='时间的值不能为空';
                return json($data);
            }
            $list_id=input('param.list_id');
            $number=input('param.number');
            $status=input('param.status');
            $closed=input('param.closed');
            $paiid=input('param.paiid');

            $arr=array(
                'pid' => $pid,
                't_val' => $t_val,
                'p_val' => $p_val,
                'number' => $number,
                'list_id' => $list_id,
                'status' => $status,
                'closed' => $closed,
                'paiid' => $paiid
            );
            $vid=Db::name('general_yuyue_time')->insertGetId($arr);
            $text = '添加了时间 id='.$vid;
            $this->writeActionLog($text);
            $data['msg']='添加成功';
            $data['status']=1;
            return json($data);
        }
        //查询预约
        $list=Db::name('general_yuyue_list')->field(['id','title'])->order('paiid desc,id desc')->select();
        View::assign('list', $list);
        return View::fetch('edit_time');
    }
    public function edittime(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            $pid=input('param.pid');
            if(!$pid){
                $data['msg']='日期类型不能为空';
                return json($data);
            }
            $p_val=trim(input('param.p_val'));
            if(!$p_val){
                $data['msg']='日期的值不能为空';
                return json($data);
            }
            $t_val=trim(input('param.t_val'));
            if(!$t_val){
                $data['msg']='时间的值不能为空';
                return json($data);
            }
            $list_id=input('param.list_id');
            $number=input('param.number');
            $status=input('param.status');
            $closed=input('param.closed');
            $paiid=input('param.paiid');

            $arr=array(
                'pid' => $pid,
                't_val' => $t_val,
                'p_val' => $p_val,
                'number' => $number,
                'list_id' => $list_id,
                'status' => $status,
                'closed' => $closed,
                'paiid' => $paiid
            );
            if($id){//修改
                Db::name('general_yuyue_time')->where('id',$id)->update($arr);
                $data['msg']='修改成功';
                $data['status']=1;
                $text = '修改了时间 id='.$id;
                $this->writeActionLog($text);
            }
            return json($data);
        }
        //查询预约
        $list=Db::name('general_yuyue_list')->field(['id','title'])->order('paiid desc,id desc')->select();
        View::assign('list', $list);
        return View::fetch('edit_time');
    }
    public function edit_form(){

        $id=input('get.id');
        if(!$id){
            $id='';
        }
        View::assign('id', $id);
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");//活动id
            $fid=input('param.fid');
            $name=input('param.name');
            $title=input('param.title');
            $type=input('param.type');
            $mandatory=input('param.mandatory');
            $only=input('param.only');
            $paiid=input('param.paiid');
            $val=input('param.val');
            $validate=input('param.validate');
            for($i=0;$i<count($name);$i++){
                $gid=$fid[$i];
                $arr=array(
                    'list_id' => $id,
                    'name'  => $name[$i],
                    'title' => $title[$i],
                    'type' => $type[$i],
                    'mandatory' => $mandatory[$i],
                    'only' => $only[$i],
                    'paiid' => $paiid[$i],
                    'validate' => $validate[$i],
                    'val' => $val[$i]
                );
                if($gid){//修改
                    Db::name('general_yuyue_form')->where('id',$gid)->update($arr);
                    $data['msg']='修改成功';
                    $data['status']=1;
                    $text = '修改了表单信息 id='.$gid;
                    $this->writeActionLog($text);
                }else{//添加
                    $xid=Db::name('general_yuyue_form')->insertGetId($arr);
                    $text = '添加了表单信息id='.$gid;
                    $this->writeActionLog($text);
                    $data['msg']='添加成功';
                    $data['status']=1;
                }
            }
            //return json($data);
        }
        //查询该活动的所有表单信息
        $list=Db::name('general_yuyue_form')->where('list_id',$id)->select();
        View::assign('list', $list);
        $permis = $this->getPermissions('Yuyue/edit_form');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        return View::fetch();
    }

    public function delform(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_form')->where('id',$arr[$i])->delete();
                $text = '删除了表单id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }

	public function yuyuespec(){
 		$dao=input('param.dao');
		if(request()->isPost() || $dao==1){
			$page=input('param.page');
			if(!$page){
				$page=1;
			}
			$limit=input('param.limit');
			if(!$limit){
				$limit=10;//每页显示条数
			}
			$where=[];
			$id=input('param.id');
			if($id){
				$where[]=['id','=',$id];
			}
			$title=input('param.title');
			if($title){
				$where[]=['title','like','%'.$title.'%'];
			}
			$list_id=input('param.list_id');
			if($list_id){
				$where[]=['list_id','like','%'.$list_id.'%'];
			}
			$money=input('param.money');
			if($money){
				$where[]=['money','like','%'.$money.'%'];
			}
			$status=input('param.status');
			if($status){
				$where[]=['status','like','%'.$status.'%'];
			}
			$paiid=input('param.paiid');
			if($paiid){
				$where[]=['paiid','like','%'.$paiid.'%'];
			}
			$number=input('param.number');
			if($number){
				$where[]=['number','like','%'.$number.'%'];
			}
			$count = Db::name('general_yuyue_yuyuespec')->where($where)->count();
			if($dao!=1){
				$data=Db::name('general_yuyue_yuyuespec')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
			}
			if($dao==1){
				$data=Db::name('general_yuyue_yuyuespec')->where($where)->order('id desc')->select()->toArray();
			}
            if($data){
                for($i=0;$i<count($data);$i++){
                    $id=$data[$i]['list_id'];
                    $name=Db::name('general_yuyue_list')->where('id',$id)->value('title');
                    $data[$i]['name']=$name?$name:'无';
                }
            }
			$res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
			return json($res);
		}
		$permis = $this->getPermissions('Yuyue/yuyuespec');
		View::assign('data', $this->actions);
		View::assign('permis', $permis);
		return View::fetch();
	}
	public function addyuyuespec(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$list_id=input('param.list_id');
			$money=input('param.money');
			$status=input('param.status');
			$paiid=input('param.paiid');
			$number=input('param.number');
            $heno=input('param.heno');
            $yueriqi=input('param.yueriqi');
            $intro=input('param.intro');
			$arr=array(
				'title'=>$title,
				'list_id'=>$list_id,
				'money'=>$money,
				'status'=>$status,
				'paiid'=>$paiid,
				'number'=>$number,
                'heno'=>$heno,
                'yueriqi'=>$yueriqi,
                'intro'=>$intro,
			);
			$id=Db::name('general_yuyue_yuyuespec')->insertGetId($arr);
			$text = '添加了预约规格-id='.$id;
			$this->writeActionLog($text);
			$data['status'] = 1;
			return json($data);exit;
		}
        //查询预约
        $list=Db::name('general_yuyue_list')->field(['id','title'])->order('paiid desc,id desc')->select();
        View::assign('list', $list);
		return View::fetch('edityuyuespec');
	}
	public function edityuyuespec(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$list_id=input('param.list_id');
			$money=input('param.money');
			$status=input('param.status');
			$paiid=input('param.paiid');
			$number=input('param.number');
            $heno=input('param.heno');
            $yueriqi=input('param.yueriqi');
            $intro=input('param.intro');
			$arr=array(
				'title'=>$title,
				'list_id'=>$list_id,
				'money'=>$money,
				'status'=>$status,
				'paiid'=>$paiid,
				'number'=>$number,
                'heno'=>$heno,
                'yueriqi'=>$yueriqi,
                'intro'=>$intro,
			);
			$id = input('param.id');
			if ($id){
				$id=Db::name('general_yuyue_yuyuespec')->where('id',$id)->update($arr);
				$text = '修改了预约规格-id='.$id;
				$this->writeActionLog($text);
				$data['status'] = 1;
				return json($data);exit;
			}
		}
        //查询预约
        $list=Db::name('general_yuyue_list')->field(['id','title'])->order('paiid desc,id desc')->select();
        View::assign('list', $list);
		return View::fetch('edityuyuespec');
	}
	public function delyuyuespec(){
 		$data = array('status' => 0,'msg' => '未知错误');
		$array=input("param.id");
		if(!$array){
			$data['msg']='参数错误';return json($data);exit;
		}
		$arr=explode(",",$array);
		for($i=0;$i<count($arr);$i++){
			if($arr[$i]){
				Db::name('general_yuyue_yuyuespec')->where('id',$arr[$i])->delete();
				$text = '删除了预约规格id='.$arr[$i].'的数据';
				$this->writeActionLog($text);
			}
		}
		$data['status']=1;
		return json($data);
	}
}