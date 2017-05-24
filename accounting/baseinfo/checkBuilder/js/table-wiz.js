{
  json : {
    title:"ایجاد جدول جدید",
    size : {width:270,height:220},
    alignTo : [document,'c-c'],
	  "scope.!callback" : Ext.emptyFn,
	  "scope.!firstFocus" : Ext.id(),
		listeners : {
		 afterjsonload :  function(){
				var e = Ext.getCmp(scope.firstFocus);
				e.focus.defer(150,e,[]);
		 }
   }
  },
	xtype:"form",
	frame:true,
	labelWidth:90,
	buttons:[{
	 text:'Ok',
	 scope:this,
	 handler: DrawTable
	 },{
	  text:'Cancel',
	  handler:function() {scope.close();}
  }],
	items:[{
	  xtype:"numberfield",
	  value : 2,
	  fieldLabel:"Columns",
	  id: scope.firstFocus,
	  width:48,
	  name:"cols"
	},{
	  xtype:"numberfield",
	  value : 2,
	  fieldLabel:"Rows",
	  width:48,
	  name:"rows"
	},{
	  xtype:"textfield",
	  value : 0,
	  fieldLabel:"cellpadding",
	  width:48,
	  name:"cellpadding"
	},{
	  xtype:"textfield",
	  fieldLabel:"cellspacing",
	  value : 0,
	  width:48,
	  name:"cellspacing"
	},{
	  xtype:"checkbox",
	  fieldLabel:"Borders",
	  name:"borders",
	  checked:true
	}]
}