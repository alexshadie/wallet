<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Currency;
use app\models\CurrencyRate;
use yii\console\Controller;

class CurrencyController extends Controller
{
    public function actionIndex()
    {
        $currencies = Currency::find()->all();

        echo "Code\tName\n";
        foreach ($currencies as $currency) {
            echo "{$currency->code}\t{$currency->name}\t{$currency->getRate()->rate}\n";
        }
    }

    public function actionRates() {
        $currencies = Currency::find()->all();

        $rate = null;
        $usd_rate = null;

        foreach ($currencies as $currency) {
            if (!in_array($currency->code, ["BCH", "ETH"])) {
                $pair = "{$currency->code}_RUB";
                $url = "https://free.currencyconverterapi.com/api/v5/convert?q={$pair}&compact=y";
                $contents = file_get_contents($url);
                $data = json_decode($contents, 1);
                $rate = $data[$pair]["val"];

                if ($currency->code == "USD") {
                    $usd_rate = $rate;
                }

                echo $currency->code;
                echo " " . $rate . "\n";

                $currencyRate = new CurrencyRate();
                $currencyRate->code = $currency->code;
                $currencyRate->date = date('Y-m-d H:i:s');
                $currencyRate->rate = floatval($rate);
                $currencyRate->save();
            }
        }

        foreach ($currencies as $currency) {
            if (in_array($currency->code, ["BCH", "ETH"])) {
                $pair = "{$currency->code}_USD";
                $url = "https://www.okcoin.com/api/v1/ticker.do?symbol={$pair}";
                $contents = file_get_contents($url);
                $data = json_decode($contents, 1);
                $rate = $usd_rate * ($data["ticker"]["buy"] + $data["ticker"]["sell"]) / 2;

                echo $currency->code;
                echo " " . $rate . "\n";

                $currencyRate = new CurrencyRate();
                $currencyRate->code = $currency->code;
                $currencyRate->date = date('Y-m-d H:i:s');
                $currencyRate->rate = floatval($rate);
                $currencyRate->save();
            }
        }



    }
}
