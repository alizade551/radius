<?php

namespace app\controllers;

use app\components\DefaultController;
use app\models\Logs;
use app\models\UserBalance;
use app\models\search\UserBalanceSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use yii\httpclient\Client;
/**
 * UserBalanceController implements the CRUD actions for UserBalance model.
 */
class UserBalanceController extends DefaultController
{
    public $modelClass = 'app\models\UserBalance';

    public function actionIndex()
    {
        $searchModel = new UserBalanceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserBalance model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new UserBalance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        die;
    }

    public function actionTransferAmountValidate(){
        $model = new \app\models\UserBalance();
        $model->scenario = \app\models\UserBalance::SCENARIO_TRANSFER_AMOUNT;

        $request = \Yii::$app->getRequest();
        if ($request->isPost && $model->load($request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
    }

    public function actionTransferAmount( $id ){   

        $siteConfig = \app\models\SiteConfig::find()
        ->asArray()
        ->one();

        // transferredCustomerModel
        $model = \app\models\UserBalance::find()->where(['id'=>$id])->one();

        $transferredCustomerFullname = $model->user->fullname;
        $transferredCustomerBalanceIn = $model->balance_in;
        $transferredCustomerBalance = $model->user->balance;
        $transferredCustomerTariff = $model->user->tariff;
        $transferredCustomerId = $model->user_id;
        $transferedRecipetId = $model->receipt_id;
        $transferedCustomerPaidDay = $model->user->paid_day;
        $transferedCustomerPaidType = $model->user->paid_time_type;
        $transferedCustomerUpdatedAt = $model->user->updated_at;
        $transferedCustomerStatus = $model->user->status;


        $model->scenario = \app\models\UserBalance::SCENARIO_TRANSFER_AMOUNT;

 
        if ( $model->load( Yii::$app->request->post() ) && $model->validate() ) {

            $toBetransferedCustomerModel = \app\models\Users::find()
            ->where(['contract_number'=>Yii::$app->request->post('UserBalance')['contract_number']])
            ->one();

            if ( $toBetransferedCustomerModel == null ) {
                return [
                    'status'=>'error',
                    'message'=>Yii::t("app","Contract number is invalid")
                ];
            }

            $transactionBalanceIn = \app\models\UserBalance::find()
            ->where(['receipt_id'=>$transferedRecipetId])
            ->andWhere(['!=', 'balance_in', 0])
            ->andWhere(['!=', 'status', '1'])
            ->one();

            $transactionBalanceIn->user_id = $toBetransferedCustomerModel->id;
            $toBetransferedCustomerModel->save( false );

            if ( $transactionBalanceIn->save( false ) ) {
                $transactionsBalanceOut = \app\models\UserBalance::find()
                    ->where(['receipt_id'=>$transferedRecipetId])
                    ->andWhere(['!=', 'balance_out', 0])
                    ->all();

                    $monthCountLaterTimestamp = null;

                    if ( $transactionsBalanceOut != null ) {

                        // Start -  calculating services total for one month
                        $transferedCustomerSevices = \app\models\UsersServicesPackets::find()
                        ->where(['user_id'=>$transferredCustomerId])
                        ->asArray()
                        ->all();

                        $transferedCustomerCreditModel = \app\models\ItemUsage::find()
                        ->where(['user_id'=>$transferredCustomerId])
                        ->andWhere(['status'=>6])
                        ->andWhere(['credit'=>1])
                        ->asArray()
                        ->all();

                        $oneMonthServiceTotal = 0;
                        foreach ( $transferedCustomerSevices as $transferedCustomerKey => $transferedCustomerService ) {
                            $oneMonthServiceTotal++;
                        }

                        $oneMonthCreditTotal = 0;
                        foreach ( $transferedCustomerCreditModel as $transferedCustomerCreditKey => $transferedCustomerCredit ) {
                            $oneMonthCreditTotal++;
                        }

                        $totalOneMonthCount = $oneMonthCreditTotal + $oneMonthServiceTotal;
                        // END - calculating services total for one month

                       $payForCounts = [
                            'pay_for_credit' => 0,
                            'pay_for_inet' => 0,
                            'pay_for_tv' => 0,
                            'pay_for_wifi' => 0,
                            'pay_for_voip' => 0,
                        ];

                        $totalBalanceOut = 0;
                        foreach ( $transactionsBalanceOut as $transactionBalanceOutKey => $transactionBalanceOut ) {
                            $payForValue = $transactionBalanceOut['pay_for'];

                            if ( $payForValue == 0 ) {
                                $payForCounts['pay_for_inet']++;
                            }elseif ( $payForValue == 1 ) {
                                $payForCounts['pay_for_tv']++;
                            }elseif ( $payForValue == 2 ) {
                                $payForCounts['pay_for_wifi']++;
                            }elseif ( $payForValue == 4 ) {
                                $payForCounts['pay_for_voip']++;
                            }elseif ( $payForValue == 3 ) {
                                $payForCounts['pay_for_credit']++;
                            }

                            $totalBalanceOut += $transactionBalanceOut->balance_out;
                            $transactionBalanceOut->status = "1";
                            $transactionBalanceOut->transaction = "transfer-amount";
                            $transactionBalanceOut->save( false );
                        }


                        if ( $payForCounts['pay_for_credit'] > 0 ) {
                            $creditHistoryModel = \app\models\UsersCredit::find()
                            ->select('users_credit.*,item_usage.status as item_status')
                            ->leftJoin('item_usage','item_usage.id=users_credit.item_usage_id')
                            ->where(['user_id'=>$transferredCustomerId])
                            ->andWhere(['item_usage.status'=>6])
                            ->andWhere(['item_usage.credit'=>1])
                            ->orderBy(['id'=>SORT_DESC])
                            ->limit($payForCounts['pay_for_credit'])
                            ->all();

                            $c = 0;
                            foreach ($creditHistoryModel as $creditItemHistoryKey => $creditItemHistory) {
                                if ( $c >= $payForCounts['pay_for_credit'] ) {
                                    $creditHistory = \app\models\UsersCredit::find()->where(['id'=>$creditItemHistory['id']])->one();
                                    $creditHistory->delete();
                                }
                               $c++;
                            }
                        }

                        $monthCount = ( $payForCounts['pay_for_credit'] + $payForCounts['pay_for_inet'] + $payForCounts['pay_for_tv'] + $payForCounts['pay_for_wifi'] + $payForCounts['pay_for_voip'] ) / $totalOneMonthCount;

                       $paidDay = $transferedCustomerPaidDay;
                       if ( $transferedCustomerPaidType == "1" && $siteConfig['paid_day_refresh'] == "1" ) {
                           $userPaidDayHistory = \app\models\UsersPaidDayHistory::find()->where(['user_id'=>$transferredCustomerId])
                           ->orderBy(['id'=>SORT_DESC])
                           ->one();
                           $userPaidDayHistory->delete();

                           $userPaidDayHistory = \app\models\UsersPaidDayHistory::find()->where(['user_id'=>$transferredCustomerId])
                           ->orderBy(['id'=>SORT_DESC])
                           ->one();

                           if (  $userPaidDayHistory != null ) {
                               $paidDay = $userPaidDayHistory['paid_day'];
                           }

                        }

                        $monthCountLaterTimestamp = \app\components\Utils::calculateNextPaymentTimestamp( 
                           -$monthCount,
                            $transferedCustomerStatus, 
                            $transferedCustomerPaidType,
                            $transferedCustomerPaidDay,
                            $transferedCustomerUpdatedAt
                        );
                    }
                // update transferredCustomerBalance
                $updatetransferredCustomer = \app\models\Users::find()
                ->where(['id'=>$transferredCustomerId])
                ->one();

                $updatetransferredCustomer->balance = \app\models\UserBalance::CalcUserTotalBalance( $updatetransferredCustomer->id );
                $updatetransferredCustomer->bonus = \app\models\UserBalance::CalcUserTotalBonus( $updatetransferredCustomer->id );

                if ( $monthCountLaterTimestamp != null ) {
                     $updatetransferredCustomer->updated_at = $monthCountLaterTimestamp;
                     if ( $updatetransferredCustomer->updated_at < time() ) {
                         $updatetransferredCustomer->status = 2;
                         $updatetransferredCustomer->paid_day = $paidDay;
                         
                         // Start - service status to deactive
                        $transferedCustomerSevices = \app\models\UsersServicesPackets::find()
                        ->where(['user_id'=>$transferredCustomerId])
                        ->all();

                        foreach ( $transferedCustomerSevices as $transferedCustomerKey => $transferedCustomerService ) {
                            $transferedCustomerService->status = 2;
                            if ( $transferedCustomerService->save( false ) ) {
                                if ( $transferedCustomerService->service->service_alias == "internet" ) {
                                    $userInetModel = \app\models\UsersInet::find()
                                    ->where([ 'u_s_p_i'=>$transferedCustomerService->id ])
                                    ->one();
                                    $userInetModel->status = 2;
                                   if ( $userInetModel->save( false ) ) {
                                       // API OR RADIUS query
                                        \app\models\radius\Radgroupreply::block( $userInetModel['login'] );
                                        \app\components\COA::disconnect( $userInetModel['login'] );
                                   }
                                }

                                if ( $transferedCustomerService->service->service_alias == "tv" ) {
                                    $tvModel = \app\models\UsersTv::find()
                                    ->where([ 'u_s_p_i'=>$transferedCustomerService->id ])
                                    ->one();
                                    $tvModel->status = 2;
                                    $tvModel->save(false);
                                }

                                if ( $transferedCustomerService->service->service_alias == "wifi" ) {
                                    $wifiModel = \app\models\UsersWifi::find()
                                    ->where([ 'u_s_p_i'=>$transferedCustomerService->id ])
                                    ->one();
                                    $wifiModel->status = 2;
                                    $wifiModel->save(false);
                                }

                                if ( $transferedCustomerService->service->service_alias == "voip" ) {
                                    $voIpModel = \app\models\UsersVoip::find()
                                    ->where([ 'u_s_p_i'=>$transferedCustomerService->id ])
                                    ->one();
                                    $voIpModel->status = 2;
                                    $voIpModel->save( false );
                                }
                            }
                        }
                        // End - service status to deactive
                     }
                }

               if ( $updatetransferredCustomer->save(false) ) {
                  $updateToBetransferedCustomerModel = \app\models\Users::find()->where(['id'=>$toBetransferedCustomerModel->id])->one();
                  
                  if ( $updateToBetransferedCustomerModel->save( false ) ) {
                        // start
                        if ( $updateToBetransferedCustomerModel->status == 2 || $updateToBetransferedCustomerModel->status == 3 ) {
                            $daily_calc =  ( $updateToBetransferedCustomerModel->paid_time_type == "0" &&  $siteConfig['half_month'] == "0" ) ? true : false;
                            $half_month =  ( $updateToBetransferedCustomerModel->paid_time_type == "0" &&  $siteConfig['half_month'] == "1" ) ? true : false;
                        }else {
                            $daily_calc =  false;
                            $half_month =  false;
                        }

                        $tariffAndServiceArray = \app\models\UserBalance::CalcUserTariffDaily(
                            $updateToBetransferedCustomerModel->id, 
                            $daily_calc, 
                            $half_month
                        );
                            
                        $created_at =  time();
                        $user_tariff = $tariffAndServiceArray['services_total_tariff'];
                        $user_credit_tariff = $tariffAndServiceArray['credit_tariff'];

                        if ( $updateToBetransferedCustomerModel->status == 2 || $updateToBetransferedCustomerModel->status == 3 ) {
                            $receiptModel = \app\models\Receipt::find()->where(['id'=>$transactionBalanceIn->receipt_id])->asArray()->one();

                            $calcUserBonusPayment = ( $transactionBalanceIn->balance_in > 0 ) ? \app\components\Utils::calcUserBonusPayment( $transactionBalanceIn->balance_in, $updateToBetransferedCustomerModel->id ) : 0;
                            $transactionBalanceIn->bonus_in = $calcUserBonusPayment;
                            if ( $transactionBalanceIn->save(false) ) {
                                $updateToBetransferedCustomerModel->bonus = \app\models\UserBalance::CalcUserTotalBonus( $toBetransferedCustomerModel->id );
                                $updateToBetransferedCustomerModel->save(false);
                            }

                            if ( $updateToBetransferedCustomerModel->paid_time_type == "0" ) {
                                $caclNextUpdateAtForUser =  \app\models\Users::caclNextUpdateAtForUser( 
                                    $updateToBetransferedCustomerModel->id,
                                    $tariffAndServiceArray['services_total_tariff'] + $tariffAndServiceArray['credit_tariff'] , 
                                    $transactionBalanceIn->balance_in,
                                    ['untilToMonthTariff'=>$user_tariff,'credit_tariff'=>$tariffAndServiceArray['credit_tariff'],'total_tariff'=>$updateToBetransferedCustomerModel->tariff]
                                );
                            }

                            if ( $updateToBetransferedCustomerModel->paid_time_type == "1" ) {
                                $caclNextUpdateAtForUser =  \app\models\Users::caclNextUpdateAtForUser( 
                                    $updateToBetransferedCustomerModel->id,
                                    $tariffAndServiceArray['services_total_tariff'] + $tariffAndServiceArray['credit_tariff'] , 
                                    $transactionBalanceIn->balance_in,
                                );
                            }

                            if ( round( $updateToBetransferedCustomerModel->balance, 2 ) >= round( $user_tariff, 2 )  ) {
                                if ( $updateToBetransferedCustomerModel['credit_status'] == 1 && round( $updateToBetransferedCustomerModel->balance, 2 ) >= round( $user_tariff, 2 ) ) {
                                    $updateToBetransferedCustomerModel->credit_status = 0;
                                    $updateToBetransferedCustomerModel->save(false);
                                }  
                              
                                foreach ( $tariffAndServiceArray['service_tariff_array'] as $tariffAndServiceKey => $tariffAndService ) {
                                   foreach ( $tariffAndService as $key => $service ) {
                                         if ( $tariffAndServiceKey == "internet" ) {
                                            $userServicePacketModel = \app\models\UsersServicesPackets::find()
                                            ->where(['id'=>$service['u_s_p_i']])
                                            ->one();
                                             $userServicePacketModel->status = 1;
                                             if ( $userServicePacketModel->save( false ) ) {
                                                $inetModel = \app\models\UsersInet::find()
                                                ->where([
                                                    'user_id' => $service['user_id'], 
                                                    'u_s_p_i' => $service['u_s_p_i']
                                                ])
                                                ->one();
                                                $inetModel->status = 1;
                                                if ( $inetModel->save( false ) ) {
                   
                                                     if ( $updateToBetransferedCustomerModel->status == 2 ) {
                                                        \app\models\radius\Radgroupreply::unblock(  $inetModel->login, $userServicePacketModel->packet->packet_name );
                                                     }

                                                     if ( $updateToBetransferedCustomerModel->status == 3 ) {
                                                        $addUserToRadius = \app\models\radius\Radcheck::addUser(
                                                            $inetModel->login,
                                                           \app\constants\RadiusAttributes::CLEARTEXT_PASSWORD,
                                                            \app\constants\RadiusAttributes::OPERATION_EQUALS,
                                                            $inetModel->password
                                                        );

                                                        if ( $addUserToRadius == true ) {
                                                            $radUserGroup = \app\models\radius\Radusergroup::createRadUserGroup(  
                                                                $inetModel->login, 
                                                                $userServicePacketModel->packet->packet_name 
                                                            );
                                                        }

                                                        if ( $inetModel->static_ip != "" ) {
                                                            $staticIpModel = \app\models\IpAdresses::find()->where(['id'=>$inetModel->static_ip])->asArray()->one();
                                                             \app\models\radius\Radreply::addStaticIP(  
                                                                $inetModel->login, 
                                                                $staticIpModel['public_ip'] 
                                                            );
                                                        }

                                                     }
                                                    \app\components\COA::disconnect( $inetModel->login );

                                                }
                                             }
                                         }
                                         if ( $tariffAndServiceKey == "tv" ) {
                                            $userServicePacketModel = \app\models\UsersServicesPackets::find()
                                            ->where(['id'=> $service['u_s_p_i']])
                                            ->one();
                                            $userServicePacketModel->status = 1;
                                            if ( $userServicePacketModel->save( false ) ) {
                                                \app\models\UsersTv::turnOnTvAccess(
                                                    $service['user_id'], 
                                                    $service['u_s_p_i']
                                                );
                                            }
                                         }

                                         if ( $tariffAndServiceKey == "wifi" ) {
                                            $userServicePacketModel = \app\models\UsersServicesPackets::find()
                                            ->where(['id'=>$service['u_s_p_i']])
                                            ->one();
                                            $userServicePacketModel->status = 1;
                                            if ( $userServicePacketModel->save( false ) ) {
                                                \app\models\UsersWifi::turnOnWifiAccess(
                                                    $service['user_id'], 
                                                    $service['u_s_p_i']
                                                );
                                            }
                                         }

                                         if ( $tariffAndServiceKey == "voip" ) {
                                            $userServicePacketModel = \app\models\UsersServicesPackets::find()
                                            ->where(['id'=>$service['u_s_p_i']])
                                            ->one();
                                            $userServicePacketModel->status = 1;
                                            if ( $userServicePacketModel->save( false ) ) {
                                                $voIpModel = \app\models\UsersVoip::find()
                                                ->where(['user_id'=>$service['user_id']])
                                                ->andWhere(['u_s_p_i'=>$service['u_s_p_i']])
                                                ->one();
                                                $voIpModel->status = 1;
                                                $voIpModel->save( false );
                                            }
                                         }
                                   }
                                }
                      
                                if ( $caclNextUpdateAtForUser['monthCount'] > 0 ) {
                                    for ( $i=0; $i < $caclNextUpdateAtForUser['monthCount']; $i++ ) { 
                                        if (  $i == 0 && $updateToBetransferedCustomerModel->paid_time_type == 0  ) {
                                            $tariffAndServiceArray = \app\models\UserBalance::CalcUserTariffDaily(
                                                $updateToBetransferedCustomerModel->id, 
                                                $daily_calc, 
                                                $half_month
                                            );
                                        }else{
                                            $daily_calc = false; 
                                            $half_month =  false;

                                            $tariffAndServiceArray = \app\models\UserBalance::CalcUserTariffDaily(
                                                $updateToBetransferedCustomerModel->id, 
                                                $daily_calc, 
                                                $half_month
                                            );
                                        }

                                        foreach ( $tariffAndServiceArray['service_tariff_array'] as $tariffAndServiceKey => $tariffAndService ) {
                                           foreach ( $tariffAndService as $key => $service ) {
                                                 if ( $tariffAndServiceKey == "internet" ) {
                                                    $pay_for = 0;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'],
                                                        $created_at + 1, 
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method, 
                                                        $receiptModel->id,
                                                        $service['packet_id']
                                                    );
                                                 }
                                                 if ( $tariffAndServiceKey == "tv" ) {
                                                    $pay_for = 1;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'], 
                                                        $created_at + 1,
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method, 
                                                        $receiptModel->id,
                                                        $service['packet_id']
                                                    );
                                                 }

                                                 if ( $tariffAndServiceKey == "wifi" ) {
                                                    $pay_for = 2;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'],
                                                        $created_at + 1,  
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method, 
                                                        $receiptModel->id,
                                                        $service['packet_id']
                                                    );
                                                 }

                                                 if ( $tariffAndServiceKey == "voip" ) {
                                                    $pay_for = 4;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'],
                                                        $created_at + 1,  
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method, 
                                                        $receiptModel->id,
                                                        $service['packet_id']
                                                    );
                                                 }
                                           }
                                        }

                                        $updateToBetransferedCustomerModel->status = 1;
                                        $updateToBetransferedCustomerModel->updated_at =  $caclNextUpdateAtForUser['updateAt'];
                                        $updateToBetransferedCustomerModel->paid_day =  $caclNextUpdateAtForUser['paidDay'];

                                        if ( $siteConfig['paid_day_refresh'] == "1" && $updateToBetransferedCustomerModel->paid_time_type == "1" ) {
                                            $usersPaidDayHistory = new \app\models\UsersPaidDayHistory;
                                            $usersPaidDayHistory->user_id = $updateToBetransferedCustomerModel->id;
                                            $usersPaidDayHistory->paid_day = $caclNextUpdateAtForUser['paidDay'];
                                            $usersPaidDayHistory->created_at = $created_at;
                                            $usersPaidDayHistory->save( false );
                                        }

                                        \app\models\UsersGifts::checkAndAddGiftHistory( $updateToBetransferedCustomerModel->id );
                                        \app\models\UsersCredit::CheckAndAddCreditHistory( $updateToBetransferedCustomerModel, 1, $receiptModel['code'] );
                                    }
                                }

                                $updateToBetransferedCustomerModel->balance = \app\models\UserBalance::CalcUserTotalBalance( $updateToBetransferedCustomerModel->id  );
                                $updateToBetransferedCustomerModel->bonus = \app\models\UserBalance::CalcUserTotalBonus( $updateToBetransferedCustomerModel->id  );
                                $updateToBetransferedCustomerModel->last_payment = time();
                                if ( $updateToBetransferedCustomerModel->save(false) ) {
                                    $logMessage =  $transactionBalanceIn->balance_in . " AZN  has been transferred from ".$transferredCustomerFullname."'s balance. Services will activated until ".date("d-m-Y H:i:s",$caclNextUpdateAtForUser['updateAt']);
                                    Logs::writeLog(
                                        Yii::$app->user->username, 
                                        intval( $updateToBetransferedCustomerModel->id ), 
                                        $logMessage, 
                                        $updateToBetransferedCustomerModel->last_payment
                                    );

                                   ###### sending  experied_service sms template ######
                                  if ( $siteConfig['expired_service'] != "0" ) {
                                    \app\components\Utils::sendExperiedDate( 
                                        $updateToBetransferedCustomerModel->id, 
                                        $updateToBetransferedCustomerModel->contract_number, 
                                        $updateToBetransferedCustomerModel->phone, 
                                        $updateToBetransferedCustomerModel->message_lang, 
                                        $updateToBetransferedCustomerModel->updated_at 
                                    );
                                  }
                                }
                            } else {
                                $updateToBetransferedCustomerModel->balance = \app\models\UserBalance::CalcUserTotalBalance( $updateToBetransferedCustomerModel->id );
                                $updateToBetransferedCustomerModel->bonus = \app\models\UserBalance::CalcUserTotalBonus( $updateToBetransferedCustomerModel->id  );
                                if ( $updateToBetransferedCustomerModel->save(false) ) {
                                    $logMessage =  $transactionBalanceIn->balance_in . " AZN  has been transferred from ".$transferredCustomerFullname."'s balance.";

                                    Logs::writeLog(
                                        Yii::$app->user->username, 
                                        intval( $updateToBetransferedCustomerModel->id ), 
                                        $logMessage, 
                                        time()
                                    );
                                }
                            }
                        }else{
                            $caclNextUpdateAtForUser = \app\models\Users::caclNextUpdateAtForUser( 
                                $updateToBetransferedCustomerModel->id,
                                $tariffAndServiceArray['services_total_tariff'] + $tariffAndServiceArray['credit_tariff'] , 
                                $transactionBalanceIn->balance_in
                            );

                            $calcUserBonusPayment = ( $transactionBalanceIn->balance_in > 0 ) ? \app\components\Utils::calcUserBonusPayment( $transactionBalanceIn->balance_in, $updateToBetransferedCustomerModel->id ) : 0;
                            $transactionBalanceIn->bonus_in = $calcUserBonusPayment;
                            $transactionBalanceIn->save(false);

                            if ( $caclNextUpdateAtForUser['monthCount'] > 0 ) {
                                for ( $i=0; $i < $caclNextUpdateAtForUser['monthCount']; $i++ ) { 
                                    \app\models\UsersGifts::checkAndAddGiftHistory( $updateToBetransferedCustomerModel->id );
                                    $receiptModel = \app\models\Receipt::find()->where(['id'=>$transactionBalanceIn->receipt_id])->asArray()->one();

                                    \app\models\UsersCredit::CheckAndAddCreditHistory( $updateToBetransferedCustomerModel->id, 1, $receiptModel['code'] );

                                        foreach ( $tariffAndServiceArray['service_tariff_array'] as $tariffAndServiceKey => $tariffAndService ) {
                                           foreach ( $tariffAndService as $key => $service ) {
                                                 if ( $tariffAndServiceKey == "internet" ) {
                                                    $pay_for = 0;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'], 
                                                        $created_at + 1, 
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method, 
                                                        $receiptModel['id'],
                                                        $service['packet_id']
                                                    );
                                                 }
                                                 if ( $tariffAndServiceKey == "tv" ) {
                                                    $pay_for = 1;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'],
                                                        $created_at + 1, 
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method, 
                                                        $receiptModel['id'],
                                                        $service['packet_id']
                                                    );
                                                 }

                                                 if ( $tariffAndServiceKey == "wifi" ) {
                                                    $pay_for = 2;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'],
                                                        $created_at + 1, 
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method,
                                                        $receiptModel['id'],
                                                        $service['packet_id']
                                                    );
                                                 }

                                                 if ( $tariffAndServiceKey == "voip" ) {
                                       
                                                    $pay_for = 4;
                                                    \app\models\UserBalance::BalanceOut(
                                                        $service['user_id'], 
                                                        $service['packet_price'],
                                                        $created_at + 1,  
                                                        0, 
                                                        $pay_for, 
                                                        $transactionBalanceIn->payment_method,
                                                        $receiptModel['id'],
                                                        $service['packet_id']
                                                    );
                                                 }
                                           }
                                        }
                                }
                        
                                $updateToBetransferedCustomerModel->updated_at = $caclNextUpdateAtForUser['updateAt'];
                                $logMessage = $transactionBalanceIn->balance_in . " AZN  has been transferred from ".$transferredCustomerFullname."'s balance. on ACTIVE status. Services will activated again until ".date("d-m-Y",$caclNextUpdateAtForUser['updateAt']);

                               ###### sending  experied_service sms template ######
                              if ( $siteConfig['expired_service'] != "0" ) {
                                    \app\components\Utils::sendExperiedDate( 
                                        $updateToBetransferedCustomerModel->id, 
                                        $updateToBetransferedCustomerModel->contract_number, 
                                        $updateToBetransferedCustomerModel->phone, 
                                        $updateToBetransferedCustomerModel->message_lang, 
                                        $updateToBetransferedCustomerModel->updated_at 
                                    );
                                }
                            }else{
                                $logMessage = $transactionBalanceIn->balance_in . " " .$siteConfig['currency']."  transfer to " . $updateToBetransferedCustomerModel->fullname . "'s balane on ACTIVE status";
                            }

                            $updateToBetransferedCustomerModel->balance = \app\models\UserBalance::CalcUserTotalBalance( $updateToBetransferedCustomerModel->id );
                            $updateToBetransferedCustomerModel->bonus = \app\models\UserBalance::CalcUserTotalBonus( $updateToBetransferedCustomerModel->id );
                            if ( $updateToBetransferedCustomerModel->save(false) ) {
                                
                                Logs::writeLog(
                                    Yii::$app->user->username,
                                    intval( $updateToBetransferedCustomerModel->id ),
                                    $logMessage,
                                    time()
                                );
                            }
                        }
                        // end
                  };
               }
            }
           return $this->redirect(['index']);
        }

        return $this->renderIsAjax('transfer-amount', [
            'model' => $this->findModel($id),
        ]);    
    }


    public function actionStatistc(){
        $today = \app\models\UserBalance::find()
        ->leftJoin('users','users.id=user_balance.user_id')
        ->orderBy(['created_at' => SORT_ASC])
        ->andWhere(['!=', 'user_balance.status', '1'])
        ->withByLocation()
        ->andWhere(['DATE_FORMAT(FROM_UNIXTIME(user_balance.created_at), "%Y-%m-%d")' => date('Y-m-d')])
        ->asArray()
        ->all();

        $lastTransactions = \app\models\UserBalance::find()
        ->select('user_balance.*,users.fullname as fullname')
        ->leftJoin('users','users.id=user_balance.user_id')
        ->andWhere(['not', ['user_id' => null]])
        ->withByLocation()
        ->orderBy(['user_balance.id'=>SORT_DESC])
        ->limit(20)
        ->asArray()
        ->all();
    
        $lastTotalModel = \app\models\TotalProfit::find()
        ->orderBy(['created_at'=>SORT_DESC])
        ->asArray()
        ->one();

        $today_balance = 0;
        foreach ($today as $key => $t_v) {
           $today_balance += $t_v['balance_in'];
        }

        $all_data = [
            'today_balance' => $today_balance,
            'lastTotalModel' => $lastTotalModel,
            'lastTransactions'=>$lastTransactions,
        ];

        return $this->render('statistic', $all_data);

    }



    public function actionPaymentCalculator()
    {
        $model = new \app\models\UserBalance;
        $data = [];
        if (Yii::$app->request->get()) {
          if( Yii::$app->request->get('start_end_date') == "" ){
            return $this->renderIsAjax('statistic', [
                'model' => $model,
                'data' => $data,
            ]);
          }
            $s_e = explode("-", Yii::$app->request->get('start_end_date'));
            $start_date = trim($s_e[0]);
            $end_date = trim($s_e[1]);

            $payment_method = Yii::$app->request->get("payment_method");
            $query = \app\models\UserBalance::find()->select('user_balance.*,users.fullname as p_fullname,receipt.code as receipt')
                ->leftJoin('users', 'users.id=user_balance.user_id')
                ->leftJoin('receipt', 'receipt.id=user_balance.receipt_id')
                ->orderBy(['user_balance.created_at' => SORT_ASC])
                ->where(['and', ['>=', "DATE_FORMAT(FROM_UNIXTIME({{%user_balance}}.created_at), '%Y/%m/%d')", $start_date], ['<=', "DATE_FORMAT(FROM_UNIXTIME({{%user_balance}}.created_at), '%Y/%m/%d')", $end_date]])
                ->andWhere(['!=', 'balance_in', 0]);
            $data = ($payment_method != "" ? $query->andWhere(['payment_method'=>$payment_method])->asArray()->all() : $query->asArray()->all());
          }

        return $this->renderIsAjax('calculator', [
            'model' => $model,
            'data' => $data,
        ]);
    }


    public function actionUpdate($id){
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $user_model = \app\models\Users::find()
            ->where(['id' => $model->user_id])
            ->withByLocation()
            ->one();
            $user_model->balance = \app\models\UserBalance::CalcUserTotalBalance($model->user_id);
            $user_model->bonus = \app\models\UserBalance::CalcUserTotalBonus($model->user_id);
            if ($user_model->save(false)) {
                $member_name = Yii::$app->user->username;
                $balance_in = Yii::$app->request->post('UserBalance')['balance_in'];
                $balance_out = Yii::$app->request->post('UserBalance')['balance_out'];
                $bonus_in = Yii::$app->request->post('UserBalance')['bonus_in'];
                $bonus_out = Yii::$app->request->post('UserBalance')['bonus_out'];
                $logMessage = "{$member_name} member (additional balance : {$balance_in} , additional bonus : {$bonus_in} ) ( out balance : {$balance_out} , out bonus : {$bonus_out} ) update from balance";
                    
                 Logs::writeLog(Yii::$app->user->username, intval($user_model->id), $logMessage, time());
                    
                
            }
            return $this->redirect(['index']);
        }

        return $this->renderIsAjax('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $receipt_id = $model->receipt_id;
        if ($model->delete()) {
            $userModel = \app\models\Users::find()
            ->where(['id' => $model->user_id])
            ->withByLocation()
            ->one();

            $receiptModel = \app\models\Receipt::find()
            ->where(['id' => $receipt_id])
            ->one();
            if ($userModel != null) {
                $userModel->balance = \app\models\UserBalance::CalcUserTotalBalance($model->user_id);
                $userModel->bonus = \app\models\UserBalance::CalcUserTotalBonus($model->user_id);
                if ($userModel->save(false)) {

                    $memberUsername = Yii::$app->user->username;
                    $balanceIn = Yii::$app->request->get('balance_in');
                    $balanceOut = Yii::$app->request->get('balance_out');
                    $userName =Yii::$app->request->get('username');

                    $logMessage = "Balance in : {$balanceIn} balance out : {$balanceOut} was deleted from {$userName} payments by {$memberUsername}";
                    
                    if ($receiptModel != null) {
                        $receiptModel->status = '2';
                        $receiptModel->save(false);
                    }
                    Logs::writeLog(Yii::$app->user->username, intval($userModel->id), $logMessage, time());
                    return ['status' => 'success'];
                }
            }

        }

    }

    public function actionBulkDelete()
    {
        if (Yii::$app->request->post('selection')) {
            foreach (Yii::$app->request->post('selection', []) as $id) {
                $model = UserBalance::findOne($id);
                $balance_in = $model->balance_in;
                $balance_out = $model->balance_out;
                $receipt_id = $model->receipt_id;
                $user_id = $model->user_id;
                if ($model) {
                    if ($model->delete()) {
                        $user_model = \app\models\Users::find()
                        ->where(['id' => $user_id])
                        ->withByLocation()
                        ->one();
                        $receipt_model = \app\models\Receipt::find()
                        ->where(['id' => $receipt_id])
                        ->one();
                        if ($user_model != null) {
                            $user_model->balance = UserBalance::CalcUserTotalBalance($model->user_id);
                            if ($user_model->save(false)) {
                                $log_text = Yii::$app->user->username . " (additional balance " . $balance_in . "  )" . " (out balance " . $balance_out . "  )" . " deleted  from  " . $user_model->fullname . " balance";
                                Logs::writeLog(Yii::$app->user->username, intval($user_model->id), $log_text, time());
                            }
                        }
                        if ($receipt_model != null) {
                            $receipt_model->status = '2';
                            $receipt_model->save(false);
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds the UserBalance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserBalance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserBalance::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
