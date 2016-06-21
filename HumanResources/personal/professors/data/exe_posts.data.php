<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/exe_posts.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once inc_manage_post;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectAll":
	      selectAll();

	case "save":
		save();

	case "delete":
		delete();
}

function selectAll()
{
	$where = " pe.staff_id = :staff_id ";
	$whereParam = array();
	$whereParam[":staff_id"] = $_POST["staff_id"];
	$where .=  dataReader::makeOrder(); 

	$temp = manage_professor_exe_posts::GetAll($where, $whereParam);
	
	echo dataReader::getJsonData ( $temp, count($temp), $_GET ["callback"] );
	die ();
}

function save()
{
	$obj = new manage_professor_exe_posts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	$postObj = new manage_posts($obj->post_id);
	if($postObj->post_id == "")
	{
		echo Response::createObjectiveResponse(false, "کد پست وارد شده معتبر نمی باشد.");
		die();
	}
		
	if ((  ($postObj->validity_start != ""  && $postObj->validity_start!= '0000-00-00' ) &&
			DateModules::CompareDate($postObj->validity_start,  str_replace("/","-",DateModules::shamsi_to_miladi($obj->from_date)) ) > 0)
		||

        (($postObj->validity_end != "" && $postObj->validity_end != '0000-00-00' ) &&
			    ( $obj->to_date != "" && $obj->to_date != "0000-00-00" ) &&
			   DateModules::CompareDate($postObj->validity_end ,  str_replace("/","-",DateModules::shamsi_to_miladi($obj->to_date))) < 0 ) )
	{
		echo Response::createObjectiveResponse(false, "پست انتخابی از نظر تاریخ اعتبار و تاریخ های شروع و پایان وارد شده معتبر نمی باشد.");
		die();
	}

	if(empty($obj->row_no))
		$return = $obj->ADD();
	else
		$return = $obj->Edit();

	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}

	if(isset($_POST["assign_post"]))
		$return = $obj->assign_post();
	else
		$return = $obj->release_post();

	echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
	die();
}

function delete()
{
	$ret = manage_professor_exe_posts::Remove($_POST["staff_id"], $_POST["row_no"]);
	echo Response::createObjectiveResponse($ret, implode(ExceptionHandler::PopAllExceptions()));
	die();
}
?>