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
* Date: 2024/07/17
* Time: 22:08
*/
namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use PHPExcel_IOFactory;
use PHPExcel;
            
class Signin extends Common{
	use AdminAuth;

 	public function codelist(){
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
			$val=input('param.val');
			if($val){
				$where[]=['val','like','%'.$val.'%'];
			}
			$addtime=input('param.addtime');
			if($addtime){
				$where[]=['addtime','like','%'.$addtime.'%'];
			}
			$updatetime=input('param.updatetime');
			if($updatetime){
				$where[]=['updatetime','like','%'.$updatetime.'%'];
			}
			$status=input('param.status');
			if($status){
				$where[]=['status','like','%'.$status.'%'];
			}
			$count = Db::name('general_signin_codelist')->where($where)->count();
			$data=Db::name('general_signin_codelist')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
			if($data){
				foreach($data as $k=>$v){
					$id=$v['id'];
					$img='storage/signin/sig'.$id.'.png';
					$img=getFullImageUrl($img);
					$img=$img.'?v='.time();
					$data[$k]['img']=$img;
				}
			}
			$res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
			return json($res);
		}
		$permis = $this->getPermissions('Signin/codelist');
		View::assign('data', $this->actions);
		View::assign('permis', $permis);
		return View::fetch();
	}
	public function addcodelist(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$val=createNoncestr(8);
			$addtime=gettime();
			$updatetime=gettime();
			$status=input('param.status');
			$hour=input('param.hour');
			$seat=input('param.seat');
			$arr=array(
				'title'=>$title,
				'addtime'=>$addtime,
				'updatetime'=>$updatetime,
				'status'=>$status,
				'hour'=>$hour,
				'seat'=>$seat,
			);
			$id=Db::name('general_signin_codelist')->insertGetId($arr);
			if($id){
				$codeid = $val.$id;
				Db::name('general_signin_codelist')->where('id',$id)->update(['val'=>$codeid]);
			}

			//调用一下签到api/Signin/signaddcode
			$url = getUrl().'/resource/Signin/signaddcode';
			$data = ['id'=>$id];
			$res = posturl($url,$data);
			$res = json_decode($res,true);
			if($res['code']!=200){
				$data['msg'] = '生成二维码失败';
				return json($data);exit;
			}

			$text = '添加了签到二维码-id='.$id;
			$this->writeActionLog($text);
			$data['status'] = 1;
			return json($data);exit;
		}
		return View::fetch('editcodelist');
	}
	public function editcodelist(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$val=createNoncestr(8);
			$updatetime=gettime();
			$status=input('param.status');
			$hour=input('param.hour');
			$seat=input('param.seat');
			$arr=array(
				'title'=>$title,
				'updatetime'=>$updatetime,
				'status'=>$status,
				'hour'=>$hour,
				'seat'=>$seat,
			);
			$id = input('param.id');
			if ($id){
				//$arr['val'] = $val.$id;
				$id=Db::name('general_signin_codelist')->where('id',$id)->update($arr);
				$text = '修改了签到二维码-id='.$id;
				$this->writeActionLog($text);
				$data['status'] = 1;
				return json($data);exit;
			}
		}
		return View::fetch('editcodelist');
	}
	public function delcodelist(){
 		$data = array('status' => 0,'msg' => '未知错误');
		$array=input("param.id");
		if(!$array){
			$data['msg']='参数错误';return json($data);exit;
		}
		$arr=explode(",",$array);
		for($i=0;$i<count($arr);$i++){
			if($arr[$i]){
				Db::name('general_signin_codelist')->where('id',$arr[$i])->delete();
				$text = '删除了签到二维码id='.$arr[$i].'的数据';
				$this->writeActionLog($text);
			}
		}
		$data['status']=1;
		return json($data);
	}

	public function signmsg(){
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
			$listid=input('param.listid');
			if($listid){
				$where[]=['listid','like','%'.$listid.'%'];
			}
			$orderid=input('param.orderid');
			if($orderid){
				$where[]=['orderid','like','%'.$orderid.'%'];
			}
			$uid=input('param.uid');
			if($uid){
				$where[]=['uid','like','%'.$uid.'%'];
			}
			$codeid=input('param.codeid');
			if($codeid){
				$where[]=['codeid','like','%'.$codeid.'%'];
			}
			$msg=input('param.msg');
			if($msg){
				$where[]=['msg','like','%'.$msg.'%'];
			}
			$addtime=input('param.addtime');
			if($addtime){
				$where[]=['addtime','like','%'.$addtime.'%'];
			}
			$count = Db::name('general_signin_signmsg')->where($where)->count();
			if($dao!=1){
				$data=Db::name('general_signin_signmsg')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
			}
			if($dao==1){
				$data=Db::name('general_signin_signmsg')->where($where)->order('id desc')->select()->toArray();
			}
			if($data){
				foreach($data as $k=>$v){
					$listid=$v['listid'];
					$title=Db::name('general_yuyue_list')->where('id',$listid)->value('title')??'无';
					$data[$k]['title']=$title;
				}
			}
			$res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
			return json($res);
		}
		$permis = $this->getPermissions('Signin/signmsg');
		View::assign('data', $this->actions);
		View::assign('permis', $permis);
		return View::fetch();
	}
	public function addsignmsg(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$listid=input('param.listid');
			$orderid=input('param.orderid');
			$uid=input('param.uid');
			$codeid=input('param.codeid');
			$msg=input('param.msg');
			$addtime=input('param.addtime');
			$arr=array(
				'listid'=>$listid,
				'orderid'=>$orderid,
				'uid'=>$uid,
				'codeid'=>$codeid,
				'msg'=>$msg,
				'addtime'=>$addtime,
			);
			$id=Db::name('general_signin_signmsg')->insertGetId($arr);
			$text = '添加了签到记录-id='.$id;
			$this->writeActionLog($text);
			$data['status'] = 1;
			return json($data);exit;
		}
		return View::fetch('editsignmsg');
	}
	public function editsignmsg(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$listid=input('param.listid');
			$orderid=input('param.orderid');
			$uid=input('param.uid');
			$codeid=input('param.codeid');
			$msg=input('param.msg');
			$addtime=input('param.addtime');
			$arr=array(
				'listid'=>$listid,
				'orderid'=>$orderid,
				'uid'=>$uid,
				'codeid'=>$codeid,
				'msg'=>$msg,
				'addtime'=>$addtime,
			);
			$id = input('param.id');
			if ($id){
				$id=Db::name('general_signin_signmsg')->where('id',$id)->update($arr);
				$text = '修改了签到记录-id='.$id;
				$this->writeActionLog($text);
				$data['status'] = 1;
				return json($data);exit;
			}
		}
		return View::fetch('editsignmsg');
	}
	public function delsignmsg(){
 		$data = array('status' => 0,'msg' => '未知错误');
		$array=input("param.id");
		if(!$array){
			$data['msg']='参数错误';return json($data);exit;
		}
		$arr=explode(",",$array);
		for($i=0;$i<count($arr);$i++){
			if($arr[$i]){
				Db::name('general_signin_signmsg')->where('id',$arr[$i])->delete();
				$text = '删除了签到记录id='.$arr[$i].'的数据';
				$this->writeActionLog($text);
			}
		}
		$data['status']=1;
		return json($data);
	}
 }