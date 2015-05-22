<?php
/**
 * project 管理模块
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Project extends Safe
{
    private $args = [];
    private $config = [
        'blackList' => ['dashboard.pantimos.io', 'mock.pantimos.io'],
        'base'      => '
##
# {$DOMAIN_NAME}
##
server {
    listen       80;
    server_name  {$DOMAIN_NAME};
    server_name  127.0.0.1;

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

    function __construct()
    {
        $this->args = core::init_args(func_get_args());

        if ($_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest') {
            switch ($this->args['action']) {
                case 'do':
                    self::doJob();
                    break;
            }
        } else {
            self::optButtons();
            echo '<textarea id="console-result">';
            switch ($this->args['action']) {
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

    private function help()
    {
        self::showProject();
    }


    private function create()
    {
        echo "请输入要创建的项目的域名。";
    }

    private function destroy()
    {
        echo "请输入要删除的项目的域名。";
    }

    private function doJob()
    {

        if (empty($_GET['data']) || empty($_GET['do'])) {
            die("请检查输入内容");
        } else {

            $domainName = strtolower(trim($_GET['data']));
            if (empty($domainName)) {
                die("请检查输入内容");
            }

            $domainPath = vmRootDir . $domainName;

            switch ($_GET['do']) {
                case 'create':
                    if (file_exists($domainPath)) {
                        die("项目已经存在，如果想重新初始化，请先删除项目。");
                    }

                    system('mkdir -p ' . $domainPath . '/public');
                    system('mkdir -p ' . $domainPath . '/conf');
                    system('mkdir -p ' . $domainPath . '/logs');
                    $tpl = str_replace('{$DOMAIN_NAME}', $_GET['data'], $this->config['base']);
                    $tpl = str_replace('{$DOMAIN_PATH}', $domainPath, $tpl);

                    system('echo "' . $tpl . '" >' . $domainPath . '/conf/nginx.conf');
                    die('ok');
                    break;
                case 'destroy':
                    foreach ($this->config['blackList'] as $key) {
                        $pos = strpos($domainPath, $key);
                        echo $pos . "\n";
                        if ($pos) {
                            die("不允许删除保留域名。");
                        }
                    }
                    if (!file_exists($domainPath)) {
                        die("目标不存在或已被删除");
                    }
                    system('rm -rf ' . $domainPath);
                    die('ok');
                    break;
            }
        }

    }


    /**
     * 操作按钮
     */
    private function optButtons()
    {
        echo '<div class="btn-group control-btn" role="group">
            <a class="btn btn-default" href="./?mod=project&action=help">help</a>
            <a class="btn btn-default" href="./?mod=project&action=create">create</a>
            <a class="btn btn-default" href="./?mod=project&action=destroy">destroy</a>
        </div>

<div class="panel panel-default panel-edit-project hide">
    <div class="panel-body">

        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="text" class="form-control input-project-name"
                           placeholder="example.code.io">
                    <span class="input-group-btn">
                        <a class="btn btn-default btn-project-do" href="./?mod=project&action=do">ok</a>
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>';
    }

    private function testStatus()
    {
        exec($this->config['bin'] . ' -t 2>' . $this->config['buffer']);

        return shell_exec('cat ' . $this->config['buffer'] . ' | grep "test is successful"') ? 200 : 400;
    }

}
