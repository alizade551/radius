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
use kartik\export\ExportMenu;
use webvimark\modules\UserManagement\models\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\RadacctSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Radius accounting');

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],
    'radacctid',
    'username',
    'fullname',
    'packet_name',
    'tariff',
    'nasipaddress',
    'nasportid',
    'nasporttype',
    'acctsessiontime',
    'framedipaddress',
    'acctstarttime',
    'acctupdatetime',
    'acctstoptime',
    'callingstationid',
    'servicetype',
    'framedprotocol',
    'acctoutputoctets',
    'acctinputoctets',

    ['class' => 'yii\grid\ActionColumn'],
];

if ( isset( Yii::$app->request->cookies->get( Yii::$app->controller->id.'GridViewVisibility')->value )) {
     $gridViewVisibility = json_decode( Yii::$app->request->cookies->get( Yii::$app->controller->id.'GridViewVisibility')->value ,true );
}else{
    $gridViewVisibility["serial-view"] = "true@Serial";
    $gridViewVisibility["radacctid-view"] = "true@ID";
    $gridViewVisibility["username-view"] = "true@Inet login";
    $gridViewVisibility["customer-view"] = "true@Customer";
    $gridViewVisibility["packet_name-view"] = "true@Packet";
    $gridViewVisibility["nasipaddress-view"] = "true@Nas";
    $gridViewVisibility["nasportid-view"] = "true@Nas port";
    $gridViewVisibility["nasporttype-view"] = "true@Port type";
    $gridViewVisibility["framedipaddress-view"] = "true@Ip address";
    $gridViewVisibility["acctstarttime-view"] = "true@Start time";
    $gridViewVisibility["acctupdatetime-view"] = "true@Update time";
    $gridViewVisibility["acctstoptime-view"] = "true@Acct stoptime";
    $gridViewVisibility["acctsessiontime-view"] = "true@Uptime";
    $gridViewVisibility["callingstationid-view"] = "true@MAC";
    $gridViewVisibility["servicetype-view"] = "true@Service type";
    $gridViewVisibility["framedprotocol-view"] = "true@Protocol";
    $gridViewVisibility["acctoutputoctets-view"] = "true@Download";
    $gridViewVisibility["acctinputoctets-view"] = "true@Upload";

}

$content = '';
$actions = '';

$viewVisibility =  \app\widgets\gridViewVisibility\viewVisibility::widget(
    [
    'params'=>$gridViewVisibility,
    'url'=>Url::to('/radacct-all/grid-view-visibility'),
    'pjaxContainer'=>'#radact-all-grid-pjax'
    ]
);

$exportMenu = ExportMenu::widget(
    [
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'clearBuffers' => true, //optional
    'filename' => 'Radius accounting_'.date('d-m-Y h:i:s'),
     'dropdownOptions' => [
        'label' => 'Export',
        'class' => 'btn btn-info btn-info',

     ],
    ]
);

$pageSize = GridPageSize::widget([
    'pjaxId'=>'radact-all-grid-pjax',
    'pageName'=>'_grid_page_size_radacct_all'
]);

$pageSizeContainer = "<div class='page-size-container'>".$pageSize."</div>";
$actions .= $viewVisibility;
$actionsContainer = "<div class='helper-action-container'>".$actions."</div>";
$content = "<div class='helper-container'>".$pageSizeContainer.$actionsContainer."</div>";
?>

<div class="widget widget-content-area mb-3">
    <div class="widget-one">
        <div class="actions-container" style="display: flex; justify-content: space-between;">
            <div class="page-title"> <h5><?=$this->title ?> </h5> </div>
            <?=$exportMenu ?>
        </div>
    </div>
</div>

<div class=" card custom-card" >
    <div class="row">
        <div class="col-sm-12">
            <?php Pjax::begin(['id'=>'radact-all-grid-pjax']); ?>
                <?= GridView::widget([
                    'id'=>'radact-all-grid',
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
                            'visible'=> ( str_contains( $gridViewVisibility["serial-view"] , 'true' ) == "true" && User::hasPermission("radactt-serial-column") ) ? true : false,
                        ],   

                        [
                            'attribute'=>'radacctid',
                            'visible'=> ( str_contains( $gridViewVisibility["radacctid-view"] , 'true' ) == "true" && User::hasPermission("radacct-radacctid-view") ) ? true : false,
                            'label'=>Yii::t('app','ID'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:20px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:20px;text-align:center;'],
                            'value'=>function ( $model ){
                                return  $model['radacctid'];
                            }
                        ],
                        [
                            'attribute'=>'username',
                            'visible'=> ( str_contains( $gridViewVisibility["username-view"], 'true' ) == "true" && User::hasPermission("radacct-username-view") ) ? true : false,
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
                            'visible'=> ( str_contains( $gridViewVisibility["customer-view"], 'true' ) == "true" && User::hasPermission("radacct-fullname-view") ) ? true : false,
                            'label'=>Yii::t('app','Customer'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ( $model ){
                                return  '<a  data-pjax="0" href="'.Url::to("/users/view").'?id='.$model['user_id'].'">'.$model['fullname'].'</a>';
                            }
                        ],
                        [
                            'attribute'=>'packet_name',
                            'visible'=> ( str_contains( $gridViewVisibility["packet_name-view"], 'true' ) == "true" && User::hasPermission("radacct-packet_name-view") ) ? true : false,
                            'label'=>Yii::t('app','Packet'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ( $model ){
                                  $isBlocked = ($model['inet_status'] == '1') ? "" : Yii::t('app','- BLOCKED');
                                return $model['packet_name'].$isBlocked;
                             
                            }
                        ],

             
                        [
                            'attribute'=>'nasipaddress',
                            'label'=>Yii::t('app','Nas'),
                            'visible'=> ( str_contains( $gridViewVisibility["nasipaddress-view"], 'true' ) == "true" && User::hasPermission("radacct-nasipaddress-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:50px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:50px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return  $model['nasipaddress'];
                            }
                        ],

                        [
                            'attribute'=>'nasportid',
                            'label'=>Yii::t('app','Nas port'),
                            'visible'=> ( str_contains( $gridViewVisibility["nasportid-view"], 'true' ) == "true" && User::hasPermission("radacct-nasportid-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:50px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:50px;text-align:center;'],
                            'value'=>function ($model){
                              return  $model['nasportid'];
                            }
                        ],
                        

                        [
                            'attribute'=>'nasporttype',
                            'label'=>Yii::t('app','Port type'),
                            'visible'=> ( str_contains( $gridViewVisibility["nasporttype-view"], 'true' ) == "true" && User::hasPermission("radacct-nasporttype-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:50px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:50px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return  $model['nasporttype'];
                            }
                        ],


                        [
                            'attribute'=>'framedipaddress',
                            'label'=>Yii::t('app','Ip address'),
                            'visible'=> ( str_contains( $gridViewVisibility["framedipaddress-view"], 'true' ) == "true" && User::hasPermission("radacct-framedipaddress-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:90px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:90px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return  $model['framedipaddress'];
                            }
                        ],
                        [
                            'attribute'=>'acctstarttime',
                            'visible'=> ( str_contains( $gridViewVisibility["acctstarttime-view"], 'true' ) == "true" && User::hasPermission("radacct-acctstarttime-view") ) ? true : false,
                            'label'=>Yii::t('app','Start time'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return  $model['acctstarttime'];
                            }
                        ],

                        [
                            'attribute'=>'acctupdatetime',
                            'label'=>Yii::t('app','Update time'),
                            'visible'=> ( str_contains( $gridViewVisibility["acctupdatetime-view"], 'true' ) == "true" && User::hasPermission("radacct-acctupdatetime-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return  $model['acctupdatetime'];
                            }
                        ],

                        [
                            'attribute'=>'acctsessiontime',
                            'label'=>Yii::t('app','Uptime'),
                            'visible'=> ( str_contains( $gridViewVisibility["acctsessiontime-view"], 'true' ) == "true" && User::hasPermission("radacct-acctsessiontime-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:150px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:150px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return \app\models\radius\Radacct::formatAcctSessionTime( $model['acctsessiontime'] );
                            }
                        ],

                        [
                            'attribute'=>'acctstoptime',
                            'visible'=> ( str_contains( $gridViewVisibility["acctstoptime-view"], 'true' ) == "true" && User::hasPermission("radacct-acctstoptime-view") ) ? true : false,
                            'format'=>'raw',
                            'label'=>Yii::t('app','Acct stoptime'),
                            'headerOptions'=>['style'=>'width:90px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:90px;text-align:center;','class'=>'hafiz'],
                            'value'=>function ($model){
                    
                              return  $model['acctstoptime'];
                            }
                        ],


                        [
                            'attribute'=>'callingstationid',
                            'visible'=> ( str_contains( $gridViewVisibility["callingstationid-view"], 'true' ) == "true" && User::hasPermission("radacct-callingstationid-view") ) ? true : false,
                            'label'=>Yii::t('app','MAC'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:120px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:120px;text-align:center;'],
                  
                            'value'=>function ($model){
                    
                              return  $model['callingstationid'];
                            }
                        ],

                        [
                            'attribute'=>'servicetype',
                            'visible'=> ( str_contains( $gridViewVisibility["servicetype-view"], 'true' ) == "true" && User::hasPermission("radacct-servicetype-view") ) ? true : false,
                            'format'=>'raw',
                            'label'=>Yii::t('app','Service type'),
                            'headerOptions'=>['style'=>'width:100px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:100px;text-align:center;'],
                            'value'=>function ($model){
                    
                              return  $model['servicetype'];
                            }
                        ],

                        [
                            'attribute'=>'framedprotocol',
                            'visible'=> ( str_contains( $gridViewVisibility["framedprotocol-view"], 'true' ) == "true" && User::hasPermission("radacct-framedprotocol-view") ) ? true : false,
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:50px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:50px;text-align:center;'],
                            'header' => Yii::t("app","Protocol"),
                            'value'=>function ($model){
                    
                              return  $model['framedprotocol'];
                            }
                        ],

                        [
                            'attribute'=>'acctoutputoctets',
                            'visible'=> ( str_contains( $gridViewVisibility["acctoutputoctets-view"], 'true' ) == "true" && User::hasPermission("radacct-acctoutputoctets-view") ) ? true : false,
                            'label'=>Yii::t('app','Download'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:50px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:50px;text-align:center;'],
                  
                            'value'=>function ($model){
                    
                              return  $model['acctoutputoctets']." MB";
                            }
                        ],


                        [
                            'attribute'=>'acctinputoctets',
                            'visible'=> ( str_contains( $gridViewVisibility["acctinputoctets-view"], 'true' ) == "true" && User::hasPermission("radacct-acctinputoctets-view") ) ? true : false,
                            'label'=>Yii::t('app','Upload'),
                            'format'=>'raw',
                            'headerOptions'=>['style'=>'width:50px;text-align:center;'],
                            'contentOptions'=>['style'=>'width:50px;text-align:center;'],
                 
                            'value'=>function ($model){
                    
                              return   $model['acctinputoctets']." MB";
                            }
                        ],






                    ],
                ]); ?>
            <?php Pjax::end(); ?>
        </div>

    </div>
</div>



