<script type="text/javascript">
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

	InsureDisk.prototype = {
		TabID: '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix: "<?= $js_prefix_address ?>",
		mainPanel: "",
		get: function (elementID) {
			return findChild(this.TabID, elementID);
		}
	};
	InsureDisk.prototype.showReport = function (btn, e)
	{
		this.form = this.get("mainForm")

		if (!this.form.pay_year.value)
		{
			alert('وارد کردن سال الزامی می باشد.');
			return false;
		}

		if (!this.form.pay_month.value)
		{
			alert('وارد کردن ماه الزامی می باشد.');
			return false;
		}

		this.form.target = "_blank";
		this.form.method = "POST";
		if (this.get("Type").value == "1")
			this.form.action = this.address_prefix + "insure_diskette.php?task=ShowList";
		else if (this.get("Type").value == "2")
			this.form.action = this.address_prefix + "insure_diskette.php?task=GetDisk";
		else if (this.get("Type").value == "3")
			this.form.action = this.address_prefix + "insure_diskette.php?task=GetDisk&TypeDisk=KAR ";
		this.form.submit();
		return;
	}

	function InsureDisk()
	{


		var RepTYP = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data: [
				['1', 'بر اساس مبالغ و تفاوت ها '],
				['2', 'بر اساس مبالغ'],
				['3', 'براساس تفاوت ها ']
			]
		});




		this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
				type: "table",
				columns: 2
			},
			collapsible: false,
			frame: true,
			title: 'گزارش تهیه دیسکت بیمه',
			bodyPadding: '5 5 0',
			width: 740,
			fieldDefaults: {
				msgTarget: 'side',
				labelWidth: 130
			},
			defaultType: 'textfield',
			items: [{
					xtype: "numberfield",
					fieldLabel: 'سال',
					inputId: 'pay_year',
					name: 'pay_year',
					width: 200,
					hideTrigger: true
				},
				{
					xtype: "numberfield",
					fieldLabel: 'ماه',
					name: 'pay_month',
					width: 200,
					hideTrigger: true
				},
				{
					xtype: "combo",
					colspan: 3,
					store: new Ext.data.Store({
						fields: ["InfoID", "InfoDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
							reader: {
								root: 'rows',
								totalProperty: 'totalCount'
							}
						}
						,
						autoLoad: true,
						listeners: {
							load: function () {
								InsureDiskObject.formPanel.down("[itemId=PayType]").setValue("1");
							}
						}

					}),
					valueField: "InfoID",
					displayField: "InfoDesc",
					hiddenName: "PayType",
					itemId: "PayType",
					fieldLabel: "نوع پرداخت&nbsp;",
					listConfig: {
						loadingText: 'در حال جستجو...',
						emptyText: 'فاقد اطلاعات',
						itemCls: "search-item"
					},
					width: 300
				}, {
					xtype: "trigger",
					fieldLabel: 'حوزه فعالیت',
					name: 'DomainDesc',
					triggerCls: 'x-form-search-trigger',
					onTriggerClick: function () {
						InsureDiskObject.ActDomainLOV();
					}
				},
				{
					xtype: "hidden",
					name: "DomainID",
					colspan: 2
				}
				/*,										
				 {
				 xtype : "combo",
				 hiddenName:"ReportType",                                    
				 fieldLabel : "نوع گزارش",
				 store: RepTYP,
				 valueField: 'val',
				 displayField: 'title'
				 } */
			],
			buttons: [
				{
					text: "لیست بیمه",
					handler: function () {
						InsureDiskObject.showReport()
					},
					listeners: {
						click: function () {
							InsureDiskObject.get('Type').value = "1";
						}
					},
					iconCls: "report"
				},
				{
					text: "تهیه دیسکت DSKWOR",
					handler: Ext.bind(this.showReport, this),
					listeners: {
						click: function () {
							InsureDiskObject.get('Type').value = "2";
						}
					},
					iconCls: "save"
				},
				{
					text: " تهیه دیسکت  DSKKAR",
					handler: Ext.bind(this.showReport, this),
					listeners: {
						click: function () {
							InsureDiskObject.get('Type').value = "3";
						}
					},
					iconCls: "save"
				}/* ,
				 {
				 text : "فرم تائید پرداخت بیمه",
				 handler : function(){InsureDiskObject.showReport()},
				 listeners : {
				 click : function(){
				 InsureDiskObject.get('Type').value = "3";
				 }
				 },
				 iconCls : "list"
				 }*/]
		});


	}

	var InsureDiskObject = new InsureDisk();

	InsureDisk.prototype.ActDomainLOV = function (record) {

		if (!this.DomainWin)
		{
			this.DomainWin = new Ext.window.Window({
				autoScroll: true,
				width: 480,
				height: 550,
				title: "حوزه فعالیت",
				closeAction: "hide",
				loader: {
					url: this.address_prefix + "../../../../framework/baseInfo/units.php?mode=adding",
					scripts: true
				}
			});

			Ext.getCmp(this.TabID).add(this.DomainWin);
		}

		this.DomainWin.show();

		this.DomainWin.loader.load({
			params: {
				ExtTabID: this.DomainWin.getEl().dom.id,
				parent: "InsureDiskObject.DomainWin",
				MenuID: this.MenuID,
				selectHandler: function (id, name) {
					InsureDiskObject.formPanel.down("[name=DomainDesc]").setValue(name);
					InsureDiskObject.formPanel.down("[name=DomainID]").setValue(id);

				}
			}
		});


	}


</script>