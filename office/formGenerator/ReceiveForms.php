<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
include_once '../global/public.php';
include_once '../formGenerator/form.class.php';
include_once '../userManagement/um.class.php';
require_once(inc_dataGrid);

$ArchiveFlag = isset($_GET["archive"]) ? true : false;
?>
<style>
	.yellowRow { background-color:#FFFFCC !important;}
</style>
<script type="text/javascript" src="../formGenerator/Forms.js?2"></script>
<script type="text/javascript">
var ArchiveFlag = <?= $ArchiveFlag ? "true" : "false"?>;
function SendStatusRender(v)
{
	if(v == 'send')
		return "<div align='center' ext:qtip='فرم ارسال شده است' class='send'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
	else if(v == 'return')
		return "<div align='center' ext:qtip='فرم برگردانده شده است' class='undo'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
	return "";
	
}

function TimeOutRender(v,p,r)
{
	if(r.data.SendStatus != 'raw')
		return "";
		
	if(r.data.TimeOut < 0)
		return "<div align='center' ext:qtip='مهلت زمانی ارسال فرم " + (r.data.TimeOut*(-1)) 
			+ " روز است که پایان یافته است' class='fail'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
			
	if(r.data.TimeOut == 0)
		return "<div align='center' ext:qtip='امروز آخرین روز مهلت ارسال فرم می باشد' class='fail'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
			
	if(r.data.TimeOut == 1)
		return "<div align='center' ext:qtip='فقط یک روز تا مهلت ارسال فرم باقی مانده است' class='warning'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
			
	return r.data.TimeOut + " روز";
}
function SendCommentRender(v)
{
	return "<div align='center' ext:qtip='" + v + "' class='comment'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
}

function operationRender(v,p,r)
{
	return "<div align='center' title='مشاهده فرم' onclick='operationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;"+
		"background-image:url(images/setting.gif);" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function senderRender(v,p,r)
{
	var post = (r.data.postTitle == null) ? "نامشخص" : r.data.postTitle;  
	var unit = (r.data.unitTitle == null) ? "نامشخص" : r.data.unitTitle;
	return "<span ext:qtip='پست: <b>" + post + "</b><br>واحد :<b>" + unit + "</b>'>" + v + "</span>";
}

function operationMenu(e)
{
	var record = dg_grid.selModel.getSelected();
	var op_menu = new Ext.menu.Menu();
	
	op_menu.add({text: 'مشاهده فرم',iconCls: 'info',handler : function(){showInfo('receive');} });
	
	var readonly = (record.data.SendStatus == "raw") ? "false" : "true";
	op_menu.add({text: 'پیوست',iconCls: 'attach',handler : function(){Attaching(readonly);} });
		
	if(record.data.SendStatus == 'raw')
	{
		op_menu.add({text: 'پاسخ به فرم',iconCls: 'comment',handler : function(){Responsing();} });
		
		if(record.data.maxStep != record.data.StepID)
		{
			op_menu.add({text: 'ارسال فرم',iconCls: 'send',handler : function(){Sending('send','receive');} });
			op_menu.add({text: 'بازگرداندن فرم',iconCls: 'undo',handler : function(){Sending('return','receive');} });
		}
		
		if(record.data.ApplyAccess == "1")
		{
			op_menu.add({text: 'اعمال تغییرات',iconCls: 'refresh2',handler : function(){ApplyChanges();} });
			op_menu.add({text: 'حذف',iconCls: 'remove',handler : function(){DeleteForm();} });
		}
	}
	else
	{
		op_menu.add({text: 'حذف',iconCls: 'remove',handler : function(){DeleteForm();} });
	}
	
	op_menu.add({text: 'سابقه',iconCls: 'history',handler : function(){showHistoryForm();} });
	op_menu.add({text: 'بایگانی سازمانی',iconCls: 'archive',handler : function(){CoArchiving();} });
	
	<?if($ArchiveFlag){?>
		op_menu.add({text: 'خروج از بایگانی شخصی',iconCls: 'archive',handler : function(){ArchiveForm(0);} });
	<?}else{?>
		op_menu.add({text: 'بایگانی شخصی',iconCls: 'archive',handler : function(){ArchiveForm(1);} });
	<?}?>
	
	op_menu.showAt([e.clientX-150, e.clientY+5]);
}

new Ext.Panel({
	id: "j1",
	applyTo: "mainpanel",
	contentEl : "searchPanel",
	title: "جستجوی فرم",
	autoHeight: true,
	width: "550px",
	collapsible : true
});
	
	
function unloadFn()
{
	if(sendWin)
	{
		sendWin.destroy();
		sendWin = null;
	}
	if(HistoryWin)
	{
		HistoryWin.destroy();
		HistoryWin = null;
	}
	if(responseWin)
	{
		responseWin.destroy();
		responseWin = null;
	}
	if(AttachWin)
	{
		AttachWin.destroy();
		AttachWin = null;
	}
	if(ArchiveWin)
	{
		ArchiveWin.destroy();
		ArchiveWin = null;
	}
}
new Ext.form.SHDateField({id: "fromDate",applyTo: 'fromDate',format: 'Y/m/d'});
new Ext.form.SHDateField({id: "toDate",applyTo: 'toDate',format: 'Y/m/d'});

var btn = new Ext.Button({renderTo : "btn_search", text: "جستجو", iconCls: 'search', 
	handler: function(){searching();}});
var btn = new Ext.Button({renderTo : "btn_clear", text: "پاک کردن فرم گزارش", iconCls: 'clear',
	handler: function(){
		Ext.getCmp("fromDate").setValue("");
		Ext.getCmp("toDate").setValue("");
		Ext.get("mainpanel").clear();
	}
});
Ext.getCmp("j1").collapse();

var ArchiveFlag = <?= $ArchiveFlag ? "true" : "false" ?>;
</script>
<?php
$url = "../formGenerator/wfm.data.php?task=ReceivedForms";
$url .= ($ArchiveFlag) ? "&archiveFlag=true" : "";

$dg = new sadaf_datagrid("dg", $url, "dg_forms");
$dg->method = "POST";

$col = $dg->addColumn('', "timeRemain", "string", true);
$col = $dg->addColumn('مهلت', "TimeOut", "string");
$col->renderer = "TimeOutRender";
$col->width = 20;

$col = $dg->addColumn('', "SendStatus", "string");
$col->renderer = "SendStatusRender";
$col->width = 18;

$col = $dg->addColumn('كد فرم', "LetterID", "string");
$col->width = 25;

$col = $dg->addColumn('كد پیگیری', "pursuitCode", "string");
$col->width = 40;

$col = $dg->addColumn('نوع فرم', "FormName", "string");
$col->width = 40;

$col = $dg->addColumn('فرستنده', "sender", "string");
$col->renderer = "senderRender";
$col->width = 80;

$col = $dg->addColumn('تاریخ دریافت', "SendDate", "string");
$col->renderer = "function(v){return v.substring(10) + ' ' + MiladiToShamsi(v.substring(0,10));}";
$col->width = 30;

$col = $dg->addColumn("وضعیت جاری فرم","StepTitle","string");
$col->width = 50;

$col = $dg->addColumn('', "referenceID", "string", true);
$col = $dg->addColumn('', "RefID", "string", true);
$col = $dg->addColumn('', "FormID", "string", true);
$col = $dg->addColumn('', "ViewFlag", "string", true);
$col = $dg->addColumn('', "SendType", "string", true);
$col = $dg->addColumn('', "StepID", "string", true);
$col = $dg->addColumn('', "Response", "string", true);
$col = $dg->addColumn('', "maxStep", "string", true);
$col = $dg->addColumn('', "ApplyAccess", "string", true);
$col = $dg->addColumn('', "CopyAccess", "string", true);

$col = $dg->addColumn('', "postTitle", "string", true);
$col = $dg->addColumn('', "unitTitle", "string", true);
//---------------------------
$col = $dg->addColumn("اقدامات","SendComment","");
$col->renderer = "SendCommentRender";
$col->sortable = false;
$col->width = 20;
//---------------------------
$col = $dg->addColumn("عملیات","","");
$col->renderer = "operationRender";
$col->sortable = false;
$col->width = 20;
//---------------------------
$dg->height = 400;
$dg->title = $ArchiveFlag ? "بایگانی شخصی" : "فرم های دریافتی";
$dg->width = 700;
$dg->DefaultSortField = "regDate";
$dg->DefaultSortDir = "asc";
$dg->EnableSearch = false;
$dg->makeGrid();
//.....................................................
$drp_forms = FormGenerator::Drp_AllForms("FormsList", "---");
$drp_users = user_management::Drp_AllUsers("PersonID", "", "---");
//.....................................................
?>
<script type="text/javascript">
dg_grid.getView().getRowClass = function(record, index)
{
	if(record.data.ViewFlag == "0")
		return "yellowRow";
	return;
}
</script>
<style>
.fail{background-image:url('../img/error.gif') !important;}
.warning{background-image:url('../img/warning.gif') !important;}
</style>
<div style="width:700px" align="center">
	<div id="mainpanel">
		<table id="searchPanel" class="x-form-text" border="0" cellpadding="2" width="100%" cellspacing="2">
		<tr>
			<td width="15%">تاریخ دریافت از:</td>
			<td><input type="text" name="fromDate" id="fromDate"></td>
			<td>تا :</td>
			<td><input type="text" name="toDate" id="toDate"></td>
		</tr>
		<tr>
			<td>نوع فرم :</td>
			<td><?= $drp_forms ?></td>
			<td>فرستنده :</td>
			<td><?= $drp_users ?></td>
		</tr>
		<tr>
			<td colspan="4" valign="middle" style="height: 25px" align="center">
				<hr>
				<table><tr>
					<td><div id="btn_search"></div></td>
					<td><div id="btn_clear"></div></td>
				</tr></table>				
			</td>
		</tr>
		</table>
	</div>
</div>
<br>
<div id="dg_forms"></div>
<!-- ----------------------------------------- -->
<div id="win_sendForm" class="x-hidden">
	<div id="pnl_sendForm" style="padding: 4px">
		<div id="sendEditor"></div>
	</div>
</div>
<!-- ----------------------------------------- -->
<div id="div_history" class="x-hidden"></div>
<!-- ----------------------------------------- -->
<div id="div_fromView" class="x-hidden"></div>
<!-- ----------------------------------------- -->
<div id="div_attach" class="x-hidden"></div>
<!-- ----------------------------------------- -->
<div id="div_CoArchive" class="x-hidden"></div>
<!-- ----------------------------------------- -->
<div id="win_response" class="x-hidden">
	<div id="pnl_response" style="padding: 4px">
		<div id="responseEditor"></div>
	</div>
</div>






