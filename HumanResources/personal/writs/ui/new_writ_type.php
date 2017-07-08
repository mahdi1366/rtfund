<?php 
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------
require_once("../../../header.inc.php");
require_once("../data/writ.data.php");
require_once '../class/writ_subtype.class.php';
//--------------------------------------------------------------------

if(!empty($_POST["SIT"]))
{	
	$obj = new manage_salary_item_type($_POST["SIT"]);		   
}
else 
{		
	$obj = new manage_salary_item_type();
}
$wstid = !empty($_POST["wstid"]) ? $_POST["wstid"] : "";
$pt = !empty($_POST["pt"]) ? $_POST["pt"] : "";
$wtid = !empty($_POST["wtid"]) ? $_POST["wtid"] : "";


$obj = new manage_writ_subType($pt,$wtid,$wstid);

$drp_EMP_STATE = manage_domains::DRP_EMP_STATE_WST("emp_state", $obj->emp_state, "", "200px");
$drp_EMP_MODE  = manage_domains::DRP_EMP_MODE_WST("emp_mode", $obj->emp_mode, "", "200px");
$drp_WTT = manage_domains::DRP_WTT("worktime_type", $obj->worktime_type, "", "200px");
$drp_WRT_TYP = manage_domains::WRT_TYP("writ_type_id",$obj->writ_type_id , "", "200px");
$drp_pay_proc_wst = manage_domains::DRP_PAY_PROC_WST("salary_pay_proc", $obj->salary_pay_proc , "" , "200px");
$drp_post_effect_wst = manage_domains::DRP_POST_EFF_WST("post_effect", $obj->post_effect , "" , "200px");
$drp_annual_effect_wst = manage_domains::DRP_ANN_EFF_WST("annual_effect", $obj->annual_effect , "" , "200px");

?>

	
        <input type="hidden" name="person_type" id="person_type" value="<?= $_POST["pt"] ?>"/>
		<input type="hidden" name="writ_type_id" id="writ_type_id" value="<?= $_POST["wtid"] ?>"/>		
		<input type="hidden" name="writ_subtype_id" id="writ_subtype_id" value="<?= isset($_POST["wstid"]) ? $_POST["wstid"] : "" ?>"/>
		<table width="100%">
	<tr>
		<td width="20%">نوع اصلي حکم:</td>
		<td width="80%" height="21px" colspan="3"><b>قراردادی</b></td>
	</tr>
   
	<tr>
		<td width="20%">عنوان کامل : </td>
		<td width="30%"><input type="text" class="x-form-text x-form-field" name="title" id="title"
			style="width:80%" value="<?= $obj->title ?>"></td>
        <td width="20%">عنوان چاپي : </td>
		<td width="30%"><input type="text" class="x-form-text x-form-field" name="print_title" id="print_title"
			style="width:90%" value="<?= $obj->print_title ?>"></td>
	</tr>
	<tr>
		<td width="20%" >وضعيت استخدامي :</td>
		<td width="30%"><?= $drp_EMP_STATE ?></td>
        <td width="20%" >حالت استخدامي :</td>
		<td width="30%" ><?= $drp_EMP_MODE ?></td>
	</tr> 
    <tr>
		<td width="20%">زمان کاري :</td>
		<td width="80%" colspan="3" ><?= $drp_WTT ?></td>
	</tr>
	        
	<tr>
		<td width="20%">
		صدور خودکار ؟
		</td>
		<td width="80%" colspan="3" >
		<input type="checkbox" value="1" id="automatic"  name="automatic" 
			class="x-form-text x-form-field" style="width: 10px" <?= ($obj->automatic == "1") ? "checked" : ""?> >
		</td>
	</tr>  
    <tr>
        <td colspan="4">
            <font color=green>گزينه صدور خودكار مشخص مي كند كه كه آيا حكم مي تواند در صدور گروهي احكام استفاده شود يا خير </font>
        </td>
    </tr>
	<tr>
		<td width="20%">
		ويرايش فيلدها؟
		</td>
		<td width="80%" colspan="3" >
		<input type="checkbox" value="1" id="edit_fields"  name="edit_fields"
			class="x-form-text x-form-field" style="width: 10px" <?= ($obj->edit_fields == "1") ? "checked" : ""?> >
		</td>
	</tr>
    <tr>
        <td colspan="4">
            <font color=green>گزينه ويرايش فيلدها مشخص مي كند كه پس از صدور حكم امكان ويرايش آيتم هاي اطلاعاتي و شرح حكم وجود دارد يا خير </font>
        </td>
    </tr> 
    <tr>
		<td width="20%" >روال پرداخت حقوق :</td>
		<td width="80%" colspan="3" ><?= $drp_pay_proc_wst ?></td>
	</tr> 
    <tr>
		<td width="20%" >پست سازماني :</td>
		<td width="30%" ><?= $drp_post_effect_wst ?></td>
        <td width="20%" >اثر سنواتي:</td>
		<td width="30%" ><?= $drp_annual_effect_wst ?></td>
	</tr>    
     
    <tr>
		<td  width="20%">فاصله زماني يادآوري : </td>
		<td  width="30%" ><input type="text" class="x-form-text x-form-field" name="remember_distance" id="remember_distance"
			style="width:45%" value="<?= $obj->remember_distance ?>"></td>
        <td width="20%" >پيام يادآوري : </td>
		<td width="30%" ><input type="text" class="x-form-text x-form-field" name="remember_message" id="remember_message"
			style="width:90%" value="<?= $obj->remember_message ?>"></td>
	</tr> 
    <tr>
		<td width="25%">
		شرح حکم :
		</td>
		<td width="75%" colspan="3">
		<textarea id="comments" name="comments" rows="4" cols="115"
		 		  class=" x-form-field" ><?= $obj->comments ?>
        </textarea><br>
 	  	</td>
	</tr>		
</table>
   
