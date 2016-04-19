<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
abstract class CNTParentClass extends PdoDataAccess {

    const ERR_Add = 'خطا در ذخیره اطلاعات';
    const ERR_Edit = 'خطا در ویرایش اطلاعات';
    const ERR_Remove = 'خطا در حذف اطلاعات';
    const UsedTplItem = 'آیتم مورد نظر استفاده شده است و قابل حذف نمی باشد.';

    function __construct($id = '') {
        if ($id != '') {
            parent::FillObject($this, "select * from " . static::TableName . " where " . static::TableKey . " =:id", array(":id" => $id));
        }
    }

    public function Add($pdo = null) {

        if (!parent::insert(static::TableName, $this, $pdo))
		{
			ExceptionHandler::PushException(self::ERR_Add);
			return false;
		}
        //return false;            

        $this->{static::TableKey} = parent::InsertID($pdo);
        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->{static::TableKey};
        $daObj->TableName = static::TableName;
        $daObj->execute();

        return true;
    }

    public function Edit($pdo = null) {
        if (parent::update(static::TableName, $this, static::TableKey . 
			" =:id ", array(":id" => $this->{static::TableKey}), $pdo) === false) 
		{
			ExceptionHandler::PushException(self::ERR_Edit);
			return false;
		}

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $this->{static::TableKey};
        $daObj->TableName = static::TableName;
        $daObj->execute();

        return true;
    }

    public function Remove($pdo = null) {
		
        if (!parent::delete(static::TableName, 
				static::TableKey . "=:id", array(":id" => $this->{static::TableKey}), $pdo))
        {
			ExceptionHandler::PushException(self::ERR_Remove);
			return false;
		}
		
        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_delete;
        $daObj->MainObjectID = $this->{static::TableKey};
        $daObj->TableName = static::TableName;
        $daObj->execute();

        return true;
    }

    public static function Get($where = '', $whereParams = array()) {
        return parent::runquery_fetchMode("select * from " . static::TableName . " where 1=1 " . $where, $whereParams);
    }

}

?>
