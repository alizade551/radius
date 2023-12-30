<?php
use yii\helpers\ArrayHelper;
use app\models\Services;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\Json;


/* @var $this yii\web\View */
/* @var $model app\models\Packets */
/* @var $form yii\widgets\ActiveForm */

  $this->title = Yii::t("app","Update - {groupname} packet attribute values",['groupname'=>$model->groupname]);

?>

<div class="widget-content " style="padding: 20px;width:600px">
	<h3><?=$this->title ?></h3>
    <?php $form = ActiveForm::begin([
      'id'=>'packet-form',
      'enableClientValidation' => false,
      'enableAjaxValidation' => true,
      'validationUrl' => Url::toRoute('validate-change-packet-attribute')
     ]); ?>

    
		<div class="form-group field-radgroupreply-groupnamedisabled required">
			<label for="radgroupreply-groupnamedisabled"><?=Yii::t("app","Groupname") ?></label>
			<input disabled type="text" id="radgroupreply-groupnamedisabled" class="form-control validating" value="<?=$model->groupname ?>" maxlength="64" aria-required="true">
		</div>
        <?= $form->field($model, 'groupname')->hiddenInput(['maxlength' => true])->label(false) ?>
        <?= $form->field($model, 'attribute')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'op')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'value')->textInput(['maxlength' => true]) ?>
   
        <div class="form-group">
            <?php if ($model->isNewRecord): ?>
                <?= $form->field($model, 'created_at')->hiddenInput(['value'=>time()])->label(false); ?>
                <?= Html::submitButton('Add', ['class' => 'btn btn-primary']) ?>
            <?php else: ?>
                 <?= Html::submitButton(Yii::t('app','Update'), ['class' => 'btn btn-primary']) ?>
            <?php endif ?>
        </div>

    <?php ActiveForm::end(); ?>
   
</div>

<style type="text/css">
    #pjax-packet-form {
        width: 100%;
        margin-left: 15px;
        margin-right: 15px;
    }
</style>