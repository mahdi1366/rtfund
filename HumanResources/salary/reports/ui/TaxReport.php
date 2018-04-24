<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.12
//---------------------------

require_once("../../../header.inc.php");

require_once 'excel.php';
require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

if(isset($_REQUEST["excel"]) && $_REQUEST["excel"] == "true"){
				
		header("Content-type: application/ms-excel");
		header("Content-disposition: inline; filename=excel.xls");
	}

if (isset($_REQUEST["show"]))
{	

	$keys = array_keys($_POST);
	$WhereCost = $WherePT = $WhereEmpstate = "" ;
	$arr = "" ;
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkcostID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WhereCost .= ($WhereCost!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
		
		
		if(strpos($keys[$i],"chkEmpState_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereEmpstate .= ($WhereEmpstate!="") ?  ",".$arr[1] : $arr[1] ;
		}	
		 
		
	}
	
	if(isset($_POST['PT_1']) && $_POST['PT_1']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,1 " :  "1 " ; 
	
	if(isset($_POST['PT_2']) && $_POST['PT_2']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,2 " :  "2 " ; 
	
	if(isset($_POST['PT_3']) && $_POST['PT_3']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,3 " :  "3 " ; 
	
	if(isset($_POST['PT_5']) && $_POST['PT_5']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,5 " :  "5" ; 
	
	if(isset($_POST['PT_10']) && $_POST['PT_10']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,10 " :  "10" ; 
	
	
	$pament_type = $_POST['PayType'];
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	
	//..........................................................................
	
	$query = ' select  p.staff_id ,pr.pfname , pr.plname ,o1.ptitle mainUnit ,o2.ptitle subUnit ,bi.Title maghta , po.title postTitle,j.title jobTitle ,
					   sum(if(sit.effect_type = 1 ,(pit.pay_value + diff_pay_value * diff_value_coef ),0)) rast ,
sum(if(pit.salary_item_type_id = 10364 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_1 ,
 sum(if(pit.salary_item_type_id = 10365 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_2 ,
 sum(if(pit.salary_item_type_id = 10366 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_3 ,
 sum(if(pit.salary_item_type_id = 10367 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_4 ,
 sum(if(pit.salary_item_type_id = 10369 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_5 ,
 sum(if(pit.salary_item_type_id = 10370 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_6 ,
 sum(if(pit.salary_item_type_id = 10371 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_7 ,
 sum(if(pit.salary_item_type_id = 10372 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_8 ,
 sum(if(pit.salary_item_type_id = 10373 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_9 ,
 sum(if(pit.salary_item_type_id = 10377 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_10,
sum(if(pit.salary_item_type_id = 506 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_11,
sum(if(pit.salary_item_type_id = 524 , (pit.pay_value + diff_pay_value * diff_value_coef ),0)) item_12,
					   sum(if(sit.insure_include = 1  ,(pit.pay_value + diff_pay_value * diff_value_coef ),0)) SumInsure ,
					   sum(if(sit.tax_include = 1  ,(pit.pay_value + diff_pay_value * diff_value_coef ),0)) SumTax ,
					   sum(if(pit.salary_item_type_id in(143,38,144,145,744,9920),(pit.get_value + diff_get_value * diff_value_coef ),0)) Bime ,
					   sum(if(pit.salary_item_type_id in(146,147,148,747),(pit.get_value + diff_get_value * diff_value_coef ),0)) Tax ,
					   sum(if((sit.effect_type = 2 and pit.salary_item_type_id not in(146,147,148,747,143,38,144,145,744,9920)),
					          (pit.get_value + diff_get_value * diff_value_coef ),0)) chap


			   from payments p inner join payment_items pit
											on p.staff_id = pit.staff_id and
											   p.pay_year = pit.pay_year and
											   p.pay_month = pit.pay_month and
											   p.payment_type = pit.payment_type

							  inner join salary_item_types sit
										  on pit.salary_item_type_id = sit.salary_item_type_id
							  inner join staff s
										  on p.staff_id = s.staff_id

							  inner join persons pr
											on pr.PersonID = s.PersonID

							  inner join writs w on
											   s.staff_id = w.staff_id and
											   s.last_writ_id = w.writ_id and
											   s.last_writ_ver = w.writ_ver

							  left join Basic_Info bi on bi.TypeID = 6 and bi.InfoID = w.education_level
							  left join position po on w.post_id = po.post_id
							  left join jobs j on j.job_id= w.job_id
							  left join org_new_units o1 on o1.ouid = s.UnitCode
							  left join org_new_units o2 on o2.ouid = s.ouid


			where p.pay_year = '.$_POST['pay_year'].' and p.pay_month = '.$_POST['pay_month'].' and p.payment_type = '.$pament_type.'

			'; 
			   
			   
		    $query .=  ($WhereCost !="") ? " AND pit.cost_center_id in (".$WhereCost.") " : " "  ; 
			$query .=  ($WhereEmpstate !="") ? " AND w.emp_state in (".$WhereEmpstate.") " : " "  ; 
			$query .=  ($WherePT !="") ? " AND s.person_type in (".$WherePT.") " : " "  ; 
			$query .=  ($staffID  !=" ") ? " AND s.staff_id in (".$staffID .") " : " "  ;  
			
			$query .= ' GROUP BY p.staff_id   ORDER BY pr.plname,pr.pfname ';
			
			$dt = PdoDataAccess::runquery($query) ; 


//......................................................................................
?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3865A1} 
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>

<?

$qry = " select bi.Title month_title 
				from  Basic_Info bi 
							where  bi.typeid = 41 AND InfoID = " . $_POST["pay_month"];
$res = PdoDataAccess::runquery($qry);
$month = $res[0]['month_title'];


echo '<html><META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';

if(!empty($_REQUEST["excel"]) && $_REQUEST["excel"] !="true"){
echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:110%'><tr>
		<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
		<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست پرداختی &nbsp; "."&nbsp;".$month." ماه ".
						  $_POST['pay_year']."  </td>				
		<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		. DateModules::shNow() . "<br>";		
echo "</td></tr></table>"; 
}
echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">';	  
echo ' <tr class="header" style="background-color:#3865A1">
		  <td  align="center" >شماره شناسایی</td> 
		  <td  align="center" >نام خانوادگي</td> 
		  <td  align="center" >نام</td> 
		  <td align="center">محل خدمت</td>
		  <td align="center">مدرک تحصیلی</td>
		  <td align="center">پست</td>
		  <td align="center">شغل</td>
<td align="center">حقوق رتبه و پایه</td>
<td align="center">فوق العاده ویژه</td>
<td align="center">فوق العاده جذب</td>
<td align="center">فوق العاده شغل</td>
<td align="center">فوق العاده سختی شرایط محیط کار</td>
<td align="center">کمک هزینه اولاد</td>
<td align="center">کمک هزینه عائله مندی</td>
<td align="center">فوق العاده نوبت کاری</td>
<td align="center">فوق العاده شغل مدیریتی</td>
<td align="center">فوق العاده شغل سرپرستی</td>
<td align="center">برگشت بیمه جانبازان</td>
<td align="center">برگشت بازنشستگی ومقرری جانبازان</td>
		  <td align="center">حقوق و مزایا</td>
		  <td align="center">مشمول بیمه</td>
		  <td align="center">مشمول مالیات</td>
		  <td align="center">بیمه </td>
		  <td align="center">مالیات</td>
		  <td align="center">سایر کسورات</td>	  
		 
		  <td align="center">خالص پرداختی</td>		 	  
	   </tr> '; 	 

for($i=0;$i<count($dt);$i++)
{
	echo "<tr>
		  <td>".$dt[$i]['staff_id']."</td>		 
		  <td>".$dt[$i]['plname']."</td> 
		  <td>".$dt[$i]['pfname']."</td>
		  <td>".$dt[$i]['mainUnit']."-".$dt[$i]['subUnit']."</td> 		 
		  <td>".$dt[$i]['maghta']."</td> 
		  <td>".$dt[$i]['postTitle']."</td> 
		  <td>".$dt[$i]['jobTitle']."</td>
<td>".number_format($dt[$i]['item_1'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_2'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_3'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_4'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_5'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_6'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_7'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_8'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_9'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_10'], 0, '.', ',')."</td>
<td>".number_format($dt[$i]['item_11'], 0, '.', ',')."</td><td>".number_format($dt[$i]['item_12'], 0, '.', ',')."</td>
		  <td>".number_format($dt[$i]['rast'], 0, '.', ',')."</td>
		  <td>".number_format($dt[$i]['SumInsure'], 0, '.', ',')."</td>
		  <td>".number_format($dt[$i]['SumTax'], 0, '.', ',')."</td> 
		  <td>".number_format($dt[$i]['Bime'], 0, '.', ',')."</td> 
		  <td>".number_format($dt[$i]['Tax'], 0, '.', ',')."</td> 
		  <td>".number_format($dt[$i]['chap'], 0, '.', ',')."</td> 
		  <td>".number_format(($dt[$i]['rast'] - $dt[$i]['chap'] - $dt[$i]['Bime'] - $dt[$i]['Tax']), 0, '.', ',')."</td> 
		  " ; 
	echo "</tr>";

} //End For
	 

} //End ShowIf




?>

</body>		
</html>	

<script>
	TaxRep.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	TaxRep.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "TaxReport.php?show=true" + (this.get("excel").value == "1" ? "&excel=true" : "");
	
		this.form.submit();		
		return;
	}
	
	function TaxRep()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش مالیات",
			fieldDefaults: {
				labelWidth: 60
			},
			layout: {
				type: 'table',
				columns: 3
			},
			items :[{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال",
					name : "pay_year",
					allowBlank : false,
					width : 150
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه",
					name : "pay_month",
					allowBlank : false,
					width : 150
				},
				{
					xtype : "numberfield",
					hideTrigger : true,
					width : 180,
					labelWidth: 110,
					fieldLabel : "شماره شناسایی",
					name : "staff_id"
				},
				{
						colspan:3,										
						xtype: 'container',  
						style : "padding:5px",
						html:"نوع فرد : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					
								"<input type=checkbox id='PT_1' name='PT_1' value=1 checked>&nbsp; هیئت علمی"+
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_2' name='PT_2' value=1 checked>&nbsp;  کارمند  " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_3' name='PT_3' value=1 checked>&nbsp;  روزمزدبیمه ای " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_5' name='PT_5' value=1 >&nbsp; قراردادی " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_10' name='PT_10' value=1 >&nbsp; بازنشسته "
					},						
					{
						xtype: 'fieldset',
						title : "مراکز هزینه",
						colspan : 3,		
						style:'background-color:#DFEAF7',					
						width : 700,						
						fieldLabel: 'Auto Layout',
						itemId : "chkgroup",
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
									parentNode = PersonSalaryObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for(i=0; i<elems.length; i++)
									{
										if(elems[i].id.indexOf("chkcostID_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
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
									parentNode = TaxRepObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
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
							fields : ["InfoID","Title"],
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
											TaxRepObject.filterPanel.down("[itemId=PayType]").setValue("1");										
									}
								}
									
													}),
						valueField : "InfoID",
						displayField : "Title",
						hiddenName : "PayType",
						itemId : "PayType",
						fieldLabel : "نوع پرداخت",						
						listConfig: {
							loadingText: 'در حال جستجو...',
							emptyText: 'فاقد اطلاعات',
							itemCls : "search-item"
						},
						width:300
					}					
					
			],
			buttons :  [ {
							text : "مشاهده گزارش ",
							handler :  Ext.bind(this.showReport,this),
							listeners : {
								click : function(){
									TaxRepObject.get('excel').value = "false";
								}
							},
							iconCls : "report"                                
						 },{
							text : "خروجی excel",
							handler : function(){TaxRepObject.showReport()},
							listeners : {
								click : function(){
									TaxRepObject.get('excel').value = "true";
								}
							},
							iconCls : "excel"
						}]
		});
		
		new Ext.data.Store({
			fields : ["cost_center_id","title"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						TaxRepObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
						});
						
					});
										
				}}
			
		});
		
		new Ext.data.Store({
			fields : ["InfoID","Title"],
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
						TaxRepObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var TaxRepObject = new TaxRep();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
