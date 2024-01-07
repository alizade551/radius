<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users_paid_day_history".
 *
 * @property int $id
 * @property int $user_id
 * @property int $paid_day
 * @property int $created_at
 */
class UsersPaidDayHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_paid_day_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'paid_day', 'created_at'], 'required'],
            [['user_id', 'paid_day', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'paid_day' => Yii::t('app', 'Paid Day'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
