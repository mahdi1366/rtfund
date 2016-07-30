<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	93.07
//---------------------------

require_once("../../../header.inc.php");
?>
<style>
	.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						text-align: center;width: 50%;padding: 2px;}
	.reportGenerator .header {color: white;font-weight: bold;background-color:#3865A1} 
	.reportGenerator td {border: 1px solid #555555;height: 20px;}
	.money{ font-size: 11px; font-weight: bold; }
</style>	
<?

if (isset($_REQUEST["task"]))
{
	switch($_REQUEST["task"])
	{
		case "ShowReport":
			ShowReport();	
				
	}
	
}

function get_writSalaryItem_value($writ_id, $writ_ver ,$staff_id , $sit ) 
{
	$resValue = PdoDataAccess::runquery(" select value 
											from writ_salary_items 
												where writ_id = ".$writ_id." and writ_ver =".$writ_ver."  and 
													staff_id =".$staff_id." and salary_item_type_id = ".$sit ) ; 
	
	if(count($resValue) > 0 )
	   return $resValue[0]['value'] ; 
	else		
	   return 0 ;
	
}

function get_WrtitTypeTitle($writ_id, $writ_ver ,$staff_id) 
{
	$res = PdoDataAccess::runquery(" select wt.title writTypeTitle , wst.title WritSubTitle 
											from writs w inner join writ_types wt
																on w.writ_type_id = wt.writ_type_id and w.person_type = wt.person_type

														 inner join writ_subtypes wst
																on  w.writ_type_id = wst.writ_type_id and
																	w.writ_subtype_id = wst.writ_subtype_id and
																	w.person_type = wst.person_type

										   where writ_id= ".$writ_id." AND writ_ver= ".$writ_ver." AND staff_id=".$staff_id ) ; 
	
	return $res[0]['writTypeTitle'].'-'.$res[0]['WritSubTitle'] ;	
}

function ShowReport(){	
	
	/*
	
	$qry = " select  cpw.staff_id , cpw.writ_id ,  cpw.writ_ver , cpw.arrear_ver , w.execute_date
		
				 from corrective_payment_writs cpw inner join  writs w 
			                                         on cpw.staff_id = w.staff_id and 
														cpw.writ_id = w.writ_id and 
														cpw.writ_ver = w.writ_ver 

			 where cpw.pay_year = 1392 and cpw.staff_id = 471479 and cpw.pay_month = 12 " ; 
	
	$res1 = PdoDataAccess::runquery($qry) ; 
	
	$qry = " select   cpw.staff_id , cpw.writ_id ,  cpw.writ_ver , w.execute_date 
		
					from hrms.payment_writs cpw inner join  writs w 
			                                         on cpw.staff_id = w.staff_id and 
														cpw.writ_id = w.writ_id and 
														cpw.writ_ver = w.writ_ver
			  where cpw.pay_year = 1392 and cpw.staff_id = 471479 and cpw.pay_month = 12 " ; 
	
	// پرس و جو بالا هم بایستی union شود با ورژن های قبلی پرداخت تا احکام آنها هم دیده شود .
	
	$res2 = PdoDataAccess::runquery($qry) ; 
	
	
	for($i=0; $i<count($res1);$i++)
	{
		for($j=0;$j<count($res2);$j++)
		{
			
			if(  $res1[$i]['execute_date'] == $res2[$j]['execute_date'] && 
				($res1[$i]['writ_id'] != $res2[$j]['writ_id'] || $res1[$i]['writ_ver'] != $res2[$j]['writ_ver'] ) )
			{
				 PdoDataAccess::runquery(" insert compare_arrear_writs (staff_id ,current_execute_date , current_writ_id , current_writ_ver, 
																	 prev_execute_date , prev_writ_id , prev_writ_ver , arrear_ver , pay_year ) values 
											(".$res1[$i]['staff_id'].",'".$res1[$i]['execute_date']."',".$res1[$i]['writ_id'].",".
												$res1[$i]['writ_ver'].",'".$res2[$j]['execute_date']."',".$res2[$j]['writ_id'].",".
												$res2[$j]['writ_ver'].",".$res1[$i]['arrear_ver'].",".$_POST['year']." ) ");  
				
			}	
			elseif($res1[$i]['execute_date'] > $res2[$j]['execute_date'])
			{
				 PdoDataAccess::runquery(" insert compare_arrear_writs (staff_id ,current_execute_date , current_writ_id , 
																		current_writ_ver, prev_execute_date , prev_writ_id ,
																		prev_writ_ver , arrear_ver , pay_year  ) values 
											(".$res1[$i]['staff_id'].",'".$res1[$i]['execute_date']."',".$res1[$i]['writ_id'].",".
											   $res1[$i]['writ_ver'].",'".$res2[$j]['execute_date']."',".$res2[$j]['writ_id'].",".
											   $res2[$j]['writ_ver'].",".$res1[$i]['arrear_ver'].",".$_POST['year']." ) "); 
				
			}
			elseif ( $res1[$i]['execute_date'] < $res2[$j]['execute_date'] )
			{				
				break ;				
			}			
		}		
		
	}
	 
	*/
	
	$res = PdoDataAccess::runquery(" select * 
										from compare_arrear_writs 
											where staff_id = ".$_POST['staff_id']." and pay_year =".$_POST['pay_year']);
	
	$writsWhereClause = "" ; 
	
	for($i=0;$i<count($res);$i++)
	{
		$writsWhereClause.='(wsi.writ_id='.$res[$i]['current_writ_id'].' AND wsi.writ_ver='.$res[$i]['current_writ_ver'].' AND wsi.staff_id='.$res[$i]['staff_id'].') OR 
							(wsi.writ_id='.$res[$i]['prev_writ_id'].' AND wsi.writ_ver='.$res[$i]['prev_writ_ver'].' AND wsi.staff_id='.$res[$i]['staff_id'].' ) OR  ';		
	}
	
	$writsWhereClause = substr($writsWhereClause,0,strlen($writsWhereClause) - 4);
	
	$ResITM =  PdoDataAccess::runquery(" select distinct wsi.salary_item_type_id  , sit.print_title 
											 from writ_salary_items wsi 
																inner join salary_item_types sit 
																		on  wsi.salary_item_type_id  = sit.salary_item_type_id
													where ".$writsWhereClause);
		
	
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
	echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:80%'>
			<tr height=200px>	
				<td align='center' style='font-family:b titr;font-size:15px'> مقایسه احکام پرداختی سال&nbsp; ".$_POST['pay_year']."  </td>				
			</tr>
		  </table></center>";      
 
	echo '<table  class="reportGenerator" style="text-align: center ;width:80%!important" cellpadding="4" cellspacing="0"> ';
	
	$prior_execute_date = $current_execute_date = $prior_writ_type = $current_writ_type = '' ; 
	$current_writ_items = $prior_writ_items = array(); 
	
	$width = round(400/count($res));
	
	for($i=0;$i<count($res);$i++) 
	{
		
		$current_writ_type .=  "<td class='money' width=".$width."px>" .	get_WrtitTypeTitle($res[$i]["current_writ_id"], $res[$i]["current_writ_ver"] , $res[$i]["staff_id"])."</td>"; 
		$prior_writ_type .= "<td class='money' >" .get_WrtitTypeTitle($res[$i]["prev_writ_id"], $res[$i]["prev_writ_ver"] , $res[$i]["staff_id"]). "</td>";
		
		$current_execute_date .= "<td class='money' >" . DateModules::miladi_to_shamsi($res[$i]['current_execute_date']) . "</td>";
		$prior_execute_date .= "<td class='money' >" . DateModules::miladi_to_shamsi($res[$i]['prev_execute_date']) . "</td>";
		
		for($j=0; $j < count($ResITM); $j++)
		{
			if(!isset($current_writ_items[$j]))
			{
				$current_writ_items[$j] = "";
				$prior_writ_items[$j] = "";
			}

			$val = get_writSalaryItem_value($res[$i]["current_writ_id"], $res[$i]["current_writ_ver"] ,
															  $res[$i]["staff_id"], $ResITM[$j]["salary_item_type_id"]);				
			$current_writ_items[$j] .= "<td class='money'>" . ($val == 0 ? "-" : CurrencyModulesclass::toCurrency($val)) . "</td>";
			

			$val = get_writSalaryItem_value($res[$i]["prev_writ_id"], $res[$i]["prev_writ_ver"] ,
															  $res[$i]["staff_id"], $ResITM[$j]["salary_item_type_id"]);
			$prior_writ_items[$j] .= "<td class='money'>" . ($val == 0 ? "-" : CurrencyModulesclass::toCurrency($val)) . "</td>";
			
		}
			
	}
	
	echo "<tr>
			<td style='width:80px' rowspan=2>عنوان حکم </td>
			<td style='width:20px'>قبلي</td>
			" . $prior_writ_type . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_writ_type . "
		</tr>";
	
	echo "<tr>
			<td style='width:80px' rowspan=2>تاريخ اجراي حکم</td>
			<td style='width:20px'>قبلي</td>
			" . $prior_execute_date . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_execute_date . "
		</tr>";
	
	for($j=0; $j<count($ResITM); $j++)
	{
		echo "
		<tr>
			<td style='width:130px'  rowspan=2>" . $ResITM[$j]["print_title"] . "</td>
			<td>قبلي</td>
			" . $prior_writ_items[$j] . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_writ_items[$j] . "
		</tr>";
	}
	
	echo "</table>" ; 	

	die();

}

?>

<script>
	ArrearWrit.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
	
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};

	function ArrearWrit()
	{
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
		 
			title :"تنظیمات گزارش",
			fieldDefaults: {
				labelWidth: 50
			},
			layout: {
				type: 'table',
				columns: 3
			},
			items :[
				new Ext.form.ComboBox({
				store: personStore,
				emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
				typeAhead: false,
				listConfig : {
					loadingText: 'در حال جستجو...'
				},
				pageSize:10,
				width: 610,
				colspan: 3,
				hiddenName : "staff_id",
				fieldLabel : "فرد",
				valueField : "staff_id",
				displayField : "fullname",
				tpl: new Ext.XTemplate(
						'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
							,'<td height="23px">کد پرسنلی</td>'
							,'<td>کد شخص</td>'
							,'<td>نام</td>'
							,'<td>نام خانوادگی</td>'
							,'<td>واحد محل خدمت</td></tr>',
						'<tpl for=".">',
						'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
							,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
						'</tpl>'
						,'</table>'),

				listeners :{
					select : function(combo, records){
						var record = records[0];
						record.data.fullname = record.data.pfname + " " + record.data.plname; 
						this.setValue(record.data.staff_id);
						this.collapse();
					}
				}
			}),{
				xtype : "numberfield",
				hideTrigger : true,
				fieldLabel : "سال",
				width : 120,
				allowBlank : false,
				name : "pay_year"
			}],
			buttons :  [{
				text : "مشاهده گزارش",
				handler : function(){ArrearWritObject.showReport()},
				listeners : {
					click : function(){
						ArrearWritObject.get('excel').value = "";
					}
				},
				iconCls : "report"
			},{
				iconCls : "clear",
				text : "پاک کردن فرم",
				handler : function(){
					this.up("form").getForm().reset();
					ArrearWritObject.get("mainForm").reset();
				}
			}]
		});
		
		//........................
		
		/* this.filterPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
			StaffInsureObject.showReport();
			e.preventDefault();
			e.stopEvent();
			return false;
		});*/
		
	}
	
	var ArrearWritObject = new ArrearWrit();
	
	ArrearWrit.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		if(this.get("excel").value == "")
			this.form.action =  this.address_prefix + "arrear_compare_writs.php?task=ShowReport";		
			
		this.form.submit();
		return;
	}

</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
