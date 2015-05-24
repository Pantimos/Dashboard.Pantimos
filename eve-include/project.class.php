<?php
/**
 * Project 管理模块
 *
 * @desc 目录项目管理
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Project extends Safe
{
    private $args = [];
    private $config = [
        'blackList' => [
            'dashboard.pantimos.io',
            'mock.pantimos.io',
            'pma.pantimos.io',
            'localhost',
            'Pantimos',
            'ip6-localhost',
            'ip6-loopback',
            'dl.hhvm.com'
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

    set     \$APP_DIR {$DOMAIN_PATH};
    root    \$APP_DIR/public;
    index   index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php?q=\$uri&\$args;
    }

    location ~ \.(hh|php)\$ {
        fastcgi_keep_conn on;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include        fastcgi_params;
    }

}
'
    ];
    private $data = null;
    private $do = null;

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        $action = isset($this->args['action']) ? $this->args['action'] : "";

        if (core::isAjax()) {
            switch ($action) {
                case 'do':
                    self::doJob();
                    break;
            }
        } else {
            self::optButtons();
            echo '<textarea id="console-result">';
            switch ($action) {
                case 'help':
                    self::help();
                    break;
                case 'create':
                    self::create();
                    break;
                case 'destroy':
                    self::destroy();
                    break;
            }
            echo '</textarea>';
        }
    }

    /**
     * 显示项目列表
     */
    private function showProject()
    {
        ob_start();
        system("tree " . vmRootDir . " -L 1");
        $ret = ob_get_contents();
        ob_end_clean();
        $ret = str_replace(vmRootDir, "当前存在项目目录: \n", $ret);
        $ret = str_replace(', 0 files', '', $ret);
        echo $ret;
    }

    /**
     * 显示帮助
     */
    private function help()
    {
        self::showProject();
    }

    /**
     * 创建项目
     */
    private function create()
    {
        echo("请输入要创建的项目的域名。");
    }

    /**
     * 删除项目
     */
    private function destroy()
    {
        echo("请输入要删除的项目的域名。");
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

    /**
     * 执行具体任务
     *
     * @param null $data
     * @param null $do
     */
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

    /**
     * 操作按钮
     */
    private function optButtons()
    {
        echo '<div class="btn-group control-btn" role="group">
            <a class="btn btn-default" href="./?pantimos_mod=project&pantimos_action=help">help</a>
            <a class="btn btn-default" href="./?pantimos_mod=project&pantimos_action=create">create</a>
            <a class="btn btn-default" href="./?pantimos_mod=project&pantimos_action=destroy">destroy</a>
        </div>

<div class="panel panel-default panel-edit-project hide">
    <div class="panel-body">

        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="text" class="form-control input-project-name"
                           placeholder="example.pantimos.io">
                    <span class="input-group-btn">
                        <a class="btn btn-default btn-project-do" href="./?pantimos_mod=project&pantimos_action=do">ok</a>
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>';
    }
}
