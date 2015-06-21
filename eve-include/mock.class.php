<?php
/**
 * Eve
 *
 * 模拟接口管理。
 *
 * @version 1.0.0
 *
 * @email   soulteary@qq.com
 * @website http://soulteary.com
 */

if (!defined('FILE_PREFIX')) include "../error-forbidden.php";

class Mock extends Safe
{
    private $config = [
        'bin'     => 'node ' . ABSPATH . FILE_PREFIX . 'bin/Mock/bin/cli',
        'wrapper' => [
            "start"   => "module.exports = function () {\n/*!\n",
            "example" => "
{
    basics: {
        boolean1: '@BOOLEAN',
        boolean2: '@BOOLEAN(1, 9, true)',

        natural1: '@NATURAL',
        natural2: '@NATURAL(10000)',
        natural3: '@NATURAL(60, 100)',

        integer1: '@INTEGER',
        integer2: '@INTEGER(10000)',
        integer3: '@INTEGER(60, 100)',

        float1: '@FLOAT',
        float2: '@FLOAT(0)',
        float3: '@FLOAT(60, 100)',
        float4: '@FLOAT(60, 100, 3)',
        float5: '@FLOAT(60, 100, 3, 5)',

        character1: '@CHARACTER',
        character2: '@CHARACTER(\"lower\")',
        character3: '@CHARACTER(\"upper\")',
        character4: '@CHARACTER(\"number\")',
        character5: '@CHARACTER(\"symbol\")',
        character6: '@CHARACTER(\"aeiou\")',

        string1: '@STRING',
        string2: '@STRING(5)',
        string3: '@STRING(\"lower\",5)',
        string4: '@STRING(7, 10)',
        string5: '@STRING(\"aeiou\", 1, 3)',

        range1: '@RANGE(10)',
        range2: '@RANGE(3, 7)',
        range3: '@RANGE(1, 10, 2)',
        range4: '@RANGE(1, 10, 3)',

        date: '@DATE',
        time: '@TIME',

        datetime1: '@DATETIME',
        datetime2: '@DATETIME(\"yyyy-MM-dd A HH:mm:ss\")',
        datetime3: '@DATETIME(\"yyyy-MM-dd a HH:mm:ss\")',
        datetime4: '@DATETIME(\"yy-MM-dd HH:mm:ss\")',
        datetime5: '@DATETIME(\"y-MM-dd HH:mm:ss\")',
        datetime6: '@DATETIME(\"y-M-d H:m:s\")',

        now: '@NOW',
        nowYear: '@NOW(\"year\")',
        nowMonth: '@NOW(\"month\")',
        nowDay: '@NOW(\"day\")',
        nowHour: '@NOW(\"hour\")',
        nowMinute: '@NOW(\"minute\")',
        nowSecond: '@NOW(\"second\")',
        nowWeek: '@NOW(\"week\")',
        nowCustom: '@NOW(\"yyyy-MM-dd HH:mm:ss SS\")'
    },
    image: {
        image1: '@IMAGE',
        image2: '@IMAGE(\"100x200\", \"#000\")',
        image3: '@IMAGE(\"100x200\", \"#000\", \"hello\")',
        image4: '@IMAGE(\"100x200\", \"#000\", \"#FFF\", \"hello\")',
        image5: '@IMAGE(\"100x200\", \"#000\", \"#FFF\", \"png\", \"hello\")',

        dataImage1: '@DATAIMAGE',
        dataImage2: '@DATAIMAGE(\"200x100\")',
        dataImage3: '@DATAIMAGE(\"300x100\", \"Hello Mock.js!\")'
    },
color: {
    color: '@COLOR'
    },
text: {
    title1: '@TITLE',
        title2: '@TITLE(5)',
        title3: '@TITLE(3, 5)',

        word1: '@WORD',
        word2: '@WORD(5)',
        word3: '@WORD(3, 5)',

        sentence1: '@SENTENCE',
        sentence2: '@SENTENCE(5)',
        sentence3: '@SENTENCE(3, 5)',

        paragraph1: '@PARAGRAPH',
        paragraph2: '@PARAGRAPH(2)',
        paragraph3: '@PARAGRAPH(1, 3)'
    },
name: {
    first: '@FIRST',
        last: '@LAST',
        name1: '@NAME',
        name2: '@NAME(true)'
    },
web: {
    url: '@URL',
        domain: '@DOMAIN',
        email: '@EMAIL',
        ip: '@IP',
        tld: '@TLD',
    },
address: {
    area: '@AREA',
        region: '@REGION'
    },
miscellaneous: {
    guid: '@GUID',
        id: '@ID',
        'increment1|3': [
        '@INCREMENT'
    ],
        'increment2|3': [
        '@INCREMENT(10)'
    ]
    },
helpers: {
    capitalize1: '@CAPITALIZE()',
        capitalize2: '@CAPITALIZE(\"hello\")',

        upper1: '@UPPER',
        upper2: '@UPPER(\"hello\")',

        lower1: '@LOWER',
        lower2: '@LOWER(\"HELLO\")',

        pick1: '@PICK',
        pick2: '@PICK(\"abc\")',
        pick3: '@PICK([\"a\", \"b\", \"c\"])'
    }
}
",
            "end"     => "\n*/\n};"
        ]
    ];

    function __construct()
    {
        $params = func_get_args()[0];
        if (isset($params['action'])) {
            $action = $params['action'];
        } else {
            $action = 'index';
        }

        switch ($action) {
            case 'list':
                self::getList();
                break;
            case 'emulate':
                header('Pantimos: Data Emulate');
                Core::isAjax() || Core::isCallback() ? self::mockXHR() : self::mockPage();
                break;
            case 'create':
                var_dump(self::analyseData());
                break;
            case 'remove':
                break;
            default:
                $data['header'] = [
                    'TITLE'        => '数据模拟 - ' . E_PAGE_TITLE,
                    'PAGE_CHARSET' => E_CHARSET,
                    'PAGE_LANG'    => E_LANG
                ];

                $data['nav'] = [];

                $data['body'] = [];
                $data['body_file'] = 'mock-index';

                $data['footer'] = [
                    'currentYear' => date('Y')
                ];

                return new Template($data);
        }
    }

    /**
     * 获取当前目录下的项目列表
     */
    private function getList()
    {
        ob_start();
        system("tree " . vmRootDir . " -id -L 1");
        $ret = ob_get_contents();
        ob_end_clean();

        $ret = str_replace(vmRootDir, "", $ret);

        $arr = explode("\n", $ret);
        $data = [];
        foreach ($arr as $key => $val) {
            $val = trim($val);
            if ($val && !strstr($val, 'directories')) {
                array_push($data, $val);
            }
        }

        $result = [];

        foreach ($data as $item) {
            $hasMock = false;
            if (is_dir(vmRootDir . "/" . $item . "/mock")) {
                $hasMock = true;
            }
            array_push($result, ['name' => $item, 'mock' => $hasMock]);
        }

        API::success($result, true, 200, '获取Mock列表成功。');
    }

    /**
     * 简单处理数据
     *
     * @return array
     */
    private function analyseData()
    {
        $params = explode('/', $_REQUEST['pantimos_query']);
        $host = $params[1];
        $query = implode('/', array_slice($params, 2));
        // 考虑是否限制目录深度，过深是否合并
        $fullPath = vmRootDir . $host . "/mock/" . $query;
        $dirPath = dirname($fullPath);
        $fileBaseName = basename($fullPath);
        $fileExt = "_mockFile";

        if (substr($dirPath, strlen($fileBaseName)) === $fileBaseName) {
            $fileName = $fileExt;
        } else {
            $fileName = $fileBaseName . $fileExt;
        }

        // fix for no domain request
        if ($dirPath === vmRootDir) {
            $dirPath .= 'None.Domain.Mock.Data';
        } elseif ($dirPath . '/' === vmRootDir) {
            $dirPath .= '/None.Domain.Mock.Data';
        }

        system("mkdir -p " . $dirPath);

        $file = $dirPath . '/' . $fileName . ".txt";
        if (file_exists($file)) {
            $code = 200;
        } else {
            $code = 404;
        }
        // 考虑加映射表
        // 这里考虑同样区分protocol 抑或使用proxy带参区分
        return ['code' => $code, 'file' => $file, 'host' => $host, 'query' => $query, 'params' => $params];
    }

    private function mockXHR()
    {
        $data = self::analyseData();
        switch ($data['code']) {
            case 200:
                $cmd = implode(' ', [$this->config['bin'], '--json', $data['file']]);
                ob_start();
                system($cmd);
                $output = ob_get_contents();
                ob_end_clean();

                if (core::isCallback()) {
                    API::callbackScript($output, false);
                } else {
                    API::json($output, false);
                }
                break;
            case 404:
                system('echo "' . $this->config['wrapper']['start'] . $this->config['wrapper']['example'] . $this->config['wrapper']['end'] . '" >' . $data['file']);
                echo "Mock模板创建成功。";
                break;
        }
    }

    private function mockPage()
    {
        $data = self::analyseData();
        switch ($data['code']) {
            case 200:
                $cmd = implode(' ', [$this->config['bin'], '--html', $data['file']]);
                ob_start();
                system($cmd);
                $output = ob_get_contents();
                ob_end_clean();

                if (core::isCallback()) {
                    API::callbackScript($output, false);
                } else {
                    echo $output;
                }
                break;
            case 404:
                system('echo "' . $this->config['wrapper']['start'] . $this->config['wrapper']['example'] . $this->config['wrapper']['end'] . '" >' . $data['file']);
                echo "Mock模板创建成功。";
                break;
        }

    }
}



