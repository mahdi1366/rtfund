<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script>

function scan(){
	
	var AttID = arr[1];
	//RunScanner and addd file 
	var hostUrl = "eVhuanhscSZ9YDlabCV4bDlpdFlsW3hscSY6MX5nf2tz";
	//var did = 'PURCHASE_' + AttID;
	var oasScanner = document.getElementById("OAS_Scanner");
	if(oasScanner == null) {
		oasScanner = document.createElement("OASScannerElement");
		oasScanner.id = 'OAS_Scanner';
		document.body.appendChild(oasScanner);            
	}

	oasScanner.setAttribute("fast", false);
	oasScanner.setAttribute("module", "Scan");
	oasScanner.setAttribute("action", "PURCHASE");
	oasScanner.setAttribute("fieldName", "filePart");        
	oasScanner.setAttribute("hostUrl", hostUrl);
	oasScanner.setAttribute("uploadHandler", "82489999");
	oasScanner.setAttribute("did", AttID);
	oasScanner.setAttribute("referID", "-1");
	oasScanner.setAttribute("otherOptions", "undefined");
	var evt = document.createEvent("Events");
	evt.initEvent("OASScannerEvent",true,false);
	oasScanner.dispatchEvent(evt);       
	document.getElementById("OAS_Scanner").remove();                  

	// Add File to the main table and place
	/*Ext.Ajax.request({
		url : FactorObject.address_prefix + "../data/factor.data.php",
		method : "POST",
		params : {
			task : "AddDirectScannedAttachmentItem",                                 
			AttID : AttID    
		},
		success : function(response){
			FactorObject.AttachmentsGrid.getStore().proxy.extraParams['ReceiptID'] = FactorObject.get('ReceiptId').value;
			FactorObject.AttachmentsGrid.getStore().load();
		},
		failure : function(){
			alert("عملیات مورد نظر با شکست مواجه شد"); 
		}                   
	});*/
}

</script>
	