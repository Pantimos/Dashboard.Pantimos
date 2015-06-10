<?php
/**
 * Eve
 *
 * 网站首页。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) include "../error-forbidden.php";

class Index extends Safe
{
    function __construct()
    {
        $params = func_get_args()[0];

        $params['nav'] = [
            'showHomeMenu' => false
        ];

        $params['body'] = [];

        $params['footer'] = [
            'currentYear'     => date('Y')
        ];

        new Template($params);
    }
}
