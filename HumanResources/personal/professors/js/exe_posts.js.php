<script type="text/javascript">
//---------------------------
// programmer:	SH.Jafarkhani
// Date:		90.06
//---------------------------

ExePosts.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ExePosts()
{
	this.form = this.get("form_exePosts");

	this.personCombo = new Ext.form.ComboBox({

		store: personStore,
		emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
		typeAhead: false,
		listConfig : {
			loadingText: 'در حال جستجو...'
		},
		pageSize:10,
		width: 550,
		hiddenName : "PersonID",
		valueField : "PersonID",
		fieldLabel : "جستجوی فرد"
		,tpl: new Ext.XTemplate(
			'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
				,'<td height="23px">کد پرسنلی</td>'
				,'<td>کد شخص</td>'
				,'<td>نام</td>'
				,'<td>نام خانوادگی</td>'
				,'<td>نوع شخص</td>'
				,'<td>واحد محل خدمت</td></tr>',
			'<tpl for=".">',
			'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
			'</tpl>'
			,'</table>')
            ,listeners :{
					select : function(combo, records){
						var record = records[0];

						this.setValue("[" + record.data.PersonID + "] " + record.data.pfname + ' ' + record.data.plname);
						 ExePostsObject.form.staff_id.value = record.data.staff_id;
						this.collapse();

					}
				}
	});

	this.mainPanel = new Ext.Panel({
		applyTo: this.get("exe_DIV"),
		contentEl : this.get("exe_TBL"),
		title: "انتخاب فرد",
		autoHeight: true,
		width: 700 ,
        frame:true ,
        items : [this.personCombo],
		collapsible : true,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
        buttons : [{
			text:'بارگزاری اطلاعات',
			iconCls: 'loading',
			handler: function(){ExePostsObject.LoadInfo();}
		}]
	});

	this.newPanel = new Ext.Panel({
		applyTo: this.get("new_exe_post"),
		contentEl : this.get("pnl_exe_post"),
		title: "ایجاد پست جدید",
        frame:true,
		autoHeight: true,
		width: 600,
		hidden : true,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
        buttons : [{
			text:'ذخیره',
			iconCls: 'save',
			handler: function(){ExePostsObject.Save();}
		},{
			text:'انصراف',
			iconCls: 'back',
			handler: function(){ExePostsObject.newPanel.hide();}
		}]
	});

	this.grid = <?= $grid?>;

	this.postlov = new Ext.form.TriggerField({
                            triggerCls:'x-form-search-trigger',
                            onTriggerClick : function(){
                                var returnVal = LOV_Post('ALL');
                                this.setValue(returnVal.post_id);
                                ExePostsObject.get("post_title").innerHTML = returnVal.post_no + "-" + returnVal.post_title;
                            },
                            applyTo : this.get("post_id"),
                            width : 120
                        });

	new Ext.form.SHDateField({
        inputId: 'letter_date',
		applyTo: this.get('letter_date'),
		format: 'Y/m/d',
		width :'80px'
	});

	new Ext.form.SHDateField({
        inputId: 'from_date',
		applyTo: this.get('from_date'),
		format: 'Y/m/d',
		width :'80px'
	});

	new Ext.form.SHDateField({
        inputId:'to_date',
		applyTo: this.get('to_date'),
		format: 'Y/m/d',
		width :'80px'
	});
}

var ExePostsObject = new ExePosts();

ExePosts.prototype.LoadInfo = function()
{
	if(this.grid.rendered == true )
           this.grid.getStore().load();
        else 
            this.grid.render(this.get("divGRID"));
	
}

ExePosts.prototype.Add = function()
{
	this.newPanel.show();
	Ext.get(this.get("pnl_exe_post")).clear();
	this.get("row_no").value = "";
}

ExePosts.prototype.Edit = function()
{
	var record =  this.grid.getSelectionModel().getLastSelected();

	if(!record)
		return false;

	this.newPanel.show();
	this.get("row_no").value = record.data.row_no;
    this.postlov.setValue(record.data.post_id);
	this.get("post_id").value = record.data.post_id;
	this.get("post_title").innerHTML = record.data.postTitle;
	this.get("letter_no").value = record.data.letter_no;
	this.get("letter_date").value = MiladiToShamsi(record.data.letter_date);
	this.get("from_date").value = MiladiToShamsi(record.data.from_date);
	this.get("to_date").value = MiladiToShamsi(record.data.to_date);
	this.get("description").value = record.data.description;

	this.get("assign_post").checked = record.data.assign_post == "1" ? true : false;
}

ExePosts.prototype.Save = function()
{
	if(this.get("post_id").value == "")
	{
		alert("انتخاب پست سازمانی الزامی است.");
		return false;
	}
	if(this.get("letter_date").value == "" || this.get("from_date").value == "")
	{
		alert("تکمیل تاریخ نامه و تاریخ شروع الزامی است.");
		return false;
	}

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/exe_posts.data.php?task=save',
		method: 'POST',
		form: this.form,

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ExePostsObject.newPanel.hide();
				ExePostsObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ExePosts.prototype.DeleteRender = function(v,p,r)
{
	return "<div  title='ویرایش اطلاعات' class='edit' onclick='ExePostsObject.Edit();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>" +
			
			"<div  title='حذف' class='remove' onclick='ExePostsObject.Remove(" + r.data.staff_id + "," + r.data.row_no + ");' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

ExePosts.prototype.asignRender = function(v,p,r)
{
	if(v == "1")
		return "<div  title='پست سازماني به فرد واگذار شده است' class='tick' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"width:50%;height:16'></div>";
	return "";
}

ExePosts.prototype.Remove = function(staff_id, row_no)
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/exe_posts.data.php?task=delete',
		method: 'POST',
		params : {
			staff_id : staff_id,
			row_no : row_no
		},

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ExePostsObject.newPanel.hide();
				ExePostsObject.grid.getStore().load();
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