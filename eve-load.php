<?php
/**
 * Eve
 *
 * 程序加载器。
 *
 * @version 1.0.0
 *
 * @todo
 *      - 配置文件不存在的时候展示初始化配置文件。
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

define('ABSPATH', dirname(__FILE__) . '/');

error_reporting(E_ALL);
//error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);

if (file_exists(ABSPATH . FILE_PREFIX . 'config.php')) {

    require_once(ABSPATH . FILE_PREFIX . 'config.php');

} else {
    //todo: replace html
    die('Can\'t find config file.');
}
