<script>
//-----------------------------
//	Date		: 1397.11
//-----------------------------

    ManageAgencyCnt.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	MenuID : "<?= $_POST["MenuID"] ?>",
	
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageAgencyCnt(){
	
	this.panel = new Ext.panel.Panel({
		renderTo : this.get("DivPanel"),
		//border : false,
		layout : "hbox",
		height : 500,
		items : [{
			xtype : "container",
			flex : 1,
			html : "<div id=div_grid width=100%></div>"
		},{
			xtype : "container",
			width : 250,
			autoScroll : true,
			height: 500,
			style : "border-left : 1px solid #99bce8;margin-left:5px",
			layout : "vbox",
			itemId : "cmp_buttons"
		}]
	});	
	
	/*new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "AgencyCnt.data.php?task=selectAgencyTypes",
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["InfoID","InfoDesc","param1","param2"],
		autoLoad : true,
		listeners : {
			load : function(){
				me = ManageAgencyCntObj;
				console.log(me);
				//..........................................................
				me.panel.down("[itemId=cmp_buttons]").removeAll();
				for(var i=0; i<this.totalCount; i++)
				{
					record = this.getAt(i);
					if(record.data.param1 != "")
					{
						btn = me.panel.down("[itemId=g_" + record.data.param1 + "]");
						if(!btn)
						{
							btn = me.panel.down("[itemId=cmp_buttons]").add({
								xtype : "button",
								width : 130,
								height : 50,
								autoScroll : true,
								scale : "large",
								style : "margin-bottom:10px",	
								itemId : "g_" + record.data.param1,
								text : record.data.param2,
								menu : []
							});
						}
						
						btn.menu.add({
							itemId : record.data.InfoID,
							text : record.data.InfoDesc,
							handler : function(){ManageAgencyCnt.LoadGrid(this)}
						});
					}
					else
						me.panel.down("[itemId=cmp_buttons]").add({
							xtype : "button",
							width : 130,
							height : 50,
							autoScroll : true,
							scale : "large",
							style : "margin-bottom:10px",	
							itemId : record.data.InfoID + "_" + record.data.param1,
							text : record.data.param1 != "" ? record.data.param1 : record.data.InfoDesc,
							handler : function(){ManageAgencyCnt.LoadGrid(this)}
						});
				}
			}
		}
	});*/


    new Ext.data.Store({
    proxy : {
    type: 'jsonp',
    url: this.address_prefix + "AgencyCnt.data.php?task=selectAgencyTypes",
    reader: {root: 'rows',totalProperty: 'totalCount'}
},
    fields : ["InfoID","InfoDesc","param1","param2"],
    autoLoad : true,
    listeners : {
    load : function(){
    /*console.log(this.totalCount);*/
    me = ManageAgencyCntObj;
    console.log(me);
    //..........................................................
    me.panel.down("[itemId=cmp_buttons]").removeAll();
    for(var i=0; i<this.totalCount; i++)
{
    record = this.getAt(i);
    console.log(record);
    if(record.data.param2 != "" && record.data.param2 != 0 )
{
    console.log('فرعیییییی');
    btn = me.panel.down("[itemId=g_" + record.data.param2 + "]");
    if(!btn)
{
    console.log('دکمه وجود ندارد');
    btn = me.panel.down("[itemId=cmp_buttons]").add({
    xtype : "button",
    width : 130,
    height : 50,
    autoScroll : true,
    scale : "large",
    style : "margin-bottom:10px",
    itemId : "g_" + record.data.param2,
    text : record.data.param3,
    menu : []
});
}

    btn.menu.add({
    itemId : record.data.InfoID,
    text : record.data.InfoDesc,
    handler : function(){ManageAgencyCnt.LoadGrid(this)}
});
}
    else{
    console.log('اصلیییییی');
    me.panel.down("[itemId=cmp_buttons]").add({
    xtype : "button",
    width : 200,
    height : 50,
    autoScroll : true,
    scale : "large",
    style : "margin-bottom:10px",
    itemId : record.data.InfoID,       /*New Edited*/
    /*itemId : record.data.InfoID + "_" + record.data.param1,*/          /*New Commented*/
    text : (record.data.param2 != "" && record.data.param2 != 0) ? record.data.param2 : record.data.InfoDesc,
    handler : function(){ManageAgencyCnt.LoadGrid(this)}
});
}

}
}
}
});
		
	this.grid = <?= $grid ?>;
	this.grid.on("itemdblclick", function(view, record){
    ManageAgencyCnt.OpenAgency(record.data.agencyCntID);
	});	
	/*this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.StatusID == "<?/*= MTG_STATUSID_DONE */?>")
			return "greenRow";
		if(record.data.StatusID == "<?/*= MTG_STATUSID_CANCLE */?>")
			return "pinkRow";
		return "";
	}	*/
	//this.grid.render(this.get("DivGrid"));
	
	framework.centerPanel.items.get(this.TabID).on("activate", function(){
    ManageAgencyCntObj.grid.getStore().load();
	});
}















    ManageAgencyCnt.LoadGrid = function(btn){
    var AgencyID = ManageAgencyCntObj.grid.getStore().proxy.extraParams.AgencyID;
    ManageAgencyCntObj.grid.getStore().proxy.extraParams.AgencyID = btn.itemId;

    ManageAgencyCntObj.grid.setTitle(btn.text);
	if(ManageAgencyCntObj.grid.rendered)
    ManageAgencyCntObj.grid.getStore().loadPage(1);
	else
    ManageAgencyCntObj.grid.render(ManageAgencyCntObj.get("div_grid"));
}

























    ManageAgencyCnt.OpenAgency = function(agencyCntID){
    console.log(ManageAgencyCntObj.grid.getStore().proxy.extraParams.AgencyID);
    framework.OpenPage('contract/agency/newAgencyCnt.php', "مشخصات سند سازمانی",{
    agencyCntID : agencyCntID,
    readOnly : true
    });
	/*framework.OpenPage("/meeting/MeetingInfo.php", "اطلاعات جلسه",
		{
            agencyCntID : agencyCntID,
            MeetingTypeID : ManageAgencyCntObj.grid.getStore().proxy.extraParams.MeetingType,
			MenuID : ManageAgencyCntObj.MenuID
		});*/
}

    ManageAgencyCnt.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManageAgencyCntObj.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

    ManageAgencyCntObj = new ManageAgencyCnt();

    ManageAgencyCnt.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();

    var record = this.grid.getSelectionModel().getLastSelected();
    console.log(record.data);
    var op_menu = new Ext.menu.Menu();


    if(this.EditAccess)
    op_menu.add({text: ' ویرایش', iconCls: 'edit',
    handler: function () {ManageAgencyCntObj.Edit(record.data.agencyCntID); }});

    if(this.RemoveAccess)
    op_menu.add({text: ' حذف', iconCls: 'remove',
    handler: function () {	ManageAgencyCntObj.delete(record.data.agencyCntID);	}});

    op_menu.add({text: 'پیوست های سند سازمانی', iconCls: 'attach',
    handler: function () {
    ManageAgencyCntObj.AgencyCntAttach('agencydoc');
}});

    op_menu.showAt(e.pageX - 120, e.pageY);
}

    ManageAgencyCnt.prototype.Edit = function (agencyCntID)
    {
        framework.OpenPage(this.address_prefix + 'newAgencyCnt.php', "مشخصات سند سازمانی",{
            agencyCntID : agencyCntID
        });
    }
    ManageAgencyCnt.prototype.delete = function(){

    Ext.MessageBox.confirm("","آیا مایل به حذف درخواست می باشید؟",function(btn){
        if(btn == "no")
            return;

        me = ManageAgencyCntObj;
        record = me.grid.getSelectionModel().getLastSelected();

        mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخيره سازي...'});
        mask.show();

        Ext.Ajax.request({
            methos : "post",
            url: me.address_prefix + 'AgencyCnt.data.php?task=DeleteAgencyCnt',
            params : {
                agencyCntID: record.data.agencyCntID
            },

            success : function(response){
                result = Ext.decode(response.responseText);
                mask.hide();
                if (!result.success) {
                    if (result.data != '')
                        Ext.MessageBox.alert('', result.data);
                    else
                        Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
                    return;
                }
                ManageAgencyCntObj.grid.getStore().load();
            }
        });
    });
}

    ManageAgencyCnt.prototype.AgencyCntAttach = function(ObjectType){

    if(!this.documentWin)
{
    this.documentWin = new Ext.window.Window({
    width : 720,
    height : 440,
    modal : true,
    bodyStyle : "background-color:white;padding: 0 10px 0 10px",
    closeAction : "hide",
    loader : {
    url : "/office/dms/documents.php",
    scripts : true
},
    buttons :[{
    text : "بازگشت",
    iconCls : "undo",
    handler : function(){this.up('window').hide();}
}]
});
    Ext.getCmp(this.TabID).add(this.documentWin);
}

    this.documentWin.show();
    this.documentWin.center();

    var record = this.grid.getSelectionModel().getLastSelected();
    this.documentWin.loader.load({
    scripts : true,
    params : {
    ExtTabID : this.documentWin.getEl().id,
    ObjectType : ObjectType,
    ObjectID : record.data.agencyCntID
}
});

}



</script>