<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\radius\Radpostauth */

$this->title = Yii::t('app', 'Create Radpostauth');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Radpostauths'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="radpostauth-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
