<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'post.data.php';
require_once '../org_units/unit.class.php';
require_once inc_dataReader;

$post_id = !empty($_GET["post_id"]) ? $_GET["post_id"] : "";
$obj = new manage_posts($post_id);

$drp_job_field = manage_domains::DRP_JobFields("jfid", $obj->jfid, "---", "428");
//$drp_units = manage_units::DRP_Units("ouid", $obj->ouid, "---", "428");
$drp_person_type = manage_domains::DRP_PersonType("person_type",$obj->person_type,""," ");
$event = "onchange='postChange();'" ;
$drp_post_type = manage_posts::dropdown_post_type("post_type", $obj->post_type ,"","",$event);
$drp_kind_super = manage_posts::dropdown_sup_type("SupervisionKind", $obj->SupervisionKind, "-");
$DRP_jcid_jfid = manage_domains::DRP_jcid_jfid("newPostForm", $jcid, $jfid, "jcid", "jfid", $obj->_jcid, $obj->jfid);
?>
<script type="text/javascript" src="/HumanResources/global/LOV/LOV.js"></script>
<script>
new Ext.form.SHDateField({id: 'validity_start',applyTo: 'validity_start',format: 'Y/m/d'});
new Ext.form.SHDateField({id: 'validity_end',applyTo: 'validity_end',format: 'Y/m/d'});
if(document.getElementById('post_type').value == 5)
   document.getElementById("kind_super").style.display="";
else 
   document.getElementById("kind_super").style.display="none";
 

var personStore = <?= dataReader::MakeStoreObject($js_prefix_address . "personal/persons/data/person.data.php?task=searchPerson&newPersons=true"
                          ,"'PersonID','pfname','plname','unit_name','person_type','staff_id','personTypeName'") ?>;
							  
new Ext.form.ComboBox({
	id: 'PID',
	store: personStore,
	emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
	typeAhead: false,
	loadingText: 'در حال جستجو...',
	pageSize:10,	
	width: 428,	
	itemSelector: 'tr.search-item',
	applyTo: 'PID'
			
	,tpl: new Ext.XTemplate(
			'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
		    	,'<td height="23px">کد پرسنلی</td>'
				,'<td>کد شخص</td>'
		    	,'<td>نام</td>'
		    	,'<td>نام خانوادگی</td>'
		    	,'<td>واحد محل خدمت</td></tr>',
		    '<tpl for=".">',
		    '<tr class="search-item" style="border-left:0;border-right:0">'
		    	,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
		    	,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
		    	,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
		    	,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
		    '</tpl>'
		    ,'</table>')
	    						        
	,onSelect: function(record){
		
		document.getElementById('staff_id').value = record.data.staff_id;
		document.getElementById('PID').value = "[" + record.data.PersonID + "] " + record.data.pfname + ' ' + record.data.plname;
		this.collapse();
	}		 	                        
});

new Ext.form.TriggerField({
	triggerCls:'x-form-search-trigger',
	onTriggerClick : function(){
		this.setValue(LOV_OrgUnit());
	},
	applyTo : document.getElementById("ouid"),
	width : 90
});

function postChange()
{
   if(document.getElementById('post_type').value == 5){
       document.getElementById("kind_super").style.display=""; 
    }
    else { document.getElementById("kind_super").style.display="none";
           document.getElementById("SupervisionKind").value = 0  ; 
    }
    
}


</script>
<form id="newPostForm">
<input type="hidden" name="post_id" id="post_id" value="<?= isset($_GET["post_id"]) ? $_GET["post_id"] : "" ?>"/>
<input type="hidden" name="parent_post_id" id="parent_post_id" value="<?= $_GET["parent_post_id"] ?>"/>
<input type="hidden" name="parent_path" value="<?= $_GET["parent_path"]?>">
<table width="100%">
	<tr>
		<td width="20%">پست سازماني رده بالاتر :</td>
		<td colspan="3" height="21px"><b><?= $_GET["parentText"] ?></b></td>
	</tr>
	<tr>
		<td height="21px">شماره شناسایی پست:</td>
		<td colspan="3"><?= isset($_GET["post_id"]) ? $_GET["post_id"] : "" ?></td>
	</tr>
	<tr>
		<td>شماره پست:</td>
		<td colspan="3"><input type="text" class="x-form-text x-form-field" name="post_no" id="post_no" 
			style="width:90%" value="<?= $obj->post_no ?>"></td>
	</tr>
	<tr>
		<td>عنوان پست:</td>
		<td colspan="3"><input type="text" class="x-form-text x-form-field" name="title" id="title" 
			style="width:90%" value="<?= $obj->title ?>"></td>
	</tr>
	<tr>
		<td>ردیف پست:</td>
		<td colspan="3"><input type="text" class="x-form-text x-form-field" name="post_rowno" 
			style="width:90%" id="post_rowno" value="<?= $obj->post_rowno ?>"></td>
	</tr>
    <tr>
		<td>گروه:</td>
		<td colspan="3"><?= $drp_person_type?></td>
	</tr>
	<tr>
		<td>نوع پست:</td>
		<td colspan="3" ><?= $drp_post_type?></td>
       
	</tr>         
        <tr id="kind_super">
            <td>نوع سرپرستی :</td>
            <td colspan="3" ><?= $drp_kind_super?></td>
        </tr>             
       
        	<tr>
		<td>رسته :</td>
		<td colspan="3"><?= $jcid?></td>
	</tr>
	<tr>
		<td>رشته شغلی:</td>
		<td colspan="3"><?= $jfid?></td>
	</tr>
	<tr>
		<td>تاریخ شروع اعتبار :</td>
		<td><input type="text" class="x-form-text x-form-field" name="validity_start" id="validity_start"
			 value="<?= DateModules::miladi_to_shamsi($obj->validity_start) ?>"></td>
		<td>تاریخ پایان اعتبار :</td>
		<td><input type="text" class="x-form-text x-form-field" name="validity_end" id="validity_end" 
			 value="<?= DateModules::miladi_to_shamsi($obj->validity_end) ?>"></td>
	</tr>
	<tr>
		<td>توضیحات:</td>
		<td colspan="3"><textarea type="text" class="x-form-field" name="description"  rows="3"
			style="width:90%" id="description" ><?= $obj->description ?></textarea></td>
	</tr>
	<tr>
		<td height="21px">داخل شمول :</td>
		<td colspan="3"><input type="checkbox" id="included" name="included" value="1" <?= $obj->included == "1" ? "checked" : "" ?>></td>
	</tr>
	<tr>
		<td height="21px">امتیاز مدیریت :</td>
		<td colspan="3"><input type="checkbox" id="ManagementCoef" name="ManagementCoef" value="1" <?= $obj->ManagementCoef == "1" ? "checked" : "" ?>></td>
	</tr>
	<tr>
		<td>واحد سازمانی :</td>
		<td colspan="3">
			<input type="text" name="ouid" id="ouid" value="<?= $obj->ouid?>">
		</td>
	</tr>
	<tr>
		<td>کد واحد سازمانی :</td>
		<td></td>
	</tr>
	<tr>
		<td colspan="4" align="center" style="color:green" height="40px"><hr>
		اين قسمت فقط براي پستهاي فرعي اشخاص است و پستهاي اصلي از طريق صدور حکم در کارگزيني ثبت مي شود .</td>
	</tr>
	<tr>
		<td>شماره شناسایی :</td>
		<td colspan="3">
            <input type="hidden" id="staff_id" name="staff_id" value="<?= $obj->staff_id ?>" />
            <div style="float:right"><input type="text" id="PID" value="<?= (!empty($obj->staff_id)) ? "[" . $obj->staff_id . "] " . $obj->_fullName : ""?>"/></div>
			<div style="float:left;width:20px"><img src="/HumanResources/img/rubber.gif" border="0" 
				onClick="clearPID();" style="cursor:pointer;" title="پاک کردن فرد انتخابی"></div>
		</td>
	</tr>
	<tr>
		<td colspan="4" align="center">
			<hr>
			<input type="button" value="ذخیره" onclick="savePost();" class="button">
			<input type="button" value="انصراف" onclick="Ext.getCmp('ext_NewPost').hide();" class="button">
		</td>
	</tr>
	
</table>
<br>
</form>