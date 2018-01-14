<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "Currency".
 *
 * @property string $code
 * @property string $name
 * @property string $format
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * @return Currency
     */
    public static function rubCurrency() {
        return self::find()
            ->where(['code' => 'RUB'])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Currency';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string', 'max' => 5],
            [['name', 'format'], 'string', 'max' => 45],
            [['code'], 'unique'],
        ];
    }

    public function getRate() {
        return CurrencyRate::find()
            ->where(['code' => $this->code])
            ->orderBy('date DESC')
            ->one();
    }

    public function format(float $value) : string {
        return sprintf($this->format, $value);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'name' => 'Name',
            'format' => 'Format',
        ];
    }
}
