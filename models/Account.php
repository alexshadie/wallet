<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Account".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property string $currency
 * @property string $initialBalance
 * @property string $initialBalanceDefaultCurrency
 * @property string $percent
 * @property string $limit
 *
 * @property string $typeName
 * @property string $currentBalance
 *
 * @property Currency $accountCurrency
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['initialBalance', 'initialBalanceDefaultCurrency', 'percent', 'limit'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1024],
            [['type'], 'string', 'max' => 10],
            [['currency'], 'string', 'max' => 5],
            [['currency'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'type' => 'Type',
            'currency' => 'Currency',
            'initialBalance' => 'Initial Balance',
            'initialBalanceDefaultCurrency' => 'Initial Balance Default Currency',
            'percent' => 'Percent',
            'limit' => 'Limit',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency']);
    }

    public function getCurrentBalance() {
        $sql = "select account_id, sum(amount) as spend
                from (
                    (
                        select source_account_id as account_id, -1 * source_account_sum as amount 
                        from Transfer
                        where source_account_id is not null
                    )
                    UNION ALL 
                    (
                        select destination_account_id as account_id, destination_account_sum as amount 
                        from Transfer    
                        where destination_account_id is not null
                    )
                ) t
                group by account_id";

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sql);
        $spends =[];
        foreach ($command->queryAll() as $row) {
            $spends[$row['account_id']] = floatval($row['spend']);
        }
        return floatval($this->initialBalance) + (isset($spends[$this->id]) ? $spends[$this->id] : 0);

    }

    public function getTypeName()
    {
        switch ($this->type) {
            case "cash": return "Наличные";
            case "card": return "Карта";
            case "account": return "Счет";
            case "credit": return "Кредит";
            case "creditcard": return "Кредитная карта";
            case "deposit": return "Вклад";
            case "debt": return "Долг";
            case "crypto": return "Криптовалюта";
        }
        return "Unknown";
    }

    /**
     * @inheritdoc
     * @return AccountQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AccountQuery(get_called_class());
    }
}
