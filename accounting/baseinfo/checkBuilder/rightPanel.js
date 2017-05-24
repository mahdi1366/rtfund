/*-------------------------
 * programmer: Jafarkhani
 * CreateDate: 90.01
 *------------------------- */
Ext.onReady(function (){
	
	var propertiesData = new Ext.data.JsonStore({
		proxy : new Ext.data.HttpProxy({
                url: 'js/Designer.Properties.js',
                method: 'GET'
            }),
		sortInfo:{field:"name",order:"ASC"},
		root:"properties",
		fields:["name","type","defaults","desc","instance","editable","values","editor"]
	});
	propertiesData.load();
	var menuItems = [{
		region: "center",
		id: "fb_treePanel",
		split : true,
		xtype:"treepanel",
		border : false,
		animate:true,
		autoScroll:true,
		containerScroll:true,
		enableDrag:true,
		ddGroup: 'designerddgroup',
		rootVisible : false,
		root: new Ext.tree.AsyncTreeNode({text:'root'}),
		loader:new Ext.ux.guid.tree.JsonTreeLoader({
			requestMethod:'GET',
			dataUrl:"js/Designer.Components.js"
		})
	},{
		region : "south",
		xtype : 'guidpropertygrid',
		disabled: true,
		id: "fb_propertyGrid",
		border  : false,
		minHeight : 150,
		split:true,
		height:170,
		source : {},
		codePress : 0,
		bodyCfg: {style: "direction:ltr"},
		propertyTypes : propertiesData,
		
		bbar : ['Add ',
		  new Ext.form.ComboBox({
			mode           : 'local',
			width          :  205,
			valueField     : 'name',
			displayField   : 'name',
			store          : propertiesData,
			listeners    : {
			  'specialkey' : function(tf,e) {
					var name = tf.getValue();
					var ds = Ext.getCmp("fb_propertyGrid").getStore(), defaultVal = "";
					if (e.getKey() == e.ENTER && name != '' && !ds.getById(name)) {
					   var i = this.store.find('name',name);
					   if (i!=-1) defaultVal = this.store.getAt(i).get('defaults') || defaultVal;
					   tf.setValue(''); //Clear add input
					   ds.add(new Ext.grid.PropertyRecord({name:name, value:defaultVal}));
					   //if(!AllStyles[SelectedElementID])
						//	AllStyles[SelectedElementID] = new Array();
					   //AllStyles[SelectedElementID][name] = defaultVal;

					   Ext.getCmp("fb_propertyGrid").startEditing(ds.getCount()-1, 1);
					}
			  }
			}
		 })
	   ]
	}];
	new Ext.Viewport({
		layout: 'border',
		items: [
            {
                region: 'east',
                id: 'MenuPanel', // see Ext.getCmp() below
                split: true,
				collapsible : true,
                width: 220,
                minSize: 220,
                maxSize: 250,
				layout : 'border',
				items : menuItems,
				tbar:[{
					text: "ذخیره",
					iconCls: "save",
					handler: SaveFn
				},{
					text: "مشاهده",
					iconCls: "preview",
					handler: PreviewFn
				}]
			 },
			//------------------------------------------------------------------
			{
				layout: 'border',
				region: 'center',

				items: [
					{
						region: 'north',
						id: "ext_SpecialPropertyDiv",
						title: "تعیین مشخصات",
						xtype:"panel",
						split:true,
						collapsible: true,
						collapsed : false,
						height:120,
						disabled: true,
						contentEl: "PropertyDiv"
					},
            new Ext.Panel({
                region: 'center',
				id : "CenterPanel",
                deferredRender: false,
               	bodyStyle:'background-color:white;border:dashed green 1px;',
				contentEl: "returnDIV",
				autoScroll : true
					})
				]
			}
		]
	});
	
	Ext.getCmp("fb_propertyGrid").on('propertychange', changeStyleValue);

	new Ext.dd.DropTarget("CenterPanel", {
		ddGroup     : 'designerddgroup',
		notifyEnter : function(ddSource, e, data) {
			elem = e.target;

		},
		notifyDrop  : dropFn
	});

	initialComponents();
	initialRightClickEvents();
	
	
});

function initialComponents()
{
	/*new Ext.form.TriggerField({
		id: "ext_fontColor",
		triggerClass:'x-form-color-trigger',
		onTriggerClick : function(){
			if(this.menu == null){
				this.menu = new Ext.menu.ColorMenu({
					hideOnClick: false,
					focusOnSelect: false,
					listeners:{
						select: function(palette,selColor)
						{
							Ext.getCmp("ext_fontColor").setValue(selColor);
							this.hide();
							changeStyleValue("", "color", "#" + selColor,"");
						}
					}
				});
			}
			this.onFocus();
			this.menu.show(this.el, "tr-br?");
		},
		applyTo : "fontColor",
		width : 90
	});

	new Ext.form.TriggerField({
		id: "ext_BackColor",
		triggerClass:'x-form-color-trigger',
		onTriggerClick : function(){
			if(this.menu == null){
				this.menu = new Ext.menu.ColorMenu({
					hideOnClick: false,
					focusOnSelect: false,
					listeners:{
						select: function(palette,selColor)
						{
							Ext.getCmp("ext_BackColor").setValue(selColor);
							this.hide();
							changeStyleValue("", "backgroundColor", "#" + selColor,"");
						}
					}
				});
			}
			this.onFocus();
			this.menu.show(this.el, "tr-br?");
		},
		applyTo : "BackColor",
		width : 90
	});*/
}

function initialRightClickEvents()
{
	Ext.getCmp("CenterPanel").getEl().on("contextmenu",function(e,src,obj){
		var target = e.getTarget();
		if(target.parentNode.parentNode.id == "CenterPanel")
			return;
		var menuItems;
		switch(target.nodeName)
		{
			case "DIV":
			case "SPAN":
			case "A":
				menuItems = [{
					text:"انتخاب",
					iconCls:"icon-deleteEl",
					handler:selectElement.createDelegate(this,[target.id])
				}];
			if(target.id != "returnDIV")
				menuItems[1] = {
					text:"حذف بلوک",
					iconCls:"icon-deleteEl",
					handler: function(target){
						Ext.get(target).remove();
					}.createDelegate(this,[target.id])
				};
				break;
			case "IMG":
				if(target.id.indexOf("formItem") == -1)
				{
					menuItems = [{
						text:"انتخاب",
						iconCls:"icon-deleteEl",
						handler:selectElement.createDelegate(this,[target.id])
					},{
						text:"حذف بلوک",
						iconCls:"icon-deleteEl",
						handler: function(target){
							Ext.get(document.getElementById(target).parentNode.id).remove();
						}.createDelegate(this,[target.id])
					}];
				}
				else
				{
					menuItems = [{
						text:"انتخاب",
						iconCls:"icon-deleteEl",
						handler:selectElement.createDelegate(this,[target.parentNode.id])
					},{
						text:"برگشت به منوی اصلی",
						iconCls:"undo",
						handler:function(){
							var index;
							for(i=0; i<FormItems.length; i++)
								if(FormItems[i].id == target.id.replace("formItem_", ""))
								{
									index = i;
									break;
								}

							Ext.getCmp("fb_treePanel").getRootNode().
								findChild("id","formItems",true).appendChild(FormItems[index]);

							target.parentNode.parentNode.removeChild(target.parentNode);
						}
					}];
				}
				break;
			case "TD":
				menuItems = [{
					text:"انتخاب جدول",
					iconCls:"icon-deleteEl",
					handler:selectElement.createDelegate(this,[target.parentNode.parentNode.parentNode.id])
				},{
					text:"انتخاب ردیف",
					iconCls:"icon-resize",
					handler:selectElement.createDelegate(this,[target.parentNode.id])
				},{
					text:"انتخاب سلول",
					iconCls:"icon-resize",
					handler:selectElement.createDelegate(this,[target.id])
				},{
					text: "حذف سلول",
					iconCls: "icon-delete",
					handler:function(target){
						Ext.get(target).remove();
					}.createDelegate(this,[target.id])
				},{
					text: "حذف ردیف",
					iconCls: "icon-delete",
					handler:function(target){
						Ext.get(target).remove();
					}.createDelegate(this,[target.parentNode.id])
				},{
					text: "حذف جدول",
					iconCls: "icon-delete",
					handler:function(target){
						Ext.get(target).remove();
					}.createDelegate(this,[target.parentNode.parentNode.parentNode.parentNode.id])
				}];
				break;
			default:
				menuItems = [{
					text:"انتخاب",
					iconCls:"icon-deleteEl",
					handler:selectElement.createDelegate(this,[target.parentNode.id])
				},{
					text:"برگشت به منوی اصلی",
					iconCls:"undo",
					handler:function(){
						var index;
						for(i=0; i<FormItems.length; i++)
							if(FormItems[i].id == target.id.replace("formItem_", ""))
							{
								index = i;
								break;
							}

						Ext.getCmp("fb_treePanel").getRootNode().
							findChild("id","formItems",true).appendChild(FormItems[index]);

						target.parentNode.parentNode.removeChild(target.parentNode);
					}
				}];
				break;
		}
			
		var m = new Ext.menu.Menu({
				items:[menuItems]
			});
			e.preventDefault();
			m.showAt(e.getXY());
		});

	setTimeout(function(){
		Ext.get('loading').remove();
		Ext.get('loading-mask').fadeOut({
			remove:true
		});
	}, 350);
}