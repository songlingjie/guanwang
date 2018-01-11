<!DOCTYPE html>
<html >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0,maximum-scale=1.0">
    <meta content="h5 view" name="description">
    <meta content="h5 view" name="keywords">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Set render engine for 360 browser -->
    <meta name="renderer" content="webkit">
    <!-- No Baidu Siteapp-->
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <meta http-equiv="Cache-Control" content="no-transform" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">  -->
    <meta name = "format-detection" content="telephone = no" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="https://m.rekoon.cn/css/reset.css">
    <link rel="stylesheet" href="https://m.rekoon.cn/css/managerCertification.css">
    <title></title>
</head>
<body>
<div id="app" class="app">

    <table >
        <tr style="width: 100%;height: 100px;">
            <td id="result">

            </td>
        </tr>
        <tr>
            <td style="text-align: center" align="center">
                <input type="submit" class="chooseImage" name="上传图片">
            </td>
        </tr>
    </table>
</div>

<script src="https://m.rekoon.cn/js/jquery-3.2.1.min.js"></script>
<script type="text/jscript" src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>


<script>
    window.error = function(e){
        console.error(e)
    }
    var startUp = function(pic,fun){
        var url = "https://api.rekoon.cn/shibie";
        //创建xhr对象
        var xhr =new XMLHttpRequest();

        //		  var xhr = new XMLHttpRequest();
        //设置xhr请求的超时时间
        xhr.timeout = 3000;
        //设置响应返回的数据格式
        xhr.responseType = "text";
        //创建一个 post 请求，采用异步
        xhr.open('POST', url);
        //注册相关事件回调处理函数
        xhr.onload = function(e) {
            if(this.status == 200||this.status == 304){
                fun(this.responseText);
            }
        };
        xhr.ontimeout = function(e) {
            //alert('timeOut:'+JSON.stringify(e))
        };
        xhr.onerror = function(e,s,c) {

        };
        xhr.upload.onprogress = function(e,s,c) {

        };

        xhr.setRequestHeader("Content-Type", "application/octet-stream");
        xhr.setRequestHeader("Authorization", "imei");
        xhr.send(pic);

    }
    $(function(){
        $.post("https://api.rekoon.cn/jssdk",{'url':window.location.href},function(res){
            var data = res.data;
            token = data.token;
            domain = data.domain;
            wx.config(data.jssdk);
        })
        //处理验证失败的信息
        wx.error(function (res) {
            //alert('验证失败返回的信息:'+res);
        });
        //处理验证成功的信息
        wx.ready(function () {
            // 5 图片接口
            // 5.1 拍照、本地选图
            var images = {
                localId: [],
                serverId: []
            };
            $('.chooseImage').on('click',function(){
                var curbox = $(this).data('cur');
                $('#'+curbox).text('正在上传')
                wx.chooseImage({
                    count: 1,
                    sizeType: ['compressed'],
                    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                    success: function (res) {
                        wx.getLocalImgData({
                            localId: res.localIds[0], // 图片的localID
                            success: function (res) {
                                var localData = res.localData; // localData是图片的base64数据，可以用img标签显示
                                startUp(localData,function(v){
                                    $('#result').html(v.data)
                                    alert(JSON.stringify(v))
                                })
                            }
                        });
                    }
                });
            })
        });
    })



</script>

</body>
</html>