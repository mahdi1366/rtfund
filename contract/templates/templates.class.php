<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

class CNT_templates extends OperationClass {

    const TableName = "CNT_templates";
    const TableKey = "TemplateID";

	public $TemplateID;
    public $TemplateTitle;
    public $TemplateContent;
    public $IsActive;

    function __construct($id = "") {
        parent::__construct($id);
    }

    public function Remove($pdo = null){
        $res = parent::runquery("select count(*) from CNT_contracts where TemplateID = ? limit 1",
				array($this->TemplateID),$pdo);
        if ($res[0]['count(*)']>0)
		{
			parent::runquery("update " . static::TableName . " set IsActive='NO' where TemplateID=?", array($this->TemplateID));
			return ExceptionHandler::GetExceptionCount() == 0;
        }
		
		parent::runquery("delete from CNT_TemplateItems where TemplateID=?", array($this->TemplateID), $pdo);
		
        return parent::Remove($pdo);
    }    
    
	static public function Get($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("select TemplateID, TemplateTitle from " . static::TableName .
			" where 1=1 " . $where, $param);
	}
	
    /* متنی که برای یک الگوی تمپلیت توسط کاربر نوشته شده است،
     *  نشانه گزاری هایش را تصحیح میکند 
     *  */
    static function CorrectTemplateContentItems($content) {
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

class CNT_TemplateItems extends OperationClass {
    
    const TableName = "CNT_TemplateItems";
    const TableKey = "TemplateItemID";

    public $TemplateItemID; 
	public $TemplateID;
    public $ItemName;
	public $FieldName;
    public $ItemType;
	public $ComboValues;
    
    public function __construct($id = ""){       
        parent::__construct($id) ;       
    }    
    
    public function Remove($pdo = null){
        $res = parent::runquery("select count(*) from CNT_ContractItems where TemplateItemID = ? limit 1",array($this->TemplateItemID),$pdo);
        if ($res[0]['count(*)']>0){
            throw new Exception(self::UsedTemplateItem);
            /*parent::PushException("UsedTemplateItem");            return false;*/
        }
        return parent::Remove($pdo);
    }    
}

?>
