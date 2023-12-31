<?php

namespace app\controllers;

use app\components\DefaultController;
use app\models\Packets;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use \RouterOS\Client;
use \RouterOS\Query;
use \app\constants\RadiusAttributes;
use app\models\radius\Radgroupreply;
/**
 * PacketsController implements the CRUD actions for Packets model.
 */
class PacketsController extends DefaultController{
    public function actionIndex(){
        $services = \app\models\Services::find()->all();
        return $this->render('index', [
            'services' => $services,
        ]);
    }
    public function actionServicePackets($id){
        $model = Packets::find()
        ->where(['service_id' => $id])
        ->orderBy(['packet_name' => SORT_ASC])
        ->all();

        $service = \app\models\Services::find()
        ->where(['id' => $id])
        ->one();

        return $this->render('service-packets', [
            'model' => $model,
            'service' => $service,
        ]);
    }

    public function actionDetail($id,$service){
        $model = Packets::find()
        ->where(['id' => $id])
        ->one();

        $usersCount = \app\models\UsersServicesPackets::find()
        ->select('sum( case  when users.status = 1 then 1 else 0 end ) as active,sum( case  when users.status = 2 then 1 else 0 end ) as deactive,sum( case  when users.status = 3 then 1 else 0 end ) as archive,sum( case  when users.status = 0 then 1 else 0 end ) as pending,sum( case  when users.status = 6 then 1 else 0 end ) as black,sum( case  when users.status = 7 then 1 else 0 end ) as free_user')
        ->leftJoin('users','users.id=users_services_packets.user_id')
        ->withByLocation()
        ->andWhere(['packet_id'=>$id])
        ->asArray()
        ->one();

        return $this->renderIsAjax('detail', [
            'model' => $model,
            'usersCount' => $usersCount,
        ]);
    }



    public function actionPacketMonthly($packet_id, $year = null){
        if ($year == null) {
            $year = date("Y");
        }

        $u_b = \app\models\UserBalance::find()->orderBy(['created_at' => SORT_ASC])
            ->leftJoin('users', 'users.id=user_balance.user_id')
            ->andWhere(['!=', 'user_balance.status', '1'])
            ->andWhere(['service_packet_id'=>$packet_id])
            ->withByLocation()
            ->andWhere(['DATE_FORMAT(FROM_UNIXTIME({{%user_balance}}.created_at), "%Y")' => $year])
            ->asArray()
            ->all();


        $month = [];
        foreach ($u_b as $key => $balance) {
            for ($i = 1; $i <= 12; $i++) {
                if ($i == date('n', $balance["created_at"])) {
                    if (isset($month[$i])) {
                        $month[$i]["a"] += round($balance["balance_out"], 1);
                    } else {
                        $month[$i] = ['y' => date('M', $balance["created_at"]), 'a' => round($balance["balance_out"]), 1];
                    }
                }
            }
        }
        $result = [];
        foreach ($month as $key => $value) {
            $result[] = $value;
        }
        return json_encode($result);
    }













    public function actionTransferPacketValidate(){
        $model = new \app\models\Packets();
        $model->scenario = \app\models\Packets::SCENARIO_TRANSFER;
        $request = \Yii::$app->getRequest();
        if ( $request->isPost && $model->load( $request->post() ) ) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
    }

   public function actionTransferPacket($id){
        $model =  \app\models\Packets::findOne($id);
        $model->scenario = \app\models\Packets::SCENARIO_TRANSFER;

        $userCount = \app\models\UsersServicesPackets::find()
        ->select("service_packets.packet_name as packet_name,sum( case  when users.status = 1 then 1 else 0 end ) as active_user_count,sum( case  when users.status = 2 then 1 else 0 end ) as deactive_user_count,sum( case  when users.status = 3 then 1 else 0 end ) as archive_user_count,sum( case  when users.status = 0 then 1 else 0 end ) as pending_user_count,sum( case  when users.status = 7 then 1 else 0 end ) as vip_user_count")
        ->leftJoin('users','users.id=users_services_packets.user_id')
        ->leftJoin('service_packets','service_packets.id=users_services_packets.packet_id')
        ->where(['packet_id'=>$model->id])
        ->asArray()
        ->one();


        $allPackets = \app\models\Packets::find()
        ->where(['service_id'=>$model->service_id])
        ->andWhere(['!=','id',$model->id])
        ->andWhere(['type'=>$model->type ])
        ->asArray()
        ->all();


        if ( Yii::$app->request->isPost ) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $newPacketId =  Yii::$app->request->post('Packets')['transfer_packet'];
            $limit =  intval( Yii::$app->request->post('Packets')['query_count'] );

            $newPacketQuery = \app\models\Packets::find()
            ->where(['id'=>$newPacketId])
            ->asArray()
            ->one();


            $findUserFromPacketes = \app\models\UsersServicesPackets::find()
            ->where(['packet_id'=>$model->id])
            ->groupBy('user_id')
            ->limit($limit)
            ->asArray()
            ->all();

            $usersListArray = [];

            foreach ($findUserFromPacketes as $userKey => $user) {
                $usersListArray[] = $user['user_id'];
            }


            $userServicePacketModel = \app\models\UsersServicesPackets::find()
            ->where(['user_id'=>$usersListArray])
            ->asArray()
            ->all();



            foreach ( $userServicePacketModel as $packetKey => $userPacket ) {

               $userServicePacketUpdateModel = \app\models\UsersServicesPackets::find()
               ->where(['id'=>$userPacket['id']])
               ->one();

               $userServicePacketUpdateModel->packet_id = intval( $newPacketId );
               $userServicePacketUpdateModel->price = 0;

              if (  $userServicePacketUpdateModel->save( false ) ) {
                  $userModel = \app\models\Users::find()
                  ->where(['id'=>$userServicePacketUpdateModel->user_id])
                  ->one();

                  $userModel->tariff = \app\models\UserBalance::CalcUserTariffDaily( $userModel->id )['per_total_tariff'];
                  if ( $userModel->save(false) ) {
                    if ( $userServicePacketUpdateModel->service->service_alias == "internet" ) {
                        $routerModel = \app\models\Routers::find()
                        ->where(['id' => $userModel->district->router_id])
                        ->asArray()
                        ->one();

                        $inetModel = \app\models\UsersInet::find()
                        ->where(['u_s_p_i'=>$userServicePacketUpdateModel->id])
                        ->one();
                        $inetModel->packet_id = intval( $newPacketId );

                        if ( $inetModel->save(false) ) {
                            \app\components\MikrotikQueries::pppSecretSet(
                                $inetModel->login,
                                $newPacketQuery['packet_name'],
                                $routerModel['nas'],
                                $routerModel['username'],
                                $routerModel['password'],
                                "pppSecretSet",
                                [
                                    'login'=>$inetModel->login,
                                    'packet_name'=>$newPacketQuery['packet_name'],
                                    'nas'=>$routerModel['nas'],
                                    'router_username'=>$routerModel['username'],
                                    'router_password'=>$routerModel['password'],
                                ]
                            );
                        }

                    }
                    if ( $userServicePacketUpdateModel->service->service_alias == "tv" ) {
                        $tvModel = \app\models\UsersTv::find()
                        ->where(['u_s_p_i' => $userServicePacketUpdateModel->id ])
                        ->one();
                        $tvModel->packet_id = $newPacketQuery['id'];
                        $tvModel->save(false);
                    } 

                    if ( $userServicePacketUpdateModel->service->service_alias == "wifi" ) {
                        $wifiModel = \app\models\UsersWifi::find()
                        ->where(['u_s_p_i' => $userServicePacketUpdateModel->id ])
                        ->one();
                        $wifiModel->packet_id = $newPacketQuery['id'];
                        $wifiModel->save(false);
                    } 

                  }
              }
           
            }

            $result = \app\models\UsersServicesPackets::find()
            ->select("service_packets.packet_name as packet_name,sum( case  when users.status = 1 then 1 else 0 end ) as active_user_count,sum( case  when users.status = 2 then 1 else 0 end ) as deactive_user_count,sum( case  when users.status = 3 then 1 else 0 end ) as archive_user_count,sum( case  when users.status = 0 then 1 else 0 end ) as pending_user_count,sum( case  when users.status = 7 then 1 else 0 end ) as vip_user_count")
            ->leftJoin('users','users.id=users_services_packets.user_id')
            ->leftJoin('service_packets','service_packets.id=users_services_packets.packet_id')
            ->where(['packet_id'=>$id])
            ->asArray()
            ->one();

        
            if ( $result['active_user_count'] != null && $result['deactive_user_count'] && $result['archive_user_count'] && $result['vip_user_count']  && $result['pending_user_count']  ) {
                $active_user_count = $result['active_user_count'];
                $deactive_user_count = $result['deactive_user_count'];
                $archive_user_count = $result['archive_user_count'];
                $vip_user_count = $result['vip_user_count'];
                $pending_user_count = $result['pending_user_count'];

                
            }else{
                $active_user_count = 0;
                $deactive_user_count = 0;
                $archive_user_count = 0;
                $vip_user_count = 0;
                $pending_user_count = 0;
            }


            $logMessage = "{$userCount['packet_name']} packet active : {$userCount['active_user_count']},deactive : {$userCount['deactive_user_count']} ,archive : {$userCount['archive_user_count']} ,vip : {$userCount['vip_user_count']} ,pending : {$userCount['pending_user_count']}   WAS UPDATED TO   packet active : {$active_user_count},deactive : {$deactive_user_count} ,archive : {$archive_user_count} ,vip : {$vip_user_count} ,pending : {$pending_user_count}     ";
            \app\models\Logs::writeLog(
                Yii::$app->user->username, 
                null, 
                $logMessage, 
                time()
            );

            return [
                'status'=>'success',
                'message'=>Yii::t(
                    'app',
                    'Successfully,{packet_name} packet have {active_user_count} active, {deactive_user_count} deactive, {archive_user_count} archive, {vip_user_count} vip, and {pending_user_count} pending users.',
                    [
                        'packet_name' =>$userCount['packet_name'],
                        'active_user_count' => $active_user_count,
                        'deactive_user_count' => $deactive_user_count,
                        'archive_user_count' => $archive_user_count,
                        'vip_user_count' => $vip_user_count,
                        'pending_user_count' => $pending_user_count,
                    ]
                )
            ];
        
        }
        return $this->renderIsAjax('transfer-packet', [
            'model' => $model,
            'userCount' => $userCount,
            'allPackets' => $allPackets
        ]);

    }





    public function actionSortPacketList(){
        // \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = Yii::$app->request->post('data');
        $service_id = Yii::$app->request->post('p_c');
        foreach ($data as $key => $id) {
            $model = Packets::find()->where(['id' => $id])->andWhere(['service_id' => $service_id])->one();
            if ($model != null) {
                $model->position = $key;
                $model->save(false);
            }
        }
    }

    public function actionCreate(){
        $model = new Packets();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->service->service_alias == "internet") {
                Radgroupreply::addPacket($model->packet_name, RadiusAttributes::CISCO_AVPAIR, "ip:sub-qos-policy-in={$model->download}");
                Radgroupreply::addPacket($model->packet_name, RadiusAttributes::CISCO_AVPAIR, "ip:sub-qos-policy-out={$model->upload}"); 

                Radgroupreply::addPacket($model->packet_name, RadiusAttributes::MIKROTIK_RATE_LIMIT, "{$model->download}M/{$model->upload}M"); 


            }
            return $this->redirect(['index']);
        }
        return $this->renderIsAjax('_form', [
            'model' => $model,
        ]);
    }
    
    public function actionUpdate($id){
        $packetModel = \app\models\Packets::find()
        ->where(['id'=>$id])
        ->asArray()
        ->one();

        
        $model =  Packets::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ( $model->service->service_alias == "internet" ) {
                $routerModel = \app\models\Routers::find()
                ->asArray()
                ->all();

                foreach ($routerModel as $rK => $router) {
                 
                        Radgroupreply::updatePacket($model['packet_name'],RadiusAttributes::CISCO_AVPAIR, $model['download'],$model['upload']);
                    
                }

            }


            $userServicePacketModel = \app\models\UsersServicesPackets::find()
            ->where(['service_id'=>$model->service_id,'packet_id'=>$model->id])
            ->asArray()
            ->all();
            foreach ($userServicePacketModel as $key => $userPacket) {
                $userModel = \app\models\Users::find()
                ->where(['id'=>$userPacket['user_id']])
                ->one();
                $userModel->tariff = \app\models\UserBalance::CalcUserTariffDaily( $userPacket['user_id'] )['per_total_tariff'];
                $userModel->save(false);
            }
            return $this->redirect(['service-packets','id'=>$model->service_id]);
            
        }
        return $this->renderIsAjax('_form', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id){
        $model = $this->findModel($id);

        if( $model != null ){
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                Radgroupreply::deletePacket($model->packet_name);
                $model->delete();
                return [
                    'code' => 'success',
                    'message'=>Yii::t('app','Packet has been deleted')
                ];
            }
        }
    }


    public function actionValidateCreatePacket(){
          $model = new \app\models\Packets;
         if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
              Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
              return \yii\widgets\ActiveForm::validate($model);
           }
    }



    protected function findModel($id){
        if (($model = Packets::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
