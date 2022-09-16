<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class LangMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new language pack';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $api_usleep = ['youdao' => 500, 'baidu' => 1000, 'test' => 500];
        $new_lang = $this->ask('请输入新的要生成的语言标识。如：zh');
        $api = $this->choice(
            '请选择翻译api?',
            array_keys($api_usleep),
            0
        );

        if (empty($new_lang)) {
            $this->error('语言标识必填');
            exit;
        }

        $this->info('即将请求外网api进行翻译……');

        $base = require(resource_path() . '/lang/en/api.php');

        $str = sprintf("<?php%s%sreturn [%s", PHP_EOL, PHP_EOL, PHP_EOL);

        $bar = $this->output->createProgressBar(count($base, 1) - count($base));

        $bar->start();


        foreach ($base as $key => $val) {
            $str .= sprintf("   '%s' => [%s", $key, PHP_EOL);
            foreach ($val as $key2 => $val2) {
                usleep($api_usleep[$api]);

                $val2 = call_user_func_array([$this, $api], [$key2, $new_lang]);

                $str .= sprintf("       '%s' => '%s',%s", $key2, $val2, PHP_EOL);
                $bar->advance();
            }
            $str .= '       ],' . PHP_EOL;

        }
        $str .= '];' . PHP_EOL;

        $new_file = sprintf("%s/lang/%s_api.php", resource_path(), $new_lang);
        file_put_contents($new_file, $str);

        $bar->finish();

        $this->info(PHP_EOL . '翻译完成');

        $this->info(PHP_EOL .$new_file . '文件已生成');

        return 0;
    }

    public static function youdao($q, $to , $from = 'en')
    {
        $appid = '2e98088644c93594';
        $secret = 'i76Kx6ouJ8ZoB78H7TpghZg8cZXLwO87';
        $salt = mt_rand(1000000, 9999999);

        $args = array(
            'q' => $q,
            'appKey' => $appid,
            'salt' => $salt,
        );
        $args['from'] = $from;
        $args['to'] = $to;
        $args['signType'] = 'v3';
        $curtime = strtotime("now");
        $args['curtime'] = $curtime;
        $len = self::abslength($q);
        $input = $len <= 20 ? $q : (mb_substr($q, 0, 10) . $len . mb_substr($q, $len - 10, $len));
        $signStr = $appid . $input . $salt . $curtime . $secret;
        $args['sign'] = hash("sha256", $signStr);
        //$args['vocabId'] = '您的用户词表ID';


        $res = Http::get('https://openapi.youdao.com/api', $args)->body();
        $res_array = json_decode($res, true);


        return $res_array['translation'][0] ?? $res;
    }

    public static function baidu($query, $to , $from = 'en')
    {
        $appid = '20220916001344583';
        $secret = '5m5No2VbojvHoKx9liB4';
        $salt = mt_rand(1000000, 9999999);

        $sign = md5($appid . $query . $salt . $secret);

        $param = [
            'q' => $query,
            'appid' => $appid,
            'salt' => $salt,
            'from' => $from,
            'to' => $to,
            'sign' => $sign,
        ];

        $res = Http::get('http://api.fanyi.baidu.com/api/trans/vip/translate', $param)->body();
        $res_array = json_decode($res, true);


        return $res_array['trans_result'][0]['dst'] ?? $res;
    }

    public static function test($q, $to)
    {
        return 'test';
    }

    private static function abslength($str)
    {
        if(empty($str)){
            return 0;
        }
        if(function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }
}
