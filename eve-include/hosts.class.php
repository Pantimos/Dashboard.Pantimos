<?php
/**
 * hosts 管理模块
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Hosts extends Safe
{
    private $args = [];
    private $config = [
        'bin'    => '/etc/hosts',
        'buffer' => vmRootDir . vmDomainName . '/public/hosts.log',
        'base'   => '# Base Hosts
127.0.0.1           localhost

# Default Hosts
127.0.1.1           Pantimos

# The following lines are desirable for IPv6 capable hosts
::1                 localhost ip6-localhost ip6-loopback
ff02::1             ip6-allnodes
ff02::2             ip6-allrouters

# Base Env Hosts
127.0.0.1           code.io www.pantimos.io pma.pantimos.io dashboard.pantimos.io

# hhvm
140.211.166.134     dl.hhvm.com

# extend hosts'
    ];

    function __construct()
    {
        $this->args = core::init_args(func_get_args());

        if ($_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest') {
            switch ($this->args['action']) {
                case 'add':
                    self::addByXHR();
                    break;
                case 'remove':
                    self::removeByXHR();
                    break;
            }
        } else {
            self::optButtons();
            echo '<textarea id="console-result">';
            switch ($this->args['action']) {
                case 'view':
                    self::view();
                    break;
                case 'restore':
                    self::restore();
                    break;
                case 'add':
                    self::add();
                    break;
                case 'remove':
                    self::remove();
                    break;
            }
            echo '</textarea>';
        }
    }


    private function addByXHR()
    {
        $test = self::testStatus();
        if ($test) {
            exec('sed -ie "\|^' . $test['ip'] . '           ' . $test['host'] . '\$|d" ' . $this->config['bin']);
            exec('echo "' . $test['ip'] . '           ' . $test['host'] . '" >> ' . $this->config['bin'] . "\n");
            echo '"ok"';
        } else {
            echo '"请检查输入内容。"';
        }
    }

    private function removeByXHR()
    {
        $test = self::testStatus();
        if ($test) {
            exec('sed -ie "\|^' . $test['ip'] . '           ' . $test['host'] . '\$|d" ' . $this->config['bin']);
            echo '"ok"';
        } else {
            echo '"请检查输入内容。"';
        }
    }

    /**
     * 测试 hosts 配置是否正确
     *
     * @return array|bool
     */
    private function testStatus()
    {
        if ($_GET['data']) {
            $query = trim(urldecode($_GET['data']));
            $ret = preg_match('/^(.+)@(.+)/', $query, $matches);

            if ($ret) {
                if (count($matches) !== 3) {
                    return false;
                } else {
                    $host = $matches[1];
                    $correct = filter_var($matches[2], FILTER_VALIDATE_IP);
                    if (!$correct) {
                        $ip = '127.0.0.1';
                    } else {
                        $ip = $matches[2];
                    }
                }
            } else {
                $host = $query;
                $ip = '127.0.0.1';
            }

            return [
                'host' => $host,
                'ip'   => $ip
            ];
        } else {
            return false;
        }
    }

    /**
     * 操作按钮
     */
    private function optButtons()
    {
        echo '<div class="btn-group control-btn" role="group">
    <a class="btn btn-default" href="./?mod=hosts&action=view">view</a>
    <a class="btn btn-default" href="./?mod=hosts&action=restore">restore</a>
    <a class="btn btn-default btn-edit-hosts" href="javascript:void(0)">edit</a>
</div>

<div class="panel panel-default panel-edit-hosts hide">
    <div class="panel-body">

        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="text" class="form-control input-host-add"
                           placeholder="example.code.io@127.0.0.1 || example.code.io">
                    <span class="input-group-btn">
                        <a class="btn btn-default btn-host-add" href="./?mod=hosts&action=add">add</a>
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="text" class="form-control input-host-remove"
                           placeholder="example.code.io@127.0.0.1 || example.code.io">
                    <span class="input-group-btn">
                        <a class="btn btn-default btn-host-remove" href="./?mod=hosts&action=remove">remove</a>
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>';
    }


    private function view(){
        system( 'cat ' . $this->config['bin'] );
    }

    private function restore(){
        exec( 'echo "' . $this->config['base'] . '" >' . $this->config['bin'] );
        system( 'cat ' . $this->config['bin'] );
    }

    private function add(){
        $test = self::testStatus();
        if ( $test ) {
            exec( 'sed -ie "\|^' . $test['ip'] . '           ' . $test['host'] . '\$|d" ' . $this->config['bin'] );
            exec( 'echo "' . $test['ip'] . '           ' . $test['host'] . '" >> ' . $this->config['bin'] . "\n" );
            system( 'cat ' . $this->config['bin'] );
        } else {
            echo '请检查输入内容。';
        }
    }

    private function remove(){
        $test = self::testStatus();
        if ( $test ) {
            exec( 'sed -ie "\|^' . $test['ip'] . '           ' . $test['host'] . '\$|d" ' . $this->config['bin'] );
            system( 'cat ' . $this->config['bin'] );
        } else {
            echo '请检查输入内容。';
        }
    }

}
