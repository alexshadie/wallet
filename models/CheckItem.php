<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "CheckItem".
 *
 * @property int $id
 * @property int $check_id
 * @property string $name
 * @property string $check_name
 * @property string $price
 * @property string $qty
 * @property string $sum
 * @property string $comment
 *
 * @property Check $check
 */
class CheckItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CheckItem';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'check_id'], 'integer'],
            [['price', 'qty', 'sum'], 'number'],
            [['name', 'comment'], 'string', 'max' => 1024],
            [['check_name'], 'string', 'max' => 255],
            [['id'], 'unique'],
            [['check_id'], 'exist', 'skipOnError' => true, 'targetClass' => Check::className(), 'targetAttribute' => ['check_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'check_id' => 'Check ID',
            'name' => 'Name',
            'check_name' => 'Check Name',
            'price' => 'Price',
            'qty' => 'Qty',
            'sum' => 'Sum',
            'comment' => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCheck()
    {
        return $this->hasOne(Check::className(), ['id' => 'check_id']);
    }

    /**
     * @inheritdoc
     * @return CheckItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CheckItemQuery(get_called_class());
    }
}
