<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <!--[if IE 8]><style>.ie8 .alert-circle,.ie8 .alert-footer{display:none}.ie8 .alert-box{padding-top:75px}.ie8 .alert-sec-text{top:45px}</style><![endif]-->
    <title>正在跳转</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #E6EAEB;
            font-family: Arial, '微软雅黑', '宋体', sans-serif
        }
        .center_top {
            position: relative;
            width: 100%;
            height: 167px;
            margin: 0 auto;
            text-align: center;
        }
        .center {
            width: 100%;
            margin: 0 auto;
            text-align: center;
            position: absolute;
            top: -38%;
        }
        .alert-box {
            display: none;
            position: relative;
            margin: 96px auto 0;
            border-radius: 10px 10px 0 0;
            background: #FFF;
            box-shadow: 5px 9px 17px rgba(102,102,102,0.75);
            width: 80%;
            color: #FFF;
            text-align: center
        }
        .alert-box p {
            margin: 0
        }
        .alert-circle {
            /*position: absolute;*/
            /*top: -38%;*/
            /*left: 0%;*/
        }
        .alert-sec-circle {
            stroke-dashoffset: 0;
            stroke-dasharray: 735;
            transition: stroke-dashoffset 1s linear
        }
        .alert-sec-text {
            /*width: 76px;*/
            color: #000;
            font-size: 68px;
            text-anchor: middle;  /* 文本水平居中 */
            dominant-baseline: middle; /* 文本垂直居中 */
        }
        .alert-sec-unit {
            font-size: 34px
        }
        .alert-body {
            margin: 35px 40px;
            padding-bottom: 50px;
        }
        .alert-head {
            color: #242424;
            font-size: 28px
        }
        .alert-concent {
            margin: 25px 0 14px;
            color: #7B7B7B;
            font-size: 18px
        }
        .alert-concent p {
            line-height: 27px
        }
        .alert-btn {
            display: block;
            border-radius: 10px;
            background-color: #4AB0F7;
            height: 55px;
            line-height: 55px;
            /*width: 286px;*/
            color: #FFF;
            font-size: 20px;
            text-decoration: none;
            letter-spacing: 2px
        }
        .alert-btn:hover {
            background-color: #6BC2FF
        }
        .alert-footer-text p {
            color: #7A7A7A;
            font-size: 22px;
            line-height: 18px
        }
    </style>
</head>
<body class="ie8">

<div id="js-alert-box" class="alert-box">
    <div class="center_top">
        <div class="center">
            <svg class="alert-circle" width="234" height="234">
                <circle cx="117" cy="117" r="108" fill="#FFF" stroke="#<?php echo $color;?>" stroke-width="17"></circle>
                <circle id="js-sec-circle" class="alert-sec-circle" cx="117" cy="117" r="108" fill="transparent" stroke="#F4F1F1" stroke-width="18" transform="rotate(-90 117 117)"></circle>
                <text id="js-sec-text" x="117" y="117" class="alert-sec-text"></text>
            </svg>

        </div>
    </div>
    <div class="alert-body">
        <div id="js-alert-head" class="alert-head"></div>
        <div class="alert-concent">
            <?php echo $msg;?>
        </div>
        <a id="js-alert-btn" class="alert-btn" href="<?php echo $url;?>"><?php echo $go;?></a>
    </div>

</div>


<script type="text/javascript">
    function alertSet(e) {
        document.getElementById("js-alert-box").style.display = "block",
            document.getElementById("js-alert-head").innerHTML = e;
        var t = <?php echo $sec;?>,
            n = document.getElementById("js-sec-circle");
        document.getElementById("js-sec-text").innerHTML = t,
            setInterval(function() {
                    if (0 == t){
                        location.href="<?php echo $url;?>";
                    }else {
                        t -= 1,
                            document.getElementById("js-sec-text").innerHTML = t;
                        var e = Math.round(t / 10 * 735);
                        n.style.strokeDashoffset = e - 735
                    }
                },
                970);
    }
</script>

<script>alertSet('<?php echo $type;?>');</script>

</body>
</html>

