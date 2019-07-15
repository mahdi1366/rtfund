<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once "header.inc.php";
require_once '../framework/person/persons.class.php';

$personObj = new BSC_persons($_SESSION["USER"]["PersonID"]);
if($personObj->IsActive == "PENDING")
{
	header("location: ../process/registration.php?ExtTabID=" . $_REQUEST["ExtTabID"]);
	die();
}

$IsStaff = $_SESSION["USER"]["IsStaff"] == "YES";
$IsCustomer = $_SESSION["USER"]["IsCustomer"] == "YES";
$IsShareholder = $_SESSION["USER"]["IsShareholder"] == "YES";
$IsAgent = $_SESSION["USER"]["IsAgent"] == "YES";
$IsSupporter = $_SESSION["USER"]["IsSupporter"] == "YES";

if($IsCustomer)
{
	$dt = PdoDataAccess::runquery("select * from LON_requests 
		where StatusID in(40,60) AND LoanPersonID=? 
		AND (ReqPersonID<>LoanPersonID)",
		array($_SESSION["USER"]["PersonID"]));
	$LoanShow = count($dt) > 0;
}

//-------------- letters ---------------
require_once 'global/global.data.php';
$list = CustomerLetters(true);
$letterShow = count($list) > 0;

//--------------- news -----------------
require_once '../framework/management/framework.class.php';
$temp = FRW_news::Get(" AND substr(now(),1,10)>= StartDate  AND substr(now(),1,10)<= EndDate");
$temp = $temp->fetchAll();
$returnStr = "";
foreach($temp as $row)
{
	/*$returnStr .= '<a href="javascript:void()" onclick="StartPageObject.ShowNews(' . $row["NewsID"] . ')" >' . 
		'&nbsp;&nbsp;<font style="color:#17C5FF;font-size:11px">◄</font>&nbsp;' . ($row["NewsTitle"]) . "</a><br>"; */
	
	$returnStr .= '<font style="color:#17C5FF;">◄&nbsp;' . $row["NewsTitle"] . 
			"</font><div style='width:100%;padding-right:20px'> ".$row["context"]."</div><br>"; 
}
?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	IsStaff :		<?= $IsStaff ? "true" : "false" ?>,
	IsCustomer :	<?= $IsCustomer ? "true" : "false" ?>,
	IsShareholder : <?= $IsShareholder ? "true" : "false" ?>,
	IsAgent :		<?= $IsAgent ? "true" : "false" ?>,
	IsSupporte :	<?= $IsSupporter ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	new Ext.panel.Panel({
		applyTo : this.get("div_news"),
		title : "اعلان ها",
		frame : true,
		contentEl : this.get("div_newsContent"),
		width: 750,
		border : false,
		autoHeight : true,
		style : "margin-bottom:10px;text-align:right;",
		bodyStyle : "padding:10px"
	});
	
	if(<?= $letterShow ? "true" : "false"?>)
	{
		new Ext.panel.Panel({
			renderTo : this.get("div_letters"),
			width: 770,
			maxHeight : 500,
			border : false,
			//autoHeight : true,
			style : "margin-bottom:10px",
			loader : {
				url : "global/letters.php",
				params : {
					ExtTabID : this.TabID
				},
				scripts : true,
				autoLoad : true
			}
		});
	}
	if(<?= $LoanShow ? "true" : "false"?>)
	{
		new Ext.panel.Panel({
			renderTo : this.get("div_loans"),
			width: 770,
			border : false,
			autoHeight : true,
			style : "margin-bottom:10px",
			loader : {
				url : "/loan/request/MyRequests.php?mode=customer&MenuID=0",
				params : {
					ExtTabID : this.TabID
				},
				scripts : true,
				autoLoad : true
			}
		});
	}
	
	new Ext.panel.Panel({
		renderTo : this.get("div_dashboard"),
		width: 770,
		border : false,
		autoHeight : true,
		style : "margin-bottom:10px",
		loader : {
			url : "/framework/ReportDB/dashboard.php?DashboardType=customer",
			params : {
				ExtTabID : this.TabID
			},
			scripts : true,
			autoLoad : true
		}
	});
	
	
}

StartPageObject = new StartPage();

StartPage.prototype.ShowNews = function(NewsID){
	
	if(!this.ContextWin)
	{
		this.ContextWin = new Ext.window.Window({
			width : 500,			
			height : 400,
			modal : true,
			bodyStyle : "background-color:white;padding:10px",
			closeAction : "hide",
			loader : {
				url : "/framework/management/ShowNews.php"				
			}
		});
		Ext.getCmp(this.TabID).add(this.ContextWin);
	}

	this.ContextWin.show();
	this.ContextWin.center();
	this.ContextWin.loader.load({
		params : {
			NewsID : NewsID
		}
	});
}

StartPage.prototype.OpenLoan = function(RequestID){

	portal.OpenPage("/loan/request/RequestInfo.php", {RequestID : RequestID});
}

</script>
<center>
	<?
		if($_SESSION["USER"]["IsActive"] == "PENDING")
		{
			echo "<br><br>" . "امکانات پرتال زمانی برای شما فعال خواهد گردید که ثبت نام شما توسط صندوق تایید گردد.";
		}
	?>
	<div><div id="div_news">
			<div id="div_newsContent"><?= $returnStr ?></div>
		</div></div>
	<div><div id="div_loans"></div></div>
	<div><div id="div_letters"></div></div>
	<div><div id="div_dashboard"></div></div>
</center>