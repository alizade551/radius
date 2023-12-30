<?php

namespace app\models\radius;


use Yii;

/**
 * This is the model class for table "radpostauth".
 *
 * @property int $id
 * @property string $username
 * @property string $pass
 * @property string $reply
 * @property string $authdate
 */
class Radpostauth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radpostauth';
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['authdate'], 'safe'],
            [['username', 'pass'], 'string', 'max' => 64],
            [['reply'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'pass' => Yii::t('app', 'Pass'),
            'reply' => Yii::t('app', 'Reply'),
            'authdate' => Yii::t('app', 'Authdate'),
        ];
    }
}
