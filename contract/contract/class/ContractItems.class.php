<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once 'CNTParentClass.class.php';
require_once 'contract.class.php';

class CNT_ContractItems extends CNTParentClass {

    const TableName = "CNT_ContractItems";
    const TableKey = "CntItemId";

    /**
     * شماره یکتای ردیف جدول 
     * @var int 
     */
    public $CntItemId;

    /**
     * شماره آی دی قرارداد از جدول CNT_contracts
     * @var int 
     */
    public $CntId;

    /**
     * شماره ردیف آیتم الگوی قرارداد
     * @var type 
     */
    public $TplItemId;

    /**
     * مقدار آیتم الگوی قرارداد در این قرارداد
     * @var type 
     */
    public $ItemValue;

    public function __construct($id = "") {
        parent::__construct($id);
    }

    public static function RemoveAll($CntId, $pdo = null) {
        $res = parent::runquery("delete from " . static::TableName . " where CntId=:CntId", array(":CntId" => $CntId));
        if ($res === false)
            throw new Exception (self::ERR_Remove);
        return $res;
    }

    public static function GetContractItems($CntId) {
        $CntObj = new CNT_contracts($CntId);
        $res_cnt = array(array("TplItemId" => 1, "ItemValue" => $CntObj->SupplierId),
            array("TplItemId" => 2, "ItemValue" => $CntObj->Supervisor),
            array("TplItemId" => 3, "ItemValue" => $CntObj->StartDate),
            array("TplItemId" => 4, "ItemValue" => $CntObj->EndDate),
            array("TplItemId" => 5, "ItemValue" => $CntObj->price)
        );
        $res = parent::runquery("select * from " . static::TableName . " where CntId=:CntId", array(":CntId" => $CntId));
        //  echo PdoDataAccess::GetLatestQueryString();
        return array_merge($res_cnt, $res);
    }

}

?>
