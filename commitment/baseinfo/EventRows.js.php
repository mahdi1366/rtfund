<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Sh.Jafarkhani
    //	Date		: 97.05
    //-----------------------------

    EventRows.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
		EventID : <?= $EventID?>,
		
		AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
		EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
		RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
		
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
    
    function EventRows(){
		
		new Ext.form.FieldSet({
			renderTo : this.get("div_Event"),
			width : 600,
			bodyStyle : "padding : 10px",
			items : [{
				xtype : "displayfield",
				labelWidth : 50,
				fieldLabel : "رویداد",
				value : "[<?= $_POST["EventID"]?>] <?= $_POST["EventTitle"]?>",
				fieldCls : "blueText"
			}]
		});
		
		this.HistoryObj = Ext.button.Button({
			xtype: "button",
			text : "سابقه تغییرات", 
			enableToggle : true,
			handler : function(){
				me = EventRowsObj<?= $random?>;
				me.itemGrid.getStore().proxy.extraParams["AllHistory"] = this.pressed ? "true" : "false";
				me.itemGrid.getStore().load();
			}
		});
		
		this.formPanel = new Ext.form.Panel({
			applyTo : this.get("DIV_formPanel"),
			title : "اطلاعات ردیف",
			frame : true,
			defaults : {
				anchor : "90%"
			},
			hidden : true,
			width : 500,
			items : [{
				xtype : "combo",
				store :  new Ext.data.Store({
					data : [{id : "DEBTOR", title : "بدهکار"},{id : "CREDITOR", title : "بستانکار"}],
					fields : ['id','title']
				}),
				valueField : "id",
				displayField : "title",
				name : "CostType",
				fieldLabel : "ماهیت حساب",
				width : 250 
			},{
				xtype : "combo",
				width : 385,
				fieldLabel : "حساب مربوطه",
				colspan : 2,
				store: new Ext.data.Store({
					fields:["CostID","CostCode","CostDesc", "TafsiliType","TafsiliType2","TafsiliType3",{
						name : "fullDesc",
						convert : function(value,record){
							return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
						}				
					}],
					proxy: {
						type: 'jsonp',
						url: '/accounting/baseinfo/baseinfo.data.php?task=SelectCostCode',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				typeAhead: false,
				itemId : "CostID",
				name : "CostID",
				valueField : "CostID",
				displayField : "fullDesc",
				listeners : {
					select : function(combo,records){
						me = EventRowsObj<?= $random ?>;
						me.formPanel.down("[name=TafsiliType]").setValue(records[0].data.TafsiliType);
						me.formPanel.down("[name=TafsiliType2]").setValue(records[0].data.TafsiliType2);
						me.formPanel.down("[name=TafsiliType3]").setValue(records[0].data.TafsiliType3);
					}
				}
			},{
				xtype : "combo",
				fieldLabel : "گروه محاسبات",
				allowBlank : true,
				store: new Ext.data.Store({
					fields:["InfoID","InfoDesc"],
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + 'baseinfo.data.php?task=selectComputeGroups',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					autoLoad : true
				}),
				typeAhead: false,
				queryMode : "local",
				valueField : "InfoID",
				displayField : "InfoDesc",
				listeners : {
					change : function(combo,records){
						me = EventRowsObj<?= $random ?>;
						el = me.formPanel.getComponent("ComputeItemID");
						el.setValue();
						el.getStore().proxy.extraParams.param1 = this.getValue();
						el.getStore().load();
					}
				}
			},{
				xtype : "combo",
				fieldLabel : "آیتم محاسباتی",
				itemId : "ComputeItemID",
				name : "ComputeItemID",
				store: new Ext.data.Store({
					fields:["InfoID","InfoDesc"],
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + 'baseinfo.data.php?task=selectComputeItems',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				queryMode : "local",
				typeAhead: false,
				valueField : "InfoID",
				displayField : "InfoDesc"
			},{
				xtype : "textfield",
				fieldLabel : "مبنای صدور سند",
				name : "DocDesc"
			},{
				xtype : "textarea",
				fieldLabel : "توضیحات تغییر ردیف",
				name : "ChangeDesc"
			},{
				xtype : "hidden",
				name : "RowID"
			},{
				xtype : "hidden",
				name : "EventID",
				value : this.EventID
			}],
			buttons : [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){ 
					me = EventRowsObj<?= $random?>;
					me.SaveItem();}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					me = EventRowsObj<?= $random?>;
					me.formPanel.hide();
				}
			}]
		});

    }
    
	var randomIndex = Math.floor((Math.random() * 100) + 1); 
	
    EventRowsObj<?= $random ?> = new EventRows();
    
	EventRows.OperationRender = function(v,p,r){
	
		if(r.data.IsActive == 'NO')
			return "";
        if(EventRowsObj<?= $random ?>.EditAccess)
			var st = "<table><tr>"+
			"<td><div title='ویرایش ردیف' class='edit' onclick='EventRowsObj<?= $random?>.EditRow();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:25px;height:16'></div></td>";
        if(EventRowsObj<?= $random ?>.RemoveAccess)
			st += "<td><div title='حذف' class='remove' onclick='EventRowsObj<?= $random?>.RemoveItem();'" +
			"style='cursor:pointer;background-repeat:no-repeat;background-position:center;" +
			"width:25px;height:16'></div></td>";
		return st + "</tr></table>";
	
	}
	
	EventRows.ChangeRender = function(v,p,r){
	
		var str = 'data-qtip="نحوه تعیین قیمت : <b> ' + r.data.PriceDesc + '</b><br> مبنای صدور سند : <b>' + r.data.DocDesc + "</b>";
		
		if(r.data.ChangeDate != null)
			str += '<br> توضیحات : <b>' + r.data.ChangeDesc 
			+ '</b><br> زمان عملیات : ' + r.data.ChangeDate.substr(11) + "  " + MiladiToShamsi(r.data.ChangeDate.substr(0,10)) + 
				'<br>عامل : ' + r.data.changePersonName; 
		
		str += '"';
		p.tdAttr = str;
		return v;	
	}
	
    EventRows.prototype.AddItem = function()
    {
        this.formPanel.getForm().reset();
        this.formPanel.show();
        this.formPanel.center();
    }
	
	EventRows.prototype.EditRow = function()
    {
		mask = new Ext.LoadMask(this.formPanel, {msg:'در حال حذف ...'});
		mask.show();
			
		me = EventRowsObj<?= $random ?>;
		var record = me.itemGrid.getSelectionModel().getLastSelected();

        this.formPanel.getForm().loadRecord(record);
		
		this.formPanel.getComponent("CostID").getStore().proxy.extraParams["CostID"] = record.data.CostID;
		this.formPanel.getComponent("CostID").getStore().load({
			callback : function(){ 
				mask.hide(); 
				me = EventRowsObj<?= $random ?>;
				me.formPanel.getComponent("CostID").getStore().proxy.extraParams["CostID"] = "";
			}
		});
		
        this.formPanel.show();
        this.formPanel.center();
    }

    EventRows.prototype.SaveItem = function(store,record){
		
        mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
        mask.show();

		this.formPanel.getForm().submit({
			clientValidation: true,
			url: this.address_prefix + 'baseinfo.data.php?task=saveEventRow',
            method: 'POST',
			
			success : function(form,action){
				mask.hide();
                if(action.result.success)
                {
					me = EventRowsObj<?= $random?>;
					me.formPanel.hide();
                    me.itemGrid.getStore().load();
					if(action.result.data != "")
						Ext.MessageBox.alert("هشدار", action.result.data);
                }
                else
                {
                    Ext.MessageBox.alert("خطا", action.result.data);
                }
			},
			failure : function(){mask.hide();}
		});
    }
       
	EventRows.prototype.RemoveItem = function(){
	
		if(!this.RemoveItemWin)
		{
			this.RemoveItemWin = new Ext.window.Window({
				title : '',
				modal : true,
				width : 400,
				closeAction : "hide",
				items : new Ext.form.Panel({
					plain: true,
					border: 0,
					bodyPadding: 5,
					items : [{
						xtype : "textarea",
						name : "ChangeDesc",
						size:35,
						fieldLabel : "توضیحات"
					}],
					buttons : [{
						text : "حذف",
						iconCls : "remove",
						handler : function(){

							var me = EventRowsObj<?= $random?>;
							mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
							mask.show();

							var record = me.itemGrid.getSelectionModel().getLastSelected();

							Ext.Ajax.request({
								url: me.address_prefix + 'baseinfo.data.php?task=DeleteEventRow',
								method: 'POST',
								params: {
									RowID : record.data.RowID,
									ChangeDesc : this.up('form').down("[name=ChangeDesc]").getValue()
								},

								success: function(response){
									mask.hide();
									var st = Ext.decode(response.responseText);
									if(st.success)
									{
										me = EventRowsObj<?= $random?>;
										me.RemoveItemWin.hide();
										me.itemGrid.getStore().load();
									}
									else
									{
										Ext.MessageBox.alert("خطا", st.data);
									}
								},
								failure: function(){}
							});  
						}
					},{
						text : "انصراف",
						iconCls : "undo",
						handler : function(){
							var me = EventRowsObj<?= $random?>;
							me.RemoveItemWin.hide();
						}
					}]//end of buttons
				})//end of items
			});//end of window       
		}
		this.RemoveItemWin.show();
    }
       
</script>
