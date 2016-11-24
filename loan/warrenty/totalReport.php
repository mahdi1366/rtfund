<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.05
//-----------------------------

require_once '../header.inc.php';
require_once 'config.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function moneyRender($row, $val) {
		return number_format($val);
	}
	
	function MakeWhere(&$where, &$whereParam){
		
		foreach($_POST as $key => $value)
		{
			if($key == "excel" || $value === "" || strpos($key, "combobox") !== false)
				continue;
			$prefix = "";
			switch($key)
			{
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
	
	//.....................................
	$where = "1=1";
	$whereParam = array();
	MakeWhere($where, $whereParam);
	
	$query = "select r.* , concat_ws(' ',fname,lname,CompanyName) fullname, sp.StepDesc,
				bf.InfoDesc TypeDesc,d.DocID, d.DocStatus 
			from WAR_requests r 
				left join BSC_persons using(PersonID)
				left join BaseInfo bf on(bf.TypeID=74 AND InfoID=r.TypeID)
				join WFM_FlowSteps sp on(sp.FlowID=" . FLOWID . " AND sp.StepID=r.StatusID)
				left join ACC_DocItems on(SourceType='" . DOCTYPE_WARRENTY . "' 
					AND SourceID=r.RequestID AND SourceID2=r.ReqVersion)	
				left join ACC_docs d using(DocID)
			where " . $where . " group by r.RequestID";
	
	
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = $dataTable;
	
	$rpg->addColumn("شماره تضمین", "RequestID");
	$rpg->addColumn("نوع تضمین", "TypeDesc");	
	$rpg->addColumn("تاریخ شروع", "StartDate", "dateRender");
	$rpg->addColumn("تاریخ پایان", "EndDate", "dateRender");
	$rpg->addColumn("مبلغ", "amount", "moneyRender");
	$rpg->addColumn("مشتری", "fullname");
	$rpg->addColumn("سازمان مربوطه", "organization");
	$rpg->addColumn("کارمزد", "wage");
	$rpg->addColumn("شماره نامه معرفی", "LetterNo");
	$rpg->addColumn("تاریخ نامه معرفی", "LetterDate");
	
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
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
	$rpg->generateReport();
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
			width : 300
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش کلی تضمین ها",
		width : 650,
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
			allowBlank : false,
			valueField : "PersonID",
			hiddenName : "PersonID"
		},{
			xtype : "textfield",
			name : "organization",
			allowBlank : false,
			fieldLabel : "سازمان مربوطه",
			colspan : 2
		},{
			xtype : "currencyfield",
			name : "FromAmount",
			hideTrigger : true,
			allowBlank : false,
			fieldLabel : "مبلغ ضمانت نامه از"
		},{
			xtype : "currencyfield",
			name : "ToAmount",
			hideTrigger : true,
			allowBlank : false,
			fieldLabel : "مبلغ ضمانت نامه تا"
		},{
			xtype : "shdatefield",
			name : "FromStartDate",
			allowBlank : false,
			fieldLabel : "تاریخ شروع از"
		},{
			xtype : "shdatefield",
			name : "ToStartDate",
			allowBlank : false,
			fieldLabel : "تاریخ شروع تا"
		},{
			xtype : "shdatefield",
			name : "FromEndDate",
			allowBlank : false,
			fieldLabel : "تاریخ پایان از"
		},{
			xtype : "shdatefield",
			name : "ToEndDate",
			allowBlank : false,
			fieldLabel : "تاریخ پایان تا"
		},{
			xtype : "shdatefield",
			allowBlank : false,
			fieldLabel : "تاریخ نامه معرفی از",
			name : "FromLetterDate"
		},{
			xtype : "shdatefield",
			allowBlank : false,
			fieldLabel : "تاریخ نامه معرفی تا",
			name : "ToLetterDate"
		},{
			xtype : "textfield",
			allowBlank : false,
			fieldLabel : "شماره نامه معرفی",
			name : "LetterNo"
		},{
			xtype : "numberfield",
			allowBlank : false,
			fieldLabel : "کارمزد",
			name : "wage",
			width : 150,
			afterSubTpl : "%",
			hideTrigger : true
		}],
		buttons : [{
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