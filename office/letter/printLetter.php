<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once 'letter.class.php';

$LetterID = !empty($_REQUEST["LetterID"]) ? $_REQUEST["LetterID"] : "";
if(empty($LetterID))
	die();

$LetterObj = new OFC_letters($LetterID);
//..............................................................................
$content = "<b><span style=font-size:11px>";
$dt = PdoDataAccess::runquery("
	select  p1.sex,FromPersonID,
		if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) FromPersonName ,
		if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) ToPersonName 
	from OFC_send s
		join OFC_letters l using(LetterID)
		join BSC_persons p1 on(s.FromPersonID=p1.PersonID)
		join BSC_persons p2 on(s.ToPersonID=p2.PersonID)
	where LetterID=? 
	order by SendID
	", array($LetterID));
foreach($dt as $row)
{
	if($row["FromPersonID"] != $LetterObj->PersonID)
		break;	
	$content .= $row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ";
	$content .= $row['ToPersonName'] . "<br>";
}

$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></span></b>";
$content .= str_replace("\r\n", "", $LetterObj->context);
$content .= "<br><br>";
?>
<html>
	<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
	<style media="print">
		.noPrint {display:none;}
	</style>
	<style>
		.header td{background-color: #cccccc; font-weight: bold;size: 12px;}
		td { font-family: tahoma; font-size: 12px; line-height: 20px; padding: 3px;}
	</style>
	<body dir="rtl">
		<center>
			<div style="width:800px;border:1px solid #ccc;">
				<table width="100%">
					<tr>
						<td>
							<div style="padding-top:20px;float:left;height:50px;width:200px;text-align:center">
								شماره نامه : <b> <?= $LetterObj->LetterID ?></b><br>
								تاریخ نامه : <b><?= DateModules::miladi_to_shamsi($LetterObj->LetterDate) ?></b>
							</div>
						</td>
					</tr>
					<tr>
						<td align="center">
							<b>بسمه تعالی</b><br><br>&nbsp;
						</td>
					</tr>
					<tr>
						<td style="padding-right:40px;padding-left: 40px;">
							<?= $content ?>
						</td>
					</tr>
					<tr>
						<td>
							<div style="float:left;width:400px;text-align:center">
								<b><?= $dt[0]["FromPersonName"] ?></b><br><br>&nbsp;
							</div>
						</td>
					</tr>
				</table>
			</div>
		</center>
	</body>
</html>