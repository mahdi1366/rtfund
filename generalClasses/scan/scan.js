//---------------------------
// programmer:	Jafarkhani
// create Date:	90.07
//---------------------------
Scan.prototype = {}

function Scan(sourceURL, callback)
{
	var feature = "help:no;status:no;center:yes;dialogHeight:700;dialogWidth:1000" +
    	";locationbar:no;menubar:no;" ;
	sourceURL.replace("&", "#");
	
	val = showModalDialog("/generalClasses/scan/scanRendering.php?src=" + sourceURL, "اسکن تصویر", feature);
	
	if(callback != "")
		window[callback](val);
}
