<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\integration\GDrive;
use app\models\Account;
use app\models\Check;
use app\models\CheckItem;
use app\models\Contractor;
use app\models\Currency;
use yii\console\Controller;

class CheckController extends Controller
{
    public function actionIndex() {
        $checks = Check::find()
            ->orderby('date ASC')
            ->all();

        $rubCurrency = Currency::rubCurrency();

        foreach ($checks as $check) {
            echo "{$check->date} {$check->contractor->name}\n";

            foreach ($check->checkItems as $item) {
                $formattedQty = rtrim(sprintf("%.3f", $item->qty), '0.');

                echo "\t$item->name ($item->check_name)\t{$rubCurrency->format($item->price)} x {$formattedQty} = {$rubCurrency->format($item->sum)}\n";
            }

            echo "\n\n======================================================\n\n";
        }
    }

    public function actionImport()
    {
        $client = new GDrive();

        $allChecks = $client->lsFiles();
        $dbc = Contractor::getDb();

        foreach ($allChecks as $checkFile) {
            $checkData = $client->getFile($checkFile);
            $content = \GuzzleHttp\json_decode($checkData);

            $ok = true;

            if (!is_array($content)) {
                $content = [$content];
            }

            $trx = $dbc->beginTransaction();

            try {
                foreach ($content as $data) {
                    $key = "{$data->userInn}-{$data->dateTime}-{$data->fiscalDocumentNumber}" .
                        "-{$data->fiscalDriveNumber}-{$data->fiscalSign}-{$data->totalSum}";

                    if (Check::find()->where(['external_id' => $key])->one()) {
                        echo "Check alreary exists - $key\n";
                        continue;
                    }

                    $contractor = Contractor::getByInn(trim($data->userInn));
                    if (!$contractor) {
                        $contractor = new Contractor();
                        $contractor->name = trim($data->user);
                        $contractor->inn = trim($data->userInn);
                        if (!$contractor->save()) {
                            throw new \Exception("Couldn't save ctor");
                        }
                    }

                    $check = new Check();
                    $check->contractor_id = $contractor->id;
                    $check->currency_code = Currency::rubCurrency()->code;
                    $check->date = date('Y-m-d H:i:s', $data->dateTime);
                    $check->sum = $data->totalSum / 100;
                    $check->external_id = $key;
                    $check->raw_data = \GuzzleHttp\json_encode($data);
                    if (!$check->save()) {
                        throw new \Exception("Couldn't save check");
                    }

                    foreach ($data->items as $item) {
                        $checkItem = new CheckItem();
                        $checkItem->check_id = $check->id;
                        $checkItem->name = "";
                        $checkItem->check_name = $item->name;
                        $checkItem->comment = "";
                        $checkItem->price = $item->price / 100;
                        $checkItem->qty = $item->quantity;
                        $checkItem->sum = $item->sum / 100;
                        if (!$checkItem->save()) {
                            throw new \Exception("Couldn't save");
                        }
                    }

                    echo "Imported check $key\n";
                }

                $trx->commit();
            } catch (\Exception $e) {
                echo "Failed processing {$checkFile} - {$e->getMessage()}";
                $trx->rollBack();
                $ok = false;
            }

            if ($ok) {
                $client->backupFile($checkFile);
//                $client->rmFile($checkFile);
            }
        }
    }
}
