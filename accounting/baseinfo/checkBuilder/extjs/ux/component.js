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
        warning : function(elementID, title, format, width){
            if(!msgCt){
                msgCt = Ext.DomHelper.insertFirst(document.getElementById(elementID), {id:'msg-div',align: 'right', style: 'top:0px!important'}, true);
            }
            msgCt.alignTo(document, 't-t');
            msgCt.setLeft(0);
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 2));
            var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s, "icon-warning", width)}, true);
        },
        
        error : function(elementID, title, format, width){
            if(!msgCt){
               msgCt = Ext.DomHelper.insertFirst(document.getElementById(elementID), {id:'msg-div',align: 'right', style: 'top:0px!important'}, true);
            }
            msgCt.alignTo(document, 'tr-tr');
            msgCt.setLeft(0);
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 2));
            var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s, "icon-error", width)}, true);
            //m.slideIn('t').pause(1).ghost("t", {remove:true});
        } 
    };
}();

function BindDropDown(slaveID, filterValue1, filterValue2, filterValue3)
{
	combo = document.getElementById(slaveID);
	eval(" var tmpdata = " + slaveID + "_EXTData;");
	
	while(combo.options.length != 0)
		combo.remove(0);
	
	if(tmpdata[0]["id"] == -1)
		combo.options.add(new Option(tmpdata[0]["text"], tmpdata[0]["id"]));
	
	for(i=0; i<tmpdata.length; i++)
	{
		if(tmpdata[i]["master1"] == filterValue1)
		{
			if(filterValue2 && filterValue2 != "")
			{
				if(tmpdata[i]["master2"] == filterValue2)
				{
					if(filterValue3 && filterValue3 != "")
					{
						if(tmpdata[i]["master3"] == filterValue3)
						{
							combo.options.add(new Option(tmpdata[i]["text"], tmpdata[i]["id"]));
						}
					}
					else
						combo.options.add(new Option(tmpdata[i]["text"], tmpdata[i]["id"]));
				}
			}
			else
				combo.options.add(new Option(tmpdata[i]["text"], tmpdata[i]["id"]));
		}
	}
	combo.disabled = false;
	combo.selectedIndex = 0;
}

function showLOV(pageName, width, height) 
{
	var feature = "help:no;status:no;center:yes;dialogHeight:" +height+ ";dialogWidth:" +width + 
    	";locationbar:no;menubar:no;dialogTop:" + ((screen.availHeight-height)/2) + ";dialogLeft:" + ((screen.availWidth-width)/2) ;
    	
    var val = showModalDialog(pageName, "", feature);
    if (val)
      return val;
}

