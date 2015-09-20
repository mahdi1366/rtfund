//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------


Ext.message = function(){
    var msgCt;

    function createBox(title, text, iconType, width){
        return ['<div class="msg" style="padding:10px;width: ' , width , ';" dir="rtl">',
                '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
                '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc">' +
                '<table width="100%"><tr><td><h3>', title, '</h3>', text, 
                	'</td><td style="width:40px" align="center">' +
                	'<img src="/sharedClasses/resources/images/default/window/', iconType , '.gif"></td></tr></table>' +
                '</div></div></div>',
                '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
                '</div>'].join('');
    }
    return {
		message : function(element, title, text, width){

			if(typeof(element) == "string")
				element = document.getElementById(element);
			
			element.innerHTML = createBox(title, text, "icon-info", width)
				+ "<br>" + element.innerHTML;

            /*if(!msgCt){
                msgCt = Ext.DomHelper.insertFirst(document.getElementById(elementID), {id:'message-div',align: 'right', style: 'top:0px!important'}, true);
            }*/
            /*msgCt.alignTo(document, 't-t');
            msgCt.setLeft(0);
            
            Ext.DomHelper.append(msgCt, {html:createBox(title, s, "icon-info", width)}, true);*/
        },
		
        warning : function(element, title, text, width){

			if(typeof(element) == "string")
				element = document.getElementById(element);
			
			//var s = String.format.apply(String, Array.prototype.slice.call(arguments, 2));
			
			element.innerHTML = createBox(title, text, "icon-warning", width)
				+ "<br>" + element.innerHTML;
            /*if(!msgCt){
                msgCt = Ext.DomHelper.insertFirst(document.getElementById(elementID), {id:'warning-div',align: 'right', style: 'top:0px!important'}, true);
            }
            msgCt.alignTo(document, 't-t');
            msgCt.setLeft(0);
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 2));
            Ext.DomHelper.append(msgCt, {html:createBox(title, s, "icon-warning", width)}, true);*/
        },
        
        error : function(element, title, text, width){
            
			if(typeof(element) == "string")
				element = document.getElementById(element);

			//var s = String.format.apply(String, Array.prototype.slice.call(arguments, 2));
			
			element.innerHTML = createBox(title, text, "icon-error", width)
				+ "<br>" + element.innerHTML;
			/*if(!msgCt){
               msgCt = Ext.DomHelper.insertFirst(document.getElementById(elementID), {id:'error-div',align: 'right', style: 'top:0px!important'}, true);
            }
            msgCt.alignTo(document, 'tr-tr');
            msgCt.setLeft(0);
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 2));
            Ext.DomHelper.append(msgCt, {html:createBox(title, s, "icon-error", width)}, true);
            //m.slideIn('t').pause(1).ghost("t", {remove:true});*/
        } 
    };
}();
