<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		93.05
//---------------------------

	Cancle.prototype = {
		TabID: '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix: "<?= $js_prefix_address ?>",
		mainPanel: "",
		get: function (elementID) {
			return findChild(this.TabID, elementID);
		}
	};

	function Cancle()
	{
		var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data: [
				['100', 'همه '],
				['1', 'هیئت علمی'],
				['2', 'کارمند'],
				['3', 'روزمزدبیمه ای'],
				['5', 'قراردادی'],
				['102', 'هیئت علمی،کارمند،روزمزد'],
			]
		});

		var pTypeStore = <?= dataReader::MakeStoreObject_Data(manage_domains::GETALL_Payment_Type(), "'InfoID','InfoDesc'") ?>;
		this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
				type: "table",
				columns: 2
			},
			collapsible: false,
			frame: true,
			title: ' ابطال پرداخت',
			bodyPadding: '5 5 0',
			width: 740,
			fieldDefaults: {
				msgTarget: 'side',
				labelWidth: 120
			},
			defaultType: 'textfield',
			items: [{
					xtype: "numberfield",
					fieldLabel: 'سال',
					name: 'pay_year',
					allowBlank: false,
					width: 200,
					hideTrigger: true
				},
				{
					xtype: "numberfield",
					fieldLabel: 'ماه',
					name: 'pay_month',
					allowBlank: false,
					width: 200,
					hideTrigger: true
				},
				{
					xtype: "combo",
					fieldLabel: "نوع پرداخت ",
					store: pTypeStore,
					inputId: "payment_type",
					valueField: 'InfoID',
					value: "1",
					displayField: 'InfoDesc'
				},
				{
					xtype: "trigger",
					name: "SID",
					fieldLabel: "شماره شناسایی",
					colspan: 2,
					onTriggerClick: function () {

						var retVal = showLOV("/HumanResources/global/LOV/StaffLOV.php", 900, 550);
						if (retVal != '')
						{
							this.setValue(retVal);
						}
					},
					width: 200,
					triggerCls: 'x-form-search-trigger'
				}
				,
				{
					xtype: 'fieldset',
					title: "وضعیت استخدامی",
					colspan: 4,
					style: 'background-color:#DFEAF7',
					width: 700,
					fieldLabel: 'Auto Layout',
					itemId: "chkgroup2",
					collapsible: true,
					collapsed: true,
					layout: {
						type: "table",
						columns: 4,
						tableAttrs: {
							width: "100%",
							align: "center"
						},
						tdAttrs: {
							align: 'right',
							width: "۱6%"
						}
					},
					items: [{
							xtype: "checkbox",
							boxLabel: "همه",
							checked: true,
							listeners : {
								change : function(){
									parentNode = CancleObject.formPanel.down("[itemId=chkgroup2]"); 

									for(i=0; i<parentNode.items.items.length; i++)
									{								
											parentNode.items.items[i].setValue(this.getValue());
									}
								}
							}							
							}]
				},
				{
					xtype: "trigger",
					fieldLabel: 'حوزه فعالیت',
					colspan: 2,
					name: 'DomainDesc',
					triggerCls: 'x-form-search-trigger',
					onTriggerClick: function () {
						CancleObject.ActDomainLOV();
					}
				},
				{
					xtype: "hidden",
					name: "DomainID",
					colspan: 2
				}
			],
			buttons: [{
					iconCls: "cross",
					text: "ابطال پرداخت",
					handler: function () {
						CancleObject.Remove(this);
					}
				}]
		});


		this.resultPanel = new Ext.Panel({
			applyTo: this.get("result_data"),
			width: 730,
			border: 0,
			autoHeight: true,
			layout: "vbox",
			items: [{
					xtype: "container",
					width: 700,
					itemId: "resultSt",
					html: "<br><br>"

				}]

		});


		new Ext.data.Store({
			fields: ["InfoID", "InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + "../../../global/domain.data.php?task=searchEmpState",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			listeners: {
				load: function () {
					
					this.each(function (record) {
						CancleObject.formPanel.down("[itemId=chkgroup2]").add({
							xtype : "checkbox",
							boxLabel: record.data.InfoDesc ,
							name : "chkEmpState_" + record.data.InfoID 	,
							checked : true							
						});
					});	
					
				}}
		});

	}

	var CancleObject = new Cancle();
	
	Cancle.prototype.ActDomainLOV = function (record) {

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
				parent: "CancleObject.DomainWin",
				MenuID: this.MenuID,
				selectHandler: function (id, name) {
					CancleObject.formPanel.down("[name=DomainDesc]").setValue(name);
					CancleObject.formPanel.down("[name=DomainID]").setValue(id);
					
				}
			}
		});


	}

	Cancle.prototype.Remove = function (btn)
	{

		if (btn.up('form').getForm().isValid()) {

			//btn.disable();
			//btn.setText('در حال ابطال لطفا منتظر بمانید...') ;

			btn.up('form').getForm().submit({

				url: this.address_prefix + '../data/payment.data.php?task=Remove',
				method: "POST",
				//form: CancleObject.get("mainForm") , 
				success: function (form, action) {

					if (action.result.success)
					{
						CancleObject.get('result').style.display = 'block';
						var cnt = action.result.data.split("_");
						CancleObject.resultPanel.down("[itemId=resultSt]").update(
								"<br><img src=<?= HR_ImagePath ?>success.gif > " + "تعداد احكام استفاده شده در محاسبه كه با موفقيت حذف شد : " + cnt[0] +
								"<br><img src=<?= HR_ImagePath ?>success.gif > " + "تعداد فيشهايي كه با موفقيت حذف شد : " + cnt[1] +
								"<br><img src=<?= HR_ImagePath ?>success.gif > " + "تعداد اقلامي از فيش كه با موفقيت حذف شد : " + cnt[2] +
								"<br><br>&nbsp;&nbsp; <a href='./../../../HumanResources/tempDir/cancel_fail_log.php' target='_blank' >" + "تعداد فيشهايي که در حذف با خطا مواجه شد : " + cnt[3] + "</a><br><br>");

						btn.enable();
						btn.setText('ابطال پرداخت');

					} else
					{
						alert("خطا در عملیات ابطال ");
					}
				}
			});

		} else
			return;

	}

</script>