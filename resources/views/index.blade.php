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
    <!-- 微信浏览器缓存清理 -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <!-- <link rel="shortcut icon" href="./app/img/newicon3.png"> -->
    <title>焕熊管家</title>
    <link rel="stylesheet" href="./app/css/model.css">
    <style type="text/css">
        [v-cloak] {
            display: none !important;
        }
        .child-view {
            position: absolute;
            width: 100%;
            /* transition: all .8s cubic-bezier(.55, 0, .1, 1); */
        }

        .slide-left-enter,
        .slide-right-leave-active {
            opacity: 0;
            -webkit-transform: translate(500px, 0);
            transform: translate(500px, 0);
        }

        .slide-left-leave-active,
        .slide-right-enter {
            opacity: 0;
            -webkit-transform: translate(-500px, 0);
            transform: translate(-500px, 0);
        }
        .transitionName-enter-active{
            transition: all .6s cubic-bezier(.55, 0, .1, 1);
            z-index: 9;
        }
        .transitionName-leave-active{
            transition: all .4s cubic-bezier(.55, 0, .1, 1);
            z-index: 8;
        }
    </style>
</head>
<body>
<div id="app" class="app" >
    <!-- <transition :name="transitionName" mode="in-out"> -->
    <router-view class="child-view">

    </router-view>
    <!-- model start-->

    <!-- </transition> -->
    <div class="myModel">
        <my-model :result="result" @on-result-change="onResultChange"></my-model>
        <my-model2 :result2="result2" @on-result-change2="onResultChange2"></my-model2>
        <my-model3 :result3="result3" @on-result-change3="onResultChange3"></my-model3>
    </div>
    <div v-show="loadImg" class="loading"></div>

</div>
<script>
    window.socketHost = 'wss://ws.rekoon.cn';
    window.openId = '{{ $uniqueId }}';
    var auths={};
    document.body.addEventListener('touchstart', function () { });
</script>
<script type="text/javascript" src="./dist/bundle.js"></script>
<script  type="text/javascript" src="./app/js/reconnecting-websocket.min.js"></script>
<script type="text/javascript">
    !function(a,b){function c(){var b=f.getBoundingClientRect().width;b/i>540&&(b=540*i);var c=b/10;f.style.fontSize=c+"px",k.rem=a.rem=c}var d,e=a.document,f=e.documentElement,g=e.querySelector('meta[name="viewport"]'),h=e.querySelector('meta[name="flexible"]'),i=0,j=0,k=b.flexible||(b.flexible={});if(g){var l=g.getAttribute("content").match(/initial\-scale=([\d\.]+)/);l&&(j=parseFloat(l[1]),i=parseInt(1/j))}else{if(h){var m=h.getAttribute("content");if(m){var n=m.match(/initial\-dpr=([\d\.]+)/),o=m.match(/maximum\-dpr=([\d\.]+)/);n&&(i=parseFloat(n[1]),j=parseFloat((1/i).toFixed(2))),o&&(i=parseFloat(o[1]),j=parseFloat((1/i).toFixed(2)))}}}if(!i&&!j){var p=a.navigator.userAgent,q=(!!p.match(/android/gi),!!p.match(/iphone/gi)),r=q&&!!p.match(/OS 9_3/),s=a.devicePixelRatio;i=q&&!r?s>=3&&(!i||i>=3)?3:s>=2&&(!i||i>=2)?2:1:1,j=1/i}if(f.setAttribute("data-dpr",i),!g){if(g=e.createElement("meta"),g.setAttribute("name","viewport"),g.setAttribute("content","initial-scale="+j+", maximum-scale="+j+", minimum-scale="+j+", user-scalable=no"),f.firstElementChild){f.firstElementChild.appendChild(g)}else{var t=e.createElement("div");t.appendChild(g),e.write(t.innerHTML)}}a.addEventListener("resize",function(){clearTimeout(d),d=setTimeout(c,300)},!1),a.addEventListener("pageshow",function(a){a.persisted&&(clearTimeout(d),d=setTimeout(c,300))},!1),"complete"===e.readyState?e.body.style.fontSize=12*i+"px":e.addEventListener("DOMContentLoaded",function(){e.body.style.fontSize=12*i+"px"},!1),c(),k.dpr=a.dpr=i,k.refreshRem=c,k.rem2px=function(a){var b=parseFloat(a)*this.rem;return"string"==typeof a&&a.match(/rem$/)&&(b+="px"),b},k.px2rem=function(a){var b=parseFloat(a)/this.rem;return"string"==typeof a&&a.match(/px$/)&&(b+="rem"),b}}(window,window.lib||(window.lib={}));
</script>
</body>
</html>