<?php

namespace app\controllers;

use Yii;
use app\models\radius\Radacct;
use app\models\search\RadacctAllSearch;
use app\components\DefaultController;

/**
 * RadacctAllController implements the CRUD actions for Radacct model.
 */
class RadacctAllController extends DefaultController
{
    public $modelClass = 'app\models\radius\Radacct';
    public $modelSearchClass = 'app\models\search\RadacctAllSearch';

}
