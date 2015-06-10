<?php
/**
 * Project 管理模块
 *
 * @desc 目录项目管理
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Project2 extends Safe
{
    private $args = [];
    private $data = null;
    private $do = null;

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        $action = isset($this->args['action']) ? $this->args['action'] : "";

        if (core::isAjax()) {
            switch ($action) {
                case 'list':
                    self::listProject();
                    break;
                case 'do':
                    self::doJob();
                    break;
            }
        } else {
            self::optButtons();
            echo '<textarea id="console-result">';
            switch ($action) {
                case 'list':
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
        system("tree " . vmRootDir . " -id -L 1");
        $ret = ob_get_contents();
        ob_end_clean();
        $ret = str_replace(vmRootDir, "", $ret);

        $arr = explode("\n", $ret);
        $data = [
            'code' => 200,
            'data' => []
        ];
        foreach ($arr as $key => $val) {
            $val = trim($val);
            if ($val && !strstr($val, 'directories')) {
                array_push($data['data'], $val);
            }
        }
        API::json($data);
    }

    /**
     * 显示项目
     */
    private function listProject()
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
            <a class="btn btn-default" href="./?pantimos_mod=project&pantimos_action=list">list</a>
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
