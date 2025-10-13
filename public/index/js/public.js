var res_url="http://127.0.0.1/";
/**导航栏**/
$(".topnav1,.navlist").mouseover(function (){
    $(".navlist").show();
}).mouseout(function (){
    $(".navlist").hide();
});
$(".nav-item").mouseover(function (){
    $(".nav-item").removeClass('back-white');
    $(".nav-item").removeClass('def_color');
    $(".nav-item").addClass('color-white');
    $(".navclass").hide();
    $(this).next().show();
    $(this).addClass('back-white');
    $(this).removeClass('color-white');
    $(this).addClass('def_color');
}).mouseout(function (){
    $(".navclass").hide();
});
$(".topnav1,.search-box,.index_top,.footvie,.swiper-container,.topnavri").mouseover(function (){
    $(".nav-item").removeClass('back-white');
    $(".nav-item").removeClass('def_color');
    $(".nav-item").addClass('color-white');
})
$(".navclass").mouseover(function (){
    //判断同级元素是否被选中  如果选中则显示当前  否则隐藏
    if($(this).prev().is('.back-white')){
        $(this).show();
    }else{
        $(this).hide();
    }
}).mouseout(function (){
    $(".navclass").hide();
});

$(".sear-btn").click(function(){
    var va=$("#searc").val();
    window.location.href='/index/Shop/classlist?txt='+va;
})
$("#out_login").click(function(){
    $.post(
        res_url+"/index/Login/login_out",
        {'type':'login'},
        function(data,state){
            if(state != "success"){
                layer.msg("请求出错!");
            }else if(data.code == 200){
                layer.msg(data.message,{
                    time:2000,
                    end:function () {
                        location.href = '/index/Login/login'
                    }
                })
            }else{
                layer.msg(data.message);
            }
        }
    );

})

