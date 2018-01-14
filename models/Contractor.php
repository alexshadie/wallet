<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Contractor".
 *
 * @property int $id
 * @property string $name
 * @property string inn
 * @property string $comment
 *
 * @property Check[] $checks
 */
class Contractor extends \yii\db\ActiveRecord
{

    public static function getByInn($inn) {
        return self::find()->
            where(['inn' => $inn])->
            one();
    }
        /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Contractor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'inn'], 'string', 'max' => 255],
            [['comment'], 'string', 'max' => 1024],
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
            'comment' => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChecks()
    {
        return $this->hasMany(Check::className(), ['contractor_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return ContractorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContractorQuery(get_called_class());
    }
}
