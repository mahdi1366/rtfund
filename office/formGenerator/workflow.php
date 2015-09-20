<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
require_once(inc_dataGrid);
include_once '../userManagement/um.class.php';
?>
<script type="text/javascript">
function UPRender(v,p,r)
{
	if(r.data.StepID == 1)
		return "";
	return "<div align='center' title='حرکت مرحله به بالا' class='up' onclick='changeLevel(\"up\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function DOWNRender(v,p,r)
{
	if(dg_store.getAt(dg_store.getCount()-1).data.StepID == r.data.StepID)
		return "";
	return "<div align='center' title='حرکت مرحله به پایین' class='down' onclick='changeLevel(\"down\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function changeLevel(direction)
{
	var record = dg_grid.selModel.getSelected();
	
	Ext.Ajax.request({
	  	url : "../formGenerator/form.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ChangeLevel",
	  		FormID : '<?= $_GET["FormID"] ?>',
	  		StepID : record.data.StepID,
	  		direction : direction
	  	},
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});		
}
//-----------------------------------------------------------------------
function DeleteRender(v,p,record)
{
	return "<div align='center' title='مشاهده فرم' class='cross' onclick='Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function EditRender()
{
	return "<div align='center' title='ویرایش اطلاعات' class='edit' onclick='showInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function showInfo()
{
	document.getElementById("MainForm").reset();
	
	var record = dg_grid.selModel.getSelected();
	
	document.getElementById("StepID").value = record.data.StepID;
	document.getElementById("StepTitle").value = record.data.StepTitle;
	document.getElementById("BreakDuration").value = record.data.BreakDuration;
	Ext.getCmp("ext_PersonID").setValue(record.data.PersonID);
	
	if(record.data.elements)
	{
		var elems = record.data.elements.split(',');
		for(i=0; i < elems.length; i++)
		{
			if(elems[i] == "2000")
				document.getElementById("referenceApply").checked = true;
			else if(elems[i] == "2001")
				document.getElementById("CopyAccess").checked = true;
			else
				document.getElementById("elem_" + elems[i]).checked = true;
		}
	}
	
}
function Deleting()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	var record = dg_grid.selModel.getSelected();
	
	Ext.Ajax.request({
	  	url : "../formGenerator/form.data.php",
	  	method : "POST",
	  	params : {
	  		task : "DeleteSteps",
	  		FormID : '<?= $_GET["FormID"] ?>',
	  		StepID : record.data.StepID
	  	},
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});		
}
function adding()
{
	document.getElementById("MainForm").reset();
	document.getElementById("StepID").value = "";
}
function AddingAction()
{
	if(document.getElementById("StepTitle").value == "")
	{
		alert("ابتدا عنوان مرحله را تکمیل کنید.");
		return;
	}
	var mask = new Ext.LoadMask(document.getElementById("div_new"),{msg: 'در حال ذخیره ...'});
	mask.show();
		
	Ext.Ajax.request({
	  	url : "../formGenerator/form.data.php",
	  	method: 'POST',		
		form: document.getElementById("MainForm"),
	  	params : {
	  		task : "SaveSteps",
	  		FormID : '<?= $_GET["FormID"] ?>'
	  	},
	  	success : function(response,o)
	  	{
	  		mask.hide();
	  		dg_store.reload();
	  		document.getElementById("MainForm").reset();
	  	}	
	});		
}
function returning()
{
	OpenPage("../formGenerator/buildForm.php");
}
new Ext.Panel({
	id: "new",
	applyTo: "div_new",
	contentEl : "tbl_new",
	title: "ایجاد مرحله",
	autoHeight: true,
	width: "600px"
});
</script>
<?php
$dg = new sadaf_datagrid("dg", "../formGenerator/form.data.php?task=SelectSteps", "dg_grid");
$dg->method = "POST";
$dg->baseParams = "FormID : " . $_GET["FormID"];

$col = $dg->addColumn('کد مرحله', "StepID", "string");
$col->width = 20;


$col = $dg->addColumn('عنوان مرحله', "StepTitle", "string");
$col->width = 40;

$col = $dg->addColumn('مجری', "fullName", "string");
$col->width = 50;

$col = $dg->addColumn('مهلت به روز', "BreakDuration", "string");
$col->width = 20;

$dg->addColumn('', "elements", "string",true);
$dg->addColumn('', "PersonID", "string",true);
//---------------------------
$col = $dg->addColumn('بالا', "", "string");
$col->renderer = "UPRender";
$col->sortable = false;
$col->width = 10;

$col = $dg->addColumn('پایین', "", "string");
$col->renderer = "DOWNRender";
$col->sortable = false;
$col->width = 10;
//---------------------------

$dg->addButton("return","بازگشت","undo","returning");


$col = $dg->addColumn("ویرایش","","");
$col->renderer = "EditRender";
$col->sortable = false;
$col->width = 10;
//---------------------------
$col = $dg->addColumn("حذف","","");
$col->renderer = "DeleteRender";
$col->sortable = false;
$col->width = 10;
//---------------------------
$dg->addButton("","ایجاد مرحله", "add", "adding");
//---------------------------
$dg->height = 300;
$dg->title = "مراحل گردش فرم";
$dg->width = 600;
$dg->DefaultSortField = "StepID";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->makeGrid();

$drp_PersonID = user_management::Drp_AllUsers("PersonID", "");

//...............................
$obj = new CHECKBOXLIST();

$obj->datasource = dataAccess::RUNQUERY("select * from fm_form_details where FormID=" . $_GET["FormID"]);
$obj->idfield = "elem_%ElementID%";
$obj->textfield = "%ElementTitle%";
$obj->Allchecked = false;
$obj->columnCount = 2;
$elements = $obj->bind_checkboxlist();
//...............................
?>
<style>
.up{background-image: url('../img/up.gif') !important}
.down{background-image: url('../img/down.gif') !important}
</style>
<input type="hidden" id="StepID" name="StepID">
<div id="div_new" style="width: 600px">
	<table width="100%" id="tbl_new">
	<tr>
		<td height="21px">عنوان مرحله :</td>
		<td><input type="text" name="StepTitle" id="StepTitle" style="width: 90%" 
			class="x-form-text x-form-field"></td>
		<td rowspan="7" valign="top"><?= $elements ?></td>
	</tr>
	<tr>
		<td height="21px">مجری مرحله :</td>
		<td><?= $drp_PersonID ?></td>
	</tr>
	<tr>
		<td height="21px">مهلت به روز :</td>
		<td><input type="text" name="BreakDuration" id="BreakDuration" style="width: 50%" 
			class="x-form-text x-form-field"></td>
	</tr>
	<tr>
		<td height="21px" colspan="2">
			<input type="checkbox" name="referenceApply" id="referenceApply" value="1">
			&nbsp;دسترسی به دکمه اعمال تغییرات فرم در آیتم وابسته به فرم
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td height="21px" align="center" colspan="2">
			<input class="button" type="button" value="ذخیره" onclick="AddingAction();"></td>
	</tr>
	</table>
</div>
<br>
<div id="dg_grid"></div>
