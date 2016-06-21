<?php 
require_once '../../header.inc.php';
require_once inc_dataGrid;
?>

<form enctype="multipart/form-data" action="baseInfo/ui/test2.php" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
Choose a file to upload: <input name="uploadedfile" type="file" /><br />
<input type="submit" value="Upload File" />
</form>



<?php die();?>