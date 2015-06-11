<?php
/**
 * Eve
 *
 * 项目管理。
 *
 * @todo 获取项目列表后，检查项目状态，是否至少有nginx.conf
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
            case 'list':
                return self::getList();
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

                return new Template($data);
        }
    }


    /**
     * 获取项目列表
     */
    private function getList()
    {
        ob_start();
        system("tree " . vmRootDir . " -id -L 1");
        $ret = ob_get_contents();
        ob_end_clean();
        $ret = str_replace(vmRootDir, "", $ret);

        $arr = explode("\n", $ret);
        $data = [];
        foreach ($arr as $key => $val) {
            $val = trim($val);
            if ($val && !strstr($val, 'directories')) {
                array_push($data, $val);
            }
        }
        API::success($data, true, 200, '获取项目列表成功。');
    }

    /**
     * 操作黑名单
     *
     * @param $blacklist
     * @param $domainPath
     */
    private function blackListChecker($blacklist, $domainPath)
    {
        $targetDir = explode("/", $domainPath);
        $targetDir = $targetDir[ count($targetDir) - 1 ];
        foreach ($blacklist as $key) {
            $pos = strpos($targetDir, $key) !== false;
            if ($pos) {
                API::fail("不允许操作保留域名。", true);
            }
        }
    }

    private function doJob($data = null, $do = null)
    {

        if (isset($data) && isset($do)) {
            $this->data = $data;
            $this->do = $do;
        } elseif (!empty($_GET['data']) && !empty($_GET['do'])) {
            $this->data = $_GET['data'];
            $this->do = $_GET['do'];
        } else {
            API::fail("请检查输入内容。");
        }

        $this->do = strtolower(trim($this->do));
        $domainName = strtolower(trim($this->data));
        if (empty($domainName)) {
            API::fail("请检查输入内容。");
        }

        $domainPath = vmRootDir . $domainName;
        switch ($this->do) {
            case 'create':
                self::blackListChecker($this->config['blackList'], $domainPath);
                if (file_exists($domainPath)) {
                    API::fail("项目已经存在，如果想重新初始化，请先删除项目。", true);
                }

                system('mkdir -p ' . $domainPath . '/public');
                system('mkdir -p ' . $domainPath . '/conf');
                system('mkdir -p ' . $domainPath . '/logs');
                $tpl = str_replace('{$DOMAIN_NAME}', $this->data, $this->config['base']);
                $tpl = str_replace('{$DOMAIN_PATH}', $domainPath, $tpl);
                system('echo "' . $tpl . '" >' . $domainPath . '/conf/nginx.conf');
                $hosts = new Hosts(func_get_args());
                $hosts->add(false, $domainName);
                $nginx = new Nginx(func_get_args());
                $nginx->reload(true);
                API::success("创建项目并绑定域名成功。", true);
                break;
            case 'destroy':
                self::blackListChecker($this->config['blackList'], $domainPath);
                if (!file_exists($domainPath)) {
                    API::fail("目标不存在或已被删除。", true);
                }

                system('rm -rf ' . $domainPath);
                $hosts = new Hosts(func_get_args());
                $hosts->remove(false, $domainName);
                $nginx = new Nginx(func_get_args());
                $nginx->reload(true);
                API::success("目标成功删除。", true);
                break;
        }

    }

}
