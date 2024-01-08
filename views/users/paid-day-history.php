<div class="check-connection">
	<?php if(  $model != null ): ?>
	   <h3><?=Yii::t("app","Paid day history") ?></h3>
	    <table class="table table-striped">
	        <thead> 
	            <tr> 
	                <th>#</th> 
	                <th><?=Yii::t('app','#') ?></th> 
	                <th><?=Yii::t('app','Day') ?></th> 
	            </tr> 
	        </thead> 
	        <tbody>
	            <?php $c=0; ?>
	            <?php foreach ( $model as $key => $day ): ?>
	            <?php $c++; ?>
	            <tr>  
	                <td><?=$c; ?></td> 
	                <td><?=$day['paid_day'] ?></td>
	               
	            </tr> 
	          <?php endforeach ?>
	        </tbody> 
	    </table>
	<?php else: ?>
	   <div style="text-align:center;padding: 0 10px;">
	         <h5 style="margin-bottom:10px"><?=Yii::t("app","Customer doesnt have any paid day changing history") ?></h5>
	         
	   </div>
	<?php endif ?>
</div>











