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
$letterYear = substr(DateModules::miladi_to_shamsi($LetterObj->LetterDate),0,4);
//..............................................................................
$content = "<b><span style=font-size:11px>";
$dt = PdoDataAccess::runquery("
	select  p1.sex,FromPersonID,p3.PersonSign signer, p1.PersonSign regSign,
		if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) RegPersonName ,
		if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) ToPersonName ,
		concat(p3.fname, ' ', p3.lname) SignPersonName 
	from OFC_send s
		join OFC_letters l using(LetterID)
		join BSC_persons p1 on(l.PersonID=p1.PersonID)
		join BSC_persons p2 on(s.ToPersonID=p2.PersonID)
		left join BSC_persons p3 on(l.SignerPersonID=p3.PersonID)
	where LetterID=? 
	order by SendID
	", array($LetterID));
if($LetterObj->LetterType == "INNER")
{
	foreach($dt as $row)
	{
		if($row["FromPersonID"] != $LetterObj->PersonID)
			break;	
		$content .= $row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ";
		$content .= $row['ToPersonName'] . "<br>";
	}

	$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></span></b>";
	$content .= str_replace("\r\n", "", $LetterObj->context);
	
	if(isset($_POST["sign"]))
		$sign = $dt[0]["regSign"] != "" ? "background-image:url('" .
			data_uri($dt[0]["regSign"],'image/jpeg') . "')" : "";
	else
		$sign = "";
	
	$content .= "<div class=signDiv style=\"" . $sign . "\"><b>" . 
			$dt[0]["RegPersonName"] . "</b></div>";
}
if($LetterObj->LetterType == "OUTCOME")
{
	$content .= $LetterObj->organization . "<br>" ;
	$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></b>";
	$content .= str_replace("\r\n", "", $LetterObj->context);
	
	if(isset($_POST["sign"]))
		$sign = $LetterObj->IsSigned == "YES" ? "background-image:url('" .
			data_uri($dt[0]["signer"],'image/jpeg') . "')" : "";
	else
		$sign = "";
	$content .= "<div class=signDiv style=\"" . $sign . "\"><b>" . 
			$dt[0]["SignPersonName"] . "</b></div>";
	
	
}
?>
<html>
	<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
	<style media="print">
		.noPrint {display:none;}
	</style>
	<style>
		.header td{background-color: #cccccc; font-weight: bold;size: 12px;}
		td { font-family: tahoma; font-size: 12px; line-height: 20px; padding: 3px;}
		.signDiv {
			height: 140px;
			float : left;
			background-repeat: no-repeat; 
			width: 200px; 
			text-align: center; 
			padding-top: 60px;
		}
	</style>	
	<body dir="rtl">
		<center>
			<div class="noPrint" style="width:500px;font-family: tahoma; font-size: 12px;">
				<form method="post" id="mainForm">
					<input onchange="document.forms.mainForm.submit()" name="sign" 
						   <?= isset($_POST["sign"]) ? "checked" : "" ?>
						   type="checkbox" > چاپ امضاء
				<br><input onchange="document.forms.mainForm.submit()" name="sarbarg" 
							<?= isset($_POST["sarbarg"]) ? "checked" : "" ?>
						   type="checkbox"> چاپ روی برگه سربرگ دار			
				</form>
			</div>
			<? if(isset($_POST["sarbarg"])){ ?>
				<div style="width:800px;height:1100px;">
				<table style="width:100%;height:100%">
					<tr style="height:150px">
						<td align="center">
						</td>
						<td align="center">
						</td>
						<td style="width:146px;line-height: 32px; vertical-align:top; padding-top:33px">
							 <b><span dir=ltr><?= $letterYear . "-" . $LetterObj->LetterID ?></span></b><br>
							<b><?= DateModules::miladi_to_shamsi($LetterObj->LetterDate) ?></b>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-right:50px;padding-left: 50px;vertical-align: top;">
							<br><br>
							<?= $content ?>
							<br>
						</td>
					</tr>
				</table>
			</div>
			<?}else{?>
				<div>
				<table style="width:100%;height:100%">
					<tr style="height:150px">
						<td align="center" style="width:200px;">
							<img  src="/framework/icons/logo.jpg" style="width:150px">
						</td>
						<td align="center" style="font-family: b titr;font-size: 14px;">
							<b>بسمه تعالی</b>
						</td>
						<td style="width:200px;line-height: 25px;">
							شماره نامه : <b>  <?= "<span dir=ltr>" . $letterYear . "-" . $LetterObj->LetterID."</span>" ?></b><br>
							تاریخ نامه : <b><?= DateModules::miladi_to_shamsi($LetterObj->LetterDate) ?></b>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-right:50px;padding-left: 50px;vertical-align: top;">
							<br><br>
							<?= $content ?>
							<br>
						</td>
					</tr>
					<tr style="height:150px;">
						<td colspan="3" style="padding-right:30px;padding-left: 30px;">
							<hr>
							<b>نشانی :</b><br>
							<b>شعبه پارک علم و فناوری خراسان : </b> مشهد، کیلومتر 12 جاده قوچان، روبروی شیر پگاه
							<b> تلفن : 5003441 - فکس : 5003409 </b>
							<br>
							<b>شعبه دانشگاه فردوسی مشهد : </b>پردیس، درب غربی( ورودی شهید باهنر ) 
							<b>تلفن : 38837392 - فکس : 38837392</b>
							<br>سایت : <b>www.krfn.ir</b>
							<br>ایمیل : <b>krfn.ir@gmail.com</b>
						</td>
					</tr>
				</table>
			</div>
			<?}?>
			
		</center>
	</body>
</html>