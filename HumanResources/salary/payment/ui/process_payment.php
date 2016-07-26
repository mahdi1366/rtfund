<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// Date:		90.01
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataReader;
require_once inc_manage_unit;

$drp_units = manage_units::DRP_Units("org_units","","","210","(parent_ouid='' or parent_ouid is null)");
$drp_personTypes = manage_domains::DRP_PersonType("person_type","","width:90");

$drp_month = manage_domains::DRP_months("tax_normalized_month");
?>
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<?php
		jsConfig::initialExt();
		jsConfig::date();
	?>
	<script type="text/javascript" src="/HumanResources/global/LOV/LOV.js"></script>

	<script type="text/javascript">
	Ext.onReady(function(){

		this.field = new Ext.form.TriggerField({
		    triggerCls:'x-form-search-trigger',
		    onTriggerClick : function(){
		    	this.setValue(LOV_PersonID());
		    },
		    applyTo : "from_PersonID",
		    width : 90
		});

		this.field = new Ext.form.TriggerField({
		    triggerCls:'x-form-search-trigger',
		    onTriggerClick : function(){
		    	this.setValue(LOV_PersonID());
		    },
		    applyTo : "to_PersonID",
		    width : 90
		});

		new Ext.Panel({
			id: "j1",
			applyTo: "issuePayment_DIV",
			contentEl : "issuePayment_TBL",
			title: "محاسبه حقوق",
			autoHeight: true,
			width: "750px",
			collapsible : true,
			frame: true,
			bodyCfg: {style : "padding-right:10px;background-color:white;"},
			buttons: [{
				text : "محاسبه حقوق",
				iconCls : "refresh",
				handler : function(){
					IssuePayment();
				}
			}]
		});

		new Ext.form.SHDateField({
			id: 'ext_start_date',
			applyTo: 'start_date',
			format: 'Y/m/d'
		});

		new Ext.form.SHDateField({
			id: 'ext_end_date',
			applyTo: 'end_date',
			format: 'Y/m/d'
		});
	});

	function IssuePayment()
	{
		var form = document.getElementById('mainForm');
		mask = new Ext.LoadMask(document.body, {msg:'در حال محاسبه حقوق ...'});
		mask.show();

		Ext.Ajax.request({
			url: '../data/payment.data.php',
			params:{
				task : "ProcessPayment"
			},
			method: 'POST',
			form: form,

			success: function(response,option){
				mask.hide();
				//Ext.getCmp("j1").collapse();

				document.getElementById("result").innerHTML = response.responseText;
			},
			failure: function(){}
		});
	}
	</script>
<?
print_r(ExceptionHandler::PopAllExceptions());
ExceptionHandler::showExceptionPanel('result'); ?>
</head>
<body dir="rtl">
	<form method="post" id="mainForm">
		<center>
		<br>
		<div id="issuePayment_DIV" style="width: 750px">
			<table id="issuePayment_TBL" style="width: 100%">
				<tr>
					<td width="20%">واحد محل خدمت :</td>
					<td width="30%"><?= $drp_units ?></td>
					<td width="20%">جستجو در زیر واحد ها :</td>
					<td width="30%"><input type="checkbox" name="sub_units"></td>
				</tr>
				<tr>
					<td>نوع افراد :</td>
					<td><?= $drp_personTypes ?></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>شماره شناسایی از :</td>
					<td><input type="text" name="from_PersonID" id="from_PersonID"></td>
					<td>تا :</td>
					<td><input type="text" name="to_PersonID" id="to_PersonID" ></td>
				</tr>
				<tr>
					<td>از تاریخ :</td>
					<td><input type="text" name="start_date" id="start_date"></td>
					<td>تا تاریخ :</td>
					<td><input type="text" name="end_date" id="end_date"></td>
				</tr>
				<tr>
					<td>از کد مرکز هزینه :</td>
					<td><input type="text" name="from_cost_center_id" id="from_cost_center_id" class="x-form-text x-form-field"></td>
					<td>تا :</td>
					<td><input type="text" name="to_cost_center_id" id="to_cost_center_id" class="x-form-text x-form-field"></td>
				</tr>
				<tr>
					<td>شروع تعديل ماليات از سال:</td>
					<td><input type="text" name="tax_normalized_year" id="tax_normalized_year" class="x-form-text x-form-field"></td>
					<td>شروع تعديل ماليات از ماه:</td>
					<td><?= $drp_month?></td>
				</tr>
				<tr>
					<td>backpay محاسبه شود ؟</td>
					<td><input type="checkbox" name="compute_backpay" id="compute_backpay" ></td>
					<td>تعديل ماليات انجام شود؟</td>
					<td><input type="checkbox" name="tax_normalize" id="tax_normalize" ></td>
				</tr>
				<tr>
					<td>فيشهاي منفي :</td>
					<td colspan="3"><input type="checkbox" name="negative_fiche" id="negative_fiche" >
					محاسبه فيشهاي منفي (هشدار! اين گزينه صرفا جهت كنترل فيشهاي منفي گذاشته شده است. لطفا در استفاده از آن دقت نماييد.)
					</td>					
				</tr>
				<tr>
					<td>پيام :</td>
					<td colspan="3"><textarea id="message" name="message" class="x-form-field" style="width:90%"></textarea></td>
				</tr>
			</table>
		</div>

		<!-- ------------------------------------------------------------ -->
		<div id="result" class="panel" style="width: 750px" align="right"></div>
		<!-- ------------------------------------------------------------ -->
		</center>
	</form>
</body>
</html>