<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
include_once '../global/public.php';
require_once(inc_dataGrid);

$access = GET_ACCESS(21);
?>
<script type="text/javascript">
function deleteRender()
{
	return "<div align='center' title='حذف' class='remove' onclick='Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function GridEdit()
{
	return "<div align='center' title='مشاهده اطلاعات فرم' class='edit' onclick='showInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function showInfo()
{
	document.getElementById('newElement').style.display = "block";
	
	var record = dg_grid.selModel.getSelected();
	
	document.getElementById("ElementID").value = record.data.ElementID;
	document.getElementById("ElementTitle").value = record.data.ElementTitle;
	document.getElementById("ordering").value = record.data.ordering;
	document.getElementById("ElementValue").value = record.data.ElementValue;
	document.getElementById("ElementType").value = record.data.ElementType;
	document.getElementById("width").value = record.data.width;
	
	if(record.data.referenceInfoID != null)
		document.getElementById("referenceField").value = "info_" + record.data.referenceField + "_" 
			+ record.data.referenceInfoID;
	else if(record.data.referenceField != "")
		document.getElementById("referenceField").value = record.data.referenceField;
	else
		document.getElementById("referenceField").value = "0";

	ElementTypeChange(document.getElementById("ElementType"));
}

function Deleting()
{
	var record = dg_grid.selModel.getSelected();
	if(record && confirm("آیا مایل به حذف می باشید؟"))
	{
		Ext.Ajax.request({
		  	url : "../formGenerator/form.data.php",
		  	method : "POST",
		  	params : {
		  		task : "elementDelete",
		  		ElementID : record.data.ElementID,
		  		ElementTitle : record.data.ElementTitle
		  	},
		  	success : function(response,o)
		  	{
		  		dg_store.reload();
		  	}	
		});
	}
}

function elementTypeRender(v,p,r)
{
	switch(v)
	{
		case "text" : return "متن ساده";
		case "date" : return "تاریخ";
		case "comment" : return "توضیحات";
		case "textarea" : return "شرح";
		case "combo" : return "کشویی";
		case "radio" : return "انتخابی یکتا";
		case "check" : return "انتخابی";
		case "bind" : return "مقدار داده اصلی";
	}
}
function Adding()
{
	document.getElementById('newElement').style.display = "block";
	Ext.get("newElement").clear();
	document.getElementById("ElementID").value = "";
	ElementTypeChange(document.getElementById("ElementType"));
}
function ElementTypeChange(elem)
{
	document.getElementById("FS_ElementValue").style.display = "none";
	document.getElementById("FS_comment").style.display = "none";
	document.getElementById("FS_datas").style.display = "none";
	
	switch(elem.value)
	{
		case "comment":
			document.getElementById("FS_comment").style.display = "";
			document.getElementById("FS_ElementValue").style.display = "block";
			break;
		case "combo":
		case "radio":
		case "check":
			document.getElementById("FS_datas").style.display = "";
			document.getElementById("FS_ElementValue").style.display = "block";
			break;
	}
}
function saveElem()
{
	if(document.getElementById("ElementTitle").value == "")
	{
		alert("تکمیل عنوان جزء الزامی است");
		return;
	}
	if(document.getElementById("ordering").value == "")
	{
		alert("تعیین شماره ترتیب الزامی است");
		return;
	}
	var elem = document.getElementById("referenceField");
	var referenceDesc = elem.options[elem.selectedIndex].text;
	
	Ext.Ajax.request({
	  	url : "../formGenerator/form.data.php",
	  	method : "POST",
	  	form : document.getElementById("MainForm"),
	  	params : {
	  		task : "elementSave",
	  		FormID : '<?= $_GET["FormID"] ?>',
	  		referenceDesc : referenceDesc
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		document.getElementById('newElement').style.display = "none";
	  		dg_store.reload();
	  	}	
	});
}

function returning()
{
	OpenPage("../formGenerator/buildForm.php");
}
</script>
<?php
$dg = new sadaf_datagrid("dg","../formGenerator/form.data.php?FormID=" . $_REQUEST["FormID"] ,"dg_elements");
$dg->method = "POST";
$dg->baseParams = "task: 'elementsSelect'";

$col = $dg->addColumn('كد جزء', "ElementID", "string");
$col->width = 40;

$col = $dg->addColumn("ترتیب","ordering","string");
$col->width = 40;
$col->editor = "new Ext.form.NumberField({alowBlank:false})";

$col = $dg->addColumn('عنوان', "ElementTitle", "string");
$col->editor = "new Ext.form.TextField({alowBlank:false})";

$col = $dg->addColumn('عرض', "width", "string");
$col->editor = "new Ext.form.TextField({alowBlank:false})";


$col = $dg->addColumn("نوع ستون","ElementType","string");
$col->renderer = "elementTypeRender";

$col = $dg->addColumn("فیلد مربوطه","referenceDesc","string");

$dg->addColumn("","ElementValue","string", true);
$dg->addColumn("","referenceField","string", true);
$dg->addColumn("","referenceInfoID","string", true);
//---------------------------
$dg->addButton("return","بازگشت","undo","returning");
if($access["add"])
{
	$dg->addButton("Add","ایجاد","add","Adding");
}
if($access['edit'])
{
	$col = $dg->addColumn("ویرایش","","");
	$col->renderer = "GridEdit";
	$col->sortable = false;
	$col->width = 30;
}
if($access["remove"])
{
	$col = $dg->addColumn("حذف","","");
	$col->renderer = "deleteRender";
	$col->sortable = false;
	$col->width = 30;
}
//---------------------------
$dg->height = 400;
$dg->title = "اجزای فرم " . $_REQUEST["FormName"];
$dg->width = 700;
$dg->editorGrid = true;
$dg->EnableSearch = false;
$dg->DefaultSortField = "ordering";
$dg->EnablePaging = false;
$dg->DefaultSortDir = "asc";
$dg->makeGrid();
//...................................................................
$refID = 0;
switch ($_REQUEST["reference"])
{
	case "devotions" :
		$refID = 1;
		break;
	case "states" : 
		$refID = 20;
		break;
	case "rents" :
		$refID = 21;
		break;
}
$obj = new DROPDOWN();
$obj->datasource = array_merge(array(array("ID"=>"0", "colName"=>"---")), 
	dataAccess::RUNQUERY("select colName,
		if(infoID=0, fieldName, concat('info_',replace(replace(fieldName,'info_',''),'.title',''),'_',infoID)) as ID
		
		from rpt_columns where JoinID=" . $refID . " and fieldName <> '-' order by ColumnID"));
$obj->valuefield = "%ID%";
$obj->textfield = "%colName%";
$obj->Style = 'class="x-form-text x-form-field" style=""';
$obj->id = "referenceField";
$drp_fields = $obj->bind_dropdown();
?>
<div id="newElement" style="display:none">
	<input type="hidden" id="ElementID" name="ElementID">
	<table class="panel" width="600px">
		<tr>
			<td>عنوان جزء :</td>
			<td><input type="text" class="x-form-text x-form-field" id="ElementTitle" name="ElementTitle"></td>
			<td>نوع جزء :</td>
			<td><select name="ElementType" id="ElementType" onchange="ElementTypeChange(this);" 
				class="x-form-text x-form-field">
				<option value="text">متن ساده</option>
				<option value="date">تاریخ</option>
				<option value="comment">توضیحات</option>
				<option value="textarea">شرح</option>
				<option value="combo">کشویی</option>
				<option value="radio">انتخابی یکتا</option>
				<option value="check">انتخابی</option>
				<option value="bind">مقدار داده اصلی</option>
			</select></td>
		</tr>
		<tr>
			<td>ترتیب در فرم :</td>
			<td><input type="text" class="x-form-text x-form-field" id="ordering" name="ordering"></td>
			<td>فیلد مربوطه در آیتم وابسته :</td>
			<td><?= $drp_fields ?></td>
		</tr>
		<tr>
			<td>عرض جزء:</td>
			<td><input type="text" class="x-form-text x-form-field" id="width" name="width"></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="4">
			<br>
				<fieldset id="FS_ElementValue" class="x-fieldset x-form-label-left" 
					style="background-color: #E9EFFE;display: none;">
					<div id="FS_comment">توضیحات مربوطه :<br>&nbsp;<br></div>
					<div id="FS_datas">اطلاعات کلیه موارد را با : جدا کنید به عنوان مثال (داده 1 : داده 2 : داده 3)<br>&nbsp;<br></div>
					
					<textarea id="ElementValue" name="ElementValue" class="x-form-field" rows="10" style="width:99%"></textarea>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center">
			<input type="button" value="ذخیره" class="button" onclick="saveElem();">
			<input type="button" value="انصراف" class="button" 
				onclick="document.getElementById('newElement').style.display = 'none';"></td>
		</tr>
	</table>
</div>
<div id="dg_elements"></div>
<script>
<?if($access["edit"]) {?>
dg_grid.addListener("afteredit",function(e){

 	 Ext.Ajax.request({
	  	url : "../formGenerator/form.data.php",
	  	method : "POST",
	  	params : {
	  		task : "elementSave",
	  		ElementID: e.record.data.ElementID,
	  		ElementTitle : e.record.data.ElementTitle,
	  		ordering : e.record.data.ordering,
	  		width : e.record.data.width
	  	},
	  	success : function(response){
  			dg_store.reload();
	  	}		  	
	});
});
<?} ?>
</script>