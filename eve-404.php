<?php
/**
 * Eve
 * 404错误页面。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */
if (!defined('FILE_PREFIX')) include "error-forbidden.php";
// 输出404 防止搜索引擎收录
header("http/1.1 404 Not Found");
echo <<<EOF
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>出错啦</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0"/>
</head>
<body>
<h1>404</h1>
<p>网站似乎遇到了一些小问题，找不到你要访问的页面。</p>
</body>
</html>
EOF;
exit;