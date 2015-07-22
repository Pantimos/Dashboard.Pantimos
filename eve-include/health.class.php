<?php
/**
 * Eve
 *
 * 组件状态页面。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) include "../error-forbidden.php";

class Health extends Safe
{
    function __construct()
    {
        $params = func_get_args()[0];
        if (isset($params['action'])) {
            $action = $params['action'];
        } else {
            $action = 'index';
        }


        switch ($action) {
            default:
                $data['header'] = [
                    'TITLE'        => '组件状态 - ' . E_PAGE_TITLE,
                    'PAGE_CHARSET' => E_CHARSET,
                    'PAGE_LANG'    => E_LANG
                ];

                $data['nav'] = [
                    'showHomeMenu' => false
                ];

                $data['body'] = [];
                $data['body_file'] = 'health-index';

                $data['footer'] = [
                    'currentYear' => date('Y')
                ];

                return new Template($data);
        }

    }
}
