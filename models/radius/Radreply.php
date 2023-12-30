<?php

namespace app\models\radius;

use Yii;

/**
 * This is the model class for table "radreply".
 *
 * @property int $id
 * @property string $username
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class Radreply extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radreply';
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


   public static function addStaticIP( $username, $ipAddress )
    {
        $radreply = new Radreply();
        $radreply->username = $username;
        $radreply->attribute = 'Framed-IP-Address';
        $radreply->op = '=';
        $radreply->value = $ipAddress;

        if ($radreply->save()) {
            return true;
        } else {
            return false;
        }
    }



    public static function updateStaticIp( $username, $newIpAddress )
    {
        $radreply = Radreply::findOne(['username' => $username, 'attribute' => 'Framed-IP-Address']);
        
        if ($radreply !== null) {
            $radreply->value = $newIpAddress;
            if ($radreply->save()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public static function deleteStaticIp( $username,$staticIp )
    {
        $radreply = Radreply::find()->where(['value'=>$staticIp])->andWhere(['username'=>$username])->one();        
        if ($radreply !== null) {
            if ($radreply->delete()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
}
