<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once '../global/CNTconfig.class.php';
require_once 'NewContract.js.php';

if (isset($_REQUEST['CntID'])) {
    ?>
    <script type="text/javascript">
        NewContractObj.ResultPanel.getComponent('CntID').setValue(<?= $_REQUEST['CntID'] ?>);
        NewContractObj.LoadContractItems();
    </script>
    <?php
}
?>
<br>
<center>
    <div id="SelectTplComboDIV"></div>
    <form id="TplContentForm">
        <div id="TplContentDIV"></div>
    </form>
</center>