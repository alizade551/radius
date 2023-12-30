<?php

namespace app\models;

use Yii;
use app\components\RouterosApi;
/**
 * This is the model class for table "users_inet".
 *
 * @property int $id
 * @property int $user_id
 * @property int $packet_id
 * @property string $login
 * @property string $password
 * @property string $static_ip
 * @property string $router_id
 * @property int $status
 * @property string $created_at
 *
 * @property Users $user
 */
class UsersInet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_inet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'packet_id', 'login', 'password'], 'required'],
            [['user_id', 'packet_id', 'status','u_s_p_i','nas_id'], 'integer'],
            [['login', 'password', 'static_ip', 'created_at','mac_address'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app','Customer'),
            'nas_id' => Yii::t('app','Router'),
            'packet_id' => Yii::t('app','Packet'),
            'mac_address' => Yii::t('app','Mac address'),
            'login' => Yii::t('app','Inet login'),
            'password' =>Yii::t('app','Inet password'),
            'static_ip' => Yii::t('app','Static Ip'),
            'nas' => Yii::t('app','NAS'),
            'status' => Yii::t('app','Status'),
            'created_at' => Yii::t('app','Created at'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    public function getRouter()
    {
        return $this->hasOne(\app\models\radius\Nas::className(), ['id' => 'nas_id']);
    }


    public function getPacket(){
        return $this->hasOne(Packets::className(), ['id' => 'packet_id']);
    }   


}




