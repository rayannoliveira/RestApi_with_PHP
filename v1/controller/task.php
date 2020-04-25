<?php

require_once('db.php');
require_once('../model/Task.php');
require_once('../model/Response.php');

	try{

	$writeDB= DB::connectWriteDB();
	$readDB= DB::connectReadDB();

	}
	catch(PDOException $ex){
	error_log("Connection error".$ex,0);
	$response= new Response();
	$response-> setSuccess(false);
	$response-> setHttpStatusCode(500);
	$response-> addMessage("Database connection error"); 
	$response->send();
	exit();
	}


if (array_key_exists("taskid", $_GET)) {
	$taskid= $_GET['taskid'];

	if ($taskid==''||!is_numeric($taskid)) {
		
		$response= new Response();
		$response-> setSuccess(false);
		$response-> setHttpStatusCode(400);
		$response-> addMessage("Task id cannot be blank or must be numeric"); 
		$response->send();
		exit;
	}

	if ($_SERVER['REQUEST_METHOD']=='GET') {

		try{

			$query=$readDB->prepare('select id,title,description, DATE_FORMAT(deadline," %d/%m/%Y %H:%i ") as deadline, completed from tbtask where id =:taskid');
			$query->bindParam(':taskid',$taskid,PDO::PARAM_INT);
			$query->execute();

			$rowCount=$query->rowCount();
			if ($rowCount==0) {
				
				$response= new Response();
				$response-> setSuccess(false);
				$response-> setHttpStatusCode(404);
				$response-> addMessage("Task not found"); 
				$response->send();
				exit;
			}

			while ($row=$query->fetch(PDO::FETCH_ASSOC)) {
				$task = new Task($row['id'],$row['title'],$row['description'],$row['edadline'],$row['completed']);
				$taskArray[]=$task->returnTaskArray();
			}

			$returnData= array();
			$returnData['rows_returned']=$rowCount;
			$returnData['task']=$taskArray;

			$response= new Response();
			$response->setHttpStatusCode(200);
			$response->setSuccess(true);
			$response->toCache(true);
			$response->setData($returnData);
			$response->send();
			exit;
		}
		catch(TaskException $ex){

			$response= new Response();
			$response->setHttpStatusCode(500);
			$response->setSuccess(false);
			$response->addMessage($ex->getMessage()); 
			$response->send();
			exit;
		}
		catch(PDOException $ex){
			error_log("Database query error".$ex,0);
			$response= new Response();
			$response-> setSuccess(false);
			$response-> setHttpStatusCode(500);
			$response-> addMessage("Faild to get text"); 
			$response->send();
			exit;
		}	
	}

	elseif ($_SERVER['REQUEST_METHOD']=='DELETE') {

		try{

			$query=$writeDB->prepare('delete from tbtask where id=:taskid');
			$query->bindParam(':taskid',$taskid,PDO::PARAM_INT);
			$query->execute();
			$rowCount=$query->rowCount();

			if ($rowCount==0) {

				$response= new Response();
				$response-> setSuccess(false);
				$response-> setHttpStatusCode(404);
				$response-> addMessage("Task not found"); 
				$response->send();
				exit;
			}
				$response= new Response();
				$response-> setSuccess(true);
				$response-> setHttpStatusCode(200);
				$response-> addMessage("Task delete"); 
				$response->send();
				exit;


		}
		catch(PDOException $ex){
			$response= new Response();
			$response-> setSuccess(false);
			$response-> setHttpStatusCode(500);
			$response-> addMessage("Faild to delete"); 
			$response->send();
			exit;
		}
		
	}
	elseif($_SERVER['REQUEST_METHOD']=='PATCH'){

	}
	else{
		$response= new Response();
		$response-> setSuccess(false);
		$response-> setHttpStatusCode(405);
		$response-> addMessage("Request method not allowed"); 
		$response->send();
		exit();
	}

}

elseif (array_key_exists("completed", $_GET)) {
 	$completed=$_GET['completed'];

 	if ($completed!=='Y' && $completed!=='N') {
 		$response= new Response();
 		$response->setHttpStatusCode(400);
 		$response->setSuccess(false);
 		$response->addMessage("completed filter must be Y or N");
 		$response->send();
 		exit;

 	}
 	if ($_SERVER['REQUEST_METHOD']=='GET') {
 		
 		try{

 			$query=$readDB->prepare('select id, title, DATE_FORMAT(deadline,"%d/%m/%Y %H:%i") as deadline, completed from tbtask where completed= :completed');

 			$query->bindParam(':completed', $completed, PDO::PARAM_STR);
 			$query->execute();

 			$rowCount=$query->rowCount();

 			$taskArray= array();

 			while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
 				$task= new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);

 				$taskArray[]=$task->returnTaskArray();
 			}

 			$returnData= array();
 			$returnData['rows_returned']=$rowCount;
 			$returnData['tasks']=$taskArray;

 			$response= new Response();
 			$response->setHttpStatusCode(200);
 			$response->setSuccess(true);
 			$response->toCache(true);
 			$response->setData($returnData);
 			$response->send();
 			exit;


 		}
 		catch(TaskException $ex){

 			$response= new Response();
 			$response->setHttpStatusCode(500);
 			$response->setSuccess(false);
 			$response->addMessage($ex->getMessage());
 			$response->send();
 			exit;

 		}catch(PDOException $ex){
 			error_log("Database query error".$ex,0);
 			$response= new Response();
 			$response->setHttpStatusCode(500);
 			$response->setSuccess(false);
 			$response->addMessage("Failed to get task");
 			$response->send();
 			exit;
 		}

 	}
 	else{
 		$response= new Response();
 		$response->setHttpStatusCode(405);
 		$response->setSuccess(false);
 		$response->addMessage("Request not allowed");
 		$response->send();
 		exit;
 	}
 } 

elseif (empty($_GET)) {
	
	if ($_SERVER['REQUEST_METHOD']=='GET') {
		
		try{

			$query=$readDB->prepare('select id,title, description, DATE_FORMAT(deadline,"%d/%m/%Y %H:%i") as deadline, completed from tbtask');
		$query->execute();

		$rowCount=$query->rowCount();
		$taskArray=array();

		while ($row=$query->fetch(PDO::FETCH_ASSOC)) {
			$task= new Task($row['id'],$row['title'],$row['description'],$row['deadline'],$row['completed']);
			$taskArray[]=$task->returnTaskArray();
		}

		$returnData=array();
		$returnData['rows_returned']=$rowCount;
		$returnData['tasks']=$taskArray;

		$response=new Response();
		$response->setHttpStatusCode(200);
		$response->setSuccess(true);
		$response->toCache(true);
		$response->setData($returnData);
		$response->send();
		exit;

		}
		catch(TaskException $ex){
			$response=new Response();
			$response->setHttpStatusCode(500);
			$response->setSuccess(false);
			$response->addMessage($ex->getMessage());
			$response->send();
			exit;
		}
		catch(PDOException $ex){
			error_log("Database query error ".$ex,0);
			$response=new Response();
			$response->setHttpStatusCode(405);
			$response->setSuccess(false);
			$response->addMessage("Request method not allowed");
			$response->send();

		}
	}
	elseif ($_SERVER['REQUEST_METHOD']=='POST') {

		try{

			if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
				$response=new Response();
				$response->setHttpStatusCode(400);
				$response->setSuccess(false);
				$response->addMessage("Content type header is not set to json");
				$response->send();
				exit;
			}

			$rawPOSTData= file_get_contents('php://input');

			if (!$jsonData=json_decode($rawPOSTData)) {
				
				$response=new Response();
				$response->setHttpStatusCode(400);
				$response->setSuccess(false);
				$response->addMessage("Request body is not valid JSON");
				$response->send();
				exit;
			}

			if (!isset($jsonData->title) || !isset($jsonData->completed)) {
				$response=new Response();
				$response->setHttpStatusCode(400);
				$response->setSuccess(false);
				(!isset($jsonData->title) ? $response->addMessage("Title field is mandatory and must be provided") : false);
				(!isset($jsonData->completed) ? $response->addMessage("Completed field is mandatory and must be provided") : false);
				$response->send();
				exit; 
			}

			$newTask= new Task(null, $jsonData->title, (isset($jsonData->description)? $jsonData->description : null), (isset($jsonData->deadline) ? $jsonData->deadline : null), $jsonData->completed);

			$title= $newTask->getTitle();
			$description=$newTask->getDescription();
			$deadline=$newTask->getDeadline();
			$completed=$newTask->getCompleted();

			$query=$writeDB->prepare('insert into tbtask 
				(title, description, deadline, completed) values 
				(:title,:description, STR_TO_DATE(:deadline,\'%d/%m/%Y %H:%i\'), :completed)');

			$query->bindParam(':title',$title,PDO::PARAM_STR);
			$query->bindParam(':description',$description,PDO::PARAM_STR);
			$query->bindParam(':deadline',$deadline,PDO::PARAM_STR);
			$query->bindParam(':completed',$completed,PDO::PARAM_STR);
			$query->execute(); 

			$rowCount=$query->rowCount();


			if ($rowCount==0) {

				$response=new Response();
				$response->setHttpStatusCode(500);
				$response->setSuccess(false);
				$response->addMessage("Faild to creat task");
				$response->send();
				exit;
			}

			$lastTaskId= $writeDB->lastInsertId();

			$query= $readDB->prepare('select id, title, description, DATE_FORMAT(deadline,"%d/%m/%Y %H:%i") as deadline,completed from tbtask where id= :taskid');
			$query->bindParam(':taskid',$lastTaskId,PDO::PARAM_INT);
			$query->execute();

			$rowCount=$query->rowCount();

			if ($rowCount==0) {
				$response=new Response();
				$response->setHttpStatusCode(500);
				$response->setSuccess(false);
				$response->addMessage("Faild to creat a retrive task after");
				$response->send();
				exit;
			}

			$taskArray=array();

			while ($row= $query->fetch(PDO::FETCH_ASSOC)) {
				$task= new Task($row['id'],$row['title'],$row['description'],$row['deadline'],$row['completed']);

				$taskArray[]=$task->returnTaskArray();
			}

			$returnData=array();
			$returnData['rows_returned']=$rowCount;
			$returnData['tasks']=$taskArray;

			$response=new Response();
			$response->setHttpStatusCode(201);
			$response->setSuccess(true);
			$response->addMessage("Task created");
			$response->setData($returnData);
			$response->send();
			exit;
		}
		catch(TaskException $ex){

			$response=new Response();
			$response->setHttpStatusCode(400);
			$response->setSuccess(false);
			$response->addMessage($ex->getMessage());
			$response->send();
			exit;
		}
		catch(PDOException $ex){
			error_log("Database erro".$ex,0);
			$response=new Response();
			$response->setHttpStatusCode(500);
			$response->setSuccess(false);
			$response->addMessage("Faild to insert into database");
			$response->send();
			exit;
		}

	
	}
	else{
		$response=new Response();
		$response->setHttpStatusCode(405);
		$response->setSuccess(false);
		$response->addMessage("Request method not allowed");
		$response->send();
		exit;
	}


}
elseif (array_key_exists("page", $_GET)) {
	if ($_SERVER['REQUEST_METHOD']=='GET') {

		$page=$_GET['page'];

		if ($page == ''|| !is_numeric($page)) {
			$response= new Response();
			$response->setHttpStatusCode(404);
			$response->setSuccess(false);
			$response->addMessage("Page number canot be blank must be numeric");
			$response->send();
			exit;
		}

		$limitPerPage=20;

		try{

			$query= $readDB->prepare('select count(id) as totalTask from tbtask');
			$query->execute();

			$row= $query->fetch(PDO::FETCH_ASSOC);
			$taskCount= intval($row['totalTask']);

			$numPages= ceil($taskCount/$limitPerPage);

			if ($numPages==0) {
				$numPages=1;
			}

			if ($page > $numPages) {

				$response= new Response();
				$response->setHttpStatusCode(400);
				$response->setSuccess(false);
				$response->addMessage("Page not found");
				$response->send();
				exit;

			}

			$offset= ($page == 1 ? 0 : ($limitPerPage*($page-1)));

			$query=$readDB->prepare('select id, title,description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i")as deadline, completed from tbtask limit :pglimit offset :offset');
			$query->bindParam(':pglimit',$limitPerPage, PDO::PARAM_INT);
			$query->bindParam(':offset',$offset, PDO::PARAM_INT);
			$query->execute();


			$rowCount=$query->rowCount();

			$taskArray=array();

			while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

				$task= new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
				
				$taskArray[]=$task->returnTaskArray();
			}

			$returnData=array();
			$returnData['rows_returned']=$rowCount;
			$returnData['total_rows']=$taskCount;
			$returnData['total_pages']=$numPages;
			($page < $numPages ? $returnData['has_next_page'] = true : $returnData['has_next_page'] = false );
			($page > 1 ? $returnData['has_previous_page'] = true : $returnData['has_previous_page'] = false );

			$returnData['task']=$taskArray;

			$response= new Response();
			$response->setHttpStatusCode(200);
			$response->setSuccess(true);
			$response->toCache(true);
			$response->setData($returnData);
			$response->send();
			exit;



		}catch(TaskException $ex){
			$response= new Response();
			$response->setHttpStatusCode(500);
			$response->setSuccess(false);
			$response->addMessage($ex->getMessage());
			$response->send();
			exit;

		}
		catch(PDOException $ex){
			error_log("Database error".$ex,0);
			$response= new Response();
			$response->setHttpStatusCode(500);
			$response->setSuccess(false);
			$response->addMessage("Faild to get tasks");
			$response->send();
			exit;
		}

	}
	else{
		$response= new Response();
		$response->setHttpStatusCode(405);
		$response->setSuccess(false);
		$response->addMessage("Request method not allowed");
		$response->send();
		exit;
	}
}
else{
	$response=new Response();
	$response->setHttpStatusCode(404);
	$response->setSuccess(false);
	$response->addMessage("Endpoint not Found");
	$response->send();
	exit;
}




?>