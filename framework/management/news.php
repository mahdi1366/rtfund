<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-----------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dgh = new sadaf_datagrid("dgh1",$js_prefix_address."framework.data.php?task=SelectNews","div_dg");

$dgh->addColumn("","NewsID",'string',true);

$col = $dgh->addColumn("عنوان", "NewsTitle");

$col=$dgh->addColumn("تاریخ شروع", "StartDate", GridColumn::ColumnType_date);
$col->width = 80;

$col=$dgh->addColumn("تاریخ پایان", "EndDate", GridColumn::ColumnType_date);
$col->width = 80;

$col=$dgh->addColumn("ویرایش", "context");
$col->renderer="FRW_news.ContextRender";
$col->width = 40;

if($accessObj->AddFlag)
{
	$dgh->addButton("", "ایجاد اعلان جدید", "add", "function(){FRW_newsObject.Add();}");
}
if($accessObj->RemoveFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "FRW_news.deleteRender";
	$col->width = 40;
}
$dgh->emptyTextOfHiddenColumns=true;
$dgh->width = 780;
$dgh->DefaultSortField = "NewsTitle";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 400;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;
$dgh->pageSize=12;
$grid = $dgh->makeGrid_returnObjects();
?>
<script>

FRW_news.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function FRW_news()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("div_form"),
		frame : true,
		title : "اطلاعات اعلان",
		width : 700,	
		layout : {
			type : "table",
			columns : 2
		},		
		height : 300,
		items : [{
			xtype : "textfield",
			fieldLabel : "عنوان اعلان",
			name : "NewsTitle",
			colspan : 2
		},{
			xtype : "shdatefield",
			name : "StartDate",
			fieldLabel : "تاریخ شروع"
		},{
			xtype : "shdatefield",
			name : "EndDate",
			fieldLabel : "تاریخ پایان"
		},{
			xtype : "container",
			colspan : 2,
			html : "<div id='FrameworkNewsEditor'></div>"
		},{
			xtype : "hidden",
			name : "NewsID"
		}],
		buttons :[{
			text : "ذخیره",
			disabled : this.AddAccess ? false : true,
			iconCls  : "save",
			handler : function(){
				me = FRW_newsObject;
				mask = new Ext.LoadMask(me.formPanel, {msg:'در حال ذخيره سازي...'});
				mask.show();

				me.formPanel.getForm().submit({
					url : me.address_prefix + "framework.data.php?task=SaveNews",
					method : "post",
					params : {
						context : CKEDITOR.instances.FrameworkNewsEditor.getData()
					},
					
					success : function(){
						me = FRW_newsObject;
						me.grid.getStore().load();
						me.formPanel.getForm().reset();
						mask.hide();
					}
				});
			}
		}]
	});
	
	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

	CKEDITOR.config.width = 'auto';
	CKEDITOR.config.height = 100;
	CKEDITOR.config.autoGrow_minHeight = 100;
	CKEDITOR.replace('FrameworkNewsEditor');	
	CKEDITOR.add;
		
	this.grid = <?= $grid ?>;                
	this.grid.render(this.get("div_dg"));
}

FRW_news.deleteRender = function(value, p, record)
{
	return "<div  title='حذف اطلاعات' class='remove' onclick='FRW_newsObject.DeleteTemplate();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:20px;height:16'></div>";
}

FRW_news.ContextRender = function(value, p, record)
{
	return "<div  title='ویرایش' class='edit' onclick='FRW_newsObject.ShowForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:20px;height:16'></div>";
}

var FRW_newsObject = new FRW_news();

FRW_news.prototype.Add = function(){
	
	this.formPanel.getForm().reset();
	CKEDITOR.instances.FrameworkNewsEditor.setData();
}

FRW_news.prototype.ShowForm = function(){
	
	record = this.grid.getSelectionModel().getLastSelected();
	this.formPanel.loadRecord(record);
	this.formPanel.down("[name=StartDate]").setValue(MiladiToShamsi(record.data.StartDate));
	this.formPanel.down("[name=EndDate]").setValue(MiladiToShamsi(record.data.EndDate));

	CKEDITOR.instances.FrameworkNewsEditor.on('instanceReady', function( ev ) {
		ev.editor.setData(record.data.context);
	});			
	CKEDITOR.instances.FrameworkNewsEditor.setData(record.data.context);

}

FRW_news.prototype.DeleteTemplate = function()
{    
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = FRW_newsObject;
		var record = me.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
			url: me.address_prefix + 'framework.data.php?task=DeleteNew',
			params:{
				NewsID : record.data.NewsID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				FRW_newsObject.grid.getStore().load();
			},
			failure: function(){}
		});		
	});
}

</script>

<center>
	<div id="div_form"></div>
	<br>
	<div id="div_dg"></div>
</center>


