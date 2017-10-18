<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.06
//---------------------------   

	bank.prototype = {
		TabID: '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix: "<?= $js_prefix_address ?>",
		get: function (elementID) {
			return findChild(this.TabID, elementID);
		}
	};
	function bank()
	{
		
		var ItemStore = new Ext.data.Store({
			pageSize: 10,			
			model:  Ext.define(Ext.id(), {
				extend: 'Ext.data.Model',
				fields:['salary_item_type_id','full_title']
			}),
			remoteSort: true,
			proxy:{
				type: 'jsonp',
				url: "/HumanResources/baseInfo/data/salary_item_type.data.php?task=searchSessionItem",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true
		});

		this.personCombo = new Ext.form.ComboBox({
			store: personStore,
			queryMode : "local",
			emptyText: 'جستجوي كارمند بر اساس نام و نام خانوادگي ...',
			typeAhead: false,
			listConfig: {
				loadingText: 'در حال جستجو...'
			},
			pageSize: 10,
			width: 550,
			hiddenName: "PersonID",
			valueField: "PersonID",
			tpl: new Ext.XTemplate(
					'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
					, '<td height="23px">کد پرسنلی</td>'
					, '<td>کد شخص</td>'
					, '<td>نام</td>'
					, '<td>نام خانوادگی</td>'
					, '<td>نوع شخص</td>'
					, '<td>واحد محل خدمت</td></tr>',
					'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
					, '<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
					, '<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
					, '<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
					, '<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
					, '<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
					, '<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
					'</tpl>'
					, '</table>'),
			listeners: {
				select: function (combo, records) {
					var record = records[0];
					this.setValue(record.data.PersonID + ":" + record.data.pfname + ' ' + record.data.plname);
					this.collapse();

				}
			}

		});
		
		
		this.SITCombo = new Ext.form.ComboBox({
			store: ItemStore,
			queryMode : "local",
			emptyText: 'جستجوی قلم مربوطه...',
			typeAhead: false,
			listConfig: {
				loadingText: 'در حال جستجو...'
			},
			pageSize: 10,
			width: 550,
			name: "salary_item_type_id",
			valueField: "salary_item_type_id",
			tpl: new Ext.XTemplate(
					'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
					, '<td height="23px">کد قلم</td>'					
					, '<td>عنوان قلم</td></tr>',
					'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
					, '<td style="border-left:0;border-right:0" class="search-item">{salary_item_type_id}</td>'
					, '<td style="border-left:0;border-right:0" class="search-item">{full_title}</td></tr>',
					'</tpl>'
					, '</table>'),
			listeners: {
				select: function (combo, records) {
					var record = records[0];
					this.setValue(record.data.salary_item_type_id + ":" + record.data.full_title );
					this.collapse();

				}
			}

		});

		return;

	}

	var bankObject = new bank();


	bank.prototype.AddBnak = function ()
	{
		var modelClass = this.grid.getStore().model;
		var record = new modelClass({
			SessionID: "",
			PersonID: null,
			TotalHour: null,
			SessionDate: null
		});

		this.grid.plugins[0].cancelEdit();
		this.grid.getStore().insert(0, record);
		this.grid.plugins[0].startEdit(0, 0);
	}

	bank.prototype.editBank = function (store, record, op)
	{
		mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: this.address_prefix + 'sessions.data.php?task=SaveBank',
			params: {
				record: Ext.encode(record.data)
			},
			method: 'POST',
			success: function (response, op) {
				mask.hide();
				var st = Ext.decode(response.responseText);

				if (st.success === "true")
				{
					alert("ذخیره سازی با موفقیت انجام گردید.");
					bankObject.grid.getStore().load();
					return;
				} else
				{
					ShowExceptions("ErrorDiv", st.data);
				}

			},
			failure: function () {}
		});
	}

	bank.opRender = function (value, p, record)
	{

		return   "<div  title='حذف' class='remove' onclick='bankObject.deletebank();' " +
				"style='float:left;background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:50%;height:16'></div>";
	}

	bank.prototype.deletebank = function ()
	{
		if (!confirm("آیا از حذف اطمینان دارید؟"))
			return;

		var record = this.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: this.address_prefix + 'sessions.data.php?task=removebank',
			params: {
				bid: record.data.SessionID
			},
			method: 'POST',
			success: function (response, option) {
				mask.hide();
				var st = Ext.decode(response.responseText);
				if (st.success)
				{
					alert("حذف با موفقیت انجام شد.");
					bankObject.grid.getStore().load();
				} else
				{
					alert(st.data);
				}
			},
			failure: function () {}
		});
	}



</script>












