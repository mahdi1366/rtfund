<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once 'letter.class.php';
require_once inc_dataReader;
require_once '../dms/dms.class.php';

$LetterID = !empty($_GET["LetterID"]) ? $_GET["LetterID"] : "";
if(empty($LetterID))
	die();

$LetterObj = new OFC_letters($LetterID);
//..............................................................................

$letterYear = substr(DateModules::miladi_to_shamsi($LetterObj->LetterDate),0,4);

$content = "<br><div style=margin-left:30px;float:left; >شماره نامه : " . 
	"<span dir=ltr>" . $letterYear . "-" . $LetterObj->LetterID . "</span>". 
	"<br>تاریخ نامه : " . DateModules::miladi_to_shamsi($LetterObj->LetterDate);

if($LetterObj->LetterType == "INCOME")
{
	$content .= "<br>شماره نامه وارده : " . $LetterObj->InnerLetterNo;
	$content .= "<br>تاریخ نامه وارده : " . DateModules::miladi_to_shamsi($LetterObj->InnerLetterDate);
}

if($LetterObj->RefLetterID != "")
{
	$refObj = new OFC_letters($LetterObj->RefLetterID);
	$RefletterYear = substr(DateModules::miladi_to_shamsi($refObj->LetterDate),0,4);
	$content .= "<br>عطف به نامه : <a href=javascript:void(0) onclick=LetterInfo.OpenRefLetter(" . 
		$LetterObj->RefLetterID . ")>".
		"<span dir=ltr>" . $RefletterYear . "-" . $LetterObj->RefLetterID. "</span></a>";
}
$content .= "</div><br><br>";

$content .= "<b><br><div align=center>بسمه تعالی</div><br>";
$dt = PdoDataAccess::runquery("
	select  p2.sex,FromPersonID,p3.PersonSign signer, p1.PersonSign regSign,
		if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) RegPersonName ,
		if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) ToPersonName ,
		concat(p3.fname, ' ', p3.lname) SignPersonName ,
		po.PostName,
		s.IsCopy
	from OFC_send s
		join OFC_letters l using(LetterID)
		join BSC_persons p1 on(l.PersonID=p1.PersonID)
		join BSC_persons p2 on(s.ToPersonID=p2.PersonID)
		left join BSC_persons p3 on(l.SignerPersonID=p3.PersonID)
		left join BSC_posts po on(l.SignPostID=po.PostID)
	where LetterID=? 
	order by SendID
	", array($LetterID));

if($LetterObj->LetterType == "INNER")
{
	foreach($dt as $row)
	{
		if($row["FromPersonID"] != $LetterObj->PersonID || $row["IsCopy"] == "YES")
			continue;	
		$content .= $row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ";
		$content .= $row['ToPersonName'] . "<br>";
	}
	
	$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></b>";
	$content .= str_replace("\r\n", "", $LetterObj->context);
	
	$sign = $dt[0]["regSign"] != "" ? "background-image:url(\"" .
			data_uri($dt[0]["regSign"],'image/jpeg') . "\")" : "";
	
	$content .= "<table width=100%><tr><td><div class=signDiv style=" . $sign . "><b>" . 
			$dt[0]["RegPersonName"] . "</b><br><br>" . $dt[0]["PostName"] . "</div></td></tr></table>";
}
if($LetterObj->LetterType == "OUTCOME")
{
	$content .= $LetterObj->OrgPost . " " . $LetterObj->organization . "<br>" ;
	$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></b>";
	$content .= str_replace("\r\n", "", $LetterObj->context);
	
	$sign = $LetterObj->IsSigned == "YES" && $dt[0]["signer"] != "" ? 
			"background-image:url(\"" . data_uri($dt[0]["signer"],'image/jpeg') . "\")" : "";
	
	$content .= "<table width=100%><tr><td><div class=signDiv style=" . $sign . "><b>" . 
			$dt[0]["SignPersonName"] . "</b><br><br>" . $dt[0]["PostName"] . "</div></td></tr></table>";
}
foreach($dt as $row)
{
	if($row["FromPersonID"] != $LetterObj->PersonID || $row["IsCopy"] == "NO")
		continue;	
	$content .= "<b> رونوشت : " . ($row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ") . 
			$row['ToPersonName'] . "<br></b>";
}

if($LetterObj->OuterCopies != "")
{
	$LetterObj->OuterCopies = str_replace("\r\n", " , ", $LetterObj->OuterCopies);
	$content .= "<br><b> رونوشت خارج از سازمان : " . $LetterObj->OuterCopies . "</b><br>";
}

echo $content;
?>