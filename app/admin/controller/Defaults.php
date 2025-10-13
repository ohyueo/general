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
* Date: 2024/08/10
* Time: 23:40
*/
namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use PHPExcel_IOFactory;
use PHPExcel;
            
class Defaults extends Common{
	use AdminAuth;

 	public function defaultlist(){
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
			$uid=input('param.uid');
			if($uid){
				$where[]=['uid','=',$uid];
			}
			$listid=input('param.listid');
			if($listid){
				$where[]=['listid','=',$listid];
			}
			$orderid=input('param.orderid');
			if($orderid){
				$where[]=['orderid','=',$orderid];
			}
			$mobile=input('param.mobile');
			if($mobile){
				//查询有这个手机号的订单id
				$ord_id=Db::name('general_yuyue_ord')->where('val','like','%'.$mobile.'%')->where('form_id',2)->column('ord_id');
				$where[]=['orderid','in',$ord_id];
			}
			$str=input('param.str');
			$end=input('param.end');
			if($str && $end){
				$where[]=['adtime','between',[$str,$end]];
			}
			$count = Db::name('general_default_defaultlist')->where($where)->count();
			$data=Db::name('general_default_defaultlist')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
			if($data){
				foreach($data as $k=>$v){
					//根据订单id查询手机号
					$val=Db::name('general_yuyue_ord')->where('ord_id',$v['orderid'])
					//->where('validate',2)
					->where('form_id',2)
					->value('val')??'无';
					$data[$k]['phone']=$val;

					$listid=$v['listid'];
					$title=Db::name('general_yuyue_list')->where('id',$listid)->value('title');
					$data[$k]['title']=$title?$title.'（ID:'.$listid.'）':'无';

				}
			}
			$res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
			return json($res);
		}
		$permis = $this->getPermissions('Defaults/defaultlist');
		View::assign('data', $this->actions);
		View::assign('permis', $permis);
		return View::fetch();
	}
	public function adddefaultlist(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$uid=input('param.uid');
			$listid=input('param.listid');
			$orderid=input('param.orderid');
			$adtime=input('param.adtime');
			$arr=array(
				'title'=>$title,
				'uid'=>$uid,
				'listid'=>$listid,
				'orderid'=>$orderid,
				'adtime'=>$adtime,
			);
			$id=Db::name('general_default_defaultlist')->insertGetId($arr);
			$text = '添加了违约记录-id='.$id;
			$this->writeActionLog($text);
			$data['status'] = 1;
			return json($data);exit;
		}
		return View::fetch('editdefaultlist');
	}
	public function editdefaultlist(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$uid=input('param.uid');
			$listid=input('param.listid');
			$orderid=input('param.orderid');
			$adtime=input('param.adtime');
			$arr=array(
				'title'=>$title,
				'uid'=>$uid,
				'listid'=>$listid,
				'orderid'=>$orderid,
				'adtime'=>$adtime,
			);
			$id = input('param.id');
			if ($id){
				$id=Db::name('general_default_defaultlist')->where('id',$id)->update($arr);
				$text = '修改了违约记录-id='.$id;
				$this->writeActionLog($text);
				$data['status'] = 1;
				return json($data);exit;
			}
		}
		return View::fetch('editdefaultlist');
	}
	public function deldefaultlist(){
 		$data = array('status' => 0,'msg' => '未知错误');
		$array=input("param.id");
		if(!$array){
			$data['msg']='参数错误';return json($data);exit;
		}
		$arr=explode(",",$array);
		for($i=0;$i<count($arr);$i++){
			if($arr[$i]){
				Db::name('general_default_defaultlist')->where('id',$arr[$i])->delete();
				$text = '删除了违约记录id='.$arr[$i].'的数据';
				$this->writeActionLog($text);
			}
		}
		$data['status']=1;
		return json($data);
	}

} 