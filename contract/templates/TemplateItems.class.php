<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../global/CNTParentClass.class.php';

class CNT_TemplateItems extends CNTParentClass {
    
    const TableName = "CNT_TemplateItems";
    const TableKey = "TplItemId";

     /**
     * شماره یکتای ردیف جدول 
     * @var int 
     */
    public $TplItemId;  
    public $TplItemName;
    public $TplItemType;
    
    public function __construct($id = ""){       
        parent::__construct($id) ;       
    }    
    
    public function Remove($pdo = null){
        $res = parent::runquery("select count(*) from CNT_ContractItems where TplItemId = ? limit 1",array($this->TplItemId),$pdo);
        if ($res[0]['count(*)']>0){
            throw new Exception(self::UsedTplItem);
            /*parent::PushException("UsedTplItem");            return false;*/
        }
        return parent::Remove($pdo);
    }    
}
?>
