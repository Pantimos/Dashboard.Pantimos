<?php
/**
 * Eve
 *
 * API输出。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class API extends Safe
{

    /**
     * 安全输出JSON
     *
     * @param      $data
     * @param bool $encode
     */
    static function json($data, $encode = true)
    {
        header('X-Content-Type-Options: nosniff');
        header('Content-Type:application/json;charset=utf-8;');
        if ($encode) {
            $data = json_encode($data);
        }
        $data = str_replace('/\u2028/g', '\\u2028', $data);
        $data = str_replace('/\u2029/g', '\\u2029', $data);
        // Only allow "[","]","a-zA-Z0123456789_", "$" and "." characters.
        $data = str_replace('/[^\[\]\w$.]/g', '', $data);
        die($data);
    }

    /**
     * 输出带callback的脚本内容
     *
     * @notice 如果
     *
     * @param $data
     */
    static function callbackScript($data, $encode = true)
    {
        $useCallback = true;
        // 限制callback name长度为30字符长度
        if (isset($_GET['CallbackName'])) {
            if (strlen($_GET['CallbackName']) && strlen($_GET['CallbackName']) < 30) {
                $callbackName = $_GET['CallbackName'];
            } else {
                $callbackName = 'callback';
            }
        } else {
            $callbackName = "callback";
        }
        $useCallbackName = $_GET[ $callbackName ] ? $_GET[ $callbackName ] : "";
        if (empty($useCallbackName)) {
            $useCallback = false;
        }

        if ($useCallback) {
            echo "/**/ typeof window." . $useCallbackName . " === 'function' && " . $useCallbackName . "(\n";
        }


        header('X-Content-Type-Options: nosniff');
        header('Content-Type:application/x-javascript;charset=utf-8;');
        if($encode){
            $data = json_encode($data);
        }
        $data = str_replace('/\u2028/g', '\\u2028', $data);
        $data = str_replace('/\u2029/g', '\\u2029', $data);
        // Only allow "[","]","a-zA-Z0123456789_", "$" and "." characters.
        $data = str_replace('/[^\[\]\w$.]/g', '', $data);
        echo $data;

        if ($useCallback) {
            echo ")";
        }
        die;
    }

    /**
     * 输出成功的信息
     *
     * @param      $data
     * @param bool $isJSON
     */
    static function success($data, $isJSON = false)
    {
        if ($isJSON) {
            self::json([
                'code' => 200,
                'desc' => $data
            ]);
        } else {
            die("ok");
        }
    }

    /**
     * 输出失败信息
     *
     * @param      $data
     * @param bool $isJSON
     */
    static function fail($data, $isJSON = false)
    {
        if ($isJSON) {
            self::json([
                'code' => 400,
                'desc' => $data
            ]);
        } else {
            die($data);
        }
    }

}