<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Check]].
 *
 * @see Check
 */
class CheckQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Check[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Check|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
