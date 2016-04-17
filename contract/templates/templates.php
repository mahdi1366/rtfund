<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.09
//-----------------------------
ini_set("display_errors", "On");

require_once '../header.inc.php';
require_once 'templates.js.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "templates.data.php?task=SelectTemplates", "div_dg");

$dg->addColumn("شماره الگو", "TplId");
$dg->addColumn("عنوان", "TplTitle");
$dg->addColumn("", "StatusDesc", "", true);
$dg->addColumn("", "TplContent", "", true);
$col = $dg->addColumn("وضعیت", "StatusCode");
$col->renderer = "function(v,p,record){return record.data.StatusDesc;} ";
$dg->addColumn("", "ExpireDate", "", true);
$col = $dg->addColumn("", "TplId");
$col->renderer = "function(v,p,r) {return  \"<div class='setting' title='عملیات ' onClick='TemplatesObject.OperationMenu(event);'  \" +
            \"style='background-repeat:no-repeat;background-position:center;\" +
            \"cursor:pointer;height:16'></div>\";}";
$col->width = 40;

$dg->addButton("", " ثبت الگوی جدید", "add", "function(){TemplatesObject.ShowNewTplForm();}");
$dg->addButton("", " مدیریت آیتم ها", "add", "function(){TemplatesObject.ShowMngTplItemsForm();}");

$dg->title = "لیست الگوهای قرارداد";
$dg->DefaultSortField = "TplId";
$dg->emptyTextOfHiddenColumns = true;
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "TplTitle";
$dg->width = 780;
$dg->pageSize = 15;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
    TemplatesObject = new Templates();
    TemplatesObject.grid = <?= $grid ?>;
    TemplatesObject.grid.render(TemplatesObject.get("div_dg"));

    Templates.prototype.ShowNewTplForm = function () {
        framework.OpenPage(this.address_prefix + "NewTemplate.php", "ثبت الگوی قرارداد");
    }

    Templates.prototype.ShowMngTplItemsForm = function () {
        framework.OpenPage(this.address_prefix + "ManageTplItems.php", "مدیریت آیتمهای الگو");
    }

    Templates.prototype.EditItem = function () {
        framework.OpenPage(this.address_prefix + "NewTemplate.php", "  ویرایش الگوی قرارداد",
                {TplId: TemplatesObject.grid.getSelectionModel().getLastSelected().data.TplId});
    }

Templates.prototype.RemoveItem = function () {
         Ext.Ajax.request({
            url: TemplatesObject.address_prefix + '../data/templates.data.php?task=deleteTpl',
            params: {                
                TplId: TemplatesObject.grid.getSelectionModel().getLastSelected().data.TplId            
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
