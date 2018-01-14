<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "CurrencyRate".
 *
 * @property string $code
 * @property string $date
 * @property string $rate
 *
 * @property Currencies $code0
 */
class CurrencyRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CurrencyRate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'date'], 'required'],
            [['date'], 'safe'],
            [['rate'], 'number'],
            [['code'], 'string', 'max' => 5],
            [['code', 'date'], 'unique', 'targetAttribute' => ['code', 'date']],
            [['code'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['code' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'date' => 'Date',
            'rate' => 'Rate',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'code']);
    }
}
