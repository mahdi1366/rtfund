<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

SearchPerson.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	personCombo : "",
	advanceSearchPanel : "",
	PersonGrid : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function SearchPerson()
{  
	this.form = this.get("form_SearchPerson");
	
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
		fieldLabel : "جستجوی فرد",
		
		tpl: new Ext.XTemplate(
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
			    ,'</table>'),
				listeners :{
					select : function(combo, records){
						var record = records[0];
				
						this.setValue("[" + record.data.PersonID + "] " + record.data.pfname + ' ' + record.data.plname);
						SearchPersonObject.form.PersonID.value = record.data.PersonID;
						this.collapse();
						
					}
				}
		
	});

	var editButtonText = '<?= ($accessObj->EditFlag) ? "ویرایش" : "مشاهده اطلاعات"  ?>';
	
	new Ext.Panel({
		applyTo: this.get("selectPersonDIV"),
		title: "انتخاب شخص",
		width: 800,
		frame:true,
		items : [this.personCombo],
		buttons : [  <?if($accessObj->AddFlag){?>
			{
				text: "ایجاد",
				iconCls: 'add',
				handler: function(){
					framework.OpenPage(SearchPersonObject.address_prefix + "new_person.php", "ایجاد فرد جدید",
					{FacilID : '<?= $_POST["MenuID"]?>'});
				}
			}, <?}?> {
				text: editButtonText ,
				iconCls:'edit' ,
				handler:function(){
					if(SearchPersonObject.form.PersonID.value == "")
					{
						alert("ابتدا فرد مورد نظر خود را انتخاب کنید");
						return;
					}
					framework.OpenPage(SearchPersonObject.address_prefix + "new_person.php", "مشخصات فرد",
								{Q0 : SearchPersonObject.form.PersonID.value,
								FacilID : '<?= $_POST["MenuID"]?>'});
					
				}
			},
			{
				text:"حذف" ,
				iconCls: 'remove',
				handler: function(){
					SearchPersonObject.deleteUser();
				}
			
			}
		]
	});

        this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("advanceSearchDIV"),
		title: "جستجوی پیشرفته اشخاص",
		width: 800,
		collapsible : true,
		collapsed : true,
		animCollapse: false,
		frame: true,
		listeners:{
			expand: function(){SearchPersonObject.form.from_SID.focus();}
		},
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
		contentEl : this.get("advanceSearchPNL"), 
		buttons : [{
			text:'جستجو',
			iconCls: 'search',
			handler: function(){SearchPersonObject.searching();}
		},{
			text : "پاک کردن فرم گزارش",
			iconCls : "clear",
			handler : function(){
				SearchPersonObject.advanceSearchPanel.getEl().clear();
				SearchPersonObject.get("org_unit").value = "";
			}
		}]
	
	});
	
	Ext.get(this.get("searchTBL")).addKeyListener(13, function(){SearchPersonObject.searching();});

	this.afterLoad();
	
}

//------------------------------------------------------
SearchPerson.prototype.searching = function()
{
	if(this.PersonGrid.rendered )
		this.PersonGrid.getStore().load();
	else
	{
		this.PersonGrid.render(this.get("personResultDIV"));
		
	}	
	this.advanceSearchPanel.collapse();
	
}

SearchPerson.opRender = function(value, p, record)
{
	return  "<div  title='ویرایش اطلاعات' class='edit' onclick='SearchPersonObject.editInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>" +
			"<div  title='حذف اطلاعات' class='remove' onclick='SearchPersonObject.deleteInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

SearchPerson.prototype.editInfo = function(record)
{
    var record =  this.PersonGrid.getSelectionModel().getLastSelected();
    
	framework.OpenPage(SearchPersonObject.address_prefix + "new_person.php", "مشخصات فرد", {Q0 : record.data.PersonID,
			FacilID : '<?= $_POST["MenuID"]?>'});

}

SearchPerson.prototype.deleteUser = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return false;

	Ext.Ajax.request({
		url : this.address_prefix + "../data/person.data.php",
		methos : "POST",
		params : {
			task : "delete",
			PersonID : this.form.PersonID.value
		},
		success : function(response)
		{
			if(response.responseText == "true")
				alert("فرد با موفقیت حذف شد.");
			else
				alert("عملیات مورد نظر با شکست مواجه شد.");
		}
	});
}

</script>