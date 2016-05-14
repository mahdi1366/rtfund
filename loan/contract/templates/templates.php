<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.09
//-----------------------------
ini_set("display_errors", "On");

require_once '../../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "templates.data.php?task=SelectTemplates", "div_dg");

$dg->addColumn("شماره الگو", "TemplateID");

$dg->addColumn("عنوان", "TemplateTitle");

$col = $dg->addColumn("حذف", "TemplateID");
$col->sortable = false;
$col->renderer = "function(v,p,r){return Templates.OperationRender(v,p,r);}";
$col->width = 40;

$dg->addButton("", "ایجاد الگوی جدید", "add", "function(){TemplatesObject.ShowNewTemplateForm();}");
$dg->addButton("", "کپی الگو", "copy", "function(){TemplatesObject.copyTemplate();}");

$dg->title = "لیست الگوهای قرارداد";
$dg->DefaultSortField = "TemplateID";
$dg->emptyTextOfHiddenColumns = true;
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "TemplateTitle";
$dg->width = 780;
$dg->height = 400;
$dg->pageSize = 15;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
Templates.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",

	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
};

Templates.OperationRender = function(v,p,r){

	return "<div align='center' title='حذف' class='remove' "+
		"onclick='TemplatesObject.RemoveItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16'></div>";
}

function Templates() {

	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_dg"));

	this.grid.on("itemdblclick", function(view, record){
		TemplatesObject.EditItem();
	});
}

TemplatesObject = new Templates();

Templates.prototype.ShowNewTemplateForm = function () {
	framework.OpenPage(this.address_prefix + "NewTemplate.php", "ثبت الگوی قرارداد");
}

Templates.prototype.EditItem = function () {
	
	record = TemplatesObject.grid.getSelectionModel().getLastSelected();
	framework.OpenPage(this.address_prefix + "NewTemplate.php", "  ویرایش الگوی قرارداد",
			{TemplateID: record.data.TemplateID});
}

Templates.prototype.RemoveItem = function () {
	
	Ext.MessageBox.confirm("","آیا مایل به حذف الگو می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = TemplatesObject;
		mask = new Ext.LoadMask(TemplatesObject.grid, {msg:'در حال حذف...'});
		mask.show();
	
		Ext.Ajax.request({
			url: me.address_prefix + 'templates.data.php?task=deleteTemplate',
			params: {                
				TemplateID: me.grid.getSelectionModel().getLastSelected().data.TemplateID            
			},
			method: 'POST',
			success: function (res) {
				mask.hide();
				var sd = Ext.decode(res.responseText);
				if (!sd.success) {
					if (sd.data != '')
						if (sd.data=='used')
							Ext.MessageBox.alert('', 'الگو استفاده شده است و قابل حذف نیست'); 
						else
							Ext.MessageBox.alert('', sd.data); 
					else
						Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
					return;
				}
				TemplatesObject.grid.getStore().load();
			}
		});
	});
		
}

Templates.prototype.copyTemplate = function () {
	
	record = this.grid.getSelectionModel().getLastSelected();
	if(record == null)
	{
		Ext.MessageBox.alert("","ابتدا الگوی مورد نظر را خود را انتخاب کنید");
		return;
	}
	
	Ext.MessageBox.confirm("","آیا مایل به ایجاد کپی از الگو می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = TemplatesObject;
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف...'});
		mask.show();
	
		Ext.Ajax.request({
			url: me.address_prefix + 'templates.data.php?task=CopyTemplate',
			params: {                
				TemplateID: me.grid.getSelectionModel().getLastSelected().data.TemplateID            
			},
			method: 'POST',
			success: function (res) {
				mask.hide();
				TemplatesObject.grid.getStore().load();
			}
		});
	});
		
}

</script>
<br>
<center>    
    <div id="div_dg"></div>
</center>
