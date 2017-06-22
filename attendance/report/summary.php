<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../header.inc.php';
require_once '../traffic/traffic.class.php';
require_once '../baseinfo/shift.class.php';
require_once inc_reportGenerator;
require_once inc_dataGrid;

if(isset($_REQUEST["show"]))
{
	ShowReport();
}
		
function ShowReport(){
	
	if($_POST["FromDate"] == "")
	{
		$OrigStartDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] . "-01", "-");
		$OrigEndDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] ."-" . DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]), "-");
	}
	else
	{
		$OrigStartDate = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
		$OrigEndDate = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}

	$where = "";
	$param = array();
	if(!empty($_POST["PersonID"]))
	{
		$where .= " AND PersonID = ?";
		$param[] = $_POST["PersonID"];
	}
	$PersonsDT = PdoDataAccess::runquery("select PersonID, concat(fname,' ',lname) fullname from BSC_persons
		where IsStaff='YES' " . $where, $param);
	
	$returnStr = "";
	foreach($PersonsDT as $personRecord)
	{
		$SUM = ATN_traffic::Compute($OrigStartDate, $OrigEndDate, $personRecord["PersonID"], false);
		if($SUM === false)
		{
			echo ExceptionHandler::GetExceptionsToString();
			die();
		}
		$SUM["absence"] = TimeModules::SecondsToTime($SUM["absence"]);
		$SUM["attend"] = TimeModules::SecondsToTime($SUM["attend"] );
		$SUM["firstAbsence"] = TimeModules::SecondsToTime($SUM["firstAbsence"]);
		$SUM["lastAbsence"] = TimeModules::SecondsToTime($SUM["lastAbsence"]);
		$SUM["extra"] = TimeModules::SecondsToTime($SUM["extra"]);
		$SUM["Off"] = TimeModules::SecondsToTime($SUM["Off"]);
		$SUM["mission"] = TimeModules::SecondsToTime($SUM["mission"]);
		$SUM["LegalExtra"] = TimeModules::SecondsToTime($SUM["LegalExtra"]);
		$SUM["AllowedExtra"] = TimeModules::SecondsToTime($SUM["AllowedExtra"]);		
			
		$returnStr .= "<tr>
			<td>" . $personRecord["fullname"] . "</td>
			<td>" . TimeModules::ShowTime($SUM["attend"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["extra"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["LegalExtra"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["AllowedExtra"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["Off"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["mission"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["firstAbsence"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["lastAbsence"]) . "</td>
			<td>" . TimeModules::ShowTime($SUM["absence"]) . "</td>
			
			<td>" . $SUM["DailyOff_1"] . "</td>
			<td>" . $SUM["DailyOff_2"] . "</td>
			<td>" . $SUM["DailyOff_3"] . "</td>
			<td>" . $SUM["DailyMission"] . "</td>
			<td>" . $SUM["DailyAbsence"] . "</td>
		</tr>";
	}
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">
<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" /></head>
<style>
	.reportTbl {border-collapse:collapse}
	.reportTbl td {padding:4px;font-family: nazanin; font-size:14px; text-align: center}
	.reportTbl th {font-family: nazanin; font-size:14px;padding:4px;text-align: center; 
				  background-color: #efefef; font-weight: bold}
	.reportTbl .attend { text-align:center}
	.reportTbl .extra { background-color: #D0F7E2; text-align:center}
	.reportTbl .off { background-color: #D7BAFF; text-align:center}
	.reportTbl .mission { text-align:center}
	.reportTbl .sub { background-color: #FFcfdd; text-align:center}
	.reportTbl .footer { background-color: #eee; text-align:center; line-height: 18px}
</style>
<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'>
	<tr>
		<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
		<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
			گزارش خلاصه کارکرد پرسنل
			<br>از تاریخ <?= DateModules::miladi_to_shamsi($OrigStartDate) ?> تا تاریخ 
				<?= DateModules::miladi_to_shamsi($OrigEndDate) ?>
		</td>
		<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : 
			<?= DateModules::shNow() ?>
		</td>
	</tr>
</table>
<table class="reportTbl" width="100%" border="1">
	<tr class="blueText">
		<th>نام و نام خانوادگی</th>
		<th>حضور</th>
		<th>اضافه کار واقعی</th>
		<th>اضافه کار مجاز</th>
		<th>اضافه کار مجوز</th>
		<th>مرخصی</th>
		<th>ماموریت</th>
		<th>تاخیر</th>
		<th>تعجیل</th>
		<th>غیبت</th>
		<th>مرخصی استعلاجی</th>
		<th>مرخصی استحقاقی</th>
		<th>مرخصی بدون حقوق</th>
		<th>ماموریت روزانه</th>
		<th>غیبت روزانه</th>
	</tr>
	<?= $returnStr ?>
</table>
<?	
	die();
}

?>
<script>
ATN_SummaryReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",


	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function ATN_SummaryReport()
{
	this.mainPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		autoHeight : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش تردد",
		width : 800,
		items :[{
			xtype : "container",
			layout : "column",
			columns : 4,
			items :[{
				xtype : "combo",
				width : 300,
				fieldLabel : "انتخاب فرد",
				store: new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['PersonID','fullname']
				}),
				displayField: 'fullname',
				valueField : "PersonID",
				hiddenName : "PersonID"
			},{
				xtype : "combo",
				store: YearStore,   
				labelWidth : 30,
				width : 120,
				fieldLabel : "سال",
				displayField: 'title',
				valueField : "id",
				hiddenName : "year",
				value : '<?= substr(DateModules::shNow(),0,4) ?>'
			},{
				xtype : "combo",
				store: MonthStore,   
				labelWidth : 30,
				width : 120,
				fieldLabel : "ماه",
				displayField: 'title',
				valueField : "id",
				hiddenName : "month",
				value : '<?= substr(DateModules::shNow(),5,2)*1 ?>'
			},{
				xtype : "button",
				border : true,
				style : "margin-right:20px",
				text : "مشاهده گزارش",
				iconCls : "report",
				handler : function(){ ATN_SummaryReportObj.LoadReport(); }
			},{
				xtype : "shdatefield",
				name : "FromDate",
				fieldLabel : "از تاریخ"
			},{
				xtype : "shdatefield",
				name : "ToDate",
				fieldLabel : "تا تاریخ"
			}]
		},{
			xtype : "container",
			html : "<hr>",
			width : 780
		},{
			xtype : "container",
			colspan : 4,
			width : 780,
			itemId : "div_report"
		}]
	});
	
}

ATN_SummaryReport.prototype.LoadReport = function(){
	
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "summary.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;

}

ATN_SummaryReportObj = new ATN_SummaryReport();

ATN_SummaryReport.DeleteRender = function(v,p,r)
{
	if(r.data.TrafficID == null)
	{
		return r.data.ReqType == "MISSION" ? "ماموریت"  : "مرخصی";
	}
	if(r.data.IsActive == "YES" )
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='ATN_SummaryReportObj.DeleteTraffic();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:left;height:16'></div>";
}

ATN_SummaryReport.prototype.TrafficList = function(TrafficDate){
	
	this.grid.getStore().proxy.extraParams.TrafficDate = TrafficDate;
	this.grid.getStore().proxy.extraParams.PersonID = this.mainPanel.down("[hiddenName=PersonID]").getValue();
	if(!this.TraffficWin)
	{
		this.TraffficWin = new Ext.window.Window({
			width : 310,
			height : 290,
			modal : true,
			bodyStyle : "background-color:white",
			items : this.grid,
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.TraffficWin);
	}
	else
		this.grid.getStore().load();
	
	this.TraffficWin.show();
	this.TraffficWin.center();

}

ATN_SummaryReport.prototype.DeleteTraffic = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ATN_SummaryReportObj;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'traffic.data.php',
			params:{
				task: "DeleteTraffic",
				TrafficID : record.data.TrafficID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				ATN_SummaryReportObj.grid.getStore().load();
				ATN_SummaryReportObj.LoadReport();
			},
			failure: function(){}
		});
	});
}

</script>
<style>
	.link{
		cursor: pointer;
		color : blue;
	}
</style>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
	</center>
</form>
