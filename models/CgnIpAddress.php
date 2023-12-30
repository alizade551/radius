<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cgn_ip_address".
 *
 * @property int $id
 * @property int $ip_address_id
 * @property int $internal_ip
 * @property string $port_range
 */
class CgnIpAddress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cgn_ip_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip_address_id', 'port_range'], 'required'],
            [['ip_address_id', 'internal_ip'], 'integer'],
            [['port_range','internal_ip'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ip_address_id' => Yii::t('app', 'Ip address'),
            'internal_ip' => Yii::t('app', 'Internal ip'),
            'port_range' => Yii::t('app', 'Port range'),
        ];
    }


    public static function staticIpAlert( $routerId ){

        $staticIpAddressModel = \app\models\IpAdresses::find()
        ->select('count(ip_adresses.id) as static_count,routers.name as router_name')
        ->leftJoin('routers','routers.id=ip_adresses.router_id')
        ->where(['router_id'=>$routerId])
        ->andWhere(['routers.type'=>'1'])
        ->asArray()
        ->one();


        $usersPacketsOnRouterCount = \app\models\UsersInet::find()
        ->where(['router_id'=>$routerId])
        ->andWhere(['!=','status','3'])
        ->andWhere("static_ip IS NOT NULL AND TRIM(static_ip) <> ''")
        ->asArray()
        ->count();

        $diffrence = $staticIpAddressModel['static_count'] - $usersPacketsOnRouterCount;

        if ( $diffrence >  0 ) {
            $result = [
                'status'=>true,
                'capacity'=>$usersPacketsOnRouterCount."/".$staticIpAddressModel['static_count'],
            ];
        }else{
            $result = [
                'status'=>false,
                'capacity'=>$usersPacketsOnRouterCount."/".$staticIpAddressModel['static_count'],
                'message'=>Yii::t('app','Please add {static_ip_count} ip to {router_name}',[ 'static_ip_count'=> abs( $diffrence ),'router_name'=> $staticIpAddressModel['router_name']]),
            ];

        }

        return $result;
    }


    public static function ipAlert( int $routerId, string $routerName ){

        $cgnIpAddressModel = \app\models\CgnIpAddress::find()
        ->select('count(cgn_ip_address.id) as ip_count')
        ->leftJoin('ip_adresses','ip_adresses.id=cgn_ip_address.ip_address_id')
        ->leftJoin('routers','routers.id=ip_adresses.router_id')
        ->where(['router_id'=>$routerId])
        ->where(['parent'=>0])
        ->asArray()
        ->one();


        $usersPacketsOnRouterCount = \app\models\UsersInet::find()
        ->where(['router_id'=>$routerId])
        ->andWhere(['!=','status','3'])
        ->andWhere([
            'or',
            ['is', 'static_ip', null],
            ['=', 'static_ip', '']
        ])
        ->asArray()
        ->count();

        $diffrence = $cgnIpAddressModel['ip_count'] - $usersPacketsOnRouterCount;

        if ( $diffrence >  0 ) {
            $result = [
                'status'=>true,
                'capacity'=>$usersPacketsOnRouterCount."/".$cgnIpAddressModel['ip_count'],
            ];
        }elseif( $diffrence ==  0 ){
            $result = [
                'status'=>false,
                'capacity'=>$usersPacketsOnRouterCount."/".$cgnIpAddressModel['ip_count'],
                'message'=>Yii::t('app','Please add dynamic ip to {router_name} router',[ 'ip_count'=> abs($diffrence) ,'router_name'=> $routerName ]),
            ];
        }else{
            $result = [
                'status'=>false,
                'capacity'=>$usersPacketsOnRouterCount."/".$cgnIpAddressModel['ip_count'],
                'message'=>Yii::t('app','Please add {ip_count} dynamic ip to {router_name} router',[ 'ip_count'=> abs($diffrence) ,'router_name'=> $routerName ]),
            ];

        }

        return $result;
    }







}
