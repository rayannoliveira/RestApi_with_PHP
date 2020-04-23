<?php


require_once('DB.php');
require_once('../model/Response.php');


try{

	$writeDB= DB::connectWriteDB();
	$readDB= DB::connectReadDB();

}
catch(PDOExeption $ex){
	$response= new Response();
	$response->setHttpStatusCode(500);
	$response->setSuccess(false);
	$response->addMessage("Database conecction error"); 
	$response->send();
	exit;
}


?>