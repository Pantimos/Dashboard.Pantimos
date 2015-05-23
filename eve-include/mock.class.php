<?php
/**
 * 模拟接口管理
 *
 * @desc 提供模拟接口管理
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Mock extends Safe
{
    private $args = [];
    private $config = [
        'bin'      => 'node ' . vmRootDir . vmDomainName . '/public/eve-bin/Mock/bin/cli',
        'dataRoot' => vmRootDir . vmDomainName . '/public/eve-content/data/',
        'wrapper'  => [
            "start" => "module.exports = function () {/*!\n",
            "end"   => "\n*/};"
        ]
    ];
    private $query = "";
    private $host = "";

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        $action = isset($this->args['action']) ? $this->args['action'] : "";
        if (core::isAjax()) {
            switch ($action) {
                case 'view':
                    break;
                case 'emulate':
                    header('Pantimos: Data Emulate');
                    self::mockXHR($this->args);
                    break;
            }
        } else {
            switch ($action) {
                case 'view':
                    self::optButtons();
                    echo '<textarea id="console-result">';
                    self::view();
                    echo '</textarea>';
                    break;
                case 'emulate':
                    header('Pantimos: Data Emulate');
                    self::mockPage($this->args);
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
            <a class="btn btn-default" href="./?pantimos_mod=mock&pantimos_action=view">view</a>
            <a class="btn btn-default" href="./?pantimos_mod=mock&pantimos_action=restart">create</a>
            <a class="btn btn-default" href="./?pantimos_mod=mock&pantimos_action=destroy">destroy</a>
        </div>';
    }

    /**
     * 简单处理数据
     *
     * @param $config
     *
     * @return array
     */
    private function analyseData($config)
    {
        $params = $_REQUEST;
        unset ($params['pantimos_mod']);
        unset ($params['pantimos_action']);
        unset ($params['pantimos_hostname']);
        unset ($params['pantimos_query']);
        // 考虑是否限制目录深度，过深是否合并
        $fullPath = $this->config['dataRoot'] . $config['host'] . $config['query'];
        $fileName = basename($fullPath . "_api");
        system("mkdir -p " . dirname($fullPath));

        $file = $fullPath . $fileName . ".txt";
        if (file_exists($file)) {
            $code = 200;
        } else {
            $code = 404;
        }
        // 考虑加映射表
        // 这里考虑同样区分protocol 抑或使用proxy带参区分
        return ['code' => $code, 'file' => $file, 'host' => $config['host'], 'query' => $config['query'], 'params' => $params];
    }

    private function mockXHR($config)
    {
        $data = self::analyseData($config);
        switch ($data['code']) {
            case 200:

                $cmd = $this->config['bin'] . ' --tpl ' . '' . $data['file'] . '';
                ob_start();
                system($cmd);
                $output = ob_get_contents();
                ob_end_clean();

                if (core::isCallback()) {
                    API::callbackScript($output, false);
                } else {
                    API::json($output, false);
                }
                break;
            case 404:
                system('echo "' . $this->config['wrapper']['start'] . $this->config['wrapper']['end'] . '" >' . $data['file']);
                echo $data['file'] . "创建成功。";
                break;
        }
    }

    private function mockPage($config)
    {
        $data = self::analyseData($config);
        switch ($data['code']) {
            case 200:
                $cmd = $this->config['bin'] . ' --tpl ' . '"' . $data['file'] . '"';
                ob_start();
                system($cmd);
                $output = ob_get_contents();
                ob_end_clean();

                if (core::isCallback()) {
                    API::callbackScript($output, false);
                } else {
                    echo $output;
                }
                break;
            case 404:
                system('echo "' . $this->config['wrapper']['start'] . $this->config['wrapper']['end'] . '">' . $data['file']);
                echo $data['file'] . "创建成功。";
                break;
        }

    }


    private function view()
    {
        
    }

}



