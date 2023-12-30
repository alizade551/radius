<div class="check-connection">

<?php if(  $data != null ): ?>
   <h3><?=Yii::t("app","Router connection information") ?></h3>
    <table class="table table-striped mb-0" >
        <tbody>
             <tr>
                <td><?=Yii::t('app','Inet login') ?></td>
                <td><?=$user_inet_model['login']  ?></td>
            </tr>

             <tr>
                <td><?=Yii::t('app','Inet password') ?></td>
                <td><?=$user_inet_model['password']  ?></td>
            </tr>
             <tr >
                <td><?=Yii::t('app','Running') ?></td>
                <td><?php 

                if ( $data['current'] == true ) {
                    echo '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="18" height="18" x="0" y="0" viewBox="0 0 367.805 367.805" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M183.903.001c101.566 0 183.902 82.336 183.902 183.902s-82.336 183.902-183.902 183.902S.001 285.469.001 183.903C-.288 82.625 81.579.29 182.856.001h1.047z" style="" fill="#3bb54a" data-original="#3bb54a" class=""></path><path d="M285.78 133.225 155.168 263.837l-73.143-72.62 29.78-29.257 43.363 42.841 100.833-100.833z" style="" fill="#d4e1f4" data-original="#d4e1f4" class=""></path></g></svg>';
                }else{
                    echo '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="16" height="16" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path fill="#f74354" d="m63.437 10.362-9.8-9.8a1.922 1.922 0 0 0-2.717 0L32 19.484 13.079.563a1.922 1.922 0 0 0-2.717 0l-9.8 9.8c-.75.75-.75 1.966 0 2.717L19.484 32 .563 50.921c-.75.75-.75 1.966 0 2.717l9.8 9.8c.75.75 1.966.75 2.717 0L32 44.516l18.921 18.921c.75.75 1.966.75 2.717 0l9.8-9.8c.75-.75.75-1.966 0-2.717L44.516 32l18.921-18.921a1.92 1.92 0 0 0 0-2.717z" data-original="#f74354" class=""></path></g></svg>';
                }

                 ?></td>
            </tr>
             <tr>
                <td><?=Yii::t('app','Uptime') ?></td>
                <td><?=$data['acctsessiontime'] ?></td>
            </tr>

             <tr style="background-color: #54c2c1 !important ;">
                <td><?=Yii::t('app','Download') ?></td>
                <td><?=$data['total_download'] ?> MB</td>
            </tr>
             <tr style="background-color: #c56dd3;">
                <td><?=Yii::t('app','Upload') ?></td>
                <td><?=$data['total_upload']  ?> MB </td>
            </tr>

             <tr>
                <td><?=Yii::t('app','NAS IP Address') ?></td>
                 <td><?=$data['nasipaddress'] ?></td>
            </tr>

             <tr>
                <td><?=Yii::t('app','MAC') ?></td>
                 <td><?=$data['mac_address'] ?></td>
            </tr>

             <tr>
               <?php if ( $user_inet_model->static_ip != "" ): ?>
                
               <td><?=Yii::t('app','Frameded IP Address (static)') ?></td>
                <td><?=$data['framedipaddress'] ?></td>
               
               <?php else: ?>
               <td><?=Yii::t('app','Frameded IP Address') ?></td>
               <td><?=$data['framedipaddress'] ?></td>
               <?php endif ?>
            </tr>


             <tr style="background-color: #54c2c1 !important;">
                <td><?=Yii::t('app','Total download - Montly') ?></td>
                <td><?=$data['montlyDownload']  ?> MB  </td>
            </tr>



             <tr style="background-color: #c56dd3 !important ;">
                <td><?=Yii::t('app','Total upload - Montly') ?></td>
                <td><?=$data['montlyUpload']  ?> MB  </td>
            </tr>


        </tbody>
    </table> 
<?php else: ?>
   <div style="text-align:center;padding: 0 10px;">
         <h5 style="margin-bottom:10px"><?=Yii::t("app","Router not connected network!") ?></h5>
         <svg viewBox="0 0 24 24" width="64" height="64" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
   </div>
<?php endif ?>
</div>




 <style type="text/css">

 .check-connection{max-width: 250px;max-width: 500px}
</style>     










