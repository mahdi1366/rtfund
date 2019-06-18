/**
*
*  created by : Shabnam Jafarkhani
*  date : 87.10
*
**/

Ext.define('Ext.toolbar.ExtraBar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.extrabar',
    alternateClassName: 'Ext.ExtraBar',
    requires: ['Ext.toolbar.TextItem', 'Ext.form.field.Number'],
    mixins: {
        bindable: 'Ext.util.Bindable'    
    },
    displayInfo: true,
	displayMsg : 'تعداد رکورد ها : {2}',
    emptyMsg : 'هيچ ركوردي وجود ندارد',
    refreshText : "Refresh",
	ExcelButton : true,
	
    getPagingItems: function() {
        var me = this;
        return ['-',
        {
            itemId: 'refresh',
            tooltip: me.refreshText,
            overflowText: me.refreshText,
            iconCls: Ext.baseCSSPrefix + 'tbar-loading',
            handler: me.doRefresh,
            scope: me
        },'-',{
            itemId: 'excel',
			hidden : !this.ExcelButton,
            tooltip: "خروجی اکسل",
            overflowText: "خروجی اکسل",
            iconCls: 'excel',
            handler: function(){this.up().downloadExcelXml();},
            scope: me
        }];
    },
   initComponent : function(){
        var me = this,
            pagingItems = me.getPagingItems(),
            userItems   = me.items || me.buttons || [];
        if (me.prependButtons) {
            me.items = userItems.concat(pagingItems);
        } else {
            me.items = pagingItems.concat(userItems);
        }
        delete me.buttons;
        if (me.displayInfo) {
            me.items.push('->');
            me.items.push({xtype: 'tbtext', itemId: 'displayItem'});
        }
        me.callParent();
        me.addEvents(
            'change',
            'beforechange'
        );
        me.on('beforerender', me.onLoad, me, {single: true});
        me.bindStore(me.store || 'ext-empty-store', true);
    },
    updateInfo : function(){
        var me = this,
            displayItem = me.child('#displayItem'),
            store = me.store,
            pageData = me.getPageData(),
            count, msg;
        if (displayItem) {
            count = store.getCount();
            if (count === 0) {
                msg = me.emptyMsg;
            } else {
                msg = Ext.String.format(
                    me.displayMsg,
                    pageData.fromRecord,
                    pageData.toRecord,
                    pageData.total
                );
            }
            displayItem.setText(msg);
            me.doComponentLayout();
        }
    },
    onLoad : function(){
        var me = this,
            pageData,
            currPage,
            pageCount,
            afterText,
            count,
            isEmpty;
        count = me.store.getCount();
        isEmpty = count === 0;
        if (!isEmpty) {
            pageData = me.getPageData();
        } else {
            pageCount = 0;
        }
        me.child('#refresh').enable();
        me.updateInfo();
        if (me.rendered) {
            me.fireEvent('change', me, pageData);
        }
    },
    getPageData : function(){
        var store = this.store,
            totalCount = store.getTotalCount();
        return {
            total : totalCount,
            currentPage : store.currentPage,
            pageCount: Math.ceil(totalCount / store.pageSize),
            fromRecord: ((store.currentPage - 1) * store.pageSize) + 1,
            toRecord: Math.min(store.currentPage * store.pageSize, totalCount)
        };
    },
    onLoadError : function(){
        if (!this.rendered) {
            return;
        }
        this.child('#refresh').enable();
    },
    beforeLoad : function(){
        if(this.rendered && this.refresh){
            this.refresh.disable();
        }
    },
    doRefresh : function(){
        var me = this,
            current = me.store.currentPage;
        if (me.fireEvent('beforechange', me, current) !== false) {
            me.store.loadPage(current);
        }
    },
    getStoreListeners: function() {
        return {
            beforeload: this.beforeLoad,
            load: this.onLoad,
            exception: this.onLoadError
        };
    },
    unbind : function(store){
        this.bindStore(null);
    },
    bind : function(store){
        this.bindStore(store);
    },
    onDestroy : function(){
        this.unbind();
        this.callParent();
    }
});