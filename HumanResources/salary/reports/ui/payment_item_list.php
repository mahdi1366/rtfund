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
					select  hp.pfname , hp.plname , pit.pay_year, pit.pay_month,
							31 item1 ,
							SUM(if( sit.salary_item_type_id = 1 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item2 ,
							SUM(if( sit.salary_item_type_id = 2 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item3 ,
							SUM(if( sit.salary_item_type_id = 9 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item4 ,
							SUM(if( sit.salary_item_type_id = 4 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item5 ,
							SUM(if( sit.salary_item_type_id = 5 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item6 ,
							SUM(if( sit.salary_item_type_id = 6 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item7 ,
							0  item8 ,
							0  item9 ,
							SUM(if( sit.salary_item_type_id = 7 , (pit.param1 + pit.diff_param1) , 0  )) item10 ,
							0 item11 /*حق سنوات*/,
							SUM(if( sit.salary_item_type_id = 8 , (pit.param1 + pit.diff_param1) , 0  )) item12 ,
							0 item13 ,
							SUM(if( sit.salary_item_type_id = 7 , (pit.param2 + pit.diff_param2) , 0  )) item14 ,
							SUM(if( sit.salary_item_type_id = 7 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0  )) item15 ,
							SUM(if( sit.salary_item_type_id = 8 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0  )) item16 ,
							0 item17  ,
							0 item18

					from HRM_payment_items pit
							INNER JOIN HRM_salary_item_types sit ON (pit.salary_item_type_id = sit.salary_item_type_id)
							INNER JOIN HRM_payments p ON (  pit.pay_year = p.pay_year AND pit.pay_month = p.pay_month AND pit.staff_id = p.staff_id AND
															pit.payment_type = p.payment_type)
							INNER JOIN HRM_writs w ON ( p.writ_id = w.writ_id AND
														p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id )
							INNER JOIN HRM_staff s ON s.staff_id = pit.staff_id
							INNER JOIN HRM_persons hp ON hp.PersonID = s.PersonID

					
					where   pit.pay_year >= " . $_POST['from_pay_year'] . " AND
							pit.pay_year <= " . $_POST['to_pay_year'] . " AND
							pit.pay_month >= " . $_POST['from_pay_month'] . " AND					
							pit.pay_month <= " . $_POST['to_pay_month'] . " AND			
							pit.payment_type = " . $_POST['PayType'];

					$query .= ($WhereEmpstate != "" ) ? " AND w.emp_state in (" . $WhereEmpstate . ") " : "";


					$query .= "  group by hp.personID , pay_year, pay_month 
								 order by hp.personID ";

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
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست حقوق  و دستمزد ماهانه" .
	"<br><br> " . DateModules::GetMonthName($dataTable[0]['pay_month']) . ' ماه &nbsp;&nbsp;' . $dataTable[0]['pay_year'] . " </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : "
	. DateModules::shNow() . "<br>";
	echo "</td></tr></table>";



	echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
			
			 <tr class="header">					
			 <td>نام و نام خانوادگی</td>	
			 <td>کارکرد روز</td>
			 <td>حقوق پایه</td>
			 <td>فوق العاده جذب</td>
			 <td>پایه سنواتی</td>
			 <td>تفاوت تطبیق</td>
			 <td>اقلام مصرفی</td>
			 <td>حق مسکن</td>
			 <td>اضافه کار</td>
			 <td>مبلغ اضافه کار</td>
			 <td>جمع مشمول کسر بیمه</td>
			 <td>حق سنوات</td>
			 <td>جمع مشمول مالیات</td>
			 <td>حق ماموریت</td>
			 <td>بیمه سهم کارفرما</td>
			 <td>بیمه سهم مجری</td>
			 <td>مالیات</td>
			 <td>بیمه تکمیلی</td>
			 <td>اقساط</td>
			 <td>جمع کسورات</td>
			 <td>قابل پرداخت</td>			 
			 </tr>';


	$Item_1 = $Item_2 = $Item_3 = $Item_4 = $Item_5 = $Item_6 = $Item_7 = $Item_8 = $Item_9 = $Item_10 = 0;
	$Item_11 = $Item_12 = $Item_13 = $Item_14 = $Item_15 = $Item_16 = $Item_17 = $Item_18 = $Item_19 = $Item_20 = 0;
	$getVal = $payVal = 0 ; 
	$PY = $dataTable[0]['pay_year'];
	$PM = $dataTable[0]['pay_month'];

	for ($i = 0; $i < count($dataTable); $i++ ) {
		
		 $getVal = $dataTable[$i]['item15'] + $dataTable[$i]['item15'] + $dataTable[$i]['item15'] + $dataTable[$i]['item15'] ; 
		 $payVal = $dataTable[$i]['item2'] + $dataTable[$i]['item3'] + $dataTable[$i]['item4'] + 
							 $dataTable[$i]['item5'] + $dataTable[$i]['item6'] + $dataTable[$i]['item7'] + 
							 $dataTable[$i]['item9'] + $dataTable[$i]['item11'] + $dataTable[$i]['item13']  ; 
		echo " <tr>					
					<td>" . $dataTable[$i]['pfname'].' '.$dataTable[$i]['plname']. "</td> 
					<td>" . $dataTable[$i]['item1'] . "</td>	
					<td>" . number_format($dataTable[$i]['item2'] ,0, '.', ',') . "</td>					
					<td>" . number_format($dataTable[$i]['item3'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item4'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item5'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item6'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item7'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item8'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item9'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item10'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item11'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item12'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item13'] ,0, '.', ',') . "</td>			
					<td>" . number_format($dataTable[$i]['item14'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item15'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item16'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item17'] ,0, '.', ',') . "</td>		
					<td>" . number_format($dataTable[$i]['item18'] ,0, '.', ',') . "</td>	
					<td>" . number_format($getVal ,0, '.', ',') . "</td>	
					<td>" . number_format(($payVal - $getVal),0, '.', ',') . "</td>
				</tr>";
		
			$Item_1 += $dataTable[$i]['item1'];
			$Item_2 += $dataTable[$i]['item2'];
			$Item_3 += $dataTable[$i]['item3'];
			$Item_4 += $dataTable[$i]['item4'];
			$Item_5 += $dataTable[$i]['item5'];
			$Item_6 += $dataTable[$i]['item6'];
			$Item_7 += $dataTable[$i]['item7'];
			$Item_8 += $dataTable[$i]['item8'];
			$Item_9 += $dataTable[$i]['item9'];
			$Item_10 += $dataTable[$i]['item10'];
			$Item_11 += $dataTable[$i]['item11'];
			$Item_12 += $dataTable[$i]['item12'];
			$Item_13 += $dataTable[$i]['item13'];
			$Item_14 += $dataTable[$i]['item14'];
			$Item_15 += $dataTable[$i]['item15'];
			$Item_16 += $dataTable[$i]['item16'];
			$Item_17 += $dataTable[$i]['item17'];
			$Item_18 += $dataTable[$i]['item18'];
			$Item_19 += $getVal;
			$Item_20 += $payVal - $getVal ;
			$getVal = $payVal = 0 ;  
	}
	echo '<tr>
			<td>جمع : </td>
			<td>'.$Item_1.'</td>
			<td>' . number_format($Item_2, 0, '.', ',') . '</td>
			<td>' . number_format($Item_3, 0, '.', ',') . '</td>
			<td>' . number_format($Item_4, 0, '.', ',') . '</td>
			<td>' . number_format($Item_5, 0, '.', ',') . '</td>
			<td>' . number_format($Item_6, 0, '.', ',') . '</td>
			<td>' . number_format($Item_7, 0, '.', ',') . '</td>
			<td>' . number_format($Item_8, 0, '.', ',') . '</td>
			<td>' . number_format($Item_9, 0, '.', ',') . '</td>
			<td>' . number_format($Item_10, 0, '.', ',') . '</td>
			<td>' . number_format($Item_11, 0, '.', ',') . '</td>
			<td>' . number_format($Item_12, 0, '.', ',') . '</td>
			<td>' . number_format($Item_13, 0, '.', ',') . '</td>
			<td>' . number_format($Item_14, 0, '.', ',') . '</td>
			<td>' . number_format($Item_15, 0, '.', ',') . '</td>
			<td>' . number_format($Item_16, 0, '.', ',') . '</td>
			<td>' . number_format($Item_17, 0, '.', ',') . '</td>
			<td>' . number_format($Item_18, 0, '.', ',') . '</td>
			<td>' . number_format($Item_19, 0, '.', ',') . '</td>
			<td>' . number_format($Item_20, 0, '.', ',') . '</td>							
		  </tr>';
	
	echo "</table>";
	echo '<br><table style="border:2px groove #9BB1CD;border-collapse:collapse;width:100%">
	<tr style="height:200px">
	<td> &nbsp; مدیر مالی</td>
	<td> &nbsp; مدیر عامل</td>
	<td> &nbsp; رییس هیءت مدیره</td> </tr></table></center>' ; 
	die();
}
?>

<script>
	PayItemList.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	PayItemList.prototype.showReport = function(type)
	{
		
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "payment_item_list.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";	
		this.form.submit();	
		return;
	}
		
	function PayItemList()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش لیست حقوق به تفکیک قلم حقوقی",
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
					fieldLabel : "سال از",
					name : "from_pay_year",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه از",
					name : "from_pay_month",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال تا",
					name : "to_pay_year",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه تا",
					name : "to_pay_month",
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
									parentNode = PayItemListObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
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
								PayItemListObject.filterPanel.down("[itemId=PayType]").setValue("1");										
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
					handler: function(){PayItemListObject.showReport('show');}
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
						PayItemListObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.InfoDesc
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var PayItemListObject = new PayItemList();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div>	
	</form>
</center>
