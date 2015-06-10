<?php if (!defined('FILE_PREFIX')) include '../../../error-forbidden.php'; ?>
<!doctype html>
<html lang="{$PAGE_LANG}">
<head>
    <meta charset="{$PAGE_CHARSET}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$PAGE_TITLE}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="assets/css/todc-bootstrap.min.css"/>
    <link rel="stylesheet" href="assets/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="assets/css/buttons.css"/>
    <link rel="stylesheet" href="assets/css/style.min.css"/>
    <script src="assets/js/lib/require.min.js"></script>
    <script src="assets/js/lib/jquery-2.1.4.min.js"></script>
    <script src="assets/js/lib/bootstrap.min.js"></script>
    <script src="assets/js/lib/headroom.min.js"></script>
    <script>
        requirejs.config({
            baseUrl: './',
            paths  : {
                'moe': './assets/js/moe.min'
            }
        });
        require(['moe'], function (theme) {
            console.log(theme)
        });
    </script>
</head>
<body>
