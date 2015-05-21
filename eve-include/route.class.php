<?php
/**
 * Eve
 *
 * 路由模块。
 *
 * @version 1.0.0
 *
 * @include
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Route extends Safe
{

    private $args = [];
    private $action = "";
    public $module = "";

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        $this->module = self::getQueryKey('mod');
        $this->action = self::getQueryKey('action');

        switch ($this->module) {
            case 'nginx':
                if (empty($this->action)) {
                    $this->action = "test";
                }
                new Nginx(['action' => $this->action]);
                break;
            case 'hosts':
                if (empty($this->action)) {
                    $this->action = "view";
                }
                new Hosts(['action' => $this->action]);
                break;
            case 'redis':
                if (empty($this->action)) {
                    $this->action = "info";
                }
                new Redis2(['action' => $this->action]);
                break;
            case 'doc':
                if (empty($this->action)) {
                    $this->action = "home";
                }
                break;
            case 'build':
                if (empty($this->action)) {
                    $this->action = "view-project";
                }
                break;
            default:
                break;
        }
    }


    /**
     * 将URI中的参数转换为小写字母
     *
     * @param $key
     *
     * @return string
     */
    private function getQueryKey($key)
    {
        if (!empty($_GET[ $key ])) {
            $action = strtolower(trim($_GET[ $key ]));
        } else {
            $action = "";
        }

        return $action;
    }

}