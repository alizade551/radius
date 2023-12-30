<div class="check-connection">
	<h4><?=Yii::t("app","{service} servis {packet} packet detail",['service'=>$model->service->service_name,'packet'=>$model->packet->packet_name]) ?></h4>
    <table class="table table-striped mb-0" >
        <tbody>
        	<?php if ( $model->service->service_alias == "internet" ): ?>
	             <tr>
	                <td><?=Yii::t('app','Running') ?></td>
	                <td>
                        <?php if ( \app\models\radius\Radacct::isUserOnline( $model->usersInet->login ) ): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="18" height="18" x="0" y="0" viewBox="0 0 367.805 367.805" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M183.903.001c101.566 0 183.902 82.336 183.902 183.902s-82.336 183.902-183.902 183.902S.001 285.469.001 183.903C-.288 82.625 81.579.29 182.856.001h1.047z" style="" fill="#3bb54a" data-original="#3bb54a" class=""></path><path d="M285.78 133.225 155.168 263.837l-73.143-72.62 29.78-29.257 43.363 42.841 100.833-100.833z" style="" fill="#d4e1f4" data-original="#d4e1f4" class=""></path></g></svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="16" height="16" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path fill="#f74354" d="m63.437 10.362-9.8-9.8a1.922 1.922 0 0 0-2.717 0L32 19.484 13.079.563a1.922 1.922 0 0 0-2.717 0l-9.8 9.8c-.75.75-.75 1.966 0 2.717L19.484 32 .563 50.921c-.75.75-.75 1.966 0 2.717l9.8 9.8c.75.75 1.966.75 2.717 0L32 44.516l18.921 18.921c.75.75 1.966.75 2.717 0l9.8-9.8c.75-.75.75-1.966 0-2.717L44.516 32l18.921-18.921a1.92 1.92 0 0 0 0-2.717z" data-original="#f74354" class=""></path></g></svg> 
                        <?php endif ?>
	                </td>
	            </tr>
	             <tr>
	                <td><?=Yii::t('app','Inet login') ?></td>
	                <td><?=$model->usersInet->login ?></td>
	            </tr>
	            	<?php if ( $model->usersInet->static_ip != "" ): ?>
			             <tr>
			                <td><?=Yii::t('app','Framedipaddress (static ip)') ?></td>
			                <td><?=\app\models\IpAdresses::find()->where(['id'=>$model->usersInet->static_ip])->asArray()->one()['public_ip'] ?></td>
			            </tr>
	            	<?php endif ?>

	             <tr>
	                <td><?=Yii::t('app','Inet password') ?></td>
	                <td><?=$model->usersInet->password ?></td>
	            </tr>
	             <tr>
	                <td><?=Yii::t('app','Router name') ?></td>
	                <td><?=$model->usersInet->router->nasname ?></td>
	            </tr>

        	<?php endif ?>


        	<?php if ($model->service->service_alias == "tv"): ?>
	             <tr>
	                <td><?=Yii::t('app','Tv login') ?></td>
	                <td><?=$model->usersTv->card_number ?></td>
	            </tr>
        	<?php endif ?>

        	<?php if ($model->service->service_alias == "wifi"): ?>
	             <tr>
	                <td><?=Yii::t('app','Wifi login') ?></td>
	                <td><?=$model->usersWifi->login ?></td>
	            </tr>
	             <tr>
	                <td><?=Yii::t('app','Wifi password') ?></td>
	                <td><?=$model->usersWifi->password ?></td>
	            </tr>
        	<?php endif ?>

        	<?php if ($model->service->service_alias == "voip"): ?>
	             <tr>
	                <td><?=Yii::t('app','Phone number') ?></td>
	                <td><?=$model->usersVoip->phone_number ?></td>
	            </tr>
        	<?php endif ?>

        </tbody>
    </table>	

</div>
 <style type="text/css">

 .check-connection{max-width: 250px;max-width: 600px}
</style>     
