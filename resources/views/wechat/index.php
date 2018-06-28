<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>
    <meta name=viewport content="width=device-width,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title>天鹅阅读</title>
<!--    <link rel=icon href=/resources/assets/wechat/static/images/favicon.ico>-->
    <script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" charset="utf-8">
        wx.config(<?php echo $js->buildConfig(array('onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQZone'), true) ?>);
    </script>
    <script type="text/javascript" charset="utf-8">
        //wx.config(<?php //echo $js->buildConfig(array('onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQZone'), false) ?>//);
        //wx.ready(function(){
        //    wx.onMenuShareQQ({
        //        <?php //echo $share_str; ?>
        //    });
        //    wx.onMenuShareWeibo({
        //        <?php //echo $share_str; ?>
        //    });
        //    wx.onMenuShareTimeline({
        //        <?php //echo $share_str; ?>
        //    });
        //    wx.onMenuShareAppMessage({
        //        <?php //echo $share_str; ?>
        //    });
        //    wx.onMenuShareQZone({
        //        <?php //echo $share_str; ?>
        //    });
        //});
    </script>
</head>
<body>
<div id=app>
    <app>天鹅阅读</app>
</div>
</body>
</html>