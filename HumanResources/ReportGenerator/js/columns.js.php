<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.06
//---------------------------

ReportGeneratorColumns.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function ReportGeneratorColumns()
{
	this.form = this.get("form_info");
	
	this.tree = new Ext.tree.ColumnTree({
		width: 1400,
        height: 600,
        autoScroll:true,
        title: 'ستون های گزارش ساز',
        rootVisible:false,
        renderTo : this.get("div_tree"),

         columns:[{
            header:"عنوان ستون",
			width: 300,
            dataIndex:'col_name'
        },{
            header:"عنوان فیلد",
			width: 200,
            dataIndex:'field_name'
        },{
            header:"کد نوع اطلاعات پایه",
			width: 100,
            dataIndex:'basic_type_id'
        },{
            header:"نام جدول",
			width: 100,
            dataIndex:'table_name'
        },{
            header:"نوع جستجو",
			width: 100,
            dataIndex:'search_mode'
        },{
            header:"کد جدول اطلاعات پایه",
			width: 100,
            dataIndex:'basic_info_table'
        },{
            header:"مقدار checkbox",
			width: 60,
            dataIndex:'check_value'
        },{
            header:"متن checkbox",
			width: 100,
            dataIndex:'check_text'
        },{
            header:"renderer",
			width: 200,
            dataIndex:'renderer'
        }],

        loader: new Ext.tree.TreeLoader({
			preloadChildren: true,
            dataUrl: this.address_prefix + "../data/ReportGenerator.data.php?task=GetTreeNodes",
            uiProviders:{'col': Ext.tree.ColumnNodeUI},
			clearOnLoad: false
        }),

        root: new Ext.tree.AsyncTreeNode({
            text:'Tasks',
			expanded: true
        }),

		tbar :[
			{
				text : "تعریف جدول",
				iconCls : "list",
				handler : function(){ReportGeneratorColumnsObject.beforeNewTable();}
			},'-',{
				text : "ایجاد رابطه",
				iconCls : "add",
				handler : function(){ReportGeneratorColumnsObject.beforeAddRelation();}
			},{}]
    });

	this.tree.render();
}

var ReportGeneratorColumnsObject = new ReportGeneratorColumns();

ReportGeneratorColumns.prototype.beforeNewTable = function()
{
	if(!this.newTableWin)
	{
		this.newTableWin = new Ext.Window({
			applyTo : "newTableWin",
			contentEl : "newTablePnl",
			width : 500,
			title: "تعریف جدول",
			layout:'fit',
			modal: true,
			autoHeight : true,
			closeAction:'hide',

			buttons : [{
				text : "دخیره",
				iconCls : "save",
				handler : function(){ReportGeneratorColumnsObject.newTable();}
			},{
				text : "انصراف",
				handler : function(){ReportGeneratorColumnsObject.newTableWin.hide();}
			}]
		});
	}
	this.newTableWin.show();
}

ReportGeneratorColumns.prototype.newTable = function()
{
	var mask = new Ext.LoadMask(this.TabID, {msg:'در حال انجام عملیات...'});
	mask.show();

	Ext.Ajax.request({
	  	url : this.address_prefix + "../data/ReportGenerator.data.php?task=newTable",
	  	method : "POST",
		form : this.form,
	  	success : function()
	  	{
	  		//ReportGeneratorColumnsObject.tree.getLoader().load(ReportGeneratorColumnsObject.tree.getRootNode());
			mask.hide();
			ReportGeneratorColumnsObject.newTableWin.hide();
	  	}
	});
}



</script>