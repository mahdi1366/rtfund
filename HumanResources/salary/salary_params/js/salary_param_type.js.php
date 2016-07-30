<script type="text/javascript">
/*-------------------------
 * programmer: Mahdipour
 * CreateDate: 	90.02
 *------------------------- */

SalaryParamTypes.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function SalaryParamTypes()
{
	this.form = this.get("form_salaryParamTypes");
	
	this.mainPanel = new Ext.Panel({
            applyTo: this.get("InfoPNL"),
            autoShow : false ,
			border : false,
            autoHeight: true ,
            width: 800,
            loader:{
                url: this.address_prefix + "../ui/salary_params.php",
                scripts : true
                    }
        });

    this.mainPanel.hide();

	this.afterLoad();
}

SalaryParamTypes.prototype.AddSPT = function()
{
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		param_type: null,
		title: null,
		dim1_id: null,
		dim2_id: null,
		dim3_id: null,
		dim4_id: null,
		person_type: null		
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
    
}


SalaryParamTypes.prototype.editPST = function(store,record,op)
{ 
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/salary_param_type.data.php?task=saveParam',
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
            SalaryParamTypeObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

SalaryParamTypes.prototype.ShowDetail = function()
{
    try {
        this.grid.plugins[0].stopEditing();
        }   
   catch(e){
       
   }

   var record = this.grid.getSelectionModel().getLastSelected();
   
   this.mainPanel.loader.load({
       url: this.address_prefix + "../ui/salary_params.php",
                params : {                 
                    param_type : record.data.param_type ,
                    person_type : record.data.person_type
                },
                scripts : true
   });

    this.mainPanel.show();
    this.grid.collapse();
}

SalaryParamTypes.prototype.ShowCheck = function(value,p,record)
{
   if(value == true)
	   return "<div><img src='/generalUI/resources/images/icons/tick.PNG' / ></div>" ;
}

SalaryParamTypes.prototype.deleteSpt = function()
{
   var record = this.grid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/salary_param_type.data.php',
		params:{
			task: "deleteParam",
			param_type : record.data.param_type ,
            person_type : record.data.person_type 
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			if(response.responseText.indexOf("true") != -1)
			{   
				alert("حذف با موفقیت انجام شد.");
				SalaryParamTypeObject.grid.getStore().load();
				return;
			}
			 else {
                 alert('با شکست مواجه شد.');
             }
		},
		failure: function(){}
	});
}

SalaryParamTypes.opRender = function(value,p,record)
{
	var st = "";
	
		st += "<div  title='حذف اطلاعات' class='remove' onclick='SalaryParamTypeObject.deleteSpt();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st += "<div  title='مشاهده' class='view' onclick='SalaryParamTypeObject.ShowDetail();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>" ;
	return st;
}

//------------------------------------------------------------------------------

SalaryParam.prototype = {
	get : function(elementID){
		return findChild(SalaryParamTypeObject.TabID, elementID);
	}
};

function SalaryParam()
{
	this.afterLoad();
}

SalaryParam.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		param_type: this.param_type ,
        person_type: this.person_type,
		from_date: null,
		to_date: null,
		value: 0.00
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

SalaryParam.prototype.editparam = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(SalaryParamTypeObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: SalaryParamTypeObject.address_prefix + '../data/salary_param.data.php?task=saveParam',
		params:{
			record: Ext.encode(record.data),
            param_type: this.param_type ,
            person_type: this.person_type
		},
		method: 'POST',


		success: function(response,option){
			mask.hide();
			SalaryParamObject.grid.getStore().load();

		},
		failure: function(){}
	});
}

SalaryParam.prototype.Deleting = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		alert("ابتدا ردیف مورد نظر را انتخاب کنید");
		return;
	}

	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(SalaryParamTypeObject.TabID), {msg:'در حال حذف...'});
	mask.show();

	Ext.Ajax.request({
		url: SalaryParamTypeObject.address_prefix + '../data/salary_param.data.php',
		params:{
			task: "deleteParam",
			param_id : record.data.param_id
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			if(response.responseText == "true")
			{
				SalaryParamObject.grid.getStore().load();
				return;
			}
			else
			   alert(response.responseText);
		},
		failure: function(){}
	});
}

SalaryParam.opDelRender = function(value, p, record)
{
	return  "<div  title='حذف اطلاعات' class='remove' onclick='SalaryParamObject.Deleting();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

</script>