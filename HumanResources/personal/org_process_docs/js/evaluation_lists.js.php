<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.01.22
//---------------------------

    EvaluationList.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",

        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };

function EvaluationList()
{
    this.form = this.get("form_EvalList");
    this.UnitLOV =   new Ext.form.TriggerField({
                        triggerCls:'x-form-search-trigger',
                        onTriggerClick : function(){
                            returnVal = LOV_OrgUnit();

                            if(returnVal != "")
                            {
                                this.setValue(returnVal);
                            }
                        },
                        width : 90
                    });

     
     this.mainPanel = new Ext.Panel({
                applyTo: this.get("mainpanelDIV"),
                contentEl : this.get("EvalTBL"),
                title: "لیست ارزشیابی",
                width: 600,
                hidden:true , 
                frame:true,
                buttons : [
                    {
                        text : "ذخیره ",
                        iconCls : "save",
                        handler : function(){ EvaluationListObject.SaveValList(); }
                    },
                    {
                        text : "انصراف ",
                        iconCls : "back",
                        handler : function(){
                            EvaluationListObject.grid.show();
                            EvaluationListObject.mainPanel.hide();
                            EvaluationListObject.mgrid.hide(); }
                    }
                ],
	    dockedItems : [{
	            xtype : "toolbar",
		    dock : "bottom",
		    ui: 'footer',
		    dir : "ltr",
		    items : [{
				text : "حذف افراد واحدها",
				iconCls : "user_delete",direction : "ltr",
				handler : function(){ EvaluationListObject.DelAllPrn(); }				
				},{
				text : "افزودن افراد واحدها ",
				iconCls : "user_add",direction : "ltr",
				handler : function(){ EvaluationListObject.InsertAllPrn(); }
			    }]
		    }]
            });

           

            
 }

var EvaluationListObject = new EvaluationList();

EvaluationList.opRender = function(value,p,record)
{
	var st = "";
	
		st += "<div  title='حذف اطلاعات' class='remove' onclick='EvaluationListObject.DelEvalList();' " +
			  "style='float:left;background-repeat:no-repeat;background-position:center;" +
			  "cursor:pointer;width:50%;height:16'></div>";

        st += "<div  title='مشاهده' class='view' onclick='EvaluationListObject.ShowEvalDetail();' " +
              "style='float:left;background-repeat:no-repeat;background-position:center;" +
              "cursor:pointer;width:50%;height:16'></div>" ;
	return st;
}

EvaluationList.opRenderMembers = function(value,p,record)
{
	var st = "";

		st += "<div  title='حذف اطلاعات' class='remove' onclick='EvaluationListObject.DelMember();' " +
			  "style='float:left;background-repeat:no-repeat;background-position:center;" +
			  "cursor:pointer;width:50%;height:16'></div>";
	return st;
}

EvaluationList.prototype.AddEvalList = function()
{
      
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		list_id: null,
        list_date: GtoJ(new Date()).format("Y/m/d") ,
		ouid: null,
		doc_state: null
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

EvaluationList.prototype.AddMember = function()
{
    var modelClass = this.mgrid.getStore().model;
	var record = new modelClass({
		list_id: this.list_id,
        staff_id: null ,
        functional_score: null ,
        job_behaviour_score:null ,
        social_behaviour_score : null ,
        annual_coef : null ,
        high_job_coef : null ,
        comments:null ,
        scores_sum : null
	});
	this.mgrid.plugins[0].cancelEdit();
	this.mgrid.getStore().insert(0, record);
	this.mgrid.plugins[0].startEdit(0, 0);
}

EvaluationList.prototype.editValList = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php?task=SaveValList',
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
            EvaluationListObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

EvaluationList.prototype.editMember = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php?task=SaveMember',
		params:{
			record: Ext.encode(record.data),
            list_id: this.list_id
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
            EvaluationListObject.mgrid.getStore().load();
		},
		failure: function(){}
	});
}

EvaluationList.prototype.SaveValList = function()
{
	
	
	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php?task=SaveValList',
		params:{
			list_id: this.list_id
		},
		method: 'POST',
		form: this.form,

		success: function(response,option){
			
            alert('ذخیره سازی با موفقیت انجام شد .');
            EvaluationListObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

EvaluationList.prototype.DelEvalList = function()
{

    var record = this.grid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php',
		params:{
			task: "deleteEval",
			list_id : record.data.list_id 
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				EvaluationListObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
    
}
EvaluationList.prototype.DelMember = function()
{

   var record = this.mgrid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php',
		params:{
			task: "deleteMember",
			ListItemID : record.data.ListItemID ,
            list_id :this.list_id 
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				EvaluationListObject.mgrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});

}
EvaluationList.prototype.ShowEvalDetail = function(record)
{

    var record =  this.grid.getSelectionModel().getLastSelected();
	this.mainPanel.show();
    this.list_id = record.data.list_id;
    this.ouid = record.data.ouid;
	this.get('list_id').innerHTML = record.data.list_id;
	this.get('ouidTitle').innerHTML = record.data.unit_full_title;
    this.get('ouid').value = record.data.ouid ;
    this.get('list_date').innerHTML =  MiladiToShamsi(record.data.list_date) ;
    this.get('doc_state').value =record.data.doc_state ;
    this.get('person_type_Title').innerHTML =record.data.person_title ;
    this.get('person_type').value =  record.data.person_type ;
    

    this.grid.hide();

   

         this.personCombo = new Ext.form.ComboBox({
                store: new Ext.data.Store({
                pageSize: 10,
                model: Ext.define(Ext.id(), {
                extend: 'Ext.data.Model',
                fields:['PersonID','pfname','plname','unit_name','person_type','staff_id','personTypeName',{
                        name : "fullname",
                        convert : function(v,record){return record.data.pfname+" "+record.data.plname;}
                }]
                }),
                remoteSort: true,
                proxy:{
                type: 'jsonp',
                url: '/HumanResources/personal/persons/data/person.data.php?task=searchPerson&newPersons=true&ouid=' + EvaluationListObject.ouid ,
                reader: {
                root: 'rows',
                totalProperty: 'totalCount'
                }
                }
                }),
                emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
                typeAhead: false,
                listConfig : {
                    loadingText: 'در حال جستجو...'
                },
                pageSize:10,
                width: 550,
                hiddenName : "staff_id",
                valueField : "staff_id",
                displayField : "fullname" ,

                tpl: new Ext.XTemplate(
                        '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                            ,'<td>کد شخص</td>'
                            ,'<td>نام</td>'
                            ,'<td>نام خانوادگی</td>'
                            ,'<td>نوع شخص</td></tr>',
                        '<tpl for=".">',
                        '<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
                            ,'</tr>'
                            ,'</tpl>'
                            ,'</table>')

            });

            <?

            $mdg = new sadaf_datagrid("MemberEvalGrid", $js_prefix_address . "../data/evaluation.data.php?task=SelectMemberEvalList", "MemberEvalDIV");

            $col = $mdg->addColumn("شناسه", "ListItemID", "string",true);
            $col->renderer = "function(v){ return ' '; }" ;
            
            $col = $mdg->addColumn("نام کامل فرد", "pfname", "string",true);
            $col->renderer = "function(v){ return ' '; }" ;

            $col = $mdg->addColumn("نام خانوادگی", "plname", "string",true);
            $col->renderer = "function(v){ return ' '; }" ;

            $col = $mdg->addColumn("نام و نام خانوادگی", "staff_id", "int");
            $col->renderer = "function(v,p,r){return r.data.pfname + '  ' + r.data.plname }";
            $col->editor = "EvaluationListObject.personCombo";


            $col = $mdg->addColumn("امتیاز اختصاصی", "ProprietaryScore", "int");
            $col->editor = ColumnEditor::NumberField(true);
            $col->width = 110;

            $col = $mdg->addColumn("امتیاز عمومی", "PublicScore", "int");
            $col->editor = ColumnEditor::NumberField(true);
            $col->width = 80;           

            $col = $mdg->addColumn("مجموع امتیازات", "scores_sum", "int");
            $col->editor = ColumnEditor::NumberField();
            $col->width = 80;

            $col = $mdg->addColumn("عملیات", "", "string");
            $col->renderer = "function(v,p,r){return EvaluationList.opRenderMembers(v,p,r);}";
            $col->width = 50;

                $mdg->addButton = true;
                $mdg->addHandler = "function(){EvaluationListObject.AddMember();}";

            $mdg->pageSize = "20";
            $mdg->EnableSearch = false ;
            $mdg->width = 680;
            $mdg->height = 630;
            $mdg->title = "لیست افراد";
            $mdg->autoExpandColumn = "staff_id";
           
                $mdg->enableRowEdit = true ;
                $mdg->rowEditOkHandler = "function(v,p,r){ return EvaluationListObject.editMember(v,p,r);}";

            $mgrid = $mdg->makeGrid_returnObjects();

            ?>
     EvaluationListObject.mgrid = <?=$mgrid?>;

     this.mgrid.getStore().proxy.extraParams["list_id"] = record.data.list_id ;

    if(this.mgrid.rendered == true )
        this.mgrid.getStore.load();
    else
        this.mgrid.render("MemberEvalDIV");

}

EvaluationList.prototype.InsertAllPrn = function()
{
	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php?task=AddAllPrn',
		params:{
			list_id: this.list_id,
            ouid:this.get('ouid').value,
            person_type:this.get('person_type').value
		},
        success: function(response,option){
            
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert('ذخیره سازی با موفقیت انجام شد .');
                 EvaluationListObject.mgrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},

		failure: function(){}
	});
}

EvaluationList.prototype.DelAllPrn = function()
{
  
   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/evaluation.data.php',
		params:{
			task: "DelAllPrn",
			list_id : this.list_id
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				EvaluationListObject.mgrid.getStore().load();
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












