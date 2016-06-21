<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------

PersonJob.prototype = {
	parent : PersonObject,
	grid : "",

	get : function(elementID){
		return findChild(this.form, elementID); 
	}
};

function PersonJob()
{
	
	  this.JobCombo = new Ext.form.ComboBox({
                store: new Ext.data.Store({
								pageSize: 10,
								model: Ext.define(Ext.id(), {
								extend: 'Ext.data.Model',
								fields:['job_id','title']
													}),
								remoteSort: true,
								proxy:{
								type: 'jsonp',
								url: '/HumanResources/baseInfo/data/jobs.data.php?task=SearchJob&JobH=1',
								reader: {
								root: 'rows',
								totalProperty: 'totalCount'
								}
								}
								}),
                emptyText:'جستجو...',
                typeAhead: false,
                listConfig : {
                    loadingText: 'در حال جستجو...'
                },
                pageSize:10,
                width: 250,
                hiddenName : "JobID",
                valueField : "job_id",
                displayField : "title" ,

                tpl: new Ext.XTemplate(
                        '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                            ,'<td>کد شغل</td>'
                            ,'<td>عنوان شغل</td></tr>',
                        '<tpl for=".">',
                        '<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{job_id}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{title}</td>'                           
                            ,'</tr>'
                            ,'</tpl>'
                            ,'</table>')

            });
	
}

var PersonJobObject = new PersonJob();

PersonJob.opRender = function(value,p,record)
{
	var st = "";
	
		st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonJobObject.DelJH();' " +
			  "style='float:left;background-repeat:no-repeat;background-position:center;" +
			  "cursor:pointer;width:50%;height:16'></div>";
	return st;
}

PersonJob.prototype.AddJobList = function()
{
      
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		PersonID: <?= $_POST['Q0']?>,
        FromDate:null ,
		ToDate: null,
		JobID: null
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

PersonJob.prototype.editJobList = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();	  

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/job_history.data.php?task=SaveJob',
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
            PersonJobObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

PersonJob.prototype.DelJH = function()
{

   var record = this.grid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال حذف...'});
   mask.show();

   Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/job_history.data.php',
		params:{
			task: "deleteJH",
			PID : record.data.PersonID ,
			RowNO: record.data.RowNO
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				PersonJobObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
    
}

</script>