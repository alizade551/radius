<?php

namespace app\models\radius;


use Yii;
use \app\constants\RadiusAttributes;

/**
 * This is the model class for table "radgroupreply".
 *
 * @property int $id
 * @property string $groupname
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class Radgroupreply extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radgroupreply';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['groupname', 'attribute','op','value'], 'required'],
            [['groupname', 'attribute'], 'string', 'max' => 64],
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
            'groupname' => Yii::t('app', 'Groupname'),
            'attribute' => Yii::t('app', 'Attribute'),
            'op' => Yii::t('app', 'Op'),
            'value' => Yii::t('app', 'Value'),
        ];
    }


    // Radgroupreply::addPacket('HOME_100mb', 'Cisco-AVPair', 'ip:sub-qos-policy-in=100M');
    // Radgroupreply::addPacket('HOME_100mb', 'Cisco-AVPair', 'ip:sub-qos-policy-out=100M');
    public static function addPacket($groupName, $attribute, $value)
    {
        $model = new Radgroupreply();
        $model->groupname = $groupName;
        $model->attribute = $attribute;
        $model->op = ':=';
        $model->value = $value;
        return $model->save();
    }


    public static function deletePacket($groupName)
    {
        $model = self::deleteAll(['groupname' => $groupName]);
    }


    public static function updatePacket( $groupName, $newGroupname )
    {
        $model = Radgroupreply::findOne(['groupname' => $groupName]);
        $model->groupname = $newGroupname;
        $model->save(false);
    }


    public static function block( $username )
    {
        $attributesToCheck = [
            'ip:sub-qos-policy-out=reject',
            'ip:sub-qos-policy-in=reject'
        ];

        $groupname = 'BLOCK';
        
        foreach ($attributesToCheck as $attribute) {
            $existingEntry = Radgroupreply::find()
                ->where(['groupname' => $groupname])
                ->andWhere(['attribute' => 'Cisco-AVPair'])
                ->andWhere(['op' => ':='])
                ->andWhere(['value' => $attribute])
                ->one();

            if ($existingEntry === null) {
                Radgroupreply::addPacket($groupname, RadiusAttributes::CISCO_AVPAIR, $attribute);
            }
        }

        \app\models\radius\Radusergroup::changeRadUserGroup( $username, $groupname );
    }


     public static function unBlock( $username, $groupname )
    {
        \app\models\radius\Radusergroup::changeRadUserGroup( $username, $groupname );
        
    }



}
