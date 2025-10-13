$(".account_btn").click(function(){
    $('.log_phone').hide();
    $('.account').show();
    $(this).addClass('def_color');
    $(".phone_btn").removeClass('def_color');
    $(".defi").removeClass('def_background');
    $(this).children().next().addClass('def_background');
});
$(".phone_btn").click(function(){
    $('.log_phone').show();
    $('.account').hide();
    $(this).addClass('def_color');
    $(".account_btn").removeClass('def_color');
    $(".defi").removeClass('def_background');
    $(this).children().next().addClass('def_background');
});
//手机号验证码登录
$(".reg_yzm_btn").click(function(){
    var phone=$("#reg_login_phone").val();
    //请求
    $.post(
        res_url+"resource/sendmsg",
        {"mobile":phone,'type':'register'},
        function(data,state){
            if(state != "success"){
                layer.msg("请求出错!");
            }else if(data.code == 200){
                layer.msg(data.message);
            }else{
                layer.msg(data.message);
            }
        }
    );
});
//手机号验证码登录
$(".log_yzm_btn").click(function(){
    var phone=$("#reg_login_phone").val();
    //请求
    $.post(
        res_url+"resource/sendmsg",
        {"mobile":phone,'type':'login'},
        function(data,state){
            if(state != "success"){
                layer.msg("请求出错!");
            }else if(data.code == 200){
                layer.msg(data.message);
            }else{
                layer.msg(data.message);
            }
        }
    );
});
//手机号验证码登录
$(".reg_logbtn").click(function(){
    var phone=$("#reg_login_phone").val();
    var code=$("#reg_login_code").val();
    //请求
    $.post(
        res_url+"resource/phonelogin",
        {"mobile":phone,'type':'login','code':code},
        function(data,state){
            if(state != "success"){
                layer.msg("请求出错!");
            }else if(data.code == 200){
                layer.msg(data.message,{
                    time:2000,
                    end:function () {
                        location.href = '/'
                    }
                })
            }else{
                layer.msg(data.message);
            }
        }
    );
});

//账号密码登录
$(".user_login_btn").click(function(){
    var user=$("#login_user").val();
    var pwd=$("#login_pwd").val();
    if(user.length<1){
        layer.msg("用户名不能为空");return false;
    }
    if(pwd.length<1){
        layer.msg("密码不能为空");return false;
    }
    //请求
    $.post(
        res_url+"resource/login_user",
        {"user":user,'type':'login','pwd':pwd},
        function(data,state){
            if(state != "success"){
                layer.msg("请求出错!");
            }else if(data.code == 200){
                layer.msg(data.message,{
                    time:2000,
                    end:function () {
                        location.href = '/'
                    }
                })
            }else{
                layer.msg(data.message);
            }
        }
    );
});


//账号密码注册
$(".reg_btn").click(function(){
    var user=$("#login_user").val();
    var pwd=$("#login_pwd").val();
    var pwd2=$("#login_pwd_to").val();
    if(user.length<1){
        layer.msg("用户名不能为空");return false;
    }
    if(pwd.length<1){
        layer.msg("密码不能为空");return false;
    }
    if(pwd!=pwd2){
        layer.msg("两次密码不一样");return false;
    }
    //请求
    $.post(
        res_url+"resource/reg_user",
        {"user":user,'type':'login','pwd':pwd},
        function(data,state){
            if(state != "success"){
                layer.msg("请求出错!");
            }else if(data.code == 200){
                layer.msg(data.message,{
                    time:2000,
                    end:function () {
                        location.href = '/'
                    }
                })
            }else{
                layer.msg(data.message);
            }
        }
    );
});