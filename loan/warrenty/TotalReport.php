<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.05
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

$page_rpg = new ReportGenerator("mainForm","WarrentyReport_totalObj");
$page_rpg->addColumn("شماره تضمین", "RequestID");
$page_rpg->addColumn("نوع تضمین", "TypeDesc");	
$col = $page_rpg->addColumn("تاریخ شروع", "StartDate");
$col->type = "date";
$col = $page_rpg->addColumn("تاریخ پایان", "EndDate");
$col->type = "date";
$page_rpg->addColumn("مبلغ ضمانتنمه", "amount");
$page_rpg->addColumn("مشتری", "fullname");
$page_rpg->addColumn("سازمان مربوطه", "organization");
$page_rpg->addColumn("کارمزد", "wage");
$page_rpg->addColumn("شماره نامه معرفی", "LetterNo");
$col = $page_rpg->addColumn("تاریخ نامه معرفی", "LetterDate");
$col->type = "date";
$page_rpg->addColumn("وضعیت", "StepDesc");
$page_rpg->addColumn("نسخه", "version");

function MakeWhere(&$where, &$whereParam){
		
		foreach($_POST as $key => $value)
		{
			if($key == "excel" || 
					$value === "" || strpos($key, "combobox") !== false || strpos($key, "rpcmp") !== false ||
					strpos($key, "reportcolumn_fld") !== false || strpos($key, "reportcolumn_ord") !== false)
				continue;
			$prefix = "";
			switch($key)
			{
				case "PersonID":
					$prefix = "r.";
					break;
				case "FromStartDate":
				case "ToStartDate":
				case "FromEndDate":
				case "ToEndDate":
				case "FromLetterDate":
				case "ToLetterDate":
					$value = DateModules::shamsi_to_miladi($value, "-");
					break;
				case "FromAmount":
				case "ToAmount":
					$value = preg_replace('/,/', "", $value);
					break;
			}
			if(strpos($key, "From") === 0)
				$where .= " AND " . $prefix . substr($key,4) . " >= :$key";
			else if(strpos($key, "To") === 0)
				$where .= " AND " . $prefix . substr($key,2) . " <= :$key";
			else
				$where .= " AND " . $prefix . $key . " = :$key";
			$whereParam[":$key"] = $value;
		}
	}	
		
function GetData(){
	
	$where = "1=1";
	$whereParam = array();
	$userFields = ReportGenerator::UserDefinedFields();
	MakeWhere($where, $whereParam);
	
	$query = "select r.* , concat_ws(' ',fname,lname,CompanyName) fullname, sp.StepDesc,
				bf.InfoDesc TypeDesc ".
				($userFields != "" ? "," . $userFields : "")
				."
			from WAR_requests r 
				left join BSC_persons using(PersonID)
				left join BaseInfo bf on(bf.TypeID=74 AND InfoID=r.TypeID)
				join WFM_FlowSteps sp on(sp.FlowID=" . WARRENTY_FLOWID . " AND sp.StepID=r.StatusID)
				
			where " . $where;
	
	$group = ReportGenerator::GetSelectedColumnsStr();
	$query .= $group == "" ? " group by r.RequestID" : " group by " . $group;
	$query .= $group == "" ? " order by r.RequestID" : " order by " . $group;
	
	return PdoDataAccess::runquery($query, $whereParam);
}

function ListDate($IsDashboard = false){
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = GetData();
	
	$rpg->addColumn("شماره تضمین", "RequestID");
	$rpg->addColumn("نوع تضمین", "TypeDesc");	
	$rpg->addColumn("تاریخ شروع", "StartDate", "ReportDateRender");
	$rpg->addColumn("تاریخ پایان", "EndDate", "ReportDateRender");
	$col = $rpg->addColumn("مبلغ ضمانتنمه", "amount", "ReportMoneyRender");
	$col->EnableSummary();
	$rpg->addColumn("مشتری", "fullname");
	$rpg->addColumn("سازمان مربوطه", "organization");
	$rpg->addColumn("کارمزد", "wage");
	$rpg->addColumn("شماره نامه معرفی", "LetterNo");
	$rpg->addColumn("تاریخ نامه معرفی", "LetterDate", "ReportDateRender");
	$rpg->addColumn("وضعیت", "StepDesc");
	$rpg->addColumn("نسخه", "version", "RefReasonRender");
	
	function RefReasonRender($row, $value){
		switch($value)
		{
			case "MAIN" : return "اصل";
			case "EXTEND" : return "تمدید";
			case "CHANGE" : return "متمم";
		}
	}
	
	if(!$rpg->excel && !$IsDashboard)
	{
		BeginReport();
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش کلی تضمین ها
				</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromReqDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromReqDate"] . 
				($_POST["toReqDate"] != "" ? " - " . $_POST["toReqDate"] : "");
		}
		echo "</td></tr></table>";
	}
	
	if($IsDashboard)
	{
		echo "<div style=direction:rtl;padding-right:10px>";
		$rpg->generateReport();
		echo "</div>";
	}
	else
		$rpg->generateReport();
	die();
}

if(isset($_REQUEST["show"]))
{
	ListDate();	
}

if(isset($_REQUEST["rpcmp_chart"]))
{
	$page_rpg->mysql_resource = GetData();
	$page_rpg->GenerateChart();
	die();
}

if(isset($_REQUEST["dashboard_show"]))
{
	$chart = ReportGenerator::DashboardSetParams($_REQUEST["rpcmp_ReportID"]);
	if(!$chart)
		ListDate(true);	
	
	$page_rpg->mysql_resource = GetData();
	$page_rpg->GenerateChart(false, $_REQUEST["rpcmp_ReportID"]);
	die();	
}

?>
<script>
WarrentyReport_total.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

WarrentyReport_total.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "TotalReport.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function WarrentyReport_total()
{		
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		defaults : {
			width : 365
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش کلی ضمانت نامه ها",
		width : 780,
		items :[{
			xtype : "combo",
			store : new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'request.data.php?task=GetWarrentyTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ["InfoID", "InfoDesc"],
				autoLoad : true
			}),
			displayField: 'InfoDesc',
			valueField : "InfoID",
			hiddenName : "TypeID",
			allowBlank : false,
			fieldLabel : "نوع ضمانت نامه"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserType=IsCustomer",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "مشتری",
			displayField : "fullname",
			pageSize : 20,
			valueField : "PersonID",
			hiddenName : "PersonID"
		},{
			xtype : "textfield",
			name : "organization",
			fieldLabel : "سازمان مربوطه",
			colspan : 2
		},{
			xtype : "currencyfield",
			name : "FromAmount",
			hideTrigger : true,
			fieldLabel : "مبلغ ضمانت نامه از"
		},{
			xtype : "currencyfield",
			name : "ToAmount",
			hideTrigger : true,
			fieldLabel : "مبلغ ضمانت نامه تا"
		},{
			xtype : "shdatefield",
			name : "FromStartDate",
			fieldLabel : "تاریخ شروع از"
		},{
			xtype : "shdatefield",
			name : "ToStartDate",
			fieldLabel : "تاریخ شروع تا"
		},{
			xtype : "shdatefield",
			name : "FromEndDate",
			fieldLabel : "تاریخ پایان از"
		},{
			xtype : "shdatefield",
			name : "ToEndDate",
			allowBlank : false,
			fieldLabel : "تاریخ پایان تا"
		},{
			xtype : "shdatefield",
			fieldLabel : "تاریخ نامه معرفی از",
			name : "FromLetterDate"
		},{
			xtype : "shdatefield",
			fieldLabel : "تاریخ نامه معرفی تا",
			name : "ToLetterDate"
		},{
			xtype : "textfield",
			fieldLabel : "شماره نامه معرفی",
			name : "LetterNo"
		},{
			xtype : "numberfield",
			fieldLabel : "کارمزد",
			name : "wage",
			width : 150,
			afterSubTpl : "%",
			hideTrigger : true
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				data : [
					[100 , "خام" ],
					[110 , "تایید شده" ],
					[120 , "خاتمه یافته" ],
					[130 , "ابطال شده" ]
				],
				fields : ['id','value']
			}),
			fieldLabel : "وضعیت",
			displayField : "value",
			valueField : "id",
			hiddenName : "StatusID"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				data : [
					['MAIN' , "اصل" ],
					['EXTEND' , "تمدید" ],
					['CHANGE' , "متمم" ]
				],
				fields : ['id','value']
			}),
			fieldLabel : "نسخه ضمانتنامه",
			displayField : "value",
			valueField : "id",
			hiddenName : "version"
		},{
			xtype : "fieldset",
			colspan :2,
			width : 730,
			title : "ستونهای گزارش",
			items :[<?= $page_rpg->ReportColumns() ?>]
		},{
			xtype : "fieldset",
			colspan :2,
			width : 730,
			title : "رسم نمودار",
			items : [<?= $page_rpg->GetChartItems("WarrentyReport_totalObj","mainForm","TotalReport.php") ?>]
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						WarrentyReport_totalObj, 
						<?= $_REQUEST["MenuID"] ?>,
						"mainForm",
						"formPanel"
						);}
		},'->',{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					WarrentyReport_totalObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				WarrentyReport_totalObj.formPanel.getForm().reset();
				WarrentyReport_totalObj.get("mainForm").reset();
			}			
		}]
	});
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		WarrentyReport_totalObj.showReport();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
}

WarrentyReport_totalObj = new WarrentyReport_total();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>