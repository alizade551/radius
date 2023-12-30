<?php

namespace app\models\radius;

use Yii;

/**
 * This is the model class for table "nas".
 *
 * @property int $id
 * @property string $nasname
 * @property string $shortname
 * @property string $type
 * @property int $ports
 * @property string $secret
 * @property string $server
 * @property string $community
 * @property string $description
 */
class Nas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nas';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nasname','vendor_name','shortname','ports','secret','server','city_id','district_id','location_id','cordinate'], 'required'],
            [['ports'], 'integer'],
            [['nasname'], 'string', 'max' => 128],
            [['shortname'], 'string', 'max' => 32],
            [['type'], 'string', 'max' => 30],
            [['secret'], 'string', 'max' => 60],
            [['server'], 'string', 'max' => 64],
            [['community'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 200],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'nasname' => Yii::t('app', 'Nasname'),
            'vendor_name' => Yii::t('app','Vendor name'),
            'shortname' => Yii::t('app', 'Shortname'),
            'type' => Yii::t('app', 'Type'),
            'ports' => Yii::t('app', 'Ports'),
            'secret' => Yii::t('app', 'Secret'),
            'server' => Yii::t('app', 'Server'),
            'community' => Yii::t('app', 'Community'),
            'description' => Yii::t('app', 'Description'),
            'city_id' =>Yii::t('app','City'),
            'district_id' =>Yii::t('app','District'),
            'location_id' =>Yii::t('app','Location'),
            'cordinate' =>Yii::t('app','Cordinate')
        ];
    }
}
