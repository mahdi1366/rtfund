<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
include_once '../userManagement/um.class.php';
require_once(inc_dataGrid);

?>
<script>
new Ext.Panel({
	id: "new",
	applyTo: "div_new",
	contentEl : "tbl_new",
	title: "ایجاد جایگزینی",
	autoHeight: true,
	width: "550px"
});
new Ext.form.SHDateField({id: 'StartDate',applyTo: 'StartDate',format: 'Y/m/d',width: 120});
new Ext.form.SHDateField({id: 'EndDate',applyTo: 'EndDate',format: 'Y/m/d',width: 120});

function DeleteRender(v,p,r)
{
	if(new Date().dateFormat("Y-m-d") <= r.data.EndDate)
		return "<div align='center' title='حذف' class='remove' onclick='Deleting();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:100%;height:16'></div>";
}

function Deleting(v,p,r)
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
		
	var record = dg_grid.selModel.getSelected();

	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ReplacementDelete",
	  		RowID : record.data.RowID
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});
}

function Saveing()
{
	if(document.getElementById("src_PersonID").value == document.getElementById("des_PersonID").value)
	{
		alert("یک فرد نمی تواند جایگزین خودش باشد.");
		return;
	}
	if(document.getElementById("StartDate").value == "" || document.getElementById("EndDate").value == "")
	{
		alert("تکمیل تاریخ شروع و پایان الزامی است.");
		return;
	}
	if(ShamsiToMiladi(document.getElementById("StartDate").value) < new Date().dateFormat("Y/m/d"))
	{
		alert("تاریخ شروع نمی تواند قبل از تاریخ امروز باشد.");
		return;
	}
	if(Ext.getCmp("StartDate").getValue() >= Ext.getCmp("EndDate").getValue())
	{
		alert("تاریخ شروع نمی تواند بزرگتر و یا برابر تاریخ پایان باشد.");
		return;
	}
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ReplacementSave"
	  	},
	  	form : document.getElementById("MainForm"),
	  	
	  	success : function(response,o)
	  	{
	  		if(response.responseText == "ConflictError")
	  		{
	  			alert("تاریخ زمانی که وارد کرده اید با یک جایگزینی دیگر تداخل دارد");
	  			return;
	  		}
	  		dg_store.reload();
	  		Ext.getCmp("new").hide();
	  		Ext.get("div_new").clear();
	  	}	
	});
}

function AddingAction()
{
	Ext.getCmp("new").show();
}
</script>
<?
$dg = new sadaf_datagrid("dg", "../formGenerator/wfm.data.php?task=ReplacementSelect", "dg_grid");
$dg->method = "POST";

$col = $dg->addColumn('فرد اصلی', "src_fullName", "string");
$col->width = 40;

$col = $dg->addColumn('فرد جایگزین', "des_fullName", "string");
$col->width = 40;

$col = $dg->addColumn('تاریخ شروع', "StartDate", "string");
$col->renderer = "function(v){return v.substring(10) + ' ' + MiladiToShamsi(v.substring(0,10));}";
$col->width = 50;

$col = $dg->addColumn('تاریخ پایان', "EndDate", "string");
$col->renderer = "function(v){return v.substring(10) + ' ' + MiladiToShamsi(v.substring(0,10));}";
$col->width = 50;

$col = $dg->addColumn('', "RowID", "string", true);
//---------------------------
$col = $dg->addColumn("حذف","","");
$col->renderer = "DeleteRender";
$col->sortable = false;
$col->width = 10;
//---------------------------
$dg->addButton("Add","ایجاد","add","AddingAction");
//---------------------------
$dg->height = 350;
$dg->title = "جایگزینی افراد سازمان برای مدت مشخص جهت دریافت فرم ها";
$dg->width = 700;
$dg->DefaultSortField = "RowID";
$dg->DefaultSortDir = "asc";
$dg->makeGrid();
//.....................................................
$drp_src_PersonID = user_management::Drp_AllUsers("src_PersonID", "");
$drp_des_PersonID = user_management::Drp_AllUsers("des_PersonID", "");
//.....................................................

?>
<div style="width: 700px" align="center">
	<div align="right" id="div_new" style="width: 500px">
		<table width="100%" id="tbl_new">
		<tr>
			<td>فرد اصلی:</td>
			<td><?= $drp_src_PersonID ?></td>
			<td>فرد جایگزین:</td>
			<td><?= $drp_des_PersonID ?></td>
		</tr>
		<tr>
			<td>تاریخ شروع :</td>
			<td><input type="text" id="StartDate" name="StartDate"></td>
			<td>تاریخ پایان :</td>
			<td><input type="text" id="EndDate" name="EndDate"></td>
		</tr>
		<tr>
			<td align="center" colspan="4">
				<input class="button" type="button" value="ذخیره" onclick="Saveing();">
				<input class="button" type="button" value="انصراف" onclick="Ext.getCmp('new').hide();">
			</td>
		</tr>
		</table>
	</div>
	<br>
	<div id="dg_grid" align="right"></div>
</div>
<script>Ext.getCmp("new").hide();</script>