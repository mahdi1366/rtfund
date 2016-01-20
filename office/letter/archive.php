<?php
//-----------------------------
// programmer: SH.Jafarkhani
// create Date: 94.10
//-----------------------------
require_once '../header.inc.php';

?>
<script>
LetterArchive.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LetterArchive()
{
	this.GroupTree = Ext.create('Ext.tree.Panel', {
        store: new Ext.data.TreeStore({
			proxy: {
				type: 'ajax',
				url: this.address_prefix + 'letter.data.php?task=SelectArchive'
			},
			root: {
				text: 'بایگانی شخصی',
				id: 'src',
				expanded: true
			}
		}),
        width: 500,
        height: 600,
        renderTo: this.get("div_tree"),
		tbar : [{
			xtype : "button",
			iconCls : "print",
			text : "چاپ درخت",
			handler : function(){
				Ext.ux.Printer.print(LetterArchiveObject.GroupTree);
			}
		}]
    });
	//--------------------------------------------------------------------------
	this.GroupTree.on("itemcontextmenu", function(view, record, item, index, e)
	{
		e.stopEvent();
		e.preventDefault();
		view.select(index);
		
		this.Menu = new Ext.menu.Menu();
		
		this.Menu.add({
			text: 'ایجاد زیر پوشه',
			iconCls: 'edit',
			handler : function(){LetterArchiveObject.BeforeSaveFolder(false);}
		});
		
		if(record.data.id != "src")
		{
			this.Menu.add({
				text: 'ویرایش عنوان',
				iconCls: 'edit',
				handler : function(){LetterArchiveObject.BeforeSaveFolder(true);}
			});
			
			this.Menu.add({
				text: 'حذف پوشه',
				iconCls: 'remove',
				handler : function(){LetterArchiveObject.DeleteFolder();}
			});
		}

		var coords = e.getXY();
		this.Menu.showAt([coords[0]-120, coords[1]]);
	});
	//------------------------------------------------------------------------

}

var LetterArchiveObject = new LetterArchive();

LetterArchive.prototype.BeforeSaveFolder = function(EditMode){

	if(!this.infoWin)
	{
		this.infoWin = new Ext.window.Window({
            applyTo: this.get("NewWIN"),
            modal : true,
            title: "اطلاعات کالا",
            width : 500,
            closeAction : "hide",

            items : new Ext.form.Panel({
                bodyStyle : "text-align:right;padding:5px",
                frame: true,
                items :[{
					xtype : "textfield",
					name : "GoodName",
					itemId : "GoodName",
					fieldLabel : "عنوان",
					anchor : "100%"
				},{
					xtype : "hidden",
					fieldLabel : "ParentID",
					itemId : "ParentID",
					name : "ParentID"
				},{
					fieldLabel : "GoodID",
					itemId : "GoodID",
					name : "GoodID"
				}],
                buttons :[{
					text : "ذخیره",
					handler : function(){LetterArchiveObject.SaveFolder();},
					iconCls : "save"
				},{
					text : "انصراف",
					handler : function(){
						this.up('window').hide();
					},
					iconCls : "undo"
				}]
            })
        });
	}
	
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.infoWin.down('form').getForm().reset();
	this.infoWin.show();
	this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id);

	if(EditMode)
	{
		this.infoWin.down('form').loadRecord(record);
	}
}

LetterArchive.prototype.SaveFolder = function(){

	var record = this.GroupTree.getSelectionModel().getSelection()[0];	
	
	if(record == null)
	{
		Ext.MessageBox.alert("Error", "فصل را انتخاب کنید");
		return;
	}
	GroupID = this.GroupTree.down("[itemId=GroupID]").getValue();
	GroupDesc = this.GroupTree.down("[itemId=GroupDesc]").getValue();
	if(GroupDesc == "")
		return;
	
	Ext.Ajax.request({
		url : this.address_prefix + "groups.data.php?task=SaveGroup",
		method : "POST",
		params : {
			ParentID : record.data.id,
			GroupID : GroupID,
			GroupDesc : GroupDesc
		},

		success : function(response)
		{
			var node = LetterArchiveObject.GroupTree.getRootNode();
			LetterArchiveObject.GroupTree.getStore().load({node : node});
			LetterArchiveObject.GroupTree.down("[itemId=GroupID]").setValue();
			LetterArchiveObject.GroupTree.down("[itemId=GroupDesc]").setValue();
		}
	});
}

LetterArchive.prototype.EditFolder = function(){

	var record = this.GroupTree.getSelectionModel().getSelection()[0];
	
	LetterArchiveObject.GroupTree.down("[itemId=GroupID]").setValue(record.data.id);
	LetterArchiveObject.GroupTree.down("[itemId=GroupDesc]").setValue(record.data.text);
}

LetterArchive.prototype.DeleteFolder = function(){

	var record = this.GroupTree.getSelectionModel().getSelection()[0];
	Ext.Ajax.request({
		url : this.address_prefix + "groups.data.php",
		method : "POST",
		params : {
			task : "DeleteGroup",
			GroupID : record.data.id
		},
		success : function(response){
			result = Ext.decode(response.responseText);
			if(!result.success)
			{
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
				return;
			}	
			
			var node = LetterArchiveObject.GroupTree.getRootNode();
			LetterArchiveObject.GroupTree.getStore().load({node : node});
			
			var node = LetterArchiveObject.BaseTree.getRootNode();
			LetterArchiveObject.BaseTree.getStore().load({node : node});
			
		}
	})
}
</script>
<center>
	<div id="div_tree"></div>
</center>