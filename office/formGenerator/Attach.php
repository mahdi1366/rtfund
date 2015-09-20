<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
require_once(inc_dataGrid);

$readonly = ($_GET["readonly"] == "true") ? true : false;
?>
<script type="text/javascript">
function DeleteRender(v,p,record)
{
	if(record.data.PersonID == '<?= $_SESSION["PersonID"] ?>')
		return "<div align='center' title='مشاهده فرم' class='cross' onclick='DeletAttach();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:100%;height:16'></div>";
}

function fileRender(v)
{
	return "<div align='center' title='" + v + "' class='search'" + 
		" onclick='window.open(\"../../<?= AttachImagePath?>" + v + "\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function DeletAttach()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	var record = dgAttach_grid.selModel.getSelected();
	
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "DeleteAttach",
	  		LetterID : '<?= $_GET["LetterID"] ?>',
	  		AttachID : record.data.AttachID,
	  		FileName : record.data.FileName
	  	},
	  	success : function(response,o)
	  	{
	  		dgAttach_store.reload();
	  	}	
	});		
}
function SaveAttach()
{
	if(document.getElementById("AttachFile").value == "")
	{
		alert("ابتدا فایل پیوست را تکمیل کنید.");
		return;
	}
	var mask = new Ext.LoadMask(document.getElementById("div_newattach"),{msg: 'در حال ذخیره ...'});
	mask.show();
		
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method: 'POST',		
		form: document.getElementById("baseform"),
		isUpload: true,
	  	params : {
	  		task : "SaveAttach",
	  		LetterID : '<?= $_GET["LetterID"] ?>'
	  	},
	  	success : function(response,o)
	  	{
	  		mask.hide();
	  		dgAttach_store.reload();
	  		document.getElementById("AttachTitle").value = "";
	  		document.getElementById("AttachFile").value = "";
	  	}	
	});		
}
<?if(!$readonly){ ?>
new Ext.Panel({
	id: "newAttach",
	applyTo: "div_newattach",
	contentEl : "tbl_newattach",
	title: "ایجاد پیوست",
	autoHeight: true,
	width: "500px"
});
<?} ?>
</script>
<?php
$dg = new sadaf_datagrid("dgAttach", "../formGenerator/wfm.data.php?task=SelectAttach", "dg_attach");
$dg->method = "POST";
$dg->baseParams = "LetterID : " . $_GET["LetterID"];

$col = $dg->addColumn('ایجاد کننده', "PersonName", "string");
$col->width = 40;

$col = $dg->addColumn('عنوان پیوست', "title", "string");
$col->width = 40;

$col = $dg->addColumn('تاریخ پیوست', "RegDate", "string");
$col->renderer = "function(v){return v.substring(10) + ' ' + MiladiToShamsi(v.substring(0,10));}";
$col->width = 50;

$col = $dg->addColumn('فایل پیوست', "FileName", "string");
$col->renderer = "fileRender";
$col->width = 40;


$col = $dg->addColumn('', "LetterID", "string", true);
$col = $dg->addColumn('', "AttachID", "string", true);
$col = $dg->addColumn('', "PersonID", "string", true);

//---------------------------
if(!$readonly)
{
	$col = $dg->addColumn("حذف","","");
	$col->renderer = "DeleteRender";
	$col->sortable = false;
	$col->width = 10;
}
//---------------------------
$dg->height = 300;
$dg->title = "پیوست های فرم";
$dg->width = 500;
$dg->DefaultSortField = "RegDate";
$dg->DefaultSortDir = "asc";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->makeGrid();
?>
<form id="baseform" method="POST" enctype='multipart/form-data'>
	<div style="padding: 10px;;background-color: white">
		<?if(!$readonly){ ?>
		<div id="div_newattach" style="width: 500px">
			<table width="100%" id="tbl_newattach">
			<tr>
				<td>عنوان پیوست :</td>
				<td><input type="text" name="AttachTitle" id="AttachTitle" style="width: 90%" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>فایل پیوست :</td>
				<td><input type="file" id="AttachFile" name="AttachFile"></td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input class="button" type="button" value="ذخیره" onclick="SaveAttach();">
					<input class="button" type="button" value="بازگشت" onclick="AttachWin.hide();">
				</td>
			</tr>
			</table>
		</div>
		<br>
		<?} ?>
		<div id="dg_attach"></div>
	</div>
</form>