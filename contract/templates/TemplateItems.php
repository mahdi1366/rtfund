<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.09
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

?>
<script>
    ManageTemplateItems.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
		
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };
    
	function ManageTemplateItems() {
		
        this.ItemTypeCombo = new Ext.form.ComboBox({
            store: new Ext.data.Store({
                fields: ["id", "name"],
                data: [
                    {"id": "numberfield", "name": "عدد"},
                    {"id": "textfield", "name": "متن"},
                    {"id": "shdatefield", "name": "تاریخ"}
                ]
            }),
            emptyText: 'انتخاب ...',
            name: "name",
            valueField: "id",
            displayField: "name",
            forceSelection:true,
            allowBlank : false
        });
		
		this.grid = <?= $grid ?>;
		this.grid.render(this.get("div_dg"));
    }
    
	ManageTemplateItemsObj = new ManageTemplateItems();

    ManageTemplateItems.prototype.AddTemplateItem = function () {
        var modelClass = this.grid.getStore().model;
        var record = new modelClass({
            TemplateItemID: 0
        });
        this.grid.plugins[0].cancelEdit();
        this.grid.getStore().insert(0, record);
        this.grid.plugins[0].startEdit(0, 0);
        this.ItemTypeCombo.focus();
    }

    ManageTemplateItems.prototype.SaveItem = function (store, record) {
		
        mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
        mask.show();
        Ext.Ajax.request({
            url: this.address_prefix + 'templates.data.php?task=saveTemplateItem',
            method: 'POST',
            params: {
                record: Ext.encode(record.data)
            },
            success: function (response) {
                mask.hide();
                var st = Ext.decode(response.responseText);
                if (st.success)
                {
                    ManageTemplateItemsObj.grid.getStore().load();
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

    ManageTemplateItems.prototype.deleteRender = function(){
        return  "<div title='حذف اطلاعات' class='remove' onclick='ManageTemplateItemsObj.removeItem();' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
    };
    
    ManageTemplateItems.prototype.removeItem = function(){  
        
		Ext.MessageBox.confirm("","آیا مایل به حذف آیتم می باشید؟", function(btn){
			if(btn == "no")
				return;
			
			me = ManageTemplateItemsObj;
			mask = new Ext.LoadMask(me.grid, {msg: 'در حال ذخیره سازی ...'});
			mask.show();
			Ext.Ajax.request({
				url: me.address_prefix + 'templates.data.php?task=deleteTemplateItem',
				method: 'POST',
				params: {
					TemplateItemID : me.grid.getSelectionModel().getLastSelected().data.TemplateItemID
				},

				success: function(response){
					mask.hide();
					var st = Ext.decode(response.responseText);
					if(st.success)
					{
						ManageTemplateItemsObj.grid.getStore().load();
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
		})
        
    };
</script>
<br>
<center>    
    <div id="div_dg"></div>
</center>

