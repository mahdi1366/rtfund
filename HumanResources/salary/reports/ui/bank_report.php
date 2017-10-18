<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	95.07
//---------------------------

require_once("../../../header.inc.php");
ini_set("display_errors", "On");

if (isset($_REQUEST["show"])) {

	$keys = array_keys($_POST);
	$WhereCost = $WherePT = $WhereBT = $WhereEmpstate = "";
	$arr = "";

	//...................... مراکز هزینه ................

	for ($i = 0; $i < count($_POST); $i++) {


		if (strpos($keys[$i], "chkEmpState_") !== false) {
			$arr = preg_split('/_/', $keys[$i]);
			if (isset($arr[1]))
				$WhereEmpstate .= ($WhereEmpstate != "") ? "," . $arr[1] : $arr[1];
		}
	}


	$query = "		
				SELECT  p.staff_id, p.pay_year,p.pay_month, pr.pfname ,pr.plname , s.account_no , 					   
						SUM(if( sit.effect_type = 1 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0 )) PayVal , 
						SUM(if( sit.effect_type = 2 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0 )) GetVal
						
				FROM HRM_payments p inner join HRM_payment_items pit
										on  p.pay_year =  pit.pay_year and
											p.pay_month =  pit.pay_month and
											p.staff_id =  pit.staff_id and
											p.payment_type =  pit.payment_type

									inner join HRM_staff s
										on p.staff_id = s.staff_id

									inner join HRM_persons pr
										on pr.PersonID = s.PersonID
										
									INNER JOIN HRM_salary_item_types sit 
										ON (pit.salary_item_type_id = sit.salary_item_type_id)

				where p.pay_year = " . $_POST['pay_year'] . " and p.pay_month = " . $_POST['pay_month'] . " and p.payment_type = " . $_POST['PayType'] . "

				group by p.staff_id ,p.pay_year,p.pay_month ";


	$dataTable = PdoDataAccess::runquery($query);
	
	?>
	<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#4D7094}
		.reportGenerator .header1 {color: white;font-weight: bold;background-color:#465E86}		
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
	</style>
	<?
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';

	//.........................................
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:50%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' width='100px' ></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست پرداختی بانک" .
	"<br><br> " . DateModules::GetMonthName($dataTable[0]['pay_month']) . ' ماه &nbsp;&nbsp;' . $dataTable[0]['pay_year'] . " </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : "
	. DateModules::shNow() . "<br>";
	echo "</td></tr></table>";



	echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">			
			 <tr class="header">								
			 <td>شماره شناسایی </td>	
			 <td>نام</td> 
			 <td>نام خانوادگی</td>
			 <td>شماره حساب</td>
			 <td>مبلغ قابل پرداخت</td>
			 </tr>';

	$Total=0;
	for ($i = 0; $i < count($dataTable); $i++) {

		echo " <tr>					
					<td>" . $dataTable[$i]['staff_id'] . "</td> 
					<td>" . $dataTable[$i]['pfname'] . "</td>	
					<td>" . $dataTable[$i]['plname'] . "</td>
					<td>" . $dataTable[$i]['account_no'] . "</td>	
					<td>" . number_format(( $dataTable[$i]['PayVal'] - $dataTable[$i]['GetVal'] ), 0, '.', ',') . "</td>						
				</tr>";
				
		$Total+= ($dataTable[$i]['PayVal'] - $dataTable[$i]['GetVal']); 
	}	

	echo " <tr>					
				<td colspan='4'>جمع:</td> 				
				<td>" . number_format($Total, 0, '.', ',') . "</td>						
			</tr>";
				
	
		
	echo "</table>";

	die();
}
?>

<script>
	BankList.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	BankList.prototype.showReport = function(type)
	{
		
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "bank_report.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";	
		this.form.submit();	
		return;
	}
		
	function BankList()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivBankList'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش لیست بانک",
			fieldDefaults: {
				labelWidth: 110
			},
			layout: {
				type: 'table',
				columns: 2
			},
			items :[{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال ",
					name : "pay_year",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه ",
					name : "pay_month",
					allowBlank : false,
					width : 200
				},
				{
					xtype: 'fieldset',
					title : "وضعیت استخدامی",
					colspan : 3,
					style:'background-color:#DFEAF7',					
					width : 700,						
					fieldLabel: 'Auto Layout',
					itemId : "chkgroup2",	
					collapsible: true,
					collapsed: true,
					layout : {
						type : "table",
						columns : 4,
						tableAttrs : {
							width : "100%",
							align : "center"
						},
						tdAttrs : {							
							align:'right',
							width : "۱6%"
						}
					},
					items : [{
							xtype : "checkbox",
							boxLabel : "همه",
							checked : true,							
							listeners : {
								change : function(){
									parentNode = BankListObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for(i=0; i<elems.length; i++)
									{
										if(elems[i].id.indexOf("chkEmpState_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
				},																		
				{
					xtype : "combo",
					colspan:3,
					store :  new Ext.data.Store({
						fields : ["InfoID","InfoDesc"],
						proxy : {
							type: 'jsonp',
							url : this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
							reader: {
								root: 'rows',
								totalProperty: 'totalCount'
							}
						}
						,
						autoLoad : true,
						listeners:{
							load : function(){
								BankListObject.filterPanel.down("[itemId=PayType]").setValue("1");										
							}
						}
								
					}),
					valueField : "InfoID",
					displayField : "InfoDesc",
					hiddenName : "PayType",
					itemId : "PayType",
					fieldLabel : "نوع پرداخت&nbsp;",						
					listConfig: {
						loadingText: 'در حال جستجو...',
						emptyText: 'فاقد اطلاعات',
						itemCls : "search-item"
					},
					width:300
				}		
					
			],
			buttons: [{
					text : "مشاهده گزارش",                                           
					iconCls : "report",
					handler: function(){BankListObject.showReport('show');}
				}
			]			
		});
		
		
		new Ext.data.Store({
			fields : ["InfoID","InfoDesc"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchEmpState",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						BankListObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.InfoDesc
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var BankListObject = new BankList();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivBankList"></div>	
	</form>
</center>
