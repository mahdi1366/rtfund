<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.07
//---------------------------

?>
<html>
	<head>
	<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
	<meta http-equiv="Content-Language" content="fa">

	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>

	<script src="scripts/prototype.js" type="text/javascript"></script>
	<script src="scripts/scriptaculous.js?load=effects,builder,dragdrop" type="text/javascript"></script>
	<script src="scripts/cropper.js" type="text/javascript"></script>
	<a href="javascript:program.open('C:\\SHJ_Twain\\SHJ_Twain.exe')"><img src="Registration.jpg"></a>
	<script>
	//netscape.security.PrivilegeManager.enablePrivilege('UniversalFileRead');
	//netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');



	var oShell = new ActiveXObject("WScript.Shell");
    oShell.Run('C:\\SHJ_Twain\\SHJ_Twain.exe', 1);

	// create rendering file ...............................................
	var renderFile = Components.classes["@mozilla.org/file/directory_service;1"].
		getService(Components.interfaces.nsIProperties).get("TmpD", Components.interfaces.nsIFile);

	renderFile.append("SHJ_renderFile.tmp");

	if(!renderFile.exists("SHJ_renderFile.tmp"))
		renderFile.create(Components.interfaces.nsIFile.NORMAL_FILE_TYPE, 0666);

	var foStream = Components.classes["@mozilla.org/network/file-output-stream;1"].
					 createInstance(Components.interfaces.nsIFileOutputStream);
	foStream.init(renderFile, 0x02 | 0x08 | 0x20, 0666, 0);
	var converter = Components.classes["@mozilla.org/intl/converter-output-stream;1"].
							  createInstance(Components.interfaces.nsIConverterOutputStream);
	converter.init(foStream, "UTF-8", 0, 0);
	converter.writeString("executing");
	converter.close();

	// run the scan program ................................................
	var file = Components.classes["@mozilla.org/file/local;1"]
		.createInstance(Components.interfaces.nsILocalFile);
	file.initWithPath("C:\\SHJ_Twain\\SHJ_Twain.exe");
	file.launch();

	// wait for scan program to completed ..................................
	var IntervalID = setInterval('checkCompletion()', 500);

	function checkCompletion()
	{
		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
		
		var fstream = Components.classes["@mozilla.org/network/file-input-stream;1"].
					createInstance(Components.interfaces.nsIFileInputStream);
		var cstream = Components.classes["@mozilla.org/intl/converter-input-stream;1"].
								createInstance(Components.interfaces.nsIConverterInputStream);

		var data = {};

		fstream.init(renderFile, -1, 0, 0);
		cstream.init(fstream, "UTF-8", 0, 0);

		cstream.readString(-1, data); // read the whole file and put it in data.value
		cstream.close();

		if(data.value.substr(0,8) == "complete")
		{
			data.value = data.value.substr(8, data.value.length - 8);
			scanComplete(data.value);
		}
		else
			return false;
	}

	function scanComplete(image)
	{
		clearInterval(IntervalID);
		
		document.getElementById("attach").value = image;

		var src = window.location.href.split("src=")[1];
		var source = src.replace('#','&');
		document.getElementById("source").value = source;

		preSaveImages();
	}

	function submitting()
	{
		form.submit();
		window.close();
	}

	function preSaveImages()
	{
		Ext.Ajax.request({
			url : "scan.data.php?task=presave",
			method : "POST",
			form : document.getElementById("mainForm"),
			isUpload : true,

			success : function(res)
			{
				document.getElementById("rawImage").src = "../../ResearchDocuments/" + res.responseText;
				document.getElementById("imagePath").value = "../../ResearchDocuments/" + res.responseText;
				new Cropper.ImgWithPreview (
					"rawImage",
					{
						minWidth: 100,
						minHeight: 100,
						//ratioDim: { x: 200, y: 200 },
						displayOnInit: true,
						onEndCrop: saveCoords,
						onloadCoords: { x1: 0, y1: 0, x2: 200, y2: 200 },
						previewWrap: 'preview'
					}
				);
			}
		});

	}

	function saveCoords (coords, dimensions)
	{
		$( 'x1' ).value = coords.x1;
		$( 'y1' ).value = coords.y1;
		$( 'width' ).value = dimensions.width;
		$( 'height' ).value = dimensions.height;
	}

	function finalSave()
	{
		Ext.Ajax.request({
			url : "scan.data.php?task=finalSave",
			method : "POST",
			form : document.getElementById("finalSave"),

			success : function(res)
			{
				window.returnValue = res.responseText;
				window.close();
			}
		});
	}
	</script>
	</head>
	<body style="font-family:tahoma">
		<center>
			لطفا تا تکمیل کامل اسکن از بستن این پنجره خودداری کنید.
		</center>

		<form id="mainForm" method="POST" enctype='multipart/form-data'>
			<div style="display: none;">
				<input type="file" id="attach" name="attach">
			</div>
		</form>		

		<form id="finalSave" method="post">
			<center>
			<div style="width:950px" align="center">
				<div style="width:100%;height:500px;overflow: scroll;border:1px solid black">
					<img id="rawImage" name="rawImage">
				</div>
				<div id="preview"></div>
				<input type="button" value="تایید" style="font-family:tahoma;" onclick="finalSave();">
				<input type="hidden" name="x1" id="x1" value="">
				<input type="hidden" name="y1" id="y1" value="">
				<input type="hidden" name="width" id="width" value="">
				<input type="hidden" name="height" id="height" value="">
				<input type="hidden" id="source" name="source">
				<input type="hidden" id="imagePath" name="imagePath">
			</div>
			</center>
		</form>
		
	</body>
</html>