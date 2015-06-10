<?php
/**
 * Eve
 *
 * 项目管理。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) include "../error-forbidden.php";

class Project extends Safe
{
    // 项目配置
    private $config = [
        'blackList' => [
            'localhost',
            'ip6-localhost',
            'ip6-loopback',
            'dl.hhvm.com',
            'Pantimos',
            'files.pantimos.io',
            'pantimos.io',
            'www.pantimos.io',
            'pma.pantimos.io',
            'dashboard.pantimos.io',
            'mock.pantimos.io',
            'editor.mock.pantimos.io',
            'mockimage.pantimos.io',
            'files.pantimos.io'
        ],
        'base'      => '##
# {$DOMAIN_NAME}
##
server {
    listen       80;
    server_name  {$DOMAIN_NAME};

    access_log  {$DOMAIN_PATH}/logs/access.log;
    error_log   {$DOMAIN_PATH}/logs/error.log;

    client_max_body_size 100m;
    server_name_in_redirect on;

    root        {$DOMAIN_PATH}/public;
    index       index.php index.html index.htm;

    location / {
        if (\$request_uri ~* ^/need_mock_api/api.name\$){
            rewrite \"(.*)\" /{$DOMAIN_NAME}/\$1 break;
            proxy_pass http://mock.pantimos.io;
        }
        if (\$request_uri ~* ^/favicon.ico\$){
            proxy_pass http://dashboard.pantimos.io;
        }

        try_files \$uri \$uri/ /index.php?q=\$uri&\$args;
    }

    location ~ \.(hh|php)\$ {
        fastcgi_keep_conn on;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include        fastcgi_params;
    }

}'
    ];

    function __construct()
    {
        $params = func_get_args()[0];
        if (isset($params['action'])) {
            $action = $params['action'];
        } else {
            $action = 'index';
        }

        // Core::init_args(func_get_args())

        switch ($action) {
            case 'create':
                if (isset($_POST['domain']) && !empty($_POST['domain'])) {
                    var_dump($_POST);

                } else {
                    API::fail('请输入正确的域名。', true);
                }
                break;
            case 'delete':
                break;
            default:
                $data['header'] = [
                    'TITLE'        => '项目管理 - ' . E_PAGE_TITLE,
                    'PAGE_CHARSET' => E_CHARSET,
                    'PAGE_LANG'    => E_LANG
                ];

                $data['nav'] = [];

                $data['body'] = [];
                $data['body_file'] = 'project-index';

                $data['footer'] = [
                    'currentYear' => date('Y')
                ];
                new Template($data);
                break;
        }
    }
}
