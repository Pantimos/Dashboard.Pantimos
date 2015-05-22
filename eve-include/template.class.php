<?php
/**
 * Eve
 * 程序模版函数库。
 *
 * @version 1.0.0
 *
 * @include
 *
 * @todo
 *      - 整理函数。
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Template extends RainTPL
{
    private $args = [];
    protected $tpl;
    private $process_time_start;
    private $process_time_end;
    private $posts = [];

    function __construct()
    {
        $this->args = core::init_args(func_get_args());

        if ($this->args['DEBUG']) {
            $this->mktimestamp();
        }

        date_default_timezone_set('PRC');

        $this->initTemplate();

        if ($this->args['GZIP'] && core::gzip_accepted()) {
            if (!ob_start(!$this->args['DEBUG'] ? 'ob_gzhandler' : null)) {
                ob_start();
            }
        }

        if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || $_SERVER["HTTP_X_REQUESTED_WITH"] != 'XMLHttpRequest') {
            $this->header();
        }

        $mod = new Route();

        if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || $_SERVER["HTTP_X_REQUESTED_WITH"] != 'XMLHttpRequest') {
            if (empty($mod->module)) {
                $this->defaultPage();
            }
            $this->footer();
        }
    }

    /**
     * 获取当前脚本运行时间
     *
     * @param bool $end
     *
     * @return string
     */
    protected function mktimestamp($end = false)
    {
        if (!$end) {
            $this->process_time_start = core::get_mircotime();
        } else {
            $this->process_time_end = core::get_mircotime();

            return number_format($this->process_time_end - $this->process_time_start, 5);
        }
    }

    /**
     * 获取当前静态数据的相关数据
     *
     * @param $current
     * @param $target
     *
     * @return mixed|string
     */
    private function get_static_single_data_resources($current, $target)
    {
        $result = explode('.' . $current['type'], $current['path'])[0] . '.' . $target['type'];
        switch ($target['type']) {
            case 'html':
                //todo: check
                return str_replace("/" . FILE_PREFIX . "content/data", "/" . FILE_PREFIX . "content/post", $result);
            case 'json':
                return $result;
        }
    }

    /**
     * 获取所有的静态数据
     *
     * @param $post
     *
     * @return bool|mixed
     */
    private function get_static_singel_post_meta($post)
    {
        $postMeta = $this->get_static_single_data_resources(
            ['type' => 'md', 'path' => $post],
            ['type' => 'json']
        );
        if (is_file($postMeta)) {
            $content = file_get_contents($postMeta);
            if (!$content) {
                return false;
            } else {
                $meta = json_decode($content);

                return $meta;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取所有的静态文章数据
     * 过滤掉POST META.JSON等文件
     *
     * @return array
     */
    private function get_all_static_posts()
    {
        $files = core::scan_file("./" . FILE_PREFIX . "content/data");
        $result = [];
        clearstatcache();
        foreach ($files as $key => $file) {
            if (strstr($file, '.md')) {
                array_push($result, $file);
            }
        }
        $result = array_flip(@array_flip($result));

        return $result;
    }

    /**
     * 获取所有的静态数据
     *
     * @return array
     */
    public function get_static_posts()
    {
        $posts = [];
        $datas = $this->get_all_static_posts();
        reset($datas);
        while (list($key, $post) = each($datas)) {
            $meta = $this->get_static_singel_post_meta($post);
            if ($meta) {
                $posts['title'] = $meta->title;
                $posts['date'] = $meta->date;
                $posts['type'] = $meta->type;
                $posts['status'] = $meta->status;
                $posts['tags'] = $meta->tags;
                $posts['template'] = $meta->template;
                $posts['content'] = core::parseMarkdown($post);
                $posts['post_url'] = $post;
                array_push($this->posts, $posts);
            }
        }

        return $this->posts;
    }

    /**
     * * THE TEMPLATE CLASS INIT
     * TODO:增加定义主题和缓存路径以及增加MEMCACHE支持
     */
    private function initTemplate()
    {
        //init static post data;
        $this->get_static_posts();
        if (file_exists(FILE_PREFIX . "content/theme/" . THEME . "/")) {
            RainTPL::$tpl_dir = FILE_PREFIX . "content/theme/" . THEME . "/";
            RainTPL::$cache_dir = FILE_PREFIX . "content/theme/" . THEME . "_cache/";
        } else {
            RainTPL::$tpl_dir = FILE_PREFIX . "content/theme/default/";
            RainTPL::$cache_dir = FILE_PREFIX . "content/theme/default_cache/";
        }
        //initialize a Rain TPL object
        $this->tpl = new RainTPL();
    }

    /**
     * THE TEMPLATE HEADER MODULE
     */
    private function header()
    {
        $data = [
            'PAGE_TITLE'   => 'Dashboard Pantimos',
            'PAGE_CHARSET' => E_CHARSET,
            'PAGE_LANG'    => E_LANG,
            'USER'         => 'SOULTEARY',
            'copyright'    => 'SOULTEARY'
        ];
        $this->tpl->assign($data);
        echo $this->tpl->draw('header', $return_string = true);
    }

    /**
     * THE TEMPLATE FOOTER MODULE
     */
    private function footer()
    {
        //调试信息输出
        if ($this->args['DEBUG']) {
            //core::clean_cache();
            //echo time();
            $this->tpl->assign("DEBUG_PAGE_ARGU", $this->args);
            $this->tpl->assign("DEBUG_DATA", Debug::theDebug());
            //$this->tpl->assign( "DEBUG_MARKDOWN", core::parseMarkdown('Readme.md') );

            $ip = new IP(['ONLYIP' => true, 'ECHO' => false]);
            $this->tpl->assign("DEBUG_IP_CURRENT", $ip->result);

            $timestamp = $this->mktimestamp(true);
            $timestamp = "\n<!--Process in $timestamp seconds.-->\n";
            $this->tpl->assign("DEBUG_TIMESTAMP", $timestamp);

        }
        $this->tpl->assign('PAGE_SCRIPT', file_get_contents(FILE_PREFIX . 'content/theme/default/assets/js/script.js'));
        echo $this->tpl->draw('footer', $return_string = true);
    }

    private function defaultPage()
    {
        echo $this->tpl->draw('default', $return_string = true);
    }

}
