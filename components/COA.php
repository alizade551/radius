<?php

namespace app\components;
use Yii;

class COA
{


	 public static function disconnect( $username , $server_port = "3799",  $shared_secret = "testing1234" )
	{
	    $model = \app\models\radius\Radacct::find()
	    ->select(['nasipaddress'])
	    ->where(['username' => $username])
	    ->orderBy(['radacctid' => SORT_DESC])
	    ->one();

	    if( $model != null ){
		    $server_ip =  $model['nasipaddress'];
	        $command = "echo \"User-Name=$username\" | radclient -x $server_ip:$server_port disconnect $shared_secret";
	        exec($command, $output, $return_var);
	    }
       
	}

	//  public static function connect( $username , $server_ip = "37.32.64.15", $server_port = "3799", $shared_secret = "testing1234" )
	// {
    //     $command = "echo \"User-Name=$username\" | radclient -x $server_ip:$server_port connect $shared_secret";
    //     exec($command, $output, $return_var);
  
	// }



}