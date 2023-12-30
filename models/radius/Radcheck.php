<?php

namespace app\models\radius;


use Yii;

/**
 * This is the model class for table "radcheck".
 *
 * @property int $id
 * @property string $username
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class Radcheck extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radcheck';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'attribute'], 'string', 'max' => 64],
            [['op'], 'string', 'max' => 2],
            [['value'], 'string', 'max' => 253],
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
            'attribute' => Yii::t('app', 'Attribute'),
            'op' => Yii::t('app', 'Op'),
            'value' => Yii::t('app', 'Value'),
        ];
    }


    public static function addUser($username, $attribute, $op, $value)
    {
 
        $radcheck = new Radcheck();
        $radcheck->username = $username;
        $radcheck->attribute = $attribute;
        $radcheck->op = $op;
        $radcheck->value = $value;
        return $radcheck->save(false);
    }



    public static function authType( $username,$value )
    {
        $model = Radcheck::find()
            ->where(['username'=>$username])
            ->andWhere(['attribute' => 'Auth-Type'])
            ->one();

        if ( $model != null ) {
 
            $model->value = $value;
            $model->save(false);
        }else{
            $newRecord = new Radcheck();
            $newRecord->attribute = "Auth-Type";
            $newRecord->username = $username;
            $newRecord->op = ":=";
            $newRecord->value = $value;
            $newRecord->save();
        }

    }


    public static function deleteUser($username)
    {
        Radcheck::deleteAll(['username' =>$username]);

    }
}
