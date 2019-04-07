<script type="text/javascript">
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------

Block.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}				
};

function Block(){

	this.accountCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["CostID","CostCode","CostDesc", "TafsiliType1","TafsiliType2",{
				name : "fullDesc",
				convert : function(value,record){
					return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
				}				
			}],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + 'baseinfo.data.php?task=SelectCostCode',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			}
		}),
		valueField : "CostID",
		displayField : "fullDesc"
	});
	
	this.mainTab = new Ext.TabPanel({
		renderTo: this.get("mainTab"),
		width : 800,
		activeTab: 0,
		plain:true,
		defaults:{autoHeight: true, autoWidth: true}
	});	
}

var BlockObj = new Block();

Block.prototype.NewRowBlock = function(index){
	
	eval("var grid = this.grid" + index + ";");
	eval("var levelID = this.levelID" + index + ";");
	var modelClass =grid.getStore().model;

	Ext.Ajax.request({
		url : this.address_prefix + "baseinfo.data.php?task=getLastID",
		methos : "post",
		params : {
			levelID : levelID
		},
		success : function(response)
		{
			var sd = Ext.decode(response.responseText);
			var record = new modelClass({
				BlockID: null,
				LevelID: levelID,
				BlockDesc: null,
				BlockCode: sd.data,
				BlockEssence: null
			});

			grid.plugins[0].cancelEdit();
			grid.getStore().insert(0, record);
			grid.plugins[0].startEdit(0, 0);
		}
	});
}

Block.prototype.SaveBlock = function(index){

	eval("var grid = this.grid" + index + ";");
	var record = grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveBlockData",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				grid.getStore().load();
			}
			else
			{
				if(st.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

Block.prototype.RemoveBlock = function(index,record)
{
	if(record.data.IsActive == "NO")
		return "";
	return  "<div  title='حذف اطلاعات' class='remove' onclick='BlockObj.Remove(" + index + ");' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";
}

Block.prototype.Remove = function(index){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		var grid;
		me = BlockObj;
		eval("grid = me.grid" + index + ";");	

		var record = grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'baseinfo.data.php',
			method: "POST",
			params: {
				BlockID:record.data.BlockID,
				task: "DeleteBlock"
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.success)
					grid.getStore().load();
				else
					alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
			},
			failure: function(){}
		});
	});

}

</script>
