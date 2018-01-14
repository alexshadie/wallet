<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[CheckItem]].
 *
 * @see CheckItem
 */
class CheckItemQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return CheckItem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CheckItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
