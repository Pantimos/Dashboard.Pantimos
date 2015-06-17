<?php
/**
 * Eve
 *
 * 项目管理。
 *
 * @todo    获取项目列表后，检查项目状态，是否至少有nginx.conf
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
            'pantimos.io',
            'www.pantimos.io',
            'files.pantimos.io',
            'pma.pantimos.io',
            'dashboard.pantimos.io',
            'mock.pantimos.io',
            'format.pantimos.io',
            'editor.mock.pantimos.io',
            'mockimage.pantimos.io',
            'files.pantimos.io',
            'sprite.pantimos.io'
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

    location ~* "^/favicon.ico$" {
        proxy_pass http://dashboard.pantimos.io;
        break;
    }

    location / {
        if ($request_uri ~* ^/mock-api/.*$){
            proxy_pass http://mock.pantimos.io/?pantimos_query=/$host$uri&$args;
            break;
        }
        if (\$request_uri ~* ^/favicon.ico\$){
            proxy_pass http://dashboard.pantimos.io;
            break;
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

                    $domainName = self::checkDomain($_POST['domain']);

                    self::blackListChecker($this->config['blackList'], $domainName);

                    $domainPath = vmRootDir . $domainName;

                    if (file_exists($domainPath)) {
                        API::fail("项目已经存在，如果想重新初始化，请先删除项目。", true);
                    }

                    system('mkdir -p ' . $domainPath . '/public');
                    system('mkdir -p ' . $domainPath . '/conf');
                    system('mkdir -p ' . $domainPath . '/logs');
                    $tpl = str_replace('{$DOMAIN_NAME}', $domainName, $this->config['base']);
                    $tpl = str_replace('{$DOMAIN_PATH}', $domainPath, $tpl);
                    system('echo "' . $tpl . '" >' . $domainPath . '/conf/nginx.conf');
                    $hosts = new Hosts(func_get_args());
                    $hosts->add(false, $domainName);
                    $nginx = new Nginx(func_get_args());
                    $nginx->reload(true);
                    API::success("创建项目并绑定域名成功。", true);
                } else {
                    API::fail('请输入正确的域名。', true);
                }
                break;
            case 'remove':
                if (isset($_POST['domain']) && !empty($_POST['domain'])) {

                    $domainName = self::checkDomain($_POST['domain']);

                    self::blackListChecker($this->config['blackList'], $domainName);

                    $domainPath = vmRootDir . $domainName;

                    if (!file_exists($domainPath)) {
                        API::fail("目标不存在或已被删除。", true);
                    }

                    system('rm -rf ' . $domainPath);

                    $hosts = new Hosts(func_get_args());
                    $hosts->remove(false, $domainName);
                    $nginx = new Nginx(func_get_args());
                    $nginx->reload(true);
                    API::success("目标成功删除。", true);
                } else {
                    API::fail('请输入正确的域名。', true);
                }
                break;
            case 'list':
                self::getList();
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

                return new Template($data);
        }
    }


    /**
     * 检查Domain
     *
     * @param $name
     *
     * @return string
     */
    public function checkDomain($name)
    {
        if (!isset($name)) {
            API::fail("请检查输入内容。", true);
        }

        $domainName = strtolower(trim($name));
        if (empty($domainName)) {
            API::fail("请检查输入内容。", true);
        }

        $domainName = str_replace('http://', '', $domainName);

        $errorCheck = preg_match('/[^0-9a-zA-Z一-龥\-_\.]/u', $domainName);
        if ($errorCheck) {
            API::fail("请检查输入内容。", true);
        }

        return $domainName;
    }


    /**
     * 获取当前目录下的项目列表
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
}
