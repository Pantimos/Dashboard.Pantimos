<?php if(!class_exists('raintpl')){exit;}?><!doctype html>
<html lang="<?php echo $PAGE_LANG;?>">
<head>
    <meta charset="<?php echo $PAGE_CHARSET;?>">
    <title><?php echo $PAGE_TITLE;?></title>
    <link rel="stylesheet/less" type="text/css" href="eve-content/theme/default/extra/style/main.less">
    <script type="text/javascript" src="eve-content/theme/default/extra/js/less-1.3.3.min.js"></script>
    <script type="text/javascript" src="eve-content/theme/default/extra/js/jquery.min.js"></script>
    <script type="text/javascript" src="eve-content/theme/default/extra/js/plugin.js"></script>
    <script type="text/javascript" src="eve-content/theme/default/extra/js/main.js"></script>
    <script src="eve-content/theme/default/assets/js/jquery-2.1.4.min.js"></script>
</head>
<body>


<?php if( DEBUG==true ){ ?>

<div id="debug-box">
    <?php echo var_dump( $DEBUG_PAGE_ARGU );?>

    <?php echo var_dump( $DEBUG_DATA );?>

    <?php echo $DEBUG_MARKDOWN;?>

    <?php echo $DEBUG_IP_CURRENT;?>

    <?php echo $DEBUG_TIMESTAMP;?>

</div>
<?php } ?>


</body>
</html>

