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
        if (isset($params['action'])) {
            $action = $params['action'];
        } else {
            $action = 'index';
        }

        $data['header'] = [
            'TITLE'        => E_PAGE_TITLE,
            'PAGE_CHARSET' => E_CHARSET,
            'PAGE_LANG'    => E_LANG
        ];

        $data['nav'] = [
            'showEssentials' => true
        ];

        $data['body'] = [];

        $data['footer'] = [
            'currentYear' => date('Y')
        ];

        new Template($data);
    }
}
