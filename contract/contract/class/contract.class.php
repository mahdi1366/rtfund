<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once 'CNTParentClass.class.php';
class CNT_contracts extends CNTParentClass {

    const TableName = "CNT_contracts";
    const TableKey = "CntId";

    public $CntId;
    public $TplId;
    public $RegPersonID;
    public $RegDate;
    public $description;
    public $PenaltyAmount;
    public $StatusCode;
    public $SupplierId;
    public $Supervisor;
    public $StartDate;
    public $EndDate;
    public $price;
    public $_TplTitle;
    
    public function __construct($id = ""){
        //parent::__construct($id) ;       
        if ($id != ''){
            parent::FillObject($this, "select c.* ,  t.TplTitle as _TplTitle
                    from CNT_contracts c
                    join CNT_templates t using(TplId) where c." . static::TableKey . " = :id", array(":id" => $id));
        }
    }

    public static function Get($where = '', $whereParams = array()) {
        return parent::runquery_fetchMode("select c.* ,  t.TplTitle 
                                  from CNT_contracts c 
                                  join CNT_templates t using(TplId) 
                                  where 1=1 " . $where, $whereParams);
    }
}

?>
