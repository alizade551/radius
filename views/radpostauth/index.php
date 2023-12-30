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
/* @var $searchModel app\models\search\RadacctSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Rad post auths logs');


if ( isset( Yii::$app->request->cookies->get( Yii::$app->controller->id.'GridViewVisibility')->value )) {
     $gridViewVisibility = json_decode( Yii::$app->request->cookies->get( Yii::$app->controller->id.'GridViewVisibility')->value ,true );

}else{

    $gridViewVisibility["id-view"] = "true@ID";
    $gridViewVisibility["serial-view"] = "true@Serial";
    $gridViewVisibility["username-view"] = "true@Inet login";
    $gridViewVisibility["customer-view"] = "true@Customer";
    $gridViewVisibility["reply-view"] = "true@Reply";
    $gridViewVisibility["authdate-view"] = "true@Auth date";
}




    $content = '';
    $actions = '';

    $viewVisibility =  \app\widgets\gridViewVisibility\viewVisibility::widget(
        [
            'params'=>$gridViewVisibility,
            'url'=>Url::to('/radpostauth/grid-view-visibility'),
            'pjaxContainer'=>'#radpostauth-grid-pjax'
        ]
    );

    $pageSize = GridPageSize::widget([
        'pjaxId'=>'radpostauth-grid-pjax',
        'pageName'=>'_grid_page_size_radpostauth'
    ]);

    $progressBar = '<div class="progress">
        <div id="auto-reload-progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" >
        </div>
    </div>';


    $pageSizeContainer = "<div class='page-size-container'>".$pageSize."</div>";

    $actions .= $viewVisibility;
    $actionsContainer = "<div class='helper-action-container'>".$actions."</div>";

    $content = "<div class='helper-container'>".$pageSizeContainer.$actionsContainer."</div>".$progressBar;






?>
<div class="widget widget-content-area mb-3">
    <div class="widget-one">
        <div class="actions-container" style="display: flex; justify-content: space-between;">
            <div class="page-title"> <h4><?=$this->title ?> </h4> </div>
        </div>
    </div>
</div>
<div class="card custom-card" >

    <div class="row">
        <div class="col-sm-12">
            <?php Pjax::begin(['id'=>'radpostauth-grid-pjax']); ?>
                <?= GridView::widget([
                    'id'=>'radpostauth',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'pager'=>[
                      'class'=>yii\bootstrap4\LinkPager::class
                    ], 
                    'layout'=>' '.$content .' {items}<div class="grid-bottom"><div class="summary">{summary}</div><div>{pager}</div></div>',

                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'headerOptions'=>['style'=>'width:10px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:10px;text-align:center;'],
                            'visible'=> ( str_contains( $gridViewVisibility["serial-view"], 'true' ) == "true" && User::hasPermission("radpost-serial-column") ) ? true : false,
                        ],
                        [
                            'attribute'=>'id',
                            'visible'=> ( str_contains( $gridViewVisibility["id-view"], 'true' ) == "true" && User::hasPermission("radpost-id-view") ) ? true : false,
                            'label'=>Yii::t('app','ID'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:20px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:20px;text-align:center;'],
                            'value'=>function ( $model ){
                                return  $model['id'];
                            }
                        ],
                        [
                            'attribute'=>'username',
                            'visible'=> ( str_contains( $gridViewVisibility["username-view"], 'true' ) == "true" && User::hasPermission("radpost-username-view") ) ? true : false,
                            'label'=>Yii::t('app','Inet login'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:80px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:80px;text-align:center;'],
                            'value'=>function ( $model ){
                                return  '<a  data-pjax="0" href="'.Url::to("/users/view").'?id='.$model['user_id'].'">'.$model['username'].'</a>';
                            }
                        ],


                        [
                            'attribute'=>'fullname',
                            'visible'=> ( str_contains( $gridViewVisibility["customer-view"], 'true' ) == "true" && User::hasPermission("radpost-customer-view") ) ? true : false,
                            'label'=>Yii::t('app','Customer'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ( $model ){
                                return  '<a data-pjax="0" href="'.Url::to("/users/view").'?id='.$model['user_id'].'">'.$model['fullname'].'</a>';
                            }
                        ],
                        [
                            'attribute'=>'reply',
                            'visible'=> ( str_contains( $gridViewVisibility["reply-view"], 'true' ) == "true" && User::hasPermission("radpost-reply-view") ) ? true : false,
                            'label'=>Yii::t('app','Reply'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ( $model ){
                                return $model['reply'];
                            }
                        ],

                        [
                            'attribute'=>'authdate',
                            'visible'=> ( str_contains( $gridViewVisibility["authdate-view"], 'true' ) == "true" && User::hasPermission("radpost-authdate-view") ) ? true : false,
                            'label'=>Yii::t('app','Auth date'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:80px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:80px;text-align:center;'],
                            'value'=>function ( $model ){
                                return $model['authdate'];
                            }
                        ],

                    ],
                ]); ?>
            <?php Pjax::end(); ?>    
        </div>

      </div>
</div>

<?php
$this->registerJs('

    var progressBarValue = 0;
    var progressBarSelector = "#auto-reload-progress-bar";
    var gridContainerSelector = "#radpostauth-grid-pjax";
    var progressBarInterval;

    function updateProgressBar() {
        var progressBar = $(progressBarSelector);
        progressBarValue += (100 / 25);
        progressBar.css("width", progressBarValue + "%");

        if (progressBarValue > 100) {
            clearInterval(progressBarInterval);
            resetProgressBar();
            reloadGrid();
        }
    }

    function resetProgressBar() {
        progressBarValue = 0;
        $(progressBarSelector).css("width", "0%");
    }

    function reloadGrid() {
        $.pjax.reload({
            container: gridContainerSelector,
            type: "POST",
            timeout: 5000
        });
    }

    function startReloadInterval() {
        return setInterval(function () {
            updateProgressBar();
        }, 1000); // 1000 milliseconds = 1 second
    }


    resetProgressBar();
    progressBarInterval = startReloadInterval();

    setInterval(function () {
        resetProgressBar();
        clearInterval(progressBarInterval);
        progressBarInterval = startReloadInterval();
    }, 28000); // 28000 milliseconds = 28 seconds

');
?>

