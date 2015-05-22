<?php
/**
 * HOSTS 管理模块
 *
 * @desc 提供HOSTS的增删改
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
    private $query = '';

    function __construct()
    {
        $this->args = core::init_args(func_get_args());

        if ($_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest') {
            switch ($this->args['action']) {
                case 'add':
                    self::add(true);
                    break;
                case 'remove':
                    self::remove(true);
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

    /**
     * 检查输入参数
     *
     * @param $data
     *
     * @return array|bool
     */
    private function checkParams($data)
    {
        if (isset($data)) {
            if (!(gettype($data) == 'string' && self::isValidateURL($data)) &&
                !(gettype($data) == 'object' && self::isValidateURL($data['host']))
            ) {
                API::fail("请检查输入内容。", true);
            }
        } else {
            $data = self::testStatus();
        }
        if (!$data) {
            API::fail("请检查输入内容。", true);
        }

        return $data;
    }

    /**
     * 添加域名绑定
     *
     * @param bool $isXHR
     * @param null $data
     */
    public function add($isXHR = false, $data = null)
    {
        $data = self::checkParams($data);
        exec('sed -ie "\|^' . $data['ip'] . '           ' . $data['host'] . '\$|d" ' . $this->config['bin']);
        exec('echo "' . $data['ip'] . '           ' . $data['host'] . '" >> ' . $this->config['bin'] . "\n");
        if ($isXHR) {
            API::success("添加域名成功。", true);
        } else {
            system('cat ' . $this->config['bin']);
        }
    }

    /**
     * 删除域名绑定
     *
     * @param bool $isXHR
     * @param null $data
     */
    public function remove($isXHR = false, $data = null)
    {
        $data = self::checkParams($data);
        exec('sed -ie "\|^' . $data['ip'] . '           ' . $data['host'] . '\$|d" ' . $this->config['bin']);
        if ($isXHR) {
            API::success("删除域名成功。", true);
        } else {
            system('cat ' . $this->config['bin']);
        }
    }

    /**
     * 验证链接
     *
     * @param $url
     *
     * @return bool
     */
    private function isValidateURL(&$url)
    {
        if (preg_match("/([a-z0-9\-]\.?)+[a-z]/i", $url)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 测试 hosts 配置是否正确
     *
     * @param string $data
     *
     * @return array|bool
     */
    private function testStatus($data = "")
    {
        $this->query = '';

        if (empty($data) && !empty($_GET['data'])) {
            $this->query = trim(urldecode($_GET['data']));
        } elseif (isset($data)) {
            $this->query = $data;
        } else {
            return false;
        }
        $this->query = strtolower(trim(urldecode($_GET['data'])));
        $ret = preg_match('/^(.+)@(.+)/', $this->query, $matches);
        if ($ret) {
            if (count($matches) !== 3) {
                return false;
            } else {
                $host = $matches[1];
                // 验证域名合法性
                if (!self::isValidateURL($host)) {
                    return false;
                }
                $correct = filter_var($matches[2], FILTER_VALIDATE_IP);
                if (!$correct) {
                    $ip = '127.0.0.1';
                } else {
                    $ip = $matches[2];
                }
            }
        } else {
            // 验证域名合法性
            if (!self::isValidateURL($this->query)) {
                return false;
            }
            $host = $this->query;
            $ip = '127.0.0.1';
        }

        return [
            'host' => $host,
            'ip'   => $ip
        ];
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

    /**
     * 查看当前系统中绑定的域名
     */
    private function view()
    {
        system('cat ' . $this->config['bin']);
    }

    /**
     * 恢复系统默认HOSTS配置
     */
    private function restore()
    {
        exec('echo "' . $this->config['base'] . '" >' . $this->config['bin']);
        system('cat ' . $this->config['bin']);
    }
}
