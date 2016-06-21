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

<script language="JavaScript">
	SalaryItemType.prototype.afterNewItemLoad = function()
	{
		this.SystemTitle = "1";		
		this.salary_item_type_id = '<?= !empty($_POST["SIT"]) ? $_POST["SIT"] : "" ?>';		
			
		this.Mode = this.salary_item_type_id == "" ? "new" : "edit";
			
		this.validity_start_date = new Ext.form.SHDateField({
			applyTo: this.get('validity_start_date'),
			format: 'Y/m/d'
		});

		this.validity_end_date = new Ext.form.SHDateField({
			applyTo: this.get('validity_end_date'),
			format: 'Y/m/d'
		});

        this.store1 = new Ext.data.Store({
                                            fields : ["InfoID","InfoDesc"],
                                            proxy : {
                                                type: 'jsonp',
                                                url : this.address_prefix + "../../global/domain.data.php?task=searchComputetype",
                                                reader: {
                                                    root: 'rows',
                                                    totalProperty: 'totalCount'
                                                }
                                            },
                                            autoLoad:true
                                        });


        this.store2 = new Ext.data.Store({
                                                fields : ["InfoID","param1","InfoDesc"],
                                                proxy : {
                                                    type: 'jsonp',
                                                    url : this.address_prefix + "../../global/domain.data.php?task=searchMultiplicand",
                                                    reader: {
                                                        root: 'rows',
                                                        totalProperty: 'totalCount'
                                                    }
                                                }
                                            });


	this.computeTypCombo = new Ext.form.field.ComboBox({
		store : this.store1,
		width : 180,
		typeAhead: false,
		queryMode : "local",
		displayField : "InfoDesc",
		valueField : "InfoID",
		hiddenName : "salary_compute_type",
		applyTo : this.get("salary_compute_type"),
		listeners : {
			select : function(combo, records){  
				SalaryItemTypeObject.MultiplicandCombo.reset();
				SalaryItemTypeObject.store2.load({
					params : { MasterID: records[0].data.InfoID}
				})
			}
		}
	});

	this.MultiplicandCombo = new Ext.form.field.ComboBox({
		store : this.store2,
		width : 150,
		typeAhead: false,
		queryMode : "local",
		displayField : "InfoDesc",
		valueField : "InfoID",
		hiddenName : "multiplicand",
		applyTo : this.get("multiplicand")
	});

    this.store1.load({
        callback:function(){
            SalaryItemTypeObject.computeTypCombo.setValue("<?= $obj->salary_compute_type ?>");
            SalaryItemTypeObject.store2.load({
                params:{MasterID:SalaryItemTypeObject.computeTypCombo.getValue()},
                callback:function(){
                    SalaryItemTypeObject.MultiplicandCombo.setValue("<?= $obj->multiplicand ?>");
                }
            });
        }
    });
	
	
 
    if(this.disabled)
    {       
        this.validity_start_date.disable();
        this.validity_end_date.disable();
        this.computeTypCombo.disable();
        this.MultiplicandCombo.disable(); 
		  
		  Ext.get(this.get("person_type")).disable();
		  Ext.get(this.get("effect_type")).disable();
		  Ext.get(this.get("available_for")).disable();
		  Ext.get(this.get("full_title")).disable();
		  Ext.get(this.get("print_title")).disable();
		  Ext.get(this.get("print_order")).disable();		  
		  Ext.get(this.get("user_data_entry")).disable();
		  Ext.get(this.get("backpay_include")).disable();
		  Ext.get(this.get("editable_value")).disable();
		  Ext.get(this.get("param1_title")).disable();
		  Ext.get(this.get("param1_input")).disable();
		  Ext.get(this.get("param2_title")).disable();
		  Ext.get(this.get("param2_input")).disable();
		  Ext.get(this.get("param3_title")).disable();
		  Ext.get(this.get("param3_input")).disable();
		  Ext.get(this.get("param4_title")).disable();
		  Ext.get(this.get("param4_input")).disable();
		  Ext.get(this.get("param5_title")).disable();
		  Ext.get(this.get("param5_input")).disable();
		  Ext.get(this.get("param6_title")).disable();
		  Ext.get(this.get("param6_input")).disable();
		  Ext.get(this.get("param7_title")).disable();
		  Ext.get(this.get("param7_input")).disable();  
		  Ext.get(this.get("remember_distance")).disable();
		  Ext.get(this.get("remember_message")).disable();
		  
        Ext.get(this.get("insure_include")).enable();
        Ext.get(this.get("tax_include")).enable();
        Ext.get(this.get("retired_include")).enable();
        Ext.get(this.get("pension_include")).enable();
        Ext.get(this.get("credit_topic")).enable();
		Ext.get(this.get("CostType1")).enable();
		Ext.get(this.get("CostType2")).enable();
	
    }
	
	
	
	}

     SalaryItemTypeObject.afterNewItemLoad();
</script>
	
        <input type="hidden" name="person_type" id="person_type" value="<?= $_POST["pt"] ?>"/>
		<input type="hidden" name="writ_type_id" id="writ_type_id" value="<?= $_POST["wtid"] ?>"/>
		<input type="hidden" name="WT_title" id="WT_title" value="<?= $_POST["WT_title"] ?>" >
		<input type="hidden" name="writ_subtype_id" id="writ_subtype_id" value="<?= isset($_POST["wstid"]) ? $_POST["wstid"] : "" ?>"/>
		<table width="100%">
	<tr>
		<td width="20%">نوع اصلي حکم:</td>
		<td width="80%" height="21px" colspan="3"><b><?= $drp_WRT_TYP ?></b></td>
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
		محدوديت زماني ؟
		</td>
		<td width="80%"  colspan="3" >
		<input type="checkbox" value="1" id="time_limited"  name="time_limited" 
			class="x-form-text x-form-field" style="width: 10px" <?= ($obj->time_limited == "1") ? "checked" : ""?> >
		</td>
	</tr>
    <tr>
        <td colspan="4">
        <font color=green>گزينه محدوديت زماني براي احكامي استفاده مي شود كه ثبت تاريخ شروع و خاتمه قرارداد در آنها اجباري مي باشد .</font>
        </td>
    </tr> 
    <tr>
		<td width="20%">
		امضاي مستخدم؟
		</td>
		<td width="80%" colspan="3" >
		<input type="checkbox" value="1" id="req_staff_signature"  name="req_staff_signature" 
			class="x-form-text x-form-field" style="width: 10px" <?= ($obj->req_staff_signature == "1") ? "checked" : ""?> >
		</td>
	</tr>
    <tr>
        <td colspan="4">
            <font color=green>گزينه امضاي مستخدم براي مواردي كه حكم بايستي توسط خود مستخدم نيز امضا گردد استفاده مي شود .</font>
        </td>
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
		<td width="20%" colspan="4">
		نمايش در گزارش خلاصه پرونده؟		
		&nbsp;<input type="checkbox" value="1" id="show_in_summary_doc"  name="show_in_summary_doc" 
			     class="x-form-text x-form-field" style="width: 10px" <?= ($obj->show_in_summary_doc == "1") ? "checked" : ""?> >
		</td>
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
   
