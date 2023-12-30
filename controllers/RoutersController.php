<?php

namespace app\controllers;

use Yii;
use app\components\DefaultController;
use app\models\radius\Nas;
use yii\web\Controller;
use app\components\RouterosApi;

/**
 * RoutersController implements the CRUD actions for Routers model.
 */
class RoutersController extends DefaultController
{

    public $modelClass = 'app\models\radius\Nas';
    public $modelSearchClass = 'app\models\search\NasSearch';



    public function actionCreate(){
        $model = new Nas();
        $siteConfig = \app\models\SiteConfig::find()->one();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            return $this->redirect(['index']);
        }

        return $this->renderIsAjax('create', [
            'model' => $model,
            'siteConfig' => $siteConfig
        ]);
    }



}
