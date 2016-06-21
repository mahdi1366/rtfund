<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.07.15
//---------------------------

require_once("../data/writ.data.php");
require_once '../../persons/class/person.class.php';
require_once inc_dataGrid;
require_once inc_manage_unit;

$FacilID = isset($_POST["FacilID"]) ? $_POST["FacilID"] : "";
//________________  GET ACCESS  _________________
$accessObj = new ModuleAccess($FacilID, "", Deputy, Module_writ);
$item_accessObj = new ModuleAccess($FacilID, 1, Deputy, Module_writ);
$accesscostcenterObj = new ModuleAccess($FacilID, SubModule_cost_center_id, Deputy, Module_writ );
//-----------------------------------------------

$writ_id = $_REQUEST["WID"];
$staff_id = $_REQUEST["STID"];

if( !empty($_REQUEST["WID"]) )
{	 
	$writver = isset($_REQUEST["WVER"]) ? $_REQUEST["WVER"] : "";
			
	$objWrt = new manage_writ($writ_id ,$writver, $staff_id);

	$writver = $objWrt->writ_ver;
	
	$where = " w.writ_id = :WID AND w.writ_ver = :WVER AND w.staff_id = :STID";
	
	$whereParam = array( ":WID" => $writ_id ,
	                     ":WVER" => $writver ,
						 ":STID" => $staff_id);
		
	$FullWrt = manage_writ::GetWritInfo($where ,$whereParam);
	
	if(count($FullWrt) == 0)
	   $FullWrt = NULL;
}

//............................................
if($objWrt->corrective && $objWrt->history_only !=0)
  	$is_new_corrective = true ;
else 
  	$is_new_corrective = false ;
//............................................
if(empty($_REQUEST["PID"]))
{ 
 	$objPerson = new manage_person("", $staff_id);
}
else
{
	$objPerson = new manage_person($_REQUEST["PID"]);
}
$fullInfo = manage_person::GetAllPersons("p.PersonID=:pid",array(":pid"=> $objPerson->PersonID));

//............................................
if(isset($_REQUEST["ExeDate"]))
	$exedate = $_REQUEST["ExeDate"];
else
	$exedate = $FullWrt[0]['execute_date'];
//............................................  
$state = $FullWrt[0]["state"];

//............................................
$readOnly = false;

$state = manage_writ::get_writ_state($_REQUEST["WID"] , $writver, $_REQUEST["STID"]) ;
$pay_calc = manage_writ::check_for_use_in_pay_calc($_REQUEST["WID"] , $writver, $_REQUEST["STID"]);

if($pay_calc == null)
{
	if($state == WRIT_PERSONAL && (($objWrt->check_corrective_state() == 'NOT_CORRECTING') || $objWrt->writ_has_new_version()))
   	{

        ExceptionHandler::PushException('اين حکم در صدور حکم اصلاحي استفاده شده است و امکان تغيير آن وجود ندارد',
		   ExceptionHandler::ExceptionType_warning);

		$readOnly = true;
	}
    else if($state != WRIT_PERSONAL)
    {
		ExceptionHandler::PushException("این حکم منتقل شده است و امکان ویرایش آن وجود ندارد",ExceptionHandler::ExceptionType_warning);
		$readOnly = true;
	}
}
else if($pay_calc != null && $state != WRIT_PERSONAL)
{      
	ExceptionHandler::PushException(" این حکم در محاسبه حقوق"."&nbsp;".$pay_calc."&nbsp;"."استفاده شده است و امکان ویرایش آن وجود ندارد.",
		   ExceptionHandler::ExceptionType_warning);
	$readOnly = true;
}
 //...................................
$is_auto_writ = manage_writ::is_auto_writ($exedate,$objWrt->person_type ,$writ_id,$writver,$staff_id);

if(manage_writ::is_first_writ($writ_id,$writver, $staff_id))
{  
	$is_new_writ = false ;
	$header_is_open = false ;
	$is_auto_writ = true ;
}  

	$is_new_writ = manage_writ::is_new_writ($exedate,$objPerson->person_type, $writ_id, $writver, $staff_id);
	$header_is_open = ($is_new_writ) ? "false"  : "true" ;
     
if(manage_writ::check_for_use_in_pay_calc($writ_id , $writver, $staff_id)==null)
	$salary_fields_is_open = true ;

$time_limited = $FullWrt[0]['time_limited'];


//$drp_MaritalStatus = manage_domains::DRP_MaritalStatus("marital_status",$objWrt->marital_status);
//$drp_educ = manage_domains::DRP_EducLevel("education_level",$objWrt->education_level,"with:50%");
//$studyFielsArr = manage_domains::DRP_StudyField_StudyBranch("form_WritForm","sfid", "sbid",$objWrt->sfid, $objWrt->sbid);
//$writTypeArr = manage_domains::DRP_writType_writSubType("form_WritForm", "writ_type_id", "writ_subtype_id",$objWrt->writ_type_id ,$objWrt->writ_subtype_id);
$drp_salary_pay_proc = manage_domains::DRP_SalaryPayProc("salary_pay_proc",$objWrt->salary_pay_proc ,"with:50%");	
$drp_annual_effect = manage_domains::DRP_Annual_Effect("annual_effect", $objWrt->annual_effect ,"with:50%");
$drp_costCenters = manage_domains::DRP_CostCenters("cost_center_id", $objWrt->cost_center_id);
$drp_jobs = manage_domains::DRP_Jobs("job_id", $objWrt->job_id, "form_WritForm");
$workplaceArr = manage_domains::DRP_State_City("form_WritForm", "work_state_id", "work_city_id", $objWrt->work_state_id, $objWrt->work_city_id);
$drp_worktime = manage_domains::DRP_WorkTimeType("worktime_type", $objWrt->worktime_type);
$drp_emp_state = manage_domains::DRP_EMP_STATE_WST("emp_state", $objWrt->emp_state);
$drp_emp_mode = manage_domains::DRP_EMP_MODE_WST("emp_mode", $objWrt->emp_mode);
$drp_science_level = manage_domains::DRP_Science_Level("science_level", $objWrt->science_level);
//............................................
if(!empty($writ_id))
{
	$drp_not_assigned_items = manage_writ_item::DRP_get_not_assigned_items("salary_item_type_id", 
			$writ_id, $writver, $staff_id);
}

if(!$is_new_corrective)
{ 	
	/*unset($writ_rec); 
	$writ_rec['writ_id']             = $_REQUEST["WID"] ;
	$writ_rec['writ_ver']            = $writver ;
	$writ_rec['staff_id']            = $objWrt->staff_id ;
	$writ_rec['execute_date']        = $exedate ;
	$writ_rec['corrective_writ_id']  = $objWrt->corrective_writ_id ;
	$writ_rec['corrective_writ_ver'] = $objWrt->corrective_writ_ver ;*/
		    
	$prior_writ_object = $objWrt->get_prior_writ(); 
	     
	if ($prior_writ_object)
	{
		$dg = new sadaf_datagrid("PreW",$js_prefix_address . "../data/writ.data.php?task=selectItemWrit&WID=" . $prior_writ_object->writ_id .
			"&WVER=" . $prior_writ_object->writ_ver .
			"&STID=" . $prior_writ_object->staff_id .
			"&Base=" . $prior_writ_object->base ,"PreWGRID");

		$col = $dg->addColumn("عنوان", "full_title", "string");
		$col->summaryRenderer = "function(){return 'جمع';}";
		$col->width =35;

		$col = $dg->addColumn("مبلغ", "value", "int");
		$col->summaryType = GridColumn::SummeryType_sum;
		$col->summaryRenderer = "function(v,p,r){return WritForm.currencyRender(v,p,r);}";
		$col->width = 15;

		$dg->EnableSummaryRow = true;
		$dg->EnableSearch = false ; 
		$dg->EnablePaging = false ;
		$dg->title = "اقلام حقوقی حکم قبلی";
		$dg->AddCurrencyStringRow(1);
		$dg->width = 340;
		$prevItemsGrid = $dg->makeGrid_returnObjects();

	} 
           
	$dg = new sadaf_datagrid("curWritItem",$js_prefix_address . "../data/writ.data.php?task=selectItemWrit&WID=" . $objWrt->writ_id .
		"&WVER=" . $objWrt->writ_ver . "&STID=" . $objWrt->staff_id ,"WGRID");

	$dg->addColumn("", "writ_id","",true);
	$dg->addColumn("", "writ_ver","",true);
	$dg->addColumn("", "staff_id","",true);
	$dg->addColumn("", "salary_item_type_id","",true);

	$col = $dg->addColumn("عنوان", "full_title", "string");
	$col->summaryRenderer = "function(){return 'جمع';}";
	$col->width =35;

	$col = $dg->addColumn("مبلغ", "value", "int");
	$col->summaryType = GridColumn::SummeryType_sum;
	$col->summaryRenderer = "function(v,p,r){return WritForm.currencyRender(v,p,r);}";
	$col->width = 15;
	 
	$col = $dg->addColumn("پارامتر1", "param1", "int");
	$col->width = 12;
	$col = $dg->addColumn("پارامتر2", "param2", "int");
	$col->width = 12;
	$col = $dg->addColumn("پارامتر3", "param3", "int");
	$col->width = 12;

	if(!$readOnly)
	{
		if($item_accessObj->FullDeleteAccess())
		{
			$dg->deleteButton = true;
			$dg->deleteHandler = "function(){WritFormObject.DeleteItem();}";
		}
		if($item_accessObj->FullUpdateAccess())
			$dg->addButton("", "ویرایش", "edit", "function(){WritFormObject.EditItem('');}");
	}
    else {
        $dg->addButton("", "مشاهده", "view", "function(){WritFormObject.EditItem('view');}");
    }
	$dg->width = 550;
    $dg->EnableSummaryRow = true;
	$dg->EnableSearch = false ;
	$dg->EnablePaging = false ;
	$dg->title = "اقلام حقوقي حکم";
	$dg->AddCurrencyStringRow(5);
	$curItemsGrid = $dg->makeGrid_returnObjects();
}  
?>
<style>
.blueText{color: #0D6EB2;font-weight: bold;}
.XX_button
{
	font:normal 12px tahoma,verdana,helvetica;
	width: 150px;
	height: 24px;
	padding-bottom: 4px;
}
</style>
<script>
WritForm.prototype.afterLoad = function()
{   
	this.writ_id = "<?=$writ_id?>";
	this.writ_ver = "<?=$writver?>";
	this.staff_id = "<?= $staff_id?>";
	this.person_type = "<?= $objWrt->person_type ?>";
	this.ouid = "<?= $objWrt->ouid ?>";

	<?if(isset($prevItemsGrid)){?>
		this.prevItemsGrid = <?= $prevItemsGrid?>;
		this.prevItemsGrid.render(this.get("PreWGRID"));
	<?}?>
	<?if(isset($curItemsGrid)){?>
		this.curItemsGrid = <?= $curItemsGrid?>;
		this.curItemsGrid.render(this.get("WGRID"));
	<?}?>
	<?if($objWrt->person_type == HR_WORKER || $objWrt->person_type == HR_CONTRACT){?>
		this.jobCombo = <?= $drp_jobs["extCombo"]?>;
		this.jobCombo.on("select", function(combo,record){
			WritFormObject.get("job_group").innerHTML = record.data.job_group;
		});
	<?}?>
	<?if($objWrt->person_type != HR_WORKER){?>
		this.stateCombo = <?= $workplaceArr["masterExtCombo"]?>;
	<?}?>

	<?if($readOnly || !$accessObj->FullUpdateAccess()){
        if(HRSystem == PersonalSystemCode ){ ?>
            Ext.get(this.form).readonly(new Array("single_print", "multi_print", "btn_save", "warning_date", "warning_message", "remembered"));
        <? } else  ?>  Ext.get(this.form).readonly(new Array("single_print", "multi_print", "btn_save", "warning_date", "warning_message", "remembered" , "cost_center_id" ));
	<?} ?>
	<? if(ExceptionHandler::GetExceptionCount() != 0){ ?>
		ShowExceptions(this.get("ErrorDiv"), <?= ExceptionHandler::ConvertExceptionsToJsObject() ?>);
	<?  }   ?>
}

</script>
<center>
<form id="form_WritForm">
<div id="ErrorDiv" ></div>
<input type='hidden' id='writ_id' name='writ_id' value="<?= $writ_id ?>">
<input type='hidden' id='writ_ver' name='writ_ver' value="<?= !empty($writver) ? $writver : "" ?>">
<input type='hidden' id='staff_id' name='staff_id' value="<?= $staff_id ?>">
<input type="hidden" id="do_not_calc_item" name="do_not_calc_item" value="1">

<input type='hidden' id='corrective_date' name='corrective_date' value="<?= !empty($objWrt->corrective_date) ? $objWrt->corrective_date : "" ?>">
<input type='hidden' id='corrective_writ_id' name='corrective_writ_id' value="<?= !empty($objWrt->corrective_writ_id) ? $objWrt->corrective_writ_id : "" ?>">
<input type='hidden' id='corrective_writ_ver' name='corrective_writ_ver' value="<?= !empty($objWrt->corrective_writ_ver) ? $objWrt->corrective_writ_ver : "" ?>">
<input type='hidden' id='execute_date' name='execute_date' value="<?= !empty($objWrt->execute_date) ? $objWrt->execute_date : "" ?>">
<input type='hidden' id='corrective' name='corrective' value="<?= !empty($objWrt->corrective) ? $objWrt->corrective : "" ?>">

<div id="mainpanel" >
<table id="InfoPanel" width="100%">
	<tr>
		<td width="15%">شماره حکم :</td>
		<td class="blueText"  ><?=$objWrt->writ_id ?></td>
		<td width="15%">نسخه حکم:</td>
		<td class="blueText" ><?=$objWrt->writ_ver ?></td>
	</tr>
	<tr>
		<td>
		شماره شناسايي :
		</td>
		<td width="75%" colspan ="3"  class="blueText" ><?=$objWrt->staff_id ?></td>
		
	</tr>
	<tr>
		<td>
		نام :
		</td>
		<td class="blueText"  ><?=$objPerson->pfname ?></td>
		<td>
		 نام خانوادگي :
		</td>
		<td class="blueText" ><?=$objPerson->plname ?></td>
	</tr>
	<tr>
		<td>
		نام پدر :
		</td>
		<td class="blueText" ><?= $objPerson->father_name ?></td>
		<td>
		جنسيت :
		</td>
		<td class="blueText" ><?= ($objPerson->sex == 1 ) ?  'مرد' : 'زن' ?></td>
	</tr>
	<tr>
		<td>
		شماره شناسنامه :
		</td>
		<td class="blueText" ><?= $objPerson->idcard_no ?></td>
		<td>
		تاريخ و محل صدور :
		</td>
		<td class="blueText" ><?= DateModules::Miladi_to_Shamsi($objPerson->issue_date)."-".
		                                      $fullInfo[0]['issueTitle']."-".$fullInfo[0]['issue_place'] ?></td>
	</tr>
	
	<tr>
		<td>
		تاريخ و محل تولد :
		</td>
		<td class="blueText" ><?= DateModules::Miladi_to_Shamsi($objPerson->birth_date)."-". 
		                                      $fullInfo[0]['birthTitle']."-". $fullInfo[0]['birth_place'] ?></td>
		<td>
		وضعيت نظام وظيفه :
		</td>
		<td class="blueText" ><?= $fullInfo[0]['militaryTitle'] ?></td>
	</tr>
	
	<tr>
		<td>
		وضعيت تاهل :
		</td>
		<td class="blueText" ><?= $fullInfo[0]['maritalTitle'] ?></td>
		<td>
		مشمول عايله مندي :
		</td>
        <td class="blueText" >
		<? ($objWrt->family_responsible == 1) ?	$ch5 = "checked" : $ch5 = "" ; ?>
		<input type="checkbox" value="1" <?= $ch5?> id="family_responsible"  name="family_responsible">
		</td>
	</tr>
		
	<tr>
		<td>
		تعداد فرزند :
		</td>
		<td class="blueText" ><?= $FullWrt[0]['children_count'] ?></td>
		
		<td>
		تعداد فرزند مشمول حق اولاد :
		</td>
		<td class="blueText" ><?= $FullWrt[0]['included_children_count'] ?></td>
	</tr>
	<tr>
		<td>
		آخرين مدرک تحصيلي :
		</td>
		<td class="blueText" ><?= $FullWrt[0]['educTitle'] ?></td>
		<td>
		رشته و گرايش :
		</td>
		<td class="blueText" ><?= $FullWrt[0]['sf_ptitle']."-".$FullWrt[0]['sb_ptitle'] ?></td>
	</tr>
	<tr>
		<td>
		نوح حکم :
		</td>
		<td class="blueText" ><?= $FullWrt[0]['MainWtitle'] ?></td>
		<td>
		نوع فرعي حکم :
		</td>
		<td class="blueText" ><?= $FullWrt[0]['wst_title'] ?></td>
	</tr>
	<tr>
		<td>روال پرداخت حقوق :</td>
		<td class="blueText" colspan="3"><?= $drp_salary_pay_proc ?></td>
	</tr>
	<tr>
		<td>اثر سنواتي: </td>
		<td class="blueText"><?= $drp_annual_effect?></td>
		<td>تاريخ صدور :</td>
		<td class="blueText" >
			<input type="text" id="issue_date" name="issue_date" class="x-form-text x-form-field" style="width: 100px"
			  	   value="<?= DateModules::Miladi_to_Shamsi($objWrt->issue_date)?>" >
		</td>
	</tr>
	<tr>
		<td>تاريخ اجراي حکم :</td>
		<td class="blueText" >
			<?= DateModules::Miladi_to_Shamsi($objWrt->execute_date)?></td>
		<?if(HRSystem == SalarySystem){?>
		<td>تاريخ پرداخت :</td>
		<td class="blueText" >
			<input type="text" id="pay_date" name="pay_date" class="x-form-text x-form-field" style="width: 100px" 
			  	   value="<?= DateModules::Miladi_to_Shamsi($objWrt->pay_date)?>" >
  	   	</td>
		<?}else{?>
		<td></td>
		<td></td>
		<?}?>
	</tr>	
	
	<?  if ($time_limited == 1) { ?>
	<tr>
		<td>تاريخ شروع قرارداد :</td>
		<td class="blueText" >
        <input type="text" id="contract_start_date" name="contract_start_date" class="x-form-text x-form-field" style="width: 100px"
			  	   value="<?= DateModules::Miladi_to_Shamsi($FullWrt[0]['contract_start_date'])?>" >
        </td>
		<td>تاريخ پايان قرارداد :</td>
		<td class="blueText" >
                <input type="text" id="contract_end_date" name="contract_end_date" class="x-form-text x-form-field" style="width: 100px"
			  	   value="<?= DateModules::Miladi_to_Shamsi($FullWrt[0]['contract_end_date'])?>" >
        </td>
	</tr>	
	<? } ?>
	<tr>
		<td>
		شماره نامه مرجع :
		</td>
		<td class="blueText" >
			<input type="text" id="ref_letter_no" name="ref_letter_no" class="x-form-text x-form-field" style="width: 100px" 
			  	   value="<?= $objWrt->ref_letter_no ?>" >
		</td>
		<td>
		تاريخ نامه مرجع :
		</td>
		<td class="blueText" >
		<input type="text" id="ref_letter_date" name="ref_letter_date" class="x-form-text x-form-field" style="width: 100px" 
			  	   value="<?= ($objWrt->ref_letter_date) ? DateModules::Miladi_to_Shamsi($objWrt->ref_letter_date) : "" ?>" >
  	   	</td>
	</tr>		
	
	<tr>
		<td>
		شماره دبيرخانه : 
		</td>
		<td class="blueText" >
			<input type="text" id="send_letter_no" name="send_letter_no" class="x-form-text x-form-field" style="width: 100px" 
			  	   value="<?= $objWrt->send_letter_no ?>" >
		</td>
		<td>
		تاريخ دبيرخانه : 
		</td>
		<td class="blueText" >
		<input type="text" id="send_letter_date" name="send_letter_date" class="x-form-text x-form-field" style="width: 100px" 
			  	   value="<?= ($objWrt->send_letter_date) ? DateModules::Miladi_to_Shamsi($objWrt->send_letter_date) : "" ?>" >
  	   	</td>
	</tr>
	<tr>
		<td>واحد محل خدمت :</td>
		<td class="blueText"><input type="text" id="ouid" name="ouid" value="<?= $objWrt->ouid?>"></td>
		<td>مرکز هزينه :</td>
		<td class="blueText">
			<?= (HRSystem == SalarySystem) ? $drp_costCenters : $FullWrt[0]['c_title']?></td>
	</tr>
	<tr>
		<td>عنوان کامل واحد محل خدمت :</td>
		<td colspan="3"><span class="blueText" id="unit_title"><?= $objWrt->ouid != "" ? manage_units::get_full_title($objWrt->ouid) : "" ?></span>
		</td>
	</tr>
	<?if($objWrt->person_type == HR_EMPLOYEE || $objWrt->person_type == HR_PROFESSOR || $objWrt->person_type == HR_CONTRACT){?>
	<tr>
		<td>پست سازمانی :</td>
		<td><input class="blueText" type="text" id="post_id" name="post_id" style="width:98%" value="<?= $objWrt->post_id ?>"></td>
		<td colspan="2">
		<span class="blueText" id="post_title">
				<?= $objWrt->post_id != "" ? $FullWrt[0]['post_no'] ."-".$FullWrt[0]['post_title'] : ""?></span>
		</td>
	</tr>
	<?}if($objWrt->person_type == HR_WORKER || $objWrt->person_type == HR_CONTRACT){?>
	<tr>
		<td>گروه مورد تطبیق :</td>
		<td><input type="text" name="cur_group" class="x-form-text x-form-field" id="cur_group" value="<?= $objWrt->cur_group ?>"></td>
		<td></td>
		<td></td>
	</tr>
	<?}if($objWrt->person_type == HR_WORKER || $objWrt->person_type == HR_CONTRACT){?>
	<tr>
		<td>شغل :</td>
		<td><?= $drp_jobs["combo"] ?></td>
		<td>گروه :</td>
		<td class="blueText" id="cur_group"><?= $FullWrt[0]['cur_group'] ?></td>
	</tr>
	<?}if($objWrt->person_type == HR_EMPLOYEE){?>
	<tr>
		<td>گروه :</td>
		<td><input type="text" name="cur_group" class="x-form-text x-form-field" id="cur_group" value="<?= $FullWrt[0]['cur_group'] ?>"></td>
		<td>طبقه :</td>
		<td class="blueText"><?= ( $FullWrt[0]['execute_date'] >= '2009-03-21' ) ?  ($FullWrt[0]['cur_group']-4) : $FullWrt[0]['job_category'] ?></td>
	</tr>	
	<?}if($objWrt->person_type != HR_WORKER){?>
	<tr>
		<td>محل خدمت :  استان :</td>
		<td><?= $workplaceArr["masterCombo"]?></td>
		<td>شهر :</td>
		<td><?= $workplaceArr["detailCombo"]?></td>
	</tr>
	<?}?>
	<tr>
		<td>سنوات خدمت :</td>
		<td><input type="text" style="width:50px" id="onduty_year" name="onduty_year" value="<?= $objWrt->onduty_year?>" class="x-form-text x-form-field"> سال
			<input type="text" style="width:50px" id="onduty_month" name="onduty_month" value="<?= $objWrt->onduty_month?>" class="x-form-text x-form-field"> ماه
			<input type="text" style="width:50px" id="onduty_day" name="onduty_day" value="<?= $objWrt->onduty_day?>" class="x-form-text x-form-field"> روز
		</td>
		<?if($objWrt->person_type == HR_EMPLOYEE){?>
		<td>زمان کاري :</td>
		<td><?= $drp_worktime?></td>
		<?}else if($objWrt->person_type == HR_PROFESSOR){?>
		<td>زمان کاري :</td>
		<td><?= $drp_worktime?></td>
		<?}else{?>
		<td></td>
		<td></td>
		<?}?>
	</tr>
	<?if($objWrt->person_type == HR_EMPLOYEE){?>
	<tr>
		<td>سنوات مرتبط و مشابه :</td>
		<td><input type="text" style="width:50px" id="related_onduty_year" name="related_onduty_year" value="<?= $objWrt->related_onduty_year?>" class="x-form-text x-form-field"> سال
			<input type="text" style="width:50px" id="related_onduty_month" name="related_onduty_month" value="<?= $objWrt->related_onduty_month?>" class="x-form-text x-form-field"> ماه
			<input type="text" style="width:50px" id="related_onduty_day" name="related_onduty_day" value="<?= $objWrt->related_onduty_day?>" class="x-form-text x-form-field"> روز
		</td>
		<td></td>
		<td></td>
	</tr>
	<?}if($objWrt->person_type == HR_PROFESSOR){?>
	<tr>
		<td>عنوان دانشگاهي :</td>
		<td><?= $drp_science_level?></td>
		<td>پايه :</td>
		<?if(($objWrt->emp_state == EMP_STATE_SOLDIER_CONTRACTUAL ||
            $objWrt->emp_state == EMP_STATE_CONTRACTUAL || $objWrt->emp_state == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL) &&
			DateModules::CompareDate($objWrt->execute_date, DateModules::shamsi_to_miladi('1389/07/01')) < 0){?>

		<td class="bluText"><?= $objWrt->base?></td>
		<?}else{?>
		<td><input type="text" id="base" name="base" class="x-form-text x-form-field" value="<?= $objWrt->base?>"></td>
		<?}?>
	</tr>
	<?}?>
	<tr>
		<td>وضعيت استخدامي :</td>
		<td><?= $drp_emp_state?></td>
		<td>حالت استخدامي :</td>
		<td><?= $drp_emp_mode?></td>
	</tr>
	<tr>
		<td>
		شرح حکم : 
		</td>
		<td width="75%" colspan="3">
		<textarea id="description" name="description" rows="4" cols="83" 
		 		  class=" x-form-field" ><?= $objWrt->description ?></textarea>
 	  	</td>
	</tr>			
	<tr>
	    <td>
		تاريخ يادآوري :
		</td>
		<td class="blueText" >
		<input type="text" id="warning_date" name="warning_date" class="x-form-text x-form-field" 
		       value="<?= DateModules::Miladi_to_Shamsi($objWrt->warning_date) ?>" style="width: 80px" >
		</td>
		<td>
		پيام ياد آوري :
		</td>
		<td class="blueText" >
		<input type="text" id="warning_message" name="warning_message" class="x-form-text x-form-field" style="width: 130px" 
			   value="<?= $objWrt->warning_message?>" >
	    </td>
	</tr>	
	<tr>
	    <td>
		فقط ثبت سابقه ؟
		</td>
		<td class="blueText" >
		<? ($objWrt->history_only == 1) ?	$ch2 = "checked" : $ch2 = "" ; ?>
		<input type="checkbox" value="1" <?= $ch2?> id="history_only"  name="history_only">
		</td>
		<td>
		يادآوري شد؟
		</td>
		<td class="blueText" >
		<? ($objWrt->remembered == 1) ?	$ch3 = "checked" : $ch3 = "" ; ?>
		<input type="checkbox" value="1" <?= $ch3?> id="remembered"  name="remembered">
		</td>
   </tr>
   <? if($state == WRIT_PERSONAL){ ?>
	<tr>
		<td>
		عدم ارسال به حقوق
		</td>
		<td class="blueText" >
		<? ($objWrt->dont_transfer == 1) ?	$ch4 = "checked" : $ch4 = "" ; ?>
		<input type="checkbox" value="1" <?= $ch4?> id="dont_transfer"  name="dont_transfer">
		</td>
	</tr>			
	<?}?>
	<tr>  
		<td  colspan="4" align="center"><br><hr><br>
		<?if($accessObj->FullUpdateAccess() || $accesscostcenterObj->FullUpdateAccess() ){ ?>
		<input type="button" id="btn_save" class="button" onclick="WritFormObject.saveInfo();" value="ذخیره">
		<?}if($is_auto_writ && !$is_new_corrective && !$readOnly && $accessObj->FullUpdateAccess()){?>
	     <input type="button" class="XX_button" onclick="WritFormObject.Calculate();" value="ثبت و محاسبه اقلام">
         <input type="button" class="XX_button" onclick="WritFormObject.Recalculate();" value="ثبت و محاسبه مجدد اقلام">
		<?}?>
   
		<input id="single_print" class="big_button" type="button" value="چاپ تك نسخه" onclick="WritFormObject.print('');" >
		<input id="multi_print" class="big_button" type="button" value="چاپ تمام نسخ" onclick="WritFormObject.print('all');" >
		</td>
	</tr>

	<?if(!$readOnly && $accessObj->FullUpdateAccess()){?>
	<tr>
		<td  colspan="4" align="center">
		<?if ( $objWrt->corrective_writ_id > 0 && $objWrt->corrective_writ_ver > 0){ ?>
        	<br><input type="button" class="big_button" onclick="WritFormObject.Next_Corrective_Writ();" value="اصلاح حکم بعدي">
    	<?}if (( $objWrt->corrective_writ_id > 0 && $objWrt->corrective_writ_ver > 0 )||($objWrt->corrective > 0)){
			echo '&nbsp;';
            if( $objWrt->corrective )
            {
				$corrective_writ_id = $objWrt->writ_id ;
                $corrective_writ_ver = $objWrt->writ_ver ;
                        
                if(manage_writ::corrective_writs_is_used($corrective_writ_id,$corrective_writ_ver,$objWrt->staff_id))
    				$disable_state = 'disabled=true';
			}
                 
			?>   
	    	<input type="button" class="big_button" onclick="WritFormObject.Prior_Corrective_Writ(); " value="لغو آخرين اصلاح"  <? $disable_state ?> >
			<?          
		}?>
        </td>  
    </tr>
	<?}if(!$objWrt->corrective && !$readOnly && $item_accessObj->InsertAccess()){?>
    <tr>
		<td colspan="4" align="center"><br><hr><br>
			<div id="div_fs">
				<div id="pnl_fs">
					<?= $drp_not_assigned_items?><input type="button" class="button" value="افزودن" onclick="WritFormObject.AddSalaryItem();">
				</div>
			</div>
		</td>
	</tr>
	<?}?>
  </table>
</div>

<?if(!$is_new_corrective){?>
 <table width="100%">
 <tr>
	 <td width="40%" valign="top"><div id="PreWGRID"></div></td>
	<td align="right" valign="top"><div id="WGRID"></div></td>
 </tr>
 </table>
<?}?>
</form>
</center>
