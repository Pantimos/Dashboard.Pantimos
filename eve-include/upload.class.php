<?php
/**
 * Eve
 *
 * 上传文件。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) include "../error-forbidden.php";

class Upload extends Safe
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
            case 'upload':
                echo '上传文件';
                break;
            default:
                $data['header'] = [
                    'TITLE'        => '上传文件 - ' . E_PAGE_TITLE,
                    'PAGE_CHARSET' => E_CHARSET,
                    'PAGE_LANG'    => E_LANG
                ];

                $data['nav'] = [
                    'showHomeMenu' => false
                ];

                $data['body'] = [];
                $data['body_file'] = 'upload-index';

                $data['footer'] = [
                    'currentYear' => date('Y')
                ];

                return new Template($data);
        }

    }
}
