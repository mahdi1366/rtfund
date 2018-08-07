<script type="text/javascript">

if (typeof framework == 'undefined')
{
	FrameWorkClass.prototype = {};
	function FrameWorkClass(){};
	var framework = new FrameWorkClass();
}

framework.PersonFilterList = [{
	xtype : "container",
	colspan : 2,
	html : "<input type=radio name='FILTERPERSON_IsReal' value='YES'>حقیقی &nbsp;&nbsp;&nbsp;"+
			"<input type=radio name='FILTERPERSON_IsReal' value='NO' checked>حقوقی" + 
			"&nbsp;&nbsp;&nbsp;<input type=radio name='FILTERPERSON_IsReal' value='' checked>هر دو"
},{
	xtype : "combo",
	store : new Ext.data.SimpleStore({
		proxy: {
			type: 'jsonp',
			url: '/framework/person/persons.data.php?task=selectCompanyTypes',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ['InfoID','InfoDesc'],
		autoLoad : true					
	}),
	displayField : "InfoDesc",
	valueField : "InfoID",
	queryMode : "local",
	fieldLabel: 'نوع شرکت',
	hiddenName: 'FILTERPERSON_CompanyType'
},{
	xtype : "combo",
	store: new Ext.data.SimpleStore({
		fields : ['id','title'],
		data : [ 
			["YES" , "دولتی"],
			["NO" , "خصوصی"]
		]
	}),  
	displayField : "title",
	valueField : "id",
	fieldLabel: 'مالکیت شرکت',
	hiddenName: 'FILTERPERSON_IsGovermental'
},{
	xtype : "treecombo",
	selectChildren: true,
	canSelectFolders: false,
	fieldLabel: 'حوزه فعالیت',
	store : new Ext.data.TreeStore({
		proxy: {
			type: 'ajax',
			url: '/framework/baseInfo/baseInfo.data.php?task=SelectDomainNodes' 
		},
		root: {
			text: "حوزه فعالیت",
			id: 'src',
			expanded: true
		}
	}),
	multiselect : true,
	hiddenName : "FILTERPERSON_DomainID"
	
	
},{
	xtype : "combo",
	fieldLabel : "شهر",
	hiddenName : "FILTERPERSON_CityID",
	store : new Ext.data.SimpleStore({
		proxy: {
			type: 'jsonp',
			url: '/framework/person/persons.data.php?task=selectCities',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ['InfoID','InfoDesc'],
		autoLoad : true					
	}),
	displayField : "InfoDesc",
	queryMode : 'local',
	valueField : "InfoID"
}];
</script>