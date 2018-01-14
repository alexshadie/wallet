<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Account;
use app\models\Currency;
use yii\console\Controller;

class AccountController extends Controller
{
    public function actionIndex()
    {
        $accounts = Account::find()->all();
        $rubCurrency = Currency::rubCurrency();

        $sumTotal = 0;
        $sumActive = 0;
        $sumCrypto = 0;
        $sumPassive = 0;
        $sumDebt = 0;

        foreach ($accounts as $account) {
            echo "{$account->id}. {$account->name} ({$account->typeName})\n";
            echo "Валюта: {$account->accountCurrency->name}\n";

            if ($account->currency == 'RUB') {
                echo "Начальный Баланс: {$account->accountCurrency->format($account->initialBalance)}\n";
            } else {
                echo "Начальный Баланс: {$account->accountCurrency->format($account->initialBalance)} ({$rubCurrency->format($account->initialBalanceDefaultCurrency)})\n";
            }

            if ($account->currency == 'RUB') {
                echo "Текущий Баланс: {$account->accountCurrency->format($account->currentBalance)}\n";
            } else {
                echo "Текущий Баланс: {$account->accountCurrency->format($account->currentBalance)}\n";
                echo "Текущий Баланс (руб): {$rubCurrency->format($account->accountCurrency->getRate()->rate * $account->currentBalance)}\n";
            }

            if ($account->type == 'credit') {
                $sumPassive += $account->accountCurrency->getRate()->rate * $account->currentBalance;
            } elseif ($account->type == 'creditcard') {
                $sumPassive += $account->accountCurrency->getRate()->rate * ($account->currentBalance - $account->limit);
            } elseif ($account->type == 'crypto') {
                $sumCrypto += $account->accountCurrency->getRate()->rate * $account->currentBalance;
            } elseif ($account->type == 'debt') {
                $sumDebt += $account->accountCurrency->getRate()->rate * $account->currentBalance;
            } else {
                $sumActive += $account->accountCurrency->getRate()->rate * $account->currentBalance;
            }

            echo "\n";
        }

        echo "Итого:\n";
        echo "Доступно: {$rubCurrency->format($sumActive)}\n";
        echo "Криптовалюты: {$rubCurrency->format($sumCrypto)}\n";
        echo "Кредиты: {$rubCurrency->format($sumPassive)}\n";
        echo "Долги: {$rubCurrency->format($sumDebt)}\n";
        echo "Общий актив: {$rubCurrency->format($sumDebt + $sumCrypto + $sumActive)}\n";
        echo "Общий итог: {$rubCurrency->format($sumDebt + $sumCrypto + $sumActive + $sumPassive)}\n";

    }
}
