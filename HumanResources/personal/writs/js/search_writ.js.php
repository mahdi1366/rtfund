<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------

SearchWrit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	grid : "",
	mainPanel : "",
	advanceSearchPanel : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function SearchWrit()
{
	this.form = this.get("form_SearchWrt");
	
	this.grid = <?= $grid?>;
    this.grid.getView().getRowClass = function(record,index)
                                        {
                                           if(record.data.correct_completed == 1 ){  return "YellowRow"; };
                                           return "";
                                        }
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceSearchDIV"),
		title: "جستجوی پیشرفته",
		autoWidth:true,
		autoHeight: true,
		collapsible : true,
		animCollapse: false,
		frame: true,
		width : 800,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
		loader : {
			url : this.address_prefix + "advance_search_writ.php",
			scripts: true
		},
		
		buttons : [{
			text:'جستجو',
			iconCls: 'search',
			handler: function(){SearchWritObject.advance_searching();}
		},{
			text : "پاک کردن فرم گزارش",
			iconCls : "clear",
			handler : function(){Ext.get(SearchWritObject.form).clear();}
		}]
	});
	this.advanceSearchPanel.loader.load({
		callback: function(){
			advanceSearchObject = new advanceSearch(SearchWritObject.form);
			SearchWritObject.advanceSearchPanel.doLayout();
		}
	});
	//Ext.get(this.get("AdvanceSearchPNL")).addKeyListener(13, function(){SearchWritObject.advance_searching();});
}

var SearchWritObject = new SearchWrit();
var advanceSearchObject;
//------------------------------------------------------

SearchWrit.prototype.advance_searching = function()
{
	this.advanceSearchPanel.collapse();
	
	if(!this.grid.rendered)
		this.grid.render(this.get("WritResultDIV"));
	else
		this.grid.getStore().load();

}

SearchWrit.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='SearchWritObject.editWrit();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
		st += "<div  title='حذف اطلاعات' class='remove' onclick='SearchWritObject.deleteWrit();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}
	    
SearchWrit.prototype.editWrit = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date
			
		 });
}	    

SearchWrit.prototype.deleteWrit = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();

	if(!confirm("آيا از حذف اطمينان داريد؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : this.address_prefix + "../data/writ.data.php",
		method : "POST",
		params : {
			task : "DeleteWrit",
			writ_id : record.data.writ_id,
			writ_ver: record.data.writ_ver,
			staff_id : record.data.staff_id
		},
		success : function(response)
		{
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حکم مورد نظر با موفقیت حذف شد");
					SearchWritObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
			mask.hide();
		}
	});
}


</script>