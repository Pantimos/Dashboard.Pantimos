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
        //  首页
        route::register('/', 'index');
        route::register('/index.php', 'index');
        route::register('/?*', 'index', true);
        route::register('/index.php?*', 'index', true);
        // 项目环境
        route::register('/api/create-project', 'project_create');
        route::register('/api/create-project?*', 'project_create', true);
        route::register('/api/remove-project', 'project_remove');
        route::register('/api/remove-project?*', 'project_remove', true);
        route::register('/api/project-list', 'project_list');
        route::register('/api/project-list?*', 'project_list', true);
        route::register('/project', 'project_index');
        route::register('/project?*', 'project_index', true);
        route::register('/project/?*', 'project_index', true);
        // 数据模拟
        route::register('/api/create-mock', 'mock_create');
        route::register('/api/create-mock?*', 'mock_create', true);
        route::register('/api/remove-mock', 'mock_remove');
        route::register('/api/remove-mock?*', 'mock_remove', true);
        route::register('/api/mock-list', 'mock_list');
        route::register('/api/mock-list?*', 'mock_list', true);
        route::register('/api/mock-emulate', 'mock_emulate');
        route::register('/api/mock-emulate?*', 'mock_emulate', true);
        route::register('/MockRequest/*', 'mock_emulate', true);
        route::register('/mock', 'mock_index');
        route::register('/mock?*', 'mock_index', true);
        route::register('/mock/?*', 'mock_index', true);
        // 图片模拟
        route::register('/mockimage', 'mockimage_index');
        route::register('/mockimage?*', 'mockimage_index', true);
        route::register('/mockimage/?*', 'mockimage_index', true);
        // 如何使用
        route::register('/how-to-use', 'how_to_use');
        route::register('//how-to-use?*', 'how_to_use', true);
        // 上传文件
        route::register('/upload', 'upload');
        route::register('/upload?*', 'upload', true);
        // 项目环境
        route::register('/about-project', 'intro');
        route::register('/about-project?*', 'intro', true);
        // 其他
        route::register('/hi-cat', 'hi_cat');
        route::register('/hi-cat?*', 'hi_cat', true);
        route::register('*', 'page404', true);
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
        return new Index(['page' => 'index']);
    }

    /**
     * @since 1.0.0
     *
     * @param string $page
     * @param string $action
     *
     * @return Index
     */
    public function page($page, $action = 'index')
    {
        return new $page(['page' => $page, 'action' => $action]);
    }

    /** ********** 项目管理 ********** **/
    public function project_index()
    {
        return self::page('Project');
    }

    public function project_create()
    {
        return self::page('Project', 'create');
    }

    public function project_remove()
    {
        return self::page('Project', 'remove');
    }

    public function project_list()
    {
        return self::page('Project', 'list');
    }

    /** ********** 数据模拟 ********** **/
    public function mock_index()
    {
        return self::page('Mock');
    }

    public function mock_list()
    {
        return self::page('Mock', 'list');
    }

    public function mock_emulate()
    {
        return self::page('Mock', 'emulate');
    }

    public function mock_create()
    {
        return self::page('Mock', 'create');
    }

    public function mock_remove()
    {
        return self::page('Mock', 'remove');
    }

    /** ********** 图片模拟 ********** **/
    public function mockimage_index()
    {
        return self::page('MockImage');
    }

    /** ********** 如何使用 ********** **/
    public function how_to_use()
    {
        return self::page('Intro', 'how-to');
    }

    /** ********** 项目介绍 ********** **/
    public function intro()
    {
        return self::page('Intro');
    }

    /** ********** 文件上传 ********** **/
    public function upload()
    {
        return self::page('Upload');
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

}