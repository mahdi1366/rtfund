<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.09
//-----------------------------

ini_set("display_errors", "On");

require_once '../../header.inc.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/templates.data.php?task=selectTemplateItems", "div_dg");

$col = $dg->addColumn("شماره ", "TplItemId");
$col->width = 50;

$col = $dg->addColumn("عنوان", "TplItemName");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("نوع", "TplItemType");
$col->editor = "ManageTplItemsObj.TplItemTypeCombo";

$col = $dg->addColumn("حذف", "TplItemId", "string");
$col->sortable = false;
$col->renderer = "function(){return ManageTplItemsObj.deleteRender();}";
//$col->renderer = "ManageTplItems.deleteRender";
$col->width = 50;

$dg->addButton("", " ایجاد", "add", "function(){ManageTplItemsObj.AddTplItem();}");

$dg->title = "لیست الگوهای قرارداد";
$dg->DefaultSortField = "TplItemId";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "TplItemName";
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){ return ManageTplItemsObj.SaveItem(v,p,r);}";

$dg->width = 780;
$dg->pageSize = 20;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
    ManageTplItems.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        UserRole: '<?= $UserRole ?>',
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };
    function ManageTplItems() {
        this.TplItemTypeCombo = new Ext.form.ComboBox({
            store: new Ext.data.Store({
                fields: ["id", "name"],
                data: [
                    {"id": "numberfield", "name": "عدد"},
                    {"id": "textfield", "name": "متن"},
                    {"id": "shdatefield", "name": "تاریخ"}
                ]
            }),
            emptyText: 'انتخاب ...',
            typeAhead: false,
            name: "name",
            valueField: "id",
            displayField: "name",
            pageSize: 3,
            forceSelection:true,
            allowBlank : false,
            width: 200
        });
    }
    ManageTplItemsObj = new ManageTplItems();
    ManageTplItemsObj.grid = <?= $grid ?>;
    ManageTplItemsObj.grid.render(ManageTplItemsObj.get("div_dg"));

    ManageTplItems.prototype.AddTplItem = function () {
        var modelClass = this.grid.getStore().model;
        var record = new modelClass({
            TplItemId: 0
        });
        this.grid.plugins[0].cancelEdit();
        this.grid.getStore().insert(0, record);
        this.grid.plugins[0].startEdit(0, 0);
        this.TplItemTypeCombo.focus();
    }

    ManageTplItems.prototype.SaveItem = function (store, record) {
        mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
        //mask.show();
        Ext.Ajax.request({
            url: this.address_prefix + '../data/templates.data.php?task=saveTplItem',
            method: 'POST',
            params: {
                record: Ext.encode(record.data)
            },
            success: function (response) {
                mask.hide();
                var st = Ext.decode(response.responseText);
                if (st.success)
                {
                    ManageTplItemsObj.grid.getStore().load();
                }
                else
                {
                    Ext.MessageBox.alert("خطا", st.data);
                }
            },
            failure: function () {
                mask.hide();
            }
        });
    }

    ManageTplItems.prototype.deleteRender = function(){
        return  "<div title='حذف اطلاعات' class='remove' onclick='ManageTplItemsObj.removeItem();' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
    };
    
    ManageTplItems.prototype.removeItem = function(){  
        if (!confirm('آیا مطمئنید؟'))
            return;
        Ext.Ajax.request({
            url: this.address_prefix + '../data/templates.data.php?task=deleteTplItem',
            method: 'POST',
            params: {
                TplItemId : ManageTplItemsObj.grid.getSelectionModel().getLastSelected().data.TplItemId
            },

            success: function(response){
                //mask.hide();
                var st = Ext.decode(response.responseText);
                if(st.success)
                {
                    ManageTplItemsObj.grid.getStore().load();
                }
                else
                {
                    if(st.data == "USED")
                        Ext.MessageBox.alert("خطا","آیتم مورد نظر استفاده شده است و امکان حذف آن وجود ندارد");
                    else
                        Ext.MessageBox.alert("خطا", st.data);
                }
            },
            failure: function(){}
        });
    };
</script>
<br>
<center>    
    <div id="div_dg"></div>
</center>

