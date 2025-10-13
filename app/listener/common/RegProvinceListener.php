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
 * Date: 2022/5/10
 * Time: 20:59
 */
declare (strict_types = 1);

namespace app\listener\common;
use app\models\GeneralMapRegprovince;


class RegProvinceListener
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        $this->store($event['province']);
    }

    public function store($province)
    {
        //查询是否有这个省  有则+1  无则存入
        $res=GeneralMapRegprovince::where('province',$province)->find();
        if($res){
            $res->value+=1;
            $res->save();
        }else{
            if($province=='内蒙古自治区'){
                $newstr='内蒙古';
            }else if($province=='新疆维吾尔自治区'){
                $newstr='新疆';
            }else if($province=='西藏自治区'){
                $newstr='西藏';
            }else if($province=='宁夏回族自治区'){
                $newstr='宁夏';
            }else if($province=='香港特别行政区'){
                $newstr='香港';
            }else if($province=='澳门特别行政区'){
                $newstr='澳门';
            }else{
                $newstr = substr($province,0,strlen($province)-3);  //这是去掉字符串中的最后一个汉字
            }
            if($province && $newstr){
                GeneralMapRegprovince::create([
                    'province' => $province,
                    'value' => 1,
                    'name' => $newstr
                ]);
            }
        }
    }
}