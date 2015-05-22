<?php
/**
 * redis 管理模块
 *
 * @notice hhvm下redis类名被占用，注意规避
 * @desc 简单的Redis管理，待封装
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Redis2 extends Safe
{
    private $args = [];
    private $config = [
        'databaseDir' => vmRootDir . vmDomainName . '/public/',
        'base'        => [
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379
        ]
    ];
    private $instance = null;
    private $cmdPrefix = '';

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        try {

            Predis\Autoloader::register();
            $this->instance = new Predis\Client($this->config['base']);
            $this->instance->connect();
            $redis_connected = true;
        } catch (Exception $exception) {
            $redis_connected = false;
        }

        $this->cmdPrefix = "$ " . $this->config['base']['host'] . ":" . $this->config['base']['port'] . " > ";
        self::optButtons();
        echo '<textarea id="console-result">';
        switch ($this->args['action']) {
            case 'info':
                self::info();
                break;
            case 'save':
                self::save();
                break;
            case 'flush':
                self::flush();
                break;
            case 'flush-mock':
                self::flushMock();
                break;
        }
        echo '</textarea>';

    }


    /**
     * 操作按钮
     */
    private function optButtons()
    {
        echo '<div class="btn-group control-btn" role="group">
    <a class="btn btn-default" href="./?pantimos_mod=redis&pantimos_action=info">info</a>
    <a class="btn btn-default" href="./?pantimos_mod=redis&pantimos_action=save">save</a>
    <a class="btn btn-default" href="./?pantimos_mod=redis&pantimos_action=flush">flush</a>
    <a class="btn btn-default" href="./?pantimos_mod=redis&pantimos_action=flush-mock">flush(mock)</a>
</div>';
    }

    private function info()
    {
        echo $this->cmdPrefix . "\n";
        $ret = $this->instance->info();
        foreach ($ret as $cat => $catContent) {
            echo "\n$cat \n";
            foreach ($catContent as $title => $content) {
                echo "\t$title\t: $content\n";
            }
        }
    }

    private function save()
    {
        echo $this->cmdPrefix . "SAVE\n";
        echo $this->cmdPrefix . $this->instance->save();
    }

    private function flush()
    {
        echo $this->cmdPrefix . "FLUSHALL\n";
        echo $this->cmdPrefix . $this->instance->flushall();
    }

    private function flushMock()
    {
        $url = 'http://mock.pantimos.io/record?flush-cache';
        echo 'QUERY: ' . $url;
    }
}
