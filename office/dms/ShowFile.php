<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------
require_once 'header.inc.php';
require_once inc_phpExcelReader;

if(empty($_REQUEST["DocumentID"]) || empty($_REQUEST["ObjectID"]))
	die();

$DocumentID = $_REQUEST["DocumentID"];
$ObjectID = $_REQUEST["ObjectID"];

$query = "select RowID,PageNo,FileType,FileContent 
	from DMS_DocFiles df join DMS_documents using(DocumentID)
	where df.DocumentID=? AND ObjectID=?" . (!empty($_REQUEST["RowID"]) ? " AND RowID=?" : "")."
	order by PageNo";
$params = array($DocumentID, $ObjectID);
if(!empty($_REQUEST["RowID"]))
	$params[] = $_REQUEST["RowID"];

$dt = PdoDataAccess::runquery($query, $params);

if(count($dt) == 0)
	die();

if(!empty($_REQUEST["RowID"]) || count($dt) == 1)
{
	$FileContent = $dt[0]["FileContent"] . file_get_contents(getenv("DOCUMENT_ROOT") . "/storage/documents/" . 
		$dt[0]["RowID"] . "." . $dt[0]["FileType"]);
	if($dt[0]["FileType"] == "pdf")
	{
		header('Content-disposition: filename=file.pdf');
		header('Content-type: file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header("Content-Transfer-Encoding: binary");
		if(isset($_REQUEST["inline"]))
			echo data_uri($FileContent, "pdf");
		else
			echo $FileContent;
		die();
	}
	else
	{
		header('Content-disposition: filename=file.' . $dt[0]["FileType"]);
		header('Content-type: file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header("Content-Transfer-Encoding: binary");

		echo $FileContent;
		die();
	}
}

function data_uri($content, $mime) {
    $base64 = base64_encode($content);
    return ('data:' . $mime . ';base64,' . $base64);
}

echo '<script src="/generalUI/pdfobject.js"></script>';
echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>';
echo "<center>";
foreach($dt as $file)
{
	$FileContent = $file["FileContent"] . 
		file_get_contents(getenv("DOCUMENT_ROOT") . "/storage/documents/" .
		$file["RowID"] . "." . $file["FileType"]);
	
	echo "<div style='width:100%;' align=center><hr>صفحه " . $file["PageNo"] . "<hr></div>";
	if($file["FileType"] == "pdf")
	{
		echo "<div id=pdf_DIV_" . $file["RowID"] . " style='height:500px'></div>";
		echo '<script>PDFObject.embed("'.data_uri($FileContent, "application/pdf") . '", "#pdf_DIV_'.$file["RowID"].'");</script>';
	} 
	else
		echo "<img src=" . data_uri($FileContent, 'image/jpeg') . " /></br>";
}
echo "</center>";

?>