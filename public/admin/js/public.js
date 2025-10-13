function getLocalTime(nS){
var time = new Date(nS*1000);
var y = time.getFullYear();//年
var m = time.getMonth() + 1;//月
var d = time.getDate();//日
var h = time.getHours();//时
var mm = time.getMinutes();//分
var s = time.getSeconds();//秒
return (y+"-"+m+"-"+d+' '+h+":"+mm+':'+s)
}