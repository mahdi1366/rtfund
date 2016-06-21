<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	90.11
//---------------------------

	ManageWorkerJobs.prototype = {
		grid : "",
				
		get : function(elementID){
				return findChild(document.body, elementID);
			}
	};
	
	function ManageWorkerJobs()
	{		
		this.afterLoad();
	}
	
	ManageWorkerJobs.prototype.AddWorkerJobs = function()
	{
		var e = new Ext.data.Record({
			job_id: null,
			title: null,
			job_group: null,
			conditions: null,
			duties: null
		});
		this.grid.plugins[0].stopEditing();
		this.grid.getStore().insert(0, e);
		this.grid.getView().refresh();
		this.grid.getSelectionModel().selectRow(0);
		this.grid.plugins[0].startEditing(0);
	}
	
	
	ManageWorkerJobs.prototype.SaveWorkerJobs = function(store,record,op)
	{
		mask = new Ext.LoadMask(document.body, {msg:'در حال ذخیره سازی...'});
		mask.show();

		Ext.Ajax.request({
			url: '<?= $js_prefix_address?>job.data.php?task=saveWorkerJobs',
			params:{
				record: Ext.encode(record.data)
			},
			method: 'POST',


			success: function(response,option){
				mask.hide();
				ManageWorkerJobsObject.grid.getStore().reload();
			},
			failure: function(){}
		});
	}
	
	ManageWorkerJobs.opDelRender = function(value, p, record)
	{
		return  "<div  title='حذف اطلاعات' class='remove' onclick='ManageWorkerJobsObject.deletejobs();' " +
				"style='float:center;background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:50%;height:16'></div>";
	}
	
	ManageWorkerJobs.prototype.deletejobs = function()
	{
		if(!confirm("آیا مایل به حذف می باشید؟"))
			return;
		
		var record = this.grid.selModel.getSelected();
		
		mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url:  '<?= $js_prefix_address?>job.data.php',
			params:{
				task: "Deljobs",
				record: Ext.encode(record.data)
			},
			method: 'POST',


			success: function(response,option){
				mask.hide();
				ManageWorkerJobsObject.grid.getStore().reload();
			},
			failure: function(){}
		});
	}
	
</script>