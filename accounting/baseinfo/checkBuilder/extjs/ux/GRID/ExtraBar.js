/**
*
*  created by : Shabnam Jafarkhani
*  date : 87.10
*
**/

Ext.ExtraBar = Ext.extend(Ext.Toolbar, {
    
    
    
    displayMsg : 'تعداد رکورد ها : {2}',
    
    emptyMsg : 'هيچ ركوردي وجود ندارد',
    
    refreshText : "Refresh",
    
    grid : '',
	
	printMode : false,
	printUrl : '',

    initComponent : function(){
        Ext.ExtraBar.superclass.initComponent.call(this);
        this.cursor = 0;
        this.bind(this.store);
    },

    onRender : function(ct, position){
        Ext.ExtraBar.superclass.onRender.call(this, ct, position);
        
        this.addSeparator();
        
        this.loading = this.addButton({
            tooltip: this.refreshText,
            iconCls: "x-tbar-loading",
            handler: this.onClick.createDelegate(this, ["refresh"])
        });
		this.addSeparator();
		
		var tgrid = this.grid;
		this.ExportToExcel = this.addButton({
            tooltip: "دریافت به فایل Excel",
            iconCls: "excel",            
            handler: function(){
            	//eval("window.open('data:application/vnd.ms-excel;base64,' + Base64.encode(" + tgrid + ".getExcelXml()));");            	
            	eval("document.write(" + tgrid + ".getExcelXml());");
            }
        });
		
		this.addSeparator();
		
		if(this.printMode && this.printUrl != "")
		{
			var turl = this.printUrl;
			
			this.printButton = this.addButton({
	            tooltip: "چاپ اطلاعات",
	            iconCls: "print",            
	            handler: function(){
					alert(turl);
	            	window.open(turl);	            	
	            }
	        });
		}
		
        if(this.displayInfo){
            this.displayEl = Ext.fly(this.el.dom).createChild({cls:'x-paging-info'});
        }
        if(this.dsLoaded){
            this.onLoad.apply(this, this.dsLoaded);
        }
    },	

    updateInfo : function(){
        if(this.displayEl){
            var count = this.store.getCount();
            var msg = count == 0 ?
                this.emptyMsg :
                String.format(
                    this.displayMsg,
                    this.cursor+1, this.cursor+count, this.store.getTotalCount()
                );
            this.displayEl.update(msg);
        }
    },

    onLoad : function(store, r, o){
        if(!this.rendered){
            this.dsLoaded = [store, r, o];
            return;
        }             
       
       this.loading.enable();
       this.updateInfo();
    },       

    onLoadError : function(){
        if(!this.rendered){
            return;
        }
        this.loading.enable();
    },   

    beforeLoad : function(){
        if(this.rendered && this.loading){
            this.loading.disable();
        }
    },

    doLoad : function(){
        this.store.load();
    },

    onClick : function(which){
        var store = this.store;
        switch(which){
           case "refresh":
                this.doLoad(this.cursor);
            break;
        }
    },

    
    unbind : function(store){
        store = Ext.StoreMgr.lookup(store);
        store.un("beforeload", this.beforeLoad, this);
        store.un("load", this.onLoad, this);
        store.un("loadexception", this.onLoadError, this);
        this.store = undefined;
    },

    
    bind : function(store){
        store = Ext.StoreMgr.lookup(store);
        store.on("beforeload", this.beforeLoad, this);
        store.on("load", this.onLoad, this);
        store.on("loadexception", this.onLoadError, this);
        this.store = store;
    }
});

//-----------------------------------------------------------------------------
/*var Base64 = (
	function() {

    // private property
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    // private method for UTF-8 encoding
    function utf8Encode(string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    }

    // public method for encoding
    return {
        encode : (typeof btoa == 'function') ? function(input) { return btoa(input); } : function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;
            input = utf8Encode(input);
            while (i < input.length) {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);
                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;
                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }
                output = output +
                keyStr.charAt(enc1) + keyStr.charAt(enc2) +
                keyStr.charAt(enc3) + keyStr.charAt(enc4);
            }
            return output;
        }
    };
})();*/