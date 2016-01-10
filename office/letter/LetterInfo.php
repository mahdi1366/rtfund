<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once 'letter.class.php';

$LetterID = !empty($_POST["LetterID"]) ? $_POST["LetterID"] : "";
if(empty($LetterID))
	die();

$LetterObj = new OFC_letters($LetterID);

$content = "<br><div style=margin-left:30px;float:left; >تاریخ نامه : " . 
	DateModules::miladi_to_shamsi($LetterObj->LetterDate) . "<br> شماره نامه : " .
	$LetterObj->LetterID . "</div><br><br>";

$content .= "<b>";
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

$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br>";

$content .= str_replace("\r\n", "", $LetterObj->context);

$content .= "<br><br><div align=left style=width:80%><b>" . $dt[0]["FromPersonName"] . "</b></div>";
?>

<script>

LetterInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : '<?= $LetterID ?>',
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LetterInfo(){
	
	this.tabPanel = new Ext.TabPanel({
		renderTo: this.get("mainForm"),
		activeTab: 0,
		plain:true,
		autoHeight : true,
		width: 750,
		height : 550,
		defaults:{
			autoHeight: true, 
			autoWidth : true            
		},
		items:[{
			title : "متن نامه",
			height : 550,
			items :[{
				xtype : "container",
				autoScroll: true,
				cls : "LetterContent",
				html : "<?= $content ?>"
			}]
			
		},{
			title : "تصاویر نامه",
			style : "padding:0 20px 0 20px"			
		}]
	});	
}

LetterInfoObject = new LetterInfo();

</script>
<style>
	.LetterContent {
		padding:20px;
		margin:10px; 
		height : 500;
		font-size : 11px;
		line-height: 20px;
		border: 1px solid #bbb;
		border-radius: 10px;
		background: -webkit-linear-gradient(left, #fff 50%, #eee); /* For Safari 5.1 to 6.0 */
		background: -o-linear-gradient(right, #fff 50%, #eee); /* For Opera 11.1 to 12.0 */
		background: -moz-linear-gradient(right, #fff 50%, #eee); /* For Firefox 3.6 to 15 */
		background: linear-gradient(right, #fff 50%, #eee); /* Standard syntax */
		box-shadow: 0 4px 4px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.19);
	} 
</style>
	<br>
	<div style="margin-right : 20px" id="mainForm"></div>