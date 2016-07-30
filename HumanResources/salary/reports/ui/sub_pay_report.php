<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.07
//---------------------------

require_once("../../../header.inc.php");
ini_set("display_errors","On") ; 
	
if (isset($_REQUEST["show"]))
{
	
	$keys = array_keys($_POST);
	$WhereCost = $WherePT =	$WhereBT = $WhereEmpstate = "" ;
	$arr = "" ;
	
	//...................... مراکز هزینه ................
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkcostID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WhereCost .= ($WhereCost!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
				
	}
	//........................... نوع فرد..............
	if(isset($_POST['PT_1']) && $_POST['PT_1']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,1 " :  "1 " ; 
	
	if(isset($_POST['PT_2']) && $_POST['PT_2']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,2 " :  "2 " ; 
	
	if(isset($_POST['PT_3']) && $_POST['PT_3']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,3 " :  "3 " ; 
	
	if(isset($_POST['PT_5']) && $_POST['PT_5']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,5 " :  "5" ; 
	
	//............................................
	
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	$SItemID = (isset($_POST['SItemID']) && $_POST['SItemID'] > 0) ? $_POST['SItemID'] : " " ; 
	
	$query = " select   s.staff_id,
						s.tafsili_id,
						ps.pfname,
						ps.plname,
						pai.salary_item_type_id salary_item_type_id,
						pai.cost_center_id,
						(pai.pay_value + pai.diff_pay_value * pai.diff_value_coef) pay_value,
						(pai.get_value + pai.diff_get_value * pai.diff_value_coef) get_value,
						pai.pay_year,
						pai.pay_month,
						sit.effect_type,
						pai.pay_month
						pay_month,
						c.title,
						s.person_type,
						sit.print_title
						
			   from    payment_items pai
					  LEFT OUTER JOIN salary_item_types sit
						   ON (pai.salary_item_type_id = sit.salary_item_type_id)
					  LEFT OUTER JOIN cost_centers  c
						   ON (pai.cost_center_id = c.cost_center_id)
					  LEFT OUTER JOIN staff s
						   ON (pai.staff_id = s.staff_id)
					  LEFT OUTER JOIN persons ps
						   ON (ps.PersonID = s.PersonID)			   
			   
			  where pai.pay_year >= ".$_POST['from_pay_year']." AND
					pai.pay_year <= ".$_POST['to_pay_year']." AND
					pai.pay_month >= ".$_POST['from_pay_month']." AND					
					pai.pay_month <= ".$_POST['to_pay_month']." AND			
					pai.payment_type = ".$_POST['PayType'] ; 
			  
    $query .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
    $query .= ($SItemID > 0 ) ? " AND sit.salary_item_type_id=".$SItemID : "" ; 
	$query .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 			
	$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 	

	$query .= " order by pai.cost_center_id,
						 pai.salary_item_type_id,
						 pai.pay_year,
						 pai.pay_month,
						 ps.plname,
						 ps.pfname "; 
				
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
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش مزایا و کسورات"." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		     . DateModules::shNow() . "<br>";		
	echo "</td></tr></table>";      
	
	echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
			 <tr class="header1">					
				<td colspan="6"> سال : '.$dataTable[0]['pay_year'].' &nbsp;&nbsp;&nbsp;&nbsp;  ماه :'.
										 DateModules::GetMonthName($dataTable[0]['pay_month']).'&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;قلم حقوقی : '.
										 $dataTable[0]['print_title'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; مرکز هزینه :'.$dataTable[0]['title'].'</td>
			 </tr>
			 <tr class="header">					
				<td>ردیف </td>
				<td>شماره شناسایی</td>
				<td>کد تفضیلی</td>		
				<td> نام خانوادگی </td>						
				<td>نام </td>				
				<td>مبلغ </td>		
			</tr>' ; 
		
	$sum = $count = 0 ; 
	$CC = $dataTable[0]['cost_center_id']; 
	$SIID = $dataTable[0]['salary_item_type_id'];
	$PY = $dataTable[0]['pay_year'];
	$PM = $dataTable[0]['pay_month']; 
	 	
	for($i=0 ; $i < count($dataTable) ; $i++)
   {
		if(($count> 0 && $count%35 == 0 ) || $dataTable[$i]['cost_center_id'] != $CC || $SIID != $dataTable[0]['salary_item_type_id'] ||
			$PY != $dataTable[0]['pay_year'] || $PM != $dataTable[0]['pay_month'] )
		{		
		
				echo '<tr><td colspan=5 >جمع : </td><td>'.number_format($sum, 0, '.', ',').'</td></tr>
					  </table><hr style="page-break-after:always; visibility: hidden"><br><br>';
				
				if($dataTable[$i]['cost_center_id'] != $CC || $SIID != $dataTable[0]['salary_item_type_id'] ||
					$PY != $dataTable[0]['pay_year'] || $PM != $dataTable[0]['pay_month']) {
					$CC = $dataTable[$i]['cost_center_id'] ; 
					$SIID = $dataTable[$i]['salary_item_type_id'];
					$PY = $dataTable[$i]['pay_year'];
					$PM = $dataTable[$i]['pay_month'];				
					$sum = $count=0 ; 
				}
				echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
					  <tr class="header1">					
						<td colspan="6"> سال : '.$dataTable[$i]['pay_year'].' &nbsp;&nbsp;&nbsp;&nbsp;  ماه :'.
												 DateModules::GetMonthName($dataTable[$i]['pay_month']).'&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;قلم حقوقی : '.
												 $dataTable[$i]['print_title'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; مرکز هزینه :'.$dataTable[$i]['title'].'</td>
					  </tr>
					  <tr class="header">					
						<td>ردیف </td>
						<td>شماره شناسایی</td>
						<td>کد تفضیلی</td>		
						<td> نام خانوادگی </td>						
						<td>نام </td>				
						<td>مبلغ </td>		
					  </tr>' ; 
		}
		
		if($dataTable[$i]['effect_type'] == 1 )
		   $pgvalue = $dataTable[$i]['pay_value'] ;
	    else 
			$pgvalue = $dataTable[$i]['get_value'] ;
			
	     echo " <tr>
					<td>".( $i + 1 )."</td>
					<td>".$dataTable[$i]['staff_id']."</td> 
					<td>".$dataTable[$i]['tafsili_id']."</td>	
					<td>".$dataTable[$i]['plname']."</td>					
					<td>".$dataTable[$i]['pfname']."</td>									
					<td>".number_format($pgvalue, 0, '.', ',')."</td>			  
				</tr>" ; 
				
		if($dataTable[$i]['effect_type'] == 1 )
		   $sum += $dataTable[$i]['pay_value'] ;
	    else 
			$sum += $dataTable[$i]['get_value'] ;
			
		$count++ ; 
	}
		echo '<tr><td colspan=5 >جمع : </td><td>'.number_format($sum, 0, '.', ',').'</td></tr>';
	   
	    echo "</table></center>" ;
		die();
				 
 }

?>

<script>
	SubPay.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	SubPay.prototype.showReport = function(type)
	{
		
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "sub_pay_report.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";	
		this.form.submit();	
		return;
	}
		
	function SubPay()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش مزایا و کسورات",
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
					xtype : "numberfield",
					hideTrigger : true,
					width : 200,
					colspan:2 ,					
					fieldLabel : "شماره شناسایی",
					name : "staff_id"
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
									parentNode = SubPayObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
								"<input type=checkbox id='PT_5' name='PT_5' value=1 >&nbsp; قراردادی "
					},					
					{
						xtype : "combo",
						colspan:3,
						store :  new Ext.data.Store({
							fields : ["salary_item_type_id","full_title"],
							proxy : {
										type: 'jsonp',
										url : this.address_prefix + "../../../global/domain.data.php?task=searchSalaryItemTypes&all=1",
										reader: {
											root: 'rows',
											totalProperty: 'totalCount'
										}
									}
									,
								autoLoad : true,
								listeners:{
									load : function(){
											SubPayObject.filterPanel.down("[itemId=SItemID]").setValue("-1");										
									}
								}
									
													}),
						valueField : "salary_item_type_id",
						displayField : "full_title",
						hiddenName : "SItemID",
						itemId : "SItemID",						
						fieldLabel : "قلم حقوقی",						
						listConfig: {
							loadingText: 'در حال جستجو...',
							emptyText: 'فاقد اطلاعات',
							itemCls : "search-item"
						},
						width:300
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
											SubPayObject.filterPanel.down("[itemId=PayType]").setValue("1");										
									}
								}
									
													}),
						valueField : "InfoID",
						displayField : "Title",
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
						handler: function(){SubPayObject.showReport('show');}
					  }/*,{
						text : "خروجی Excel",
						iconCls : "excel",
						handler : function(){SubPayObject.showReport('excel');}
					}*/]			
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
						SubPayObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
						});
						
					});
										
				}}
			
		});
		
		
	}
	
	var SubPayObject = new SubPay();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div>	
	</form>
</center>
