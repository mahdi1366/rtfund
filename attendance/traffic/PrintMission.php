<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.03
//-------------------------
include('../../header.inc.php');
require_once "traffic.class.php";

if(empty($_REQUEST["RequestID"]))
	die();

$obj = new ATN_requests($_REQUEST["RequestID"]);
if(empty($obj->RequestID))
	die();

?>
<head>
	<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
	<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
	<style>
		.page {
			width: 130mm;
			height: 190mm;
			border: 1px solid black;
			border-collapse: collapse;
			margin-top: 20px;
			font-family: nazanin;
			font-size: 14px;
		}
		.page td{
			border: 1px solid black;
			padding: 4px;
		}
		
		.page th{
			font-family: nazanin;
			font-size: 14px;
			font-weight: normal;
		}
		
	</style>
</head>
<body dir="rtl">
<center>
	<table class="page">
		<tr>
			<th width="120px"><img style=width:120px src="/framework/icons/logo.jpg" /></th>
			<th colspan="2" align="center" style="font-size: 18px;font-family: titr">برگه ماموریت اداری</th>
			<th width="170px">شماره درخواست : <b><?= $obj->RequestID ?></b>
				<br>تاریخ درخواست : <b><?= DateModules::miladi_to_shamsi($obj->ReqDate) ?></b></th>
		</tr>
		<tr>
			<td>واحد اعزام کننده : </td>
			<td colspan="3"><b><?= SoftwareName ?></b></td>
		</tr>
		<tr>
			<td>نام و نام خانوادگی مامور : </td>
			<td colspan="3"><b><?= $obj->_fullname ?></b></td>
		</tr>
		<tr>
			<td>محل ماموریت :</td>
			<td colspan="3"><b><?= $obj->MissionPlace ?></b></td>
		</tr>
		<tr>
			<td>موضوع ماموریت :</td>
			<td colspan="3"><b><?= $obj->MissionSubject ?></b></td>
		</tr>
		<tr>
			<td>از تاریخ : </td>
			<td><b><?= DateModules::miladi_to_shamsi($obj->FromDate) ?></b></td>
			<td>تا تاریخ :</td>
			<td><b><?= DateModules::miladi_to_shamsi($obj->ToDate) ?></b></td>
		</tr>
		<tr>
			<td>وسیله نقلیه رفت : </td>
			<td width="120px"><b><?= $obj->_GoMeanDesc ?></b></td>
			<td width="110px" >وسیله نقلیه برگشت :</td>
			<td><b><?= $obj->_ReturnMeanDesc ?></b></td>
		</tr>
		<tr>
			<td>توضیحات : </td>
			<td colspan="3"><?= $obj->details ?></td>
		</tr>
		<tr>
			<td colspan="4">
				<br><br>
				<span style="float:left;text-align: center;margin-left:20px;font-family: titr;font-size: 14px">
				مقام تایید کننده : مهدی مروی
				<br>مدیر عامل
				</span>
				<br><br><br><br>
			</td>
		</tr>
		<tr>
			<td colspan="4">تاریخ و ساعت چاپ : <?= DateModules::shNow() ?> <?= DateModules::CurrentTime() ?></td>
		</tr>
	</table>
</center>
</body>