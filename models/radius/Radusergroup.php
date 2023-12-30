<?php

namespace app\models\radius;


use Yii;

/**
 * This is the model class for table "radusergroup".
 *
 * @property int $id
 * @property string $username
 * @property string $groupname
 * @property int $priority
 */
class Radusergroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radusergroup';
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['priority'], 'integer'],
            [['username', 'groupname'], 'string', 'max' => 64],
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
            'groupname' => Yii::t('app', 'Groupname'),
            'priority' => Yii::t('app', 'Priority'),
        ];
    }

      public static function createRadUserGroup($username, $groupname, $priority = 1)
        {
            $radUserGroup = new \app\models\radius\Radusergroup;
            $radUserGroup->username = $username;
            $radUserGroup->groupname = $groupname;
            $radUserGroup->priority = $priority;
            $radUserGroup->save(false);
        }


      public static function changeRadUserGroup( $username , $newGroupName )
        {
            $radUserGroup = \app\models\radius\Radusergroup::find()->where(['username'=>$username])->one();
            $radUserGroup->groupname = $newGroupName;
            $radUserGroup->save(false);
        }



      public static function deleteRadUserGroup($username)
        {
            Radusergroup::deleteAll(['username' =>$username]);

        }




}
