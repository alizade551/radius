<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use webvimark\modules\UserManagement\models\User;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Items */
/* @var $form yii\widgets\ActiveForm */
$langUrl = (Yii::$app->language == "en") ? "" : "/".Yii::$app->language."/";
?>

<div class="items-form">

    <?php $form = ActiveForm::begin(['id'=>'item-form']); ?>

    <?= $form->field($model, 'groupname')->hiddenInput(['value' =>$packetModel['packet_name'] ])->label(false) ?>

	<div class="form-group field-radgroupreply-grpn">
		<label for="radgroupreply-grpn"><?=Yii::t('app','Groupname') ?></label>
		<input type="text" disabled id="radgroupreply-grpn" class="form-control is-valid" aria-invalid="false" value="<?=$packetModel['packet_name'] ?>">

		<div class="invalid-feedback"></div>
	</div>

    <?= $form->field($model, 'attribute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'op')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'value')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?php if ( $model->isNewRecord ): ?>
            <?= $form->field($model, 'created_at')->hiddenInput(['value'=>time()])->label(false) ?>
            <?= Html::submitButton(Yii::t('app', 'Add an attribute'), ['class' => 'btn btn-primary']) ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>

            <div class="col-lg-12 animatedParent animateOnce z-index-50">
                <div class="panel panel-default animated fadeInUp">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead> 
                                    <tr> 
                                        <th>#</th> 
                                        <th><?=Yii::t('app','Groupname') ?></th> 
                                        <th><?=Yii::t('app','Attribute') ?></th> 
                                        <th><?=Yii::t('app','Op') ?></th> 
                                        <th><?=Yii::t('app','Value') ?></th> 
                                        <th><?=Yii::t('app','Update') ?></th> 
                                        <th><?=Yii::t('app','Delete') ?></th> 
                                    </tr> 
                                </thead> 
                                <tbody> 
                                    <?php $c = 1;?>
                                    <?php foreach ($radgroupreplyModel as $key => $radgroup): ?>
                               		 <tr> 
                                        <td><?=$c++; ?></td> 
                                        <td><?=$radgroup['groupname'] ?></td> 
                                        <td><?=$radgroup['attribute'] ?></td> 
                                        <td><?=$radgroup['op'] ?></td> 
                                        <td><?=$radgroup['value'] ?></td> 

                                        <?php if ( User::canRoute('/packets/change-packet-attribute') && User::canRoute('/packets/change-packet-validate')  ): ?>
                                            <td class="change-packet">
                                                <a data-fancybox="" data-type="ajax" data-fancybox data-type="ajax" data-options='{"touch" : false}'  data-src="<?=$langUrl ?>/packets/change-packet-attribute?id=<?=$radgroup['id'] ?>" href="javascript:;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-feather"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path><line x1="16" y1="8" x2="2" y2="22"></line><line x1="17.5" y1="15" x2="9" y2="15"></line></svg>
                                                </a>
                                            </td>
                                        <?php endif ?>

                                        <?php if ( User::canRoute('/packets/delete-packet-attribute') ): ?>
                                            <td>
                                                <a data-fancybox data-src="#hidden-delete-packet-content-<?=$radgroup['id'] ?>" href="javascript:void(0)"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>
                                            </td> 


                                            <div style="display: none; width: 400px;" id="hidden-delete-packet-content-<?=$radgroup['id'] ?>">
                                                <div class="fcc">
                                                 
                                                  <h4 ><?=Yii::t('app', 'Are you sure want to delete <em> {groupname} </em> packet  <em> {attribute} </em> attribute <em> {op} </em> op  and <em> {value} </em> value  ?', [
                                                    'groupname' => $radgroup['groupname'],
                                                    'attribute' => $radgroup['attribute'],
                                                    'op' => $radgroup['op'],
                                                    'packet_name' => $radgroup['groupname'],
                                                    'value' => $radgroup['value'],
                                                    ]) ?></h4>
                                                  <button class="btn btn-danger delete-packet" data-radgroup_id="<?=$radgroup['id'] ?>"  title="<?=Yii::t("app","Delete") ?>" ><?=Yii::t("app","Delete") ?></button>
                                                  <button data-fancybox-close="" class="btn btn-primary"  title="<?=Yii::t('app','Close') ?>" ><?=Yii::t("app","Close") ?></button>           
                                                </div>
                                            </div>

                                        <?php endif ?>
                                    </tr> 
                                <?php endforeach ?>
                                </tbody> 
                            </table>
                        </div>
                    </div>
                </div>
            </div>
</div>



<?php $this->registerJs('

$(document).on("click",".delete-packet",function(){
    var url = "/packets/index";    
    var radgroup_id = $(this).attr("data-radgroup_id");
    var that = $(this);
   $.ajax({
        url:"'.$langUrl.Url::to('/packets/packet-attribute-delete').'",
        method:"POST",
        data:{radgroup_id},
        success:function(res){
           if(res.code == "success"){
           window.location.href = url;
           }
        }
    });
 e.preventDefault();
 return false;
});

') ?>