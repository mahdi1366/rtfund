<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		96.05
//---------------------------

    LoanSubtract.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        mainPanel: "",
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };
    LoanSubtract.prototype.showReport = function (btn, e)
    {
        this.form = this.get("mainForm")
        this.form.target = "_blank";
        this.form.method = "POST";
        this.form.action = this.address_prefix + "tax_salary_report.php?show=true";
        this.form.submit();
        return;
    }

    LoanSubtract.prototype.showReport2 = function (btn, e)
    {
        this.form = this.get("mainForm")
        this.form.target = "_blank";
        this.form.method = "POST";
        this.form.action = this.address_prefix + "tax_salary_report.php?show=true&summary=true";
        this.form.submit();
        return;
    }

    LoanSubtract.prototype.showReport3 = function (btn, e)
    {
        this.form = this.get("mainForm")
        this.form.target = "_blank";
        this.form.method = "POST";
        this.form.action = this.address_prefix + "tax_salary_report.php?show=true&WP=true";
        this.form.submit();
        return;
    }

    function LoanSubtract()
    {

        var Persontypes = Ext.create('Ext.data.ArrayStore', {
            fields: ['val', 'title'],
            data: [
                ['3', 'قراردادی ']
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
            title: 'دریافت فایل مالیات',
            bodyPadding: '5 5 0',
            width: 680,
            fieldDefaults: {
                msgTarget: 'side',
                labelWidth: 120
            },
            defaultType: 'textfield',
            items: [{
                    xtype: "numberfield",
                    fieldLabel: 'سال',
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
                 xtype:"numberfield" ,
                 fieldLabel: 'شماره سریال چک',
                 name: 'check-serial',										 
                 width:300,
                 hideTrigger:true
                 },
                 {
                 xtype : "shdatefield",
                 name : "check_date",
                 fieldLabel : "تاریخ چک",
                 width:250
                 },  
                {
                    xtype: "combo",
                    store: new Ext.data.Store({
                        fields: ["bank_id", "name"],
                        proxy: {
                            type: 'jsonp',
                            url: this.address_prefix + "../../../global/domain.data.php?task=searchBank",
                            reader: {
                                root: 'rows',
                                totalProperty: 'totalCount'
                            }
                        }
                    }),
                    valueField: "bank_id",
                    displayField: "name",
                    hiddenName: "BankCode",
                    allowBlank: false,
                    fieldLabel: "نام بانک ",
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls: "search-item"
                    },
                    width: 300
                }, {
                    xtype: 'textfield',
                    name: "BankTitle",
                    fieldLabel: 'نام شعبه',
                    anchor: "100%"
                }/*,{
                 xtype:"numberfield" ,
                 fieldLabel: 'شماره حساب',
                 name: 'account_no',										 
                 width:300,
                 hideTrigger:true
                 },{
                 xtype:"numberfield" ,
                 fieldLabel: 'مبلغ پرداختی',
                 name: 'PayVal',										 
                 width:300,
                 hideTrigger:true
                 }*/,
                {
					xtype: 'fieldset',
					title: "وضعیت استخدامی",
					colspan: 3,
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
							listeners: {
								change: function () {
									parentNode = LoanSubtractObject.formPanel.down("[itemId=chkgroup2]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for (i = 0; i < elems.length; i++)
									{
										if (elems[i].id.indexOf("chkEmpState_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
				},
                {
                    xtype: "combo",
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
                    }),
                    valueField: "InfoID",
                    displayField: "InfoDesc",
                    hiddenName: "PayType",
                    allowBlank: false,
                    fieldLabel: "نوع پرداخت",
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls: "search-item"
                    },
                    width: 300
                }
            ],
            buttons: [{
                    text: "فهرست حقوق(WH)",
                    handler: Ext.bind(this.showReport, this),
                    iconCls: "report"
                },
                {
                    text: "(WP)",
                    handler: Ext.bind(this.showReport3, this),
                    iconCls: "report"
                },
                 {
                 text : "خلاصه فهرست حقوق(WK)",
                 handler : Ext.bind(this.showReport2,this),
                 iconCls : "report"                                
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
						LoanSubtractObject.formPanel.down("[itemId=chkgroup2]").add({
							xtype: "container",
							html: "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.InfoDesc
						});

					});

				}}

		});

    }

    var LoanSubtractObject = new LoanSubtract();


</script>