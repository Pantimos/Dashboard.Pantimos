<?php
/**
 * Nginx 管理模块
 *
 * @desc 提供nginx的简单管理
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Nginx extends Safe
{
    private $args = [];
    private $config = [
        'bin'    => '/usr/local/openresty/nginx/sbin/nginx',
        'buffer' => vmRootDir . vmDomainName . '/public/nginx.log'
    ];

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        $action = isset($this->args['action']) ? $this->args['action'] : "";

        if (core::isAjax()) {
            switch ($action) {
                case 'reload':
                    self::reload(true);
                    break;
            }
        } else {
            self::optButtons();
            echo '<textarea id="console-result">';
            switch ($action) {
                case 'reload':
                    self::reload();
                    break;
                case 'restart':
                    self::restart();
                    break;
                case 'test':
                    self::test();
                    break;
            }
            echo '</textarea>';
        }
    }

    /**
     * 测试 nginx 配置是否正确
     *
     * @return int
     */
    private function testStatus()
    {
        return shell_exec('cat ' . $this->config['buffer'] . ' | grep "test is successful"') ? 200 : 400;
    }

    /**
     * 操作按钮
     */
    private function optButtons()
    {
        echo '<div class="btn-group control-btn" role="group">
            <a class="btn btn-default" href="./?pantimos_mod=nginx&pantimos_action=test">test</a>
            <a class="btn btn-default" href="./?pantimos_mod=nginx&pantimos_action=reload">reload</a>
            <a class="btn btn-default btn-nginx-restart" href="./?pantimos_mod=nginx&pantimos_action=restart">restart</a>
        </div>';
    }

    /**
     * 重新加载配置
     *
     * @param bool $silent
     *
     * @return bool
     */
    public function reload($silent = false)
    {
        switch (self::testStatus()) {
            case 200:
                exec($this->config['bin'] . ' -s reload');
                echo "\n";
                ob_start();
                system('ps -ef | grep nginx');
                $ret = ob_get_contents();
                ob_end_clean();
                if ($silent) {
                    return true;
                } else {
                    $ret = explode("\n", $ret);
                    foreach ($ret as $line) {
                        if (strpos($line, 'nginx:')) {
                            echo $line . "\n";
                        }
                    }
                }
                break;
            case 400:
                if ($silent) {
                    return false;
                } else {
                    system('cat ' . $this->config['buffer']);
                    echo('配置有误，请确定配置无误，再尝试重载。');
                }
                break;
        }
    }

    /**
     * 重启服务
     */
    private function restart()
    {
        switch (self::testStatus()) {
            case 200:
                passthru($this->config['bin'] . ' -s stop && ' . $this->config['bin'] . '');
                system('ps -ef | grep nginx');
                break;
            case 400:
                system('cat ' . $this->config['buffer']);
                API::fail('配置有误，请确定配置无误，再尝试重载。');
                break;
        }
    }

    /**
     * 测试输出
     */
    private function test()
    {
        system($this->config['bin'] . ' -t 2>' . $this->config['buffer']);
        switch (self::testStatus()) {
            case 200:
                echo '配置一切正常。';
                break;
            case 400:
                system('cat ' . $this->config['buffer']);
                echo '配置有误，请确定配置无误，再尝试重载。';
                break;
        }
    }
}
