{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
        a{
            text-decoration: none;
        }
        .mask {

            position: fixed;
            z-index: 10001;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,.5);


        }
        .pop {
            width: 400px;
            height: auto;
            margin-left: -200px;

            margin-top: -200px;

            display: block;

            position: fixed;
            top: 50%;
            left: 50%;
            background: #fff;
            padding: 10px;
            z-index: 10002;

        }
        .system-message{ padding: 30px; margin-bottom: 0px; height: auto;}
        .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        .system-message .jump{ padding-top: 10px; }
        .system-message .jump a{ color: #333; }
        .system-message .success,.system-message .error{ line-height: 30px; font-size: 26px; }
        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display: none; }

        .system-message .leftimg{
            float: left;
            margin-right: 20px;
        }
        .system-message .rightcontent{
            float: left;
            max-width: 248px;
        }

    </style>
</head>
<body>
<div class="mask">
    <div class="pop">


        <div class="system-message">
            <?php switch ($code) {?>
            <?php case 1:?>
            <div class="leftimg"> <img src="http://www.shoppingip.com/static/images/success.png" /> </div>
            <div class="rightcontent">  <p class="success"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
            <?php case 0:?>
            <div class="leftimg">  <img src="http://www.shoppingip.com/static/images/error.png" /></div>
            <div class="rightcontent">    <p class="error"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
            <?php } ?>
            <p class="detail"></p>
            <p class="jump">
                页面自动<a id="href" href="<?php echo($url);?>">跳转</a>等待时间： <b id="wait"><?php echo($wait);?></b>
            </p>
        </div>
        <div style="clear: both; height: 0px;"></div>
    </div>
    <div style="clear: both; height: 0px;"></div>
</div>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
