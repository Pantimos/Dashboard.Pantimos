<?php if(!class_exists('raintpl')){exit;}?><!doctype html>
<html lang="<?php echo $PAGE_LANG;?>">
<head>
    <meta charset="<?php echo $PAGE_CHARSET;?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $PAGE_TITLE;?></title>
    <link rel="stylesheet" href="eve-content/theme/default/assets/css/bootstrap.css"/>
    <link rel="stylesheet" href="eve-content/theme/default/assets/css/cover.css"/>
    <link rel="stylesheet" href="eve-content/theme/default/assets/css/codemirror.css"/>
    <link rel="stylesheet" href="eve-content/theme/default/assets/css/extend.css"/>
    <script src="eve-content/theme/default/assets/js/jquery-2.1.4.min.js"></script>
    <script src="eve-content/theme/default/assets/js/codemirror/codemirror.js"></script>
    <script src="eve-content/theme/default/assets/js/codemirror/mode/shell/shell.js"></script>
</head>
<body>

<div class="site-wrapper">
    <div class="site-wrapper-inner">
        <div class="cover-container">

            <div class="masthead clearfix">
                <div class="inner">
                    <h3 class="masthead-brand"><a href="/">Dashboard</a></h3>
                    <nav>
                        <ul class="nav masthead-nav">
                            <li class="nav-home"><a href="/">Home</a></li>
                            <li class="nav-doc"><a href="/?mod=doc">Doc/API</a></li>
                            <li class="nav-nginx"><a href="/?mod=nginx">Nginx</a></li>
                            <li class="nav-redis"><a href="/?mod=redis">Redis</a></li>
                            <li class="nav-hosts"><a href="/?mod=hosts">Hosts</a></li>
                            <li class="nav-mock"><a href="/?mod=mock">Mock</a></li>
                            <li class="nav-build"><a href="/?mod=build">Build</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
