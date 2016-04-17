<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../global/CNTParentClass.class.php';

class CNT_templates extends CNTParentClass {

    const TableName = "CNT_templates";
    const TableKey = "TplId";

	public $TplId;
    public $TplTitle;
    public $TplContent;
    public $ExpireDate;
    public $StatusCode;

    function __construct($id = "") {
        parent::__construct($id);
    }

    public function Remove($pdo = null){
        $res = parent::runquery("select count(*) from CNT_contracts where TplId = ? limit 1",
				array($this->TplId),$pdo);
        if ($res[0]['count(*)']>0){
            throw new Exception(self::UsedTpl);
            //parent::PushException("used");            return false;
        }
        return parent::Remove($pdo);
    }    
    
    /* متنی که برای یک الگوی تمپلیت توسط کاربر نوشته شده است،
     *  نشانه گزاری هایش را تصحیح میکند 
     *  */
    static function CorrectTplContentItems($content) {
        $contentsArr = explode(CNTconfig::TplItemSeperator, $content);
        $CorrectContent = '';
        if (substr($content, 0, 3) === CNTconfig::TplItemSeperator) {
            $contentsArr = array_merge(array(''), $contentsArr);
        }

        for ($i = 0; $i < count($contentsArr); $i++) {
            $ArrCell = $contentsArr[$i];
            if ($i % 2 == 0) {
                $CorrectContent .= $ArrCell;
            } else {
                $arr = explode('--', $ArrCell);
                $CorrectContent .= CNTconfig::TplItemSeperator . $arr[0] . CNTconfig::TplItemSeperator;
            }
        }
        return $CorrectContent;
    }

}

?>
