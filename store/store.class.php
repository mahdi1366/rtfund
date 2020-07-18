<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 98.03
//-----------------------------

class STO_goods extends OperationClass {

	const TableName = "STO_goods";
	const TableKey = "GoodID"; 
 
    public $GoodID;
    public $ParentID;
    public $GoodName;
    public $ScaleID;
    public $depreciateType;
    public $depreciateRatio;
    public $CostID;
    public $IsActive;
    public $IncreasePriceRatio;


    static function GetAll($where = "", $whereParam = array(), $SelActive = 1) {
        $query = "
			select g.*, g3.GoodName p1Desc, g2.GoodName p2Desc , bi.title as GoodScale
                from STO_goods g
				join STO_goods g2 on(g.ParentID=g2.GoodID)
				join STO_goods g3 on(g2.ParentID=g3.GoodID)
				left join BaseInfo  bi on(bi.typeID=94 AND bi.InfoID=g.ScaleID) 
								
			where 1=1 
		";

        if ($SelActive > 0)
            $query .= " AND g.IsActive='YES'  ";
        $query .= ($where != "") ? " AND " . $where : "";
        return parent::runquery_fetchMode($query, $whereParam);
    }
}

class STO_GoodProperties extends OperationClass {

	const TableName = "STO_GoodProperties";
	const TableKey = "PropertyID"; 
	
	public $PropertyID;
	public $GoodID;
	public $PropertyTitle;
	public $PropertyType;
	public $PropertyValues;
	public $IsActive;
	
	function Remove($pdo = null) {
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);		
	}
}


class STO_Assets extends OperationClass {

	const TableName = "STO_Assets";
	const TableKey = "AssetID"; 
	
    public $AssetID;
	public $BranchID;
    public $LabelNo;
    public $GoodID;
    public $RegDate;
	public $amount;
    public $details;
    public $BuyDate;
    public $StatusID;
    public $NetPeriod; //new added
    public $NetMethod; //new added

    function __construct($AssetID = "") {
	
		$this->DT_BuyDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
        return parent::__construct($AssetID);
	}
    
    static function Get($where = "", $whereParam = array(), $pdo = null) {
		
        $query = "select a.*, GoodName, bf.InfoDesc StatusDesc
			from STO_assets a
			join STO_goods g using(GoodID)
			join BaseInfo bf on(TypeID=95 AND bf.InfoID=a.StatusID)
			where 1=1 " . $where;
        
        return parent::runquery_fetchMode($query, $whereParam, $pdo);
    }
}

//new added
class STO_AssetEvent extends OperationClass{

    const TableName = "STO_AssetEvent";
    const TableKey = "eventID";

    public $eventID;
    public $AssetID;
    public $actionType;
    public $actionDesc;
    public $referDate;
    public $actionDate;
    public $actionPID;
    public $FollowUpDate;
    public $FollowUpPID;
    public $FollowUpDesc;
    public $RegPID;

    function __construct($id = ""){

        $this->DT_referDate = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_actionDate = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_FollowUpDate = DataMember::CreateDMA(DataMember::DT_DATE);

        parent::__construct($id);
    }

    static function Get($where = '', $whereParams = array(), $pdo = null) {

        return PdoDataAccess::runquery_fetchMode("
			select e.*, concat_ws(' ',p1.CompanyName,p1.fname,p1.lname) actionFullname, 
				concat_ws(' ',p2.CompanyName,p2.fname,p2.lname) FollowUpFullname,
				concat_ws(' ',p3.CompanyName,p3.fname,p3.lname) RegFullname
			from STO_AssetEvent e 
				left join BSC_persons p1 on(p1.PersonID = e.actionPID)
				left join BSC_persons p2 on(p2.PersonID = e.FollowUpPID)
				left join BSC_persons p3 on(p3.PersonID = e.RegPID)
			where 1=1 " . $where, $whereParams, $pdo);
    }
}
class STO_AssetNet extends OperationClass{

    const TableName = "STO_AssetNet";
    const TableKey = "netID";

    public $netID;
    public $AssetID;
    public $NetPeriod;
    public $NetMethod;
    public $RegPID;
    public $RegDate;

    function __construct($id = ""){

        $this->DT_RegDate = DataMember::CreateDMA(DataMember::DT_DATE);

        parent::__construct($id);
    }

    static function Get($where = '', $whereParams = array(), $pdo = null) {

        return PdoDataAccess::runquery_fetchMode("
			select n.*, concat_ws(' ',p1.CompanyName,p1.fname,p1.lname) RegFullname 
				
			from STO_AssetNet n 
				left join BSC_persons p1 on(p1.PersonID = n.RegPID)
				
			where 1=1 " . $where, $whereParams, $pdo);
    }
}


class STO_AssetProperties extends OperationClass {

	const TableName = "STO_AssetProperties";
	const TableKey = "AssetID"; 
	
    public $AssetID;
    public $PropertyID;
    public $PropertyValue;

}

class STO_AssetFlow extends OperationClass{
	
	const TableName = "STO_AssetFlow";
	const TableKey = "FlowID"; 
	
	public $FlowID;
	public $AssetID;
	public $ActDate;
	public $ActPersonID;
	public $StatusID;
	public $IsUsable;	
	public $amount;	
	public $DepreciationAmount;	
	public $ReceiverPersonID;	
	public $details;	
	public $IsActive;	
	public $IsLock;	
	
	static function Get($where = "", $whereParam = array(), $pdo = null) {
		
        $query = "select af.*, bf.InfoDesc StatusDesc,
				concat_ws(' ', p1.fname,p1.lname,p1.CompanyName) ActFullname,
				concat_ws(' ', p2.fname,p2.lname,p2.CompanyName) ReceiverFullName
			from STO_AssetFlow af
			join BaseInfo bf on(TypeID=95 AND bf.InfoID=af.StatusID)
			join BSC_persons p1 on(p1.PersonID=af.ActPersonID)
			left join BSC_persons p2 on(p2.PersonID=af.ReceiverPersonID)
			where 1=1 " . $where;
        
        return parent::runquery_fetchMode($query, $whereParam, $pdo);
    }
	
	function Remove($pdo = null) {
		$this->IsActive = "NO";
		return $this->Edit($pdo);
	}


	static function AddFlow($AssetID, $StatusID, $IsUsable = true){
		
		$obj = new STO_AssetFlow();
		$obj->AssetID = $AssetID;
		$obj->ActDate = PDONOW;
		$obj->ActPersonID = $_SESSION["USER"]["PersonID"];
		$obj->StatusID = $StatusID;
		$obj->IsUsable = $IsUsable;
		$obj->Add();
				
	}
}
?>