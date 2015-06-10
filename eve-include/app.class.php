<?php
/**
 * Eve
 *
 * 程序入口。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) include "../error-forbidden.php";

class App extends Safe
{

    function __construct()
    {
        // 初始化运行参数
        core::init_args(func_get_args());

        // 初始化路由
        self::init_route();
    }

    /**
     * 初始化路由
     *
     * @since  1.0.0
     * @notice 主要的路径下，尽可能囊括更多的选择，诸如/join/?123
     */
    private function init_route()
    {
        route::register('/', 'index');
        route::register('/index.php', 'index');
        route::register('/\?.*', 'index', true);
        route::register('/index.php\?.*', 'index', true);




        route::register('/hi-cat', 'hi_cat');
        route::register('/hi-cat\?.*', 'hi_cat', true);
        route::register('.*', 'page404', true);

        new Route();
    }

    /**
     * 网站首页
     *
     * @since 1.0.0
     *
     * @return Index
     */
    public function index()
    {
        return new Index(['header' => self::get_page_data('index')]);
    }

    public function hi_cat()
    {
        echo 'hi-cat';
    }

    /**
     * 显示404页面
     *
     * @since 1.0.0
     *
     * @return Page404
     */
    public function page404()
    {
        include ABSPATH . FILE_PREFIX . "404.php";
    }

    /**
     * 根据路由名称获得页面基础数据
     *
     * @since 1.0.1
     *
     * @param $route
     *
     * @return mixed
     */
    private function get_page_data($route)
    {
        $data['MODULE'] = $route;
        $data['PAGE_CHARSET'] = E_CHARSET;
        $data['PAGE_LANG'] = E_LANG;

        return $data;
    }

}