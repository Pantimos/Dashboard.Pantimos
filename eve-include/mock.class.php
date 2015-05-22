<?php
/**
 * 模拟接口管理
 *
 * @desc 提供模拟接口管理
 */

if (!defined('FILE_PREFIX')) die('Silence is golden.');

class Mock extends Safe
{
    private $args = [];
    private $config = [
        'bin'      => 'node ' . vmRootDir . vmDomainName . '/public/eve-bin/Mock/bin/cli',
        'dataRoot' => vmRootDir . vmDomainName . '/public/eve-content/data/'
    ];
    private $query = "";
    private $host = "";

    function __construct()
    {
        $this->args = core::init_args(func_get_args());
        $action = isset($this->args['action']) ? $this->args['action'] : "";
        if (core::isAjax()) {
            switch ($action) {
                case 'view':
                    break;
                case 'emulate':
                    header('Pantimos: Data Emulate');
                    self::mockXHR($this->args);
                    break;
            }
        } else {
            switch ($action) {
                case 'view':
                    self::optButtons();
                    echo '<textarea id="console-result">';
                    self::view();
                    echo '</textarea>';
                    break;
                case 'emulate':
                    header('Pantimos: Data Emulate');
                    self::mockPage($this->args);
                    break;
            }
        }
    }

    /**
     * 操作按钮
     */
    private function optButtons()
    {
        echo '<div class="btn-group control-btn" role="group">
            <a class="btn btn-default" href="./?pantimos_mod=mock&pantimos_action=view">view</a>
            <a class="btn btn-default" href="./?pantimos_mod=mock&pantimos_action=restart">create</a>
            <a class="btn btn-default" href="./?pantimos_mod=mock&pantimos_action=destroy">destroy</a>
        </div>';
    }

    /**
     * 简单处理数据
     *
     * @param $config
     *
     * @return array
     */
    private function analyseData($config)
    {
        $params = $_REQUEST;
        unset ($params['pantimos_mod']);
        unset ($params['pantimos_action']);
        unset ($params['pantimos_hostname']);
        unset ($params['pantimos_query']);
        // 考虑是否限制目录深度，过深是否合并
        $fullPath = $this->config['dataRoot'] . $config['host'] . $config['query'];
        $fileName = basename($fullPath . "_api");
        system("mkdir -p " . dirname($fullPath));

        $file = $fullPath . $fileName . ".txt";
        if (file_exists($file)) {
            $code = 200;
        } else {
            $code = 404;
        }
        // 考虑加映射表
        // 这里考虑同样区分protocol 抑或使用proxy带参区分
        return ['code' => $code, 'file' => $file, 'host' => $config['host'], 'query' => $config['query'], 'params' => $params];
    }

    private function mockXHR($config)
    {
        $data = self::analyseData($config);
    }

    private function mockPage($config)
    {
        $data = self::analyseData($config);
        var_dump($data);
        switch ($data['code']) {
            case 200:
                echo '1111';
                break;
            case 404:
                echo 'cat "test">' . $data['file'];
                system('cat test>' . $data['file']);
                break;
        }

    }


    private function view()
    {


//
//        $query = preg_split( '/\//', PATH );
//        array_shift( $query );
//        $count = count( $query );
//        if ( empty( $query[ $count - 1 ] ) ) {
//            array_pop( $query );
//        }
////if ( empty( $query ) ) {
////    exit( '请选择接口' );
////}
//
//// 限制callback name长度为30字符长度
//        if ( isset($_REQUEST['CallbackName']) ) {
//            if ( strlen( $_REQUEST['CallbackName'] ) && strlen( $_REQUEST['CallbackName'] ) < 30 ) {
//                $callbackName = $_REQUEST['CallbackName'];
//            } else {
//                $callbackName = 'callback';
//            }
//        } else {
//            $callbackName = false;
//        }
//        if ( $callbackName ) {
//            $useCallback = $_REQUEST[ $callbackName ] ? $_REQUEST[ $callbackName ] : false;
//        } else {
//            $useCallback = $_REQUEST['callback'] ? $_REQUEST['callback'] : false;
//        }
//
//        $params = $_REQUEST;
//        unset( $params['_uriPath_'] );
//        unset( $params[ $callbackName ] );
//
//#if (!isset($apiList[$query[0]])) exit('接口:' . $query[0] . '不存在!');
//#if (!in_array($query[1], $apiList[$query[0]])) exit('接口:' . $query[1] . '不存在!');
//
//
//
//        echo 'view'.$this->config['bin'];
    }

}



