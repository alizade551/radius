<?php
namespace app\controllers;

use app\components\DefaultController;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * RadpostauthController implements the CRUD actions for Radpostauth model.
 */
class RadpostauthController extends DefaultController
{
    public $modelClass = 'app\models\radius\Radpostauth';
    public $modelSearchClass = 'app\models\search\RadpostauthSearch';

}
