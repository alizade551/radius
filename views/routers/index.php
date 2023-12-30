<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Arrayhelper;
use app\widgets\GridBulkActions;
use app\widgets\GridPageSize;
use yii\helpers\Url;
use kartik\date\DatePicker;
use yii\bootstrap4\Modal;
use webvimark\modules\UserManagement\models\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RoutersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Routers');
$langUrl = (Yii::$app->language == "en") ? "" : "/".Yii::$app->language."/";


$content = '';
$actions = '';



$pageSize = GridPageSize::widget([
    'pjaxId'=>'router-grid-pjax',
    'pageName'=>'_grid_page_size_bras'
]);

$pageSizeContainer = "<div class='page-size-container'>".$pageSize."</div>";



$actionsContainer = "<div class='helper-action-container'>".$actions."</div>";

$content = "<div class='helper-container'>".$pageSizeContainer.$actionsContainer."</div>";

?>

<div class="widget widget-content-area mb-3">
    <div class="widget-one">
        <div class="actions-container" style="display: flex; justify-content: space-between;">
            <div class="page-title"> <h5><?=$this->title ?> </h5> </div>
            <?php if (User::canRoute("/routers/create")): ?>
                <a class="btn btn-success add-element" data-pjax="0" href="/routers/create" title=" <?=Yii::t("app","Create a router") ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?=Yii::t("app","Create a router") ?>
                </a>
            <?php endif?>
        </div>
    </div>
</div>

<div class="card custom-card ">
    <div class="row">

        <div class="col-sm-6">
            <div>
               
                </div>
        </div>


        <div class="col-sm-12">
            <?php Pjax::begin(['id'=>'router-grid-pjax']); ?>

                <?= GridView::widget([
                    'id'=>'ub-grid',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'layout'=>' '.$content .' {items}<div class="grid-bottom"><div class="summary">{summary}</div><div>{pager}</div></div>',
                    'columns' => [
                        [
                        'class' => 'yii\grid\SerialColumn',
                        'visible'=>User::hasPermission("router-serial-column-view"),
                        'options'=>['style'=>'width:1%;text-align:center'],
                        'headerOptions'=>['style'=>'width:1%;text-align:center;'],
                        'contentOptions'=>['style'=>'width:1%;text-align:center;'],
                        ],


                        [
                            'attribute'=>'vendor_name',
                            'visible'=>User::hasPermission("router-vendor-name-view"),
                            'format'=>'raw',
                            'options'=>['style'=>'width:32%%;text-align:center'],
                            'headerOptions'=>['style'=>'width:32%%;text-align:center;'],
                            'contentOptions'=>['style'=>'width:32%%;text-align:center;'],
                            'value'=>function ($model){
                                return  $model->vendor_name;
                            }
                        ],
                         [
                            'attribute'=>'nasname',
                            'visible'=>User::hasPermission("router-name-view"),
                            'format'=>'raw',
                            'options'=>['style'=>'width:30%;text-align:center'],
                            'headerOptions'=>['style'=>'width:30%;text-align:center;'],
                            'contentOptions'=>['style'=>'width:30%;text-align:center;'],
                            'value'=>function ($model){
                            return  $model->nasname;
                             
                            }
                        ],

                         [
                            'attribute'=>'server',
                            'visible'=>User::hasPermission("router-server-view"),
                            'format'=>'raw',
                            'options'=>['style'=>'width:32%;text-align:center'],
                            'headerOptions'=>['style'=>'width:32%;text-align:center;'],
                            'contentOptions'=>['style'=>'width:32%;text-align:center;'],
                            'value'=>function ($model){
                            return  $model->server;
                             
                            }
                        ],




                    // [
                    //           'class' => 'yii\grid\ActionColumn',
                    //           'options'=>['style'=>'width:75px;text-align:center;'],
                    //           'options'=>['style'=>'width:75px;text-align:center;'],
                    //           'header' => Yii::t("app","Integrate"),
                    //           'visible'=>User::hasPermission("router-integrate-view"),
                    //           'headerOptions' => ['style' => 'text-align:center'],
                    //           'template' => '{integrate}',
                    //           'buttons'=>[
                    //                 'integrate'=>function($url,$model){
                    //                     return Html::a('<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',$url,[
                    //                         'data'=>['pjax'=>0],
                    //                         'style'=>'text-align:center;display:block;',
                    //                         'class'=>'modal-d',
                    //                         'title'=>$model['vendor_name']." - ".$model['name']
                    //                     ]); 
                    //                  }
                    //             ]
                    // ],




                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header'=>Yii::t('app','Update'),
                        'options'=>['style'=>'width:5%;text-align:center'],
                        'headerOptions'=>['style'=>'width:5%;text-align:center;'],
                        'contentOptions'=>['style'=>'width:5%;text-align:center;line-height: 0'],
                        'visible'=>User::canRoute("/routers/update"),
                        'template'=>'{update}',
                        'buttons'=>[
                            'update'=>function($url,$model){
                                return Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-feather"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path><line x1="16" y1="8" x2="2" y2="22"></line><line x1="17.5" y1="15" x2="9" y2="15"></line></svg>',$url."&city_id=".$model['city_id']."&district_id=".$model['district_id']."&location_id=".$model['location_id'],[
                                    'data'=>['pjax'=>0],
                                    'title'=>Yii::t('app','Update router : {router_name}',['router_name'=>$model['nasname']])
                                ]); 
                             }
                        ]
                    ],


                [
                    'class' => 'yii\grid\ActionColumn',
                    'visible'=>User::canRoute("/routers/delete"),
                    'header'=>Yii::t('app','Delete'),
                    'options'=>['style'=>'width:2%;text-align:center'],
                    'headerOptions'=>['style'=>'width:2%;text-align:center;'],
                    'contentOptions'=>['style'=>'width:2%;text-align:center;line-height: 0'],
                    'template'=>'{delete}',
                    'buttons'=>[
                    'delete' => function($url, $model){
                         return '<a href="javascript:void(0)" data-href="'.$url.'" class="alertify-confirm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>';
                            }
                    ]

                ]

                    ],
                ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

<?php 
Modal::begin([
    'title' => Yii::t("app","Routers"),
    'id' => 'modal',
    'options' => [
        'tabindex' => false // important for Select2 to work properly
    ],
    'size' => 'modal-lg',
]);
echo "<div id='modalContent'></div>";
Modal::end();
?>

<?php 
$this->registerJs('
  $(document).on("click",".alertify-confirm",function(){
      var that = $(this);
      console.log()
      var message  = "'.Yii::t("app","Are you sure want to delete this ?").'";
          alertify.confirm( message, function (e) {
            if (e) {
               $.ajax({
                   url:that.attr("data-href"),
                   type:"post",
                   success:function(response){
                     that.closest("tr").fadeOut("slow");
                    alertify.set("notifier","position", "top-right");
                    alertify.success("'.Yii::t("app","Router was deleted successfuly").'");
                   }
               });
            } 
        }).set({title:"'.Yii::t("app","Delete a router").'"}).set("labels", {ok:"'.Yii::t('app','Confrim').'", cancel:"'.Yii::t('app','Cancel').'"});;      
        return false;
    });

');

 ?>