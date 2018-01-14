<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Check".
 *
 * @property int $id
 * @property int $contractor_id
 * @property string $currency_code
 * @property string $date
 * @property string $sum
 * @property string $comment
 * @property string $external_id
 * @property string $raw_data
 *
 * @property Contractor $contractor
 * @property Currency $currencyCode
 * @property CheckItem[] $checkItems
 */
class Check extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Check';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contractor_id'], 'integer'],
            [['date'], 'safe'],
            [['sum'], 'number'],
            [['raw_data'], 'string'],
            [['currency_code'], 'string', 'max' => 5],
            [['comment'], 'string', 'max' => 1024],
            [['external_id'], 'string', 'max' => 255],
            [['contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['contractor_id' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_code' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contractor_id' => 'Contractor ID',
            'currency_code' => 'Currency Code',
            'date' => 'Date',
            'sum' => 'Sum',
            'comment' => 'Comment',
            'external_id' => 'External ID',
            'raw_data' => 'Raw Data',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['id' => 'contractor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyCode()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCheckItems()
    {
        return $this->hasMany(CheckItem::className(), ['check_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return CheckQuery the active query used by this AR class.
     */
    public static function find()
    {
        return (new CheckQuery(get_called_class()))
            ->where("date >= '2018-01-12 00:00:00'");
    }
}
