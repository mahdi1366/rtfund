<script>
/*-------------------------
 * programmer: Jafarkhani
 * CreateDate: 90.01
 *------------------------- */

Ext.BLANK_IMAGE_URL="extjs/s.gif";

Ext.onReady(function (){
	
	var root = Ext.getCmp("fb_treePanel").getRootNode();
	root = root.appendChild({id: "formItems", text: "عناصر فرم", draggable: false});
	//for(var i=0; i<FormItems.length; i++)
	root.appendChild(FormItems);

	Ext.getCmp("fb_treePanel").getRootNode().expand(true);

	reload("index.data.php?task=getFileContent&ChequeBookID=<?= $checkID?>");
});

var trickerSize = 10;
var ElementIndex = 0;
//var AllStyles = new Array();
var SelectedElementID = "";
var HelperParamArray = new Array();

function dropFn(ddSource, e, data)
{
	var sourceID = e.getTarget().id;
	
	if(!data.node)
	{
		document.getElementById(sourceID).appendChild(
			document.getElementById(data.sourceEl.id));
		return true;
	}
	else
	{
		if(data.node && data.node.parentNode.id == "formItems")
		{
			data.node.remove();
		}
	}
	if(typeof data.node.attributes["config"] == "function")
	{
		HelperParamArray.push(sourceID);
		HelperParamArray.push(e.getXY());
		data.node.attributes["config"].call();
		return true;
	}
	switch(data.node.id)
	{
		case "fb_srcLabel":
			DrawLabel(sourceID, e);
			return true;

		case "fb_srcImage":
			DrawImage(sourceID, e);
			return true;

		case "fb_srcDiv":
			DrawDiv(sourceID, e);
			return true;

		case "fb_srcLink":
			DrawLink(sourceID, e);
			return true;
	}
	switch(data.node.attributes["type"])
	{
		case "label":
			DrawKeyLabel(sourceID, data.node.attributes,e);
			return true;
		case "text":
			DrawTextbox(sourceID, data.node.attributes,e);
			return true;
		case "combo":
			DrawCombo(sourceID, data.node.attributes,e);
			return true;
		case "checkbox":
			DrawCheckbox(sourceID, data.node.attributes,e);
			return true;
		case "radio":
			DrawRadio(sourceID, data.node.attributes,e);
			return true;
		case "textarea":
			DrawTextaera(sourceID, data.node.attributes,e);
			return true;
		case "img":
			DrawImageFile(sourceID, data.node.attributes,e);	 
		case "button":
			DrawButton(sourceID, data.node.attributes,e);
			return true;
	}
	
	return true;
}

function selectElement()
{
	if(SelectedElementID != "" && Ext.get(SelectedElementID))
		Ext.get(SelectedElementID).removeClass("label-select");

	if(arguments.length != 0 && typeof arguments[0] == "string")
		SelectedElementID = arguments[0];
	else
		SelectedElementID = this.id;
	Ext.get(SelectedElementID).addClass("label-select");

	Ext.getCmp("fb_propertyGrid").enable();
	Ext.getCmp("ext_SpecialPropertyDiv").enable();
	FillSpecialProperties();

	/*Ext.getCmp("fb_propertyGrid").setSource(
		window.getComputedStyle(document.getElementById(SelectedElementID), ""));*/
	//Ext.getCmp("fb_propertyGrid").setSource(AllStyles[SelectedElementID] );
	var elem = document.getElementById(SelectedElementID);
	var source = elem.style;
	/*cssTxt = elem.style.cssText.split(";");
	var source = {};
	for(i=0;i<cssTxt.length-1; i++)
	{
		arr = cssTxt[i].split(":");
		eval("source." + arr[0] + " = '" + arr[1] + "';");
	}*/
	//-------------------------
	if(elem.tagName != "DIV")
	{
		var tmpValue = Ext.isIE ? elem.innerText : elem.textContent;
		if(tmpValue != "")
			source.value = tmpValue;
	}
	

	var properties = new Array("align","colspan","rowspan","src");
	for(i=0; i<properties.length; i++)
	{
		tmpValue = elem.getAttribute(properties[i]);
		if(tmpValue != "")
			eval("source." + properties[i] + "='" + tmpValue + "';");
	}
	//-------------------------
	Ext.getCmp("fb_propertyGrid").setSource(source);
	
}
//..............................................................................
function ResetID()
{
	var elemTypes = new Array('div','span','img','table','td','tr');
	for(j=0; j<elemTypes.length; j++)
	{
		var elems = document.getElementsByTagName(elemTypes[j]);
		for(i=0; i<elems.length; i++)
		{
			arr = elems[i].id.split('_');
			if(arr.length == 3 && arr[0] == "fb" && Ext.isNumber(arr[2]*1))
				ElementIndex = (arr[2]*1 > ElementIndex*1) ? arr[2]*1 : ElementIndex*1;
		}
	}
	
	ElementIndex = ElementIndex*1;
	ElementIndex++;
}

function DrawKeyLabel(sourceID, nodeAtt, e)
{
	var newElem = document.createElement("span");
	newElem.id = "formItem_" + nodeAtt["id"];
	newElem.innerHTML = nodeAtt["text"];
	newElem.style.position = "absolute";
	newElem.style.cursor = "move";
	newElem.style.width = "100px";
	newElem.style.height = "20px";
	document.getElementById(sourceID).appendChild(newElem);

	initOperations(newElem);
}

function DrawLabel(sourceID,e)
{
	var newElem = document.createElement("span");
	newElem.id = "fb_label_" + ElementIndex++;
	newElem.innerHTML = "برچسب جدید";
	newElem.style.position = "absolute";
	newElem.style.cursor = "move";
	newElem.style.width = "100px";
	newElem.style.height = "20px";
	document.getElementById(sourceID).appendChild(newElem);

	initOperations(newElem);
}

function DrawImage(sourceID,e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divImage_" + ElementIndex++;
	newDiv.style.width = "100px";
	newDiv.style.height = "100px";
	document.getElementById(sourceID).appendChild(newDiv);
		
	var newElem = document.createElement("img");
	newElem.id = "fb_image_" + ElementIndex++;
	newElem.style.position = "absolute";
	newElem.style.cursor = "move";
	newElem.style.width = "100%";
	newElem.style.height = "100%";
	document.getElementById(newDiv.id).appendChild(newElem);

	initOperations(newElem);
	/*var cord = e.getXY();
	Ext.get(newElem.id).setLocation(cord[0], cord[1]);*/

	

	/*eval("AllStyles." + newElem.id + " = {};");
	eval("AllStyles." + newElem.id + "['width'] = '100%';");
	eval("AllStyles." + newElem.id + "['height'] = '100%';");*/

	
}

function DrawTable(source,e)
{
	var cord = HelperParamArray.pop();
	var sourceID = HelperParamArray.pop();
	
	var values = this.items.first().form.getValues();
	var cols = parseInt(values.cols,10);
	var rows = parseInt(values.rows,10);
	if (isNaN(cols) || isNaN(rows)) {
		Ext.Msg.alert("Error", "Columns/Rows are incorrect");
		return false;
	}
	this.close();
	//---------------------
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divTable_" + ElementIndex++;
	document.getElementById(sourceID).appendChild(newDiv);
	
	/*new Ext.dd.DragZone(Ext.get(newDiv.id), {
		ddGroup     : 'designerddgroup',
        getDragData: function(e) {
            var sourceEl = newDiv;
            if (sourceEl) {
                d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                return {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d
                }
            }
        },
        getRepairXY: function() {return this.dragData.repairXY;}
    });*/
	//---------------------
	var newTable = document.createElement("table");
	newTable.id = "fb_table_" + ElementIndex++;
	newTable.style.width = "100%";
	newTable.style.height = "100%";
	newTable.setAttribute("cellpadding", values.cellpadding);
	newTable.setAttribute("cellspacing", values.cellspacing);
	if(values.borders)
		newTable.setAttribute("border", values.borders);
	newDiv.appendChild(newTable);
	
	//eval("AllStyles." + newTable.id + " = {};");
	//eval("AllStyles." + newTable.id + "['width'] = '100%';");
	
	for (var j = 0; j < rows; j++)
	{
		var newRow = newTable.insertRow(j);
		newRow.id = "fb_tr_" + ElementIndex++;
				
		for (var i = 0; i < cols; i++)
		{
			var newCell = newRow.insertCell(i);
			newCell.id = "fb_td_" + ElementIndex++;
			newCell.appendChild(document.createTextNode("ردیف " + j + " ستون " + i));
			//eval("AllStyles." + newCell.id + " = {};");
			//eval("AllStyles." + newCell.id + "['value'] = 'ردیف " + j + " ستون " + i + "';");
		}
	}

	initOperations(newTable);

	return true;
}

function DrawLink(source,e)
{
	var cord = HelperParamArray.pop();
	var sourceID = HelperParamArray.pop();
	var values = this.items.first().form.getValues();
	this.close();

	var newDiv = document.createElement("div");
	newDiv.id = "fb_divLink_" + ElementIndex++;
	newDiv.style.position = "absolute";
	newDiv.style.cursor = "move";
	newDiv.style.width = "200px";
	newDiv.style.height = "30px";
	document.getElementById(sourceID).appendChild(newDiv);

	var newElem = document.createElement("a");
	newElem.id = "fb_link_" + ElementIndex++;
	newElem.href = values.address;
	newElem.innerHTML = values.title;
	newDiv.appendChild(newElem);

	initOperations(newElem);
}

function DrawDiv(sourceID,e)
{
	var newElem = document.createElement("div");
	newElem.id = "fb_div_" + ElementIndex++;
	newElem.style.width = "100%";
	newElem.style.height = "200px";
	document.getElementById(sourceID).appendChild(newElem);

	initOperations(newElem);

	/*new Ext.dd.DragZone(Ext.get(newElem.id), {
		ddGroup     : 'designerddgroup',
        getDragData: function(e) {
            var sourceEl = e.getTarget();
            if (sourceEl) {
                d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                return {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d
                }
            }
        },
        getRepairXY: function() {return this.dragData.repairXY;}
    });
	new Ext.dd.DropZone(Ext.get(newElem.id), {
		ddGroup     : 'designerddgroup',
        getTargetFromEvent: function(e) {return e.getTarget();},
        onNodeEnter : function(target){Ext.fly(target).addClass('label-over');},
        onNodeOut : function(target){Ext.fly(target).removeClass('label-over');},
        onNodeOver : function(){return Ext.dd.DropZone.prototype.dropAllowed;},
        onNodeDrop : function(target, dd, e, data){dropFn(null, e, data);return true;}
    });*/
	
}

function DrawTextbox(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divTextbox_" + ElementIndex++;
	newDiv.style.width = "200px";
	document.getElementById(sourceID).appendChild(newDiv);
	
	//-------------------------------
	var newText = document.createElement("input");
	newText.type = 'text';
	newText.id = 'formItem_' + nodeAtt["id"];
	newText.style.width = "100%";	
	newDiv.appendChild(newText);
	newText.value = "input : " + nodeAtt["text"];

	initOperations(newText);
}

function DrawCombo(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divCombo_" + ElementIndex++;
	newDiv.style.width = "200px";
	document.getElementById(sourceID).appendChild(newDiv);
	
	/*new Ext.dd.DragZone(Ext.get(newDiv.id), {
		ddGroup     : 'designerddgroup',
        getDragData: function(e) {
            var sourceEl = newDiv;
            if (sourceEl) {
                d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                return {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d
                }
            }
        },
        getRepairXY: function() {return this.dragData.repairXY;}
    });*/
	//-------------------------------
	var newCombo = document.createElement("select");
	newCombo.id = 'formItem_' + nodeAtt["id"];
	newCombo.style.width = "100%";
	newDiv.appendChild(newCombo);
	//eval("AllStyles." + newCombo.id + " = {};");
	//eval("AllStyles." + newCombo.id + "['width'] = '100%';");
	newCombo.options.add(new Option("Combo : " + nodeAtt["text"]));
	
	initOperations(newCombo);
}

function DrawCheckbox(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divCheckbox_" + ElementIndex++;
	newDiv.style.width = "200px";
	document.getElementById(sourceID).appendChild(newDiv);
		
	var newCheck = document.createElement("input");
	newCheck.type = "checkbox";
	newCheck.id = 'formItem_' + nodeAtt["id"];
	newDiv.appendChild(newCheck);
	newCheck.setAttribute("title", nodeAtt["text"]);
	//eval("AllStyles." + newCheck.id + " = {};");
	//eval("AllStyles." + newCheck.id + "['title'] = '" + nodeAtt["text"] + "';");
	
	initOperations(newCheck);

	/*new Ext.dd.DragZone(Ext.get(newCheck.id), {
		ddGroup     : 'designerddgroup',
        getDragData: function(e) {
            var sourceEl = newCheck;
            if (sourceEl) {
                d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                return {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d
                }
            }
        },
        getRepairXY: function() {return this.dragData.repairXY;}
    });*/
}

function DrawRadio(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divRadio_" + ElementIndex++;
	newDiv.style.width = "200px";
	document.getElementById(sourceID).appendChild(newDiv);
		
	var newRadio = document.createElement("input");
	newRadio.type = "radio";
	newRadio.id = 'formItem_' + nodeAtt["id"];
	newDiv.appendChild(newRadio);
	newRadio.setAttribute("title", nodeAtt["text"]);

	initOperations(newRadio);
	
	/*new Ext.dd.DragZone(Ext.get(newCheck.id), {
		ddGroup     : 'designerddgroup',
        getDragData: function(e) {
            var sourceEl = newCheck;
            if (sourceEl) {
                d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                return {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d
                }
            }
        },
        getRepairXY: function() {return this.dragData.repairXY;}
    });*/
}

function DrawImageFile(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divImage_" + ElementIndex++;
	newDiv.style.width = "200px";
	newDiv.style.height = "200px";
	document.getElementById(sourceID).appendChild(newDiv);

	var newImage = document.createElement("img");
	
	newImage.id = 'formItem_' + nodeAtt["id"];
	newDiv.appendChild(newImage);
	newImage.style.width = "100%";
	newImage.style.height = "100%";
	newImage.setAttribute("title", nodeAtt["text"]);

	initOperations(newImage);
}

function DrawTextaera(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divTextarea_" + ElementIndex++;
	newDiv.style.width = "200px";
	newDiv.style.height = "200px";
	document.getElementById(sourceID).appendChild(newDiv);
	
	/*new Ext.dd.DragZone(Ext.get(newDiv.id), {
		ddGroup     : 'designerddgroup',
        getDragData: function(e) {
            var sourceEl = newDiv;
            if (sourceEl) {
                d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                return {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d
                }
            }
        },
        getRepairXY: function() {return this.dragData.repairXY;}
    });*/
	//-------------------------------
	var newText = document.createElement("textarea");
	newText.id = 'formItem_' + nodeAtt["id"];
	newText.style.width = "100%";
	newText.style.height = "100%";
	newDiv.appendChild(newText);
	newText.value = "textarea : " + nodeAtt["text"];
	
	initOperations(newText);
}

function DrawButton(sourceID, nodeAtt, e)
{
	var newDiv = document.createElement("div");
	newDiv.id = "fb_divButton_" + ElementIndex++;
	newDiv.style.width = "200px";
	newDiv.style.height = "21px";
	document.getElementById(sourceID).appendChild(newDiv);
	//-------------------------------
	var newBtn = document.createElement("button");
	newBtn.id = 'formItem_' + nodeAtt["id"];
	newBtn.style.width = "100%";
	newBtn.style.height = "100%";
	newBtn.innerHTML = nodeAtt["text"];
	newDiv.appendChild(newBtn);
	initOperations(newBtn);
}

function changeStyle(elem)
{
	switch(elem.id)
	{
		case "autoSize":
			changeStyleValue("", "width", "","");
			changeStyleValue("", "height", "","");
			break;
		case "fontWeight":
			changeStyleValue("", "fontWeight", elem.checked ? "bold" : "","");
			break;
		case "fontStyle":
			changeStyleValue("", "fontStyle", elem.checked ? "italic" : "","");
			break;
		/*case "fontColor":
			elem.value = "#" + elem.value;
			changeStyleValue("", "color", elem.value,"");
			break;
		case "BackColor":
			elem.value = "#" + elem.value;
			changeStyleValue("", "backgroundColor", elem.value,"");
			break;*/
		default:
			changeStyleValue("", elem.id, elem.value,"");
	}
}

function FillSpecialProperties()
{
	var elem = document.getElementById(SelectedElementID);
	
	document.getElementById("width").value = elem.style.width;
	document.getElementById("height").value = elem.style.height;
	document.getElementById("direction").value = elem.style.direction;
	document.getElementById("textAlign").value = elem.style.textAlign;
	document.getElementById("fontFamily").value = elem.style.fontFamily;
	document.getElementById("fontSize").value = elem.style.fontSize;
	document.getElementById("fontWeight").checked = 
		(elem.style.fontWeight == "bold") ? true : false;
	document.getElementById("fontStyle").checked =
		(elem.style.fontWeight == "bold") ? true : false;
	//document.getElementById("fontColor").value = elem.style.color;
	//document.getElementById("BackColor").value = elem.style.backgroundColor;

	/*document.getElementById("tr_image").style.display = 
		(elem.tagName == "IMG") ? "" : "none";*/

}

function changeStyleValue(source, recordId, value, oldValue)
{
	//var id = this.getStore().getById(recordId).data.name;
	var id = recordId;
	value = value.toString().replace("\n", "<br>", "g");
	value = value.trim();
	//eval("AllStyles." + SelectedElementID + "['" + id + "'] = '" + value + "'");
	switch(id)
	{
		case "value":
			var el = document.getElementById(SelectedElementID);
			HelperParamArray = new Array();
			for(var i=0; i<el.childNodes.length; i++)
				if(el.childNodes[i].nodeName != "#text")
					HelperParamArray.push(el.childNodes[i]);
			el.innerHTML = value;
			for(var i=0; i<HelperParamArray.length; i++)
				el.appendChild(HelperParamArray.pop());
			new Ext.Resizable(SelectedElementID, {handles:"all"});
			break;
		case "align":
		case "colspan":
		case "rowspan":
			if(value == "")
				document.getElementById(SelectedElementID).removeAttribute(id);
			else
				document.getElementById(SelectedElementID).setAttribute(id, value);

			break;
		case "src":
			document.getElementById(SelectedElementID).setAttribute(id , ImagePath + value);
			break;
		default:
			if(value == "")
			{
				if(Ext.isIE)
					document.getElementById(SelectedElementID).style.removeAttribute(recordId);
				else
					eval("document.getElementById('" + SelectedElementID + "').style." + recordId + " = '';");
			}
			else
			{
				if(Ext.isIE)
					document.getElementById(SelectedElementID).style.setAttribute(recordId,value);
				else
					eval("document.getElementById('" + SelectedElementID + "').style." + recordId + " = '" + value + "';");
			}
			
			
	}
}

function SaveFn()
{
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();

	if(SelectedElementID != "" && Ext.get(SelectedElementID))
		Ext.get(SelectedElementID).removeClass("label-select");

	for(i=0; i<FormItems.length; i++)
	{
		var elem = document.getElementById("formItem_" + FormItems[i].id);
		if(!elem)
			continue;
		//elem.parentNode.replaceChild(document.createTextNode("#key" + FormItems[i].id + "#"),elem);
		elem.innerHTML = "#key" + FormItems[i].id + "#";
	}

	var returnElem = document.getElementById("returnDIV");
	var tempElems = returnElem.getElementsByTagName("div");
	var arr = new Array();
	for(i=0; i<tempElems.length; i++)
		if(tempElems[i].className.indexOf("x-resizable-handle") != -1)
		{
			tempElems[i].parentNode.removeChild(tempElems[i]);
			i--;
		}
	
	Ext.Ajax.request({
	
		url: "index.data.php",
		params : {
			task : "SaveCheck",
			content : returnElem.parentNode.innerHTML,
			chequeID : <?= $checkID?> 
		},
		method : "post",
		
		success : function(response)
		{
			alert("ذخیره فرم با موفقیت انجام شد");
			window.location = window.location;
		}
	});	
}

function PreviewFn()
{
	window.open("PrintCheck.php?ChequeBookID=<?= $checkID?>");
}

function reload(url)
{
	Ext.Ajax.request({
		method : "post",
		url : url,
		
		success : function(response)
		{
			var content = response.responseText;
			if(content == "")
				return;
			for(i=0; i<FormItems.length; i++)
			{
				if(content.indexOf("#key" + FormItems[i].id + "#") != -1)
				{
					Ext.getCmp("fb_treePanel").getRootNode().findChild("id",FormItems[i].id,true).remove();
					switch(FormItems[i].type)
					{
						case "label":
							content = content.replace("#key" + FormItems[i].id + "#", FormItems[i].text);

							break;
						case "img":
							content = content.replace("#key" + FormItems[i].id + "#",
								"<img id='formItem_" + FormItems[i].id +
									"' style='width:100%;height:100%' title='" + FormItems[i].text + "'>");
							break;
					}
				}
			}
			document.getElementById("returnDIV").parentNode.innerHTML = content;
			
			elems = document.getElementById("returnDIV").getElementsByTagName("span");
			for(i=0; i<elems.length; i++)
				if(elems[i].id.indexOf("fb_label_") != -1)
					initOperations(elems[i]);

			elems = document.getElementById("returnDIV").getElementsByTagName("img");
			for(i=0; i<elems.length; i++)
				if(elems[i].id.indexOf("fb_image_") != -1)
					initOperations(elems[i]);

			elems = document.getElementById("returnDIV").getElementsByTagName("table");
			for(i=0; i<elems.length; i++)
				if(elems[i].id.indexOf("fb_table_") != -1)
					initOperations(elems[i]);

			elems = document.getElementById("returnDIV").getElementsByTagName("div");
			for(i=0; i<elems.length; i++)
				if(elems[i].id.indexOf("fb_div_") != -1)
					initOperations(elems[i]);

			for(i=0; i<FormItems.length; i++)
			{
				elem = document.getElementById("formItem_" + FormItems[i].id);
				if(elem)
					initOperations(elem);
			}

			ResetID();
			
			document.head.innerHTML = 
				'<style>@media screen  {  div#fb_div_0 {background: url(\"backgrounds/<?= $checkID?>.jpg\") no-repeat no-repeat top right;}  }</style>' +
				document.head.innerHTML;
		}

	});
}

function initOperations(element)
{
	switch(element.nodeName)
	{
		case "SPAN":
			Ext.get(element.id).initDD();
			new Ext.Resizable(element.id,
				{handles:"all",heightIncrement: trickerSize, widthIncrement: trickerSize});
			Ext.get(element.id).addClassOnOver("label-over");
			Ext.get(element.id).on("click",selectElement);
			break;
		case "IMG":
			Ext.get(element.id).addClassOnOver("label-over");
			Ext.get(element.id).on("click",selectElement);

			Ext.get(element.parentNode.id).initDD();
			new Ext.Resizable(element.parentNode.id,
				{handles:"s w sw",heightIncrement: trickerSize, widthIncrement: trickerSize});
			break;
		case "TABLE":
			Ext.get(element.parentNode.id).initDD();
			new Ext.Resizable(element.parentNode.id,
				{handles:"all",heightIncrement: trickerSize, widthIncrement: trickerSize});
			elems = element.getElementsByTagName("TD");
			for(i=0; i<elems.length; i++)
			{
				Ext.get(elems[i].id).addClassOnOver("label-over");
				Ext.get(elems[i].id).on("click",selectElement);
			}
			break;
		case "DIV":
			Ext.get(element.id).initDD();
			new Ext.Resizable(element.id,
				{handles:"all",heightIncrement: trickerSize, widthIncrement: trickerSize});
			Ext.get(element.id).addClassOnOver("label-over");
			Ext.get(element.id).on("click",selectElement);
			break;
		case "INPUT":
			switch(element.type)
			{
				case "text" :
					Ext.get(element.parentNode.id).initDD();
					new Ext.Resizable(element.parentNode.id,
						{handles:"w",heightIncrement: trickerSize, widthIncrement: trickerSize});
					Ext.get(element.id).on("click", selectElement);
					break;
				case "checkbox":
					Ext.get(element.parentNode.id).initDD();
					new Ext.Resizable(element.parentNode.id,
						{handles:"all",heightIncrement: trickerSize, widthIncrement: trickerSize});
					Ext.get(element.id).on("click", selectElement);
					break;
				case "radio":
					Ext.get(element.parentNode.id).initDD();
					Ext.get(element.id).on("click", selectElement);
			}
			break;
		case "SELECT":
			Ext.get(element.parentNode.id).initDD();
			new Ext.Resizable(element.parentNode.id,
				{handles:"w",heightIncrement: trickerSize, widthIncrement: trickerSize});
			Ext.get(element.id).on("click", selectElement);
			break;
		case "TEXTAREA":
		case "BUTTON":
		case "A":
			Ext.get(element.parentNode.id).initDD();
			new Ext.Resizable(element.parentNode.id,
				{handles:"w s sw",heightIncrement: trickerSize, widthIncrement: trickerSize});
			Ext.get(element.id).on("click", selectElement);
			break;
	}

}

function saveImage()
{
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		method : "post",
		url : "index.data.php?task=SaveBackground",
		isUpload : true,
		form : document.getElementById("saveImageForm"),
		params : {
			ChequeBookID : <?= $checkID?>
		},
		success : function(response)
		{
			mask.hide();
			document.getElementById("imageAttach").value = "";
			document.head.innerHTML = document.head.innerHTML.replace(/\<style\>@media([a-z]|[0-9]|#|{|}|\?|=| |"|_|;|:|\(|\)|\/|\.|-)*\<\/style\>/g, "");
			var now = new Date();
			document.head.innerHTML = 
				'<style>@media screen  {  div#fb_div_0 {background: url(\"backgrounds/<?= $checkID?>.jpg?v=' + now.getTime()
				+ '\") no-repeat no-repeat top right;}  }</style>' +
				document.head.innerHTML;
			
		}
	});
	
}

</script>