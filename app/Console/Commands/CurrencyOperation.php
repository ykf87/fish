<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CurrencyOperation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:operation {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '货币相关转化操作，如：生成货币符号、生成货币汇率缓存';

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
        $type = $this->argument('type');
        switch ($type) {
            case 'makeSymbol':
                $this->makeSymbol();
                break;

            case 'syncExchangeRate':
                $this->syncExchangeRate();
                break;

            default :
                $this->info("暂无此操作");
                break;
        }
    }

    public function syncExchangeRate()
    {
        $currency = config('currency');

        $data = [];

        $bar = $this->output->createProgressBar(count($currency));
        $bar->start();

        foreach ($currency as $k => $v) {
            $res = file_get_contents(sprintf("https://api.jijinhao.com/plus/convert.htm?from_tkc=%s&to_tkc=USD&amount=1", $k));
            preg_match("/var result = '(.*)'/", $res, $match);
            if (isset($match[1]) && is_numeric($match[1])) {
                $data[$k] = $match[1];
            }
            $bar->advance();
            usleep(500);
        }
        $bar->finish();

        if (!empty($data)) {
            Cache::store('file')->put('exchange_rate', json_encode($data));
            $this->info(PHP_EOL . '汇率缓存已更新');
        }

    }

    public function makeSymbol()
    {
        $res = DB::connection('ucenter')->table('currencies')->get()->pluck('symbol', 'code')->toArray();
        foreach ($res as $k => $v) {
            echo sprintf("'%s' => '%s',%s", $k, $v, PHP_EOL);
        }
    }
}
