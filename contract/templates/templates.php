<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.09
//-----------------------------
ini_set("display_errors", "On");

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "templates.data.php?task=SelectTemplates", "div_dg");

$dg->addColumn("شماره الگو", "TemplateID");
$dg->addColumn("عنوان", "TemplateTitle");
$dg->addColumn("", "StatusDesc", "", true);
$dg->addColumn("", "TemplateContent", "", true);
$col = $dg->addColumn("وضعیت", "StatusCode");
$col->renderer = "function(v,p,record){return record.data.StatusDesc;} ";
$dg->addColumn("", "ExpireDate", "", true);
$col = $dg->addColumn("", "TemplateID");
$col->renderer = "function(v,p,r) {return  \"<div class='setting' title='عملیات ' onClick='TemplatesObject.OperationMenu(event);'  \" +
            \"style='background-repeat:no-repeat;background-position:center;\" +
            \"cursor:pointer;height:16'></div>\";}";
$col->width = 40;

$dg->addButton("", " ثبت الگوی جدید", "add", "function(){TemplatesObject.ShowNewTemplateForm();}");
$dg->addButton("", " مدیریت آیتم ها", "add", "function(){TemplatesObject.ShowMngTemplateItemsForm();}");

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
    
	function Templates() {
		
		this.grid = <?= $grid ?>;
		this.grid.render(this.get("div_dg"));
	
	}
	
    TemplatesObject = new Templates();
	
    Templates.prototype.ShowNewTemplateForm = function () {
        framework.OpenPage(this.address_prefix + "NewTemplate.php", "ثبت الگوی قرارداد");
    }

    Templates.prototype.ShowMngTemplateItemsForm = function () {
        framework.OpenPage(this.address_prefix + "ManageTemplateItems.php", "مدیریت آیتمهای الگو");
    }

    Templates.prototype.EditItem = function () {
        framework.OpenPage(this.address_prefix + "NewTemplate.php", "  ویرایش الگوی قرارداد",
                {TemplateID: TemplatesObject.grid.getSelectionModel().getLastSelected().data.TemplateID});
    }

	Templates.prototype.RemoveItem = function () {
         Ext.Ajax.request({
            url: TemplatesObject.address_prefix + 'templates.data.php?task=deleteTemplate',
            params: {                
                TemplateID: TemplatesObject.grid.getSelectionModel().getLastSelected().data.TemplateID            
            },
            method: 'POST',
            success: function (res) {
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
    }

	Templates.prototype.OperationMenu = function (e)
    {
        var record = this.grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();

        
            op_menu.add({text: 'ویرایش', iconCls: 'edit',
                handler: function () {
                    TemplatesObject.EditItem();
                }});

            op_menu.add({text:'حذف', iconCls: 'remove',
                handler: function () {
                    TemplatesObject.RemoveItem();
                }});

        op_menu.showAt(e.pageX - 120, e.pageY);
}

</script>
<br>
<center>    
    <div id="div_dg"></div>
</center>
