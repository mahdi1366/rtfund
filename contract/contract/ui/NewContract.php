<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../../header.inc.php';
require_once '../../global/CNTconfig.class.php';
require_once '../js/NewContract.js.php';

if (isset($_REQUEST['CntId'])) {
    ?>
    <script type="text/javascript">
        NewContractObj.ResultPanel.getComponent('CntId').setValue(<?= $_REQUEST['CntId'] ?>);
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