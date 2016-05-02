


Ext.define('Ext.calendar', {
    extend: 'Ext.panel.Panel',

    layout: {
        type: 'table',
        align: 'stretch',
		columns : 7
    },
	
	StartDate : new Ext.SHDate(),

    initComponent: function () {
		
		this.tbar = {
            border: true,
            items: ['->',{
				id: this.id + '-tb-next',
				handler: this.onNextClick,
				scope: this,
				iconCls: 'x-tbar-page-next'                
            },{
				id : this.id + '-start_date',
				scope: this,
				text : this.StartDate
			},{
				id: this.id + '-tb-prev',
				handler: this.onPrevClick,
				scope: this,
				iconCls: 'x-tbar-page-prev'
			},'->']
        };
		
        this.callParent();
		
		this.dockedItems.add({
			dock : "top",
			items : [{
				id : this.id + '-start_date2',
				scope: this,
				text : this.StartDate
			}]
		});
    },
	
	onRender: function() {
		
		this.currentMonth = this.StartDate.month;
		
		/*daysStyle = "background-color:#ddd;text-align:center;vertical-align:middle";
		this.tbarWeekDays = {
			dock : "top",
			xtype : "toolbar",
			scope: this,
			itemId : this.id + "_week_days",
			id : this.id + "_week_days",
            border: true,
            items: [{
				xtype : "container",
				html : "شنبه",
				height : 30,
				style : daysStyle
			},{
				xtype : "container",
				html : "یکشنبه",
				height : 30,
				style : daysStyle
			},{
				xtype : "container",
				html : "دوشنبه",
				height : 30,
				style : daysStyle
			},{
				xtype : "container",
				html : "سه شنبه",
				height : 30,
				style : daysStyle
			},{
				xtype : "container",
				html : "چهارشنبه",
				height : 30,
				style : daysStyle
			},{
				xtype : "container",
				html : "پنجشنبه",
				height : 30,
				style : daysStyle
			},{
				xtype : "container",
				html : "جمعه",
				height : 30,
				style : daysStyle
			}]
        };
		this.dockedItems.add(this.tbarWeekDays);*/
		
		this.callParent();
	},
	
	// private
    afterRender: function() {
        this.callParent(arguments);        
        this.loadMonthDays(this.StartDate.month);
    },

	loadMonthDays : function()
	{
		this.currentMonth = this.StartDate.month;
		Ext.getCmp(this.id + '-start_date').setText(this.StartDate);
		
		this.removeAll();
		
		days = Ext.SHDate.daysInMonth[this.currentMonth];
		for(i=0; i<10; i++)
		{
			this.add({
				xtype : "container",
				html : "sd"
			})
		}
	},
	
	// private
    onPrevClick: function() {
        
		this.StartDate = Ext.SHDate.add(this.StartDate,Ext.SHDate.MONTH,-1);
		this.loadMonthDays()
    },

    // private
    onNextClick: function() {
		
		this.StartDate = Ext.SHDate.add(this.StartDate,Ext.SHDate.MONTH,1);
		this.loadMonthDays()
    }
	
  
});

