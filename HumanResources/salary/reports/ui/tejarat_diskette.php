<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

require_once("../../../header.inc.php");
ini_set("display_errors","On") ; 

if (isset($_REQUEST["show"]))
{
	
	$keys = array_keys($_POST);
	$WhereCost = $WherePT =	$WhereBT = $WhereEmpstate = "" ;
	$arr = "" ;
	
	//...................... مراکز هزینه و وضعیت استخدامی ................
	
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
	//........................... نوع فرد..............
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
	
	//........... بانکها..........................
	if(isset($_POST['BI_2001']) && $_POST['BI_2001']== 1) 
	   	$WhereBT .= ($WhereBT !="" ) ?  " ,2001 " :  "2001" ; 
	
	if(isset($_POST['BI_2004']) && $_POST['BI_2004']== 1) 
	   	$WhereBT .= ($WhereBT !="" ) ?  " ,2004 " :  "2004 " ; 
	
	if(isset($_POST['BI_2005']) && $_POST['BI_2005']== 1) 
	   	$WhereBT .= ($WhereBT !="" ) ?  " ,2005 " :  "2005 " ; 
	
	if(isset($_POST['BI_2008']) && $_POST['BI_2008']== 1) 
	   	$WhereBT .= ($WhereBT !="" ) ?  " ,2008 " :  "2008" ; 
	
	//............................................
	
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	
	
	if($_POST['RepFormat'] == 0 ) // .... حقوق 
	{
		$query = "  select pa.account_no,
						   pa.pay_year,
						   pa.pay_month,
						   ba.branch_code,
						   sum(pai.pay_value + pai.diff_pay_value * pai.diff_value_coef - pai.get_value - pai.diff_get_value * pai.diff_value_coef) amount
							   
					from payment_items pai
						 INNER JOIN  payments  pa
								ON (pai.staff_id     = pa.staff_id  AND
									pai.pay_year     = pa.pay_year  AND
									pai.pay_month    = pa.pay_month  AND
									pai.payment_type = pa.payment_type)
						 INNER JOIN banks ba
							  ON (ba.bank_id = pa.bank_id)
						 INNER JOIN staff s 
							  ON (pai.staff_id = s.staff_id)
						 LEFT JOIN writs w
							  ON (pa.writ_id = w.writ_id AND pa.writ_ver = w.writ_ver AND pa.staff_id = w.staff_id )

					where pai.pay_year = ".$_POST['pay_year']." AND
						  pai.pay_month = ".$_POST['pay_month']." AND
						  pai.payment_type = ".$_POST['PayType'] ; 
						  
		$query .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
		$query .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 		
		$query .= ($WhereEmpstate !="" ) ? " AND w.emp_state in (".$WhereEmpstate.") " : "" ; 
		$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 
		$query .= ($WhereBT  !="" ) ? " AND  pa.bank_id in (".$WhereBT.") " : "" ; 
		
		$query .= "group by pa.account_no,
							pa.pay_year,
							pa.pay_month,
							ba.branch_code	" ; 
							
							
		$dt = PdoDataAccess::runquery($query);	
		$year = $_POST['pay_year'] - 1300;
		$list_date = $year.str_pad($_POST['pay_month'],2,"0",STR_PAD_LEFT).'29';
		$output = "" ;
		$sum =0 ;
		
		for($i=0;$i<count($dt);$i++)
		{
			if(!empty($dt[$i]['branch_code']))
			{
				$brno= str_pad($dt[$i]['branch_code'],5,"0",STR_PAD_LEFT);
				$acc= str_pad($dt[$i]['account_no'],5,"0",STR_PAD_LEFT);
			}
			else {
				$brno = '';
				$acc = str_pad($dt[$i]['account_no'],10,"0",STR_PAD_LEFT);
			}
			$amn= str_pad($dt[$i]['amount'],13,"0",STR_PAD_LEFT);
			
			$record = $brno.$acc.$amn.$list_date."\n";
			
			$output.=$record ;
			
			$sum+= $dt[$i]['amount']; 
								
		}
		
		//.............................footer...............
		$sum= str_pad($sum,29,"0",STR_PAD_LEFT);		
		$output = $sum.$output ;
			
		$file = "TRANS.DAT" ;
		$filename = "../../../HRProcess/".$file ;
					
		$fp=fopen($filename,'w+');
		fwrite($fp,$output);
		fclose($fp);
			
		header('Content-disposition: filename="'.$file.'"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file);
		die() ; 			
	
	}
	elseif($_POST['RepFormat'] == 1 ) // ..... خزانه
	{
		manage_salary_utils::simulate_tax($_POST['pay_year'], $_POST['pay_month'] , $_POST['PayType'] ) ;	 
		manage_salary_utils::simulate_bime($_POST['pay_year'], $_POST['pay_month'] , $_POST['PayType'] ) ; 
		
		$query = "  select pa.account_no,
						   pa.pay_year,
						   pa.pay_month,
						   ba.branch_code,
						   sum(pai.pay_value + pai.diff_pay_value * pai.diff_value_coef -
						   CASE WHEN pai.salary_item_type_id IN(146,147,148) THEN tts.value WHEN pai.salary_item_type_id IN(9920,145,144) THEN tis.value
						   ELSE (pai.get_value + pai.diff_get_value * pai.diff_value_coef) END) amount

					from payment_items pai
							 INNER JOIN salary_item_types sit
								ON pai.salary_item_type_id = sit.salary_item_type_id AND sit.credit_topic = ".CREDIT_TOPIC_1."
							 INNER JOIN payments  pa
								 ON    (pai.staff_id     = pa.staff_id  AND
										pai.pay_year     = pa.pay_year  AND
										pai.pay_month    = pa.pay_month  AND
										pai.payment_type = pa.payment_type)
							 INNER JOIN banks ba
								 ON (ba.bank_id = pa.bank_id) 
							 LEFT OUTER JOIN temp_tax_include_sum tts
								 ON (tts.staff_id = pai.staff_id AND pai.salary_item_type_id IN(146,147,148))
							 LEFT OUTER JOIN temp_insure_include_sum tis
								 ON (tis.staff_id = pai.staff_id AND pai.salary_item_type_id IN(9920,145,144))
							 INNER JOIN staff s 
								  ON pai.staff_id = s.staff_id
							 LEFT JOIN writs w
								  ON s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver

					where pai.pay_year = ".$_POST['pay_year']." AND
						  pai.pay_month = ".$_POST['pay_month']." AND
						  pai.payment_type = ".$_POST['PayType'] ; 
						  
						  
		$query .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
		$query .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 		
		$query .= ($WhereEmpstate !="" ) ? " AND w.emp_state in (".$WhereEmpstate.") " : "" ; 
		$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 
		$query .= ($WhereBT  !="" ) ? " AND  pa.bank_id in (".$WhereBT.") " : "" ; 
		
		$query .= "	group by pa.account_no,
							 pa.pay_year,
							 pa.pay_month,
							 ba.branch_code " ; 
							 
		$dt = PdoDataAccess::runquery($query);	 
	
		$year = $_POST['pay_year'] - 1300;
		$list_date = $year.str_pad($_POST['pay_month'],2,"0",STR_PAD_LEFT).'29';
		
		$output = "" ;
		$sum =0 ;
		
		for($i=0;$i<count($dt);$i++)
		{
			if(!empty($dt[$i]['branch_code']))
			{
				$brno= str_pad($dt[$i]['branch_code'],5,"0",STR_PAD_LEFT);
				$acc= str_pad($dt[$i]['account_no'],5,"0",STR_PAD_LEFT);
			}
			else {
				$brno = '';
				$acc = str_pad($dt[$i]['account_no'],10,"0",STR_PAD_LEFT);
			}
			$amn= str_pad($dt[$i]['amount'],13,"0",STR_PAD_LEFT);
			
			$record = $brno.$acc.$amn.$list_date."\n";
			
			$output.=$record ;
			
			$sum+= $dt[$i]['amount']; 
											
		}
		
		//.............................footer...............
			
		$sum= str_pad($sum,29,"0",STR_PAD_LEFT);		
		$output = $sum.$output ;
			
		$file = "TRANS.DAT" ;
		$filename = "../../../HRProcess/".$file ;
					
		$fp=fopen($filename,'w+');
		fwrite($fp,$output);
		fclose($fp);
			
		header('Content-disposition: filename="'.$file.'"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file);
		die() ; 			
			
	}
	elseif($_POST['RepFormat'] == 2 ) // ..... وام
	{
		$query = "  select ps.loan_no,
						   pa.pay_year,
						   pa.pay_month,
						   ba.branch_code,
						   pai.get_value + pai.diff_get_value * pai.diff_value_coef as amount

					from payment_items pai
						 INNER JOIN  payments  pa
							   ON ( pai.staff_id     = pa.staff_id  AND
									pai.pay_year     = pa.pay_year  AND
									pai.pay_month    = pa.pay_month  AND
									pai.payment_type = pa.payment_type)
						 INNER JOIN person_subtracts ps
							   ON (pai.param1 = 'LOAN' AND pai.param2 = ps.subtract_id AND ps.subtract_type = ".LOAN.")  
						 INNER JOIN banks ba
							   ON (ba.bank_id = pa.bank_id)
						 INNER JOIN staff s 
							  ON (pa.staff_id = s.staff_id) 
						 LEFT JOIN writs w
							  ON (pa.writ_id = w.writ_id AND pa.writ_ver = w.writ_ver AND pa.staff_id = w.staff_id )

					 where 	pai.pay_year = ".$_POST['pay_year']." AND
							pai.pay_month = ".$_POST['pay_month']." AND
							pai.payment_type = ".$_POST['PayType']." AND ps.bank_id IS NOT NULL 
							AND pai.param2 not in (
							270376904	,
270376908	,
270376909	,
270376911	,
270376912	,
270376914	,
270376915	,
270376916	,
270376917	,
270376929	,
270376930	,
270376931	,
270376933	,
270376934	,
270376935	,
270376936	,
270376937	,
270376938	,
270376939	,
270376940	,
270376941	,
270376942	,
270376944	,
270376945	,
270376946	,
270376948	,
270376950	,
270376958	,
270376960	,
270376961	,
270376962	,
270376963	,
270376965	, 178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 ) " ; 
						  						  
		$query .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
		$query .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 		
		$query .= ($WhereEmpstate !="" ) ? " AND w.emp_state in (".$WhereEmpstate.") " : "" ; 
		$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 
		$query .= ($WhereBT  !="" ) ? " AND  ps.bank_id in (".$WhereBT.") " : "" ; 
		
		
		$dt = PdoDataAccess::runquery($query);	

		$output = "" ;
		$sum =0 ;
		for($i=0;$i<count($dt);$i++)
		{
			$year = $_POST['pay_year'] - 1300;
			$list_date = $year.str_pad($_POST['pay_month'],2,"0",STR_PAD_LEFT).'29';
			
			if(!empty($dt[$i]['branch_code'])) {
				$brno= str_pad($dt[$i]['branch_code'],5,"0",STR_PAD_LEFT);
				$acc= str_pad($dt[$i]['loan_no'],5,"0",STR_PAD_LEFT);
			} else {
				$brno = '';
				$acc = str_pad($dt[$i]['loan_no'],10,"0",STR_PAD_LEFT);
			}
			$amn= str_pad($dt[$i]['amount'],13,"0",STR_PAD_LEFT);
			$record = $brno.$acc.$amn.$list_date."\n";
			$output.=$record ;
			$sum+= $dt[$i]['amount']; 			
			
		}
		//.............................footer...............
		$sum= str_pad($sum,29,"0",STR_PAD_LEFT);		
		$output = $sum."\n".$output ;
			
		$file = "TRANS.DAT" ;
		$filename = "../../../HRProcess/".$file ;
					
		$fp=fopen($filename,'w+');
		fwrite($fp,$output);
		fclose($fp);
			
		header('Content-disposition: filename="'.$file.'"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file);
		die() ; 						
		
	}
			 
}

?>

<script>
	TjaratDiskette.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	TjaratDiskette.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "tejarat_diskette.php?show=true";
	
		this.form.submit();
		this.get("excel").value = "";
		return;
	}
	
	function TjaratDiskette()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش تهیه دیسکت حقوق/وام بانک تجارت",
			fieldDefaults: {
				labelWidth: 80
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
									parentNode = TjaratDisketteObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
									parentNode = TjaratDisketteObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
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
								"<input type=checkbox id='PT_10' name='PT_10' value=1 >&nbsp;  بازنشسته"
					},
					{
						colspan:3,										
						xtype: 'container',  
						style : "padding:5px",
						html:"بانک &nbsp; &nbsp; : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					
								"<input type=checkbox id='BI_2001' name='BI_2001' value=1 checked>&nbsp; تجارت دانشگاه"+
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='BI_2004' name='BI_2004' value=1 checked>&nbsp;  تجارت شیروان  " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='BI_2005' name='BI_2005' value=1 checked>&nbsp;  تجارت نیشابور " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='BI_2008' name='BI_2008' value=1 >&nbsp; تجارت عابر بانک"
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
											TjaratDisketteObject.filterPanel.down("[itemId=PayType]").setValue("1");										
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
					},
					{
						colspan:4,										
						xtype: 'container',                    											
						html:" نوع گزارش : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_0' name='RepFormat' value='0' checked>&nbsp;  حقوق "+
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_1' name='RepFormat' value='1'>&nbsp; خزانه " +
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_2' name='RepFormat' value='2'>&nbsp; وام " 
					}		
					
			],
			buttons :  [ {
							text : "تهیه دیسکت ",
							handler :  Ext.bind(this.showReport,this),
							iconCls : "save"                                
						},{
						iconCls : "clear",
						text : "پاک کردن فرم",
						handler : function(){
						this.up("form").getForm().reset();
						TjaratDisketteObject.get("mainForm").reset();
					}
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
						TjaratDisketteObject.filterPanel.down("[itemId=chkgroup]").add({
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
						TjaratDisketteObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var TjaratDisketteObject = new TjaratDiskette();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
