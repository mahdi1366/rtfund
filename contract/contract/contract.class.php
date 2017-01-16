<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/office/dms/dms.class.php';
require_once inc_CurrencyModule;

class CNT_contracts extends OperationClass {

    const TableName = "CNT_contracts";
    const TableKey = "ContractID";

    public $ContractID;
    public $TemplateID;
    public $RegPersonID;
    public $RegDate;
    public $description;
    public $PersonID;
	public $PersonID2;
    public $StartDate;
    public $EndDate;
	public $content;
	public $ContractType;
	public $LoanRequestID;
	public $ContractAmount;
	public $StatusID;

	public $_TemplateTitle;
	public $_PersonName;
    
    public function __construct($id = ""){
        
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
        if ($id != ''){
            parent::FillObject($this, "
					select c.* ,  t.TemplateTitle as _TemplateTitle,
						concat_ws(' ',fname,lname,CompanyName) _PersonName
                    from CNT_contracts c
                    left join CNT_templates t using(TemplateID) 
					left join BSC_persons using(PersonID)
					where c." . static::TableKey . " = :id", array(":id" => $id));
        }
    }

    public static function Get($content = false, $where = '', $whereParams = array(), $order = "") {
		
        return parent::runquery_fetchMode("
			select c.ContractID,
				c.TemplateID,
				c.RegPersonID,
				c.RegDate,
				c.description,
				c.PersonID,
				c.PersonID2,
				c.StartDate,
				c.EndDate,
				c.StatusID,
				sp.StepDesc," .
				($content ? "c.content," : "") .
				"c.ContractType,
				c.LoanRequestID,
				c.ContractAmount,
				t.TemplateTitle,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) PersonFullname
			
			from CNT_contracts c 
			join CNT_templates t using(TemplateID) 
			join WFM_FlowSteps sp on(sp.FlowID=" . CONTRACT_FLOWID . " AND sp.StepID=c.StatusID)
			left join BSC_persons p1 on(c.PersonID=p1.PersonID)
			
			where 1=1 " . $where . " group by ContractID " . $order, $whereParams);
    }

	public function GetContractContext(){

		if($this->StatusID != CNT_STEPID_RAW)
			return $this->content;
		
		$temp = parent::runquery("
			select c.ContractID,
				c.RegDate,
				c.description,
				c.StartDate,
				c.EndDate,
				c.ContractType,
				c.LoanRequestID,
				c.ContractAmount,

				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) PersonFullname,
				p1.NationalID,
				p1.address,
				p1.mobile,
				p1.WebSite,
				p1.email,
				bfp1.InfoDesc CompanyTypeDesc,
				
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) PersonFullname2,
				p2.NationalID NationalID2,
				p2.address address2,
				p2.mobile mobile2,
				p2.WebSite WebSite2,
				p2.email email2,
				bfp2.InfoDesc CompanyTypeDesc2,

				r.RequestID,
				r.ReqAmount,
				rp.InstallmentCount,
				rp.CustomerWage,
				rp.DelayMonths,
				concat(rp.PayInterval,' ',if(IntervalType='MONTH','ماه','روز')) PayInterval
				
			from CNT_contracts c 
			left join LON_requests r on(LoanRequestID=RequestID)
			left join LON_ReqParts rp on(rp.IsHistory='NO' AND rp.RequestID=r.RequestID)
			left join BSC_persons p1 on(c.PersonID=p1.PersonID)
			left join BaseInfo bfp1 on(bfp1.TypeID=14 AND p1.CompanyType=bfp1.InfoID)
			left join BSC_persons p2 on(c.PersonID2=p2.PersonID)
			left join BaseInfo bfp2 on(bfp2.TypeID=14 AND p2.CompanyType=bfp2.InfoID)
			
			where ContractID=? group by ContractID ", array($this->ContractID));
		
		print_r(ExceptionHandler::PopAllExceptions());
		$ContractRecord = $temp[0];
		//---------------------- installment Info --------------------------
		if($this->LoanRequestID > 0)
		{
			$dt = PdoDataAccess::runquery("select * from LON_installments where RequestID=?",
				array($this->LoanRequestID));
			if(count($dt) > 0)
			{
				$ContractRecord["InstallmentStartDate"] = $dt[0]["InstallmentDate"];
				$ContractRecord["InstallmentEndDate"] = $dt[count($dt)-1]["InstallmentDate"];
				$ContractRecord["InstallmentAmount"] = $dt[0]["InstallmentAmount"];
				$ContractRecord["LastInstallmentAmount"] = $dt[count($dt)-1]["InstallmentAmount"];
				unset($dt);
			}
		}
		//------------------------------------------------------------------
		$temp = CNT_TemplateItems::Get(" AND TemplateID in(0,?)", array($this->TemplateID));
		$TplItems = $temp->fetchAll();

		$TplItemsStore = array();
		foreach ($TplItems as $it) {
			$TplItemsStore[$it['TemplateItemID']] = $it;
		}
		$res = explode(CNTconfig::TplItemSeperator, $this->content);
		$CntItems = CNT_ContractItems::GetContractItems($this->ContractID);

		$ValuesStore = array();
		foreach ($CntItems as $it) {
			$ValuesStore[$it['TemplateItemID']] = $it['ItemValue'];
		}

		if (substr($this->content, 0, 3) == CNTconfig::TplItemSeperator) {
			$res = array_merge(array(''), $res);
		}

		$st = '';
		for ($i = 0; $i < count($res); $i++) {
			if ($i % 2 != 0) {
				$tempValue = "";
				$TempType = "";
				if(isset($ValuesStore[$res[$i]]))
				{
					$tempValue = $ValuesStore[$res[$i]];
					$TempType = $TplItemsStore[$res[$i]]["ItemType"];
				}
				else if(isset($TplItemsStore[ $res[$i] ]["FieldName"]))
				{
					$tempValue = $ContractRecord [ $TplItemsStore[ $res[$i] ]["FieldName"] ];
					$TempType = $TplItemsStore[ $res[$i] ]["ItemType"];
				}

				switch ($TempType) {
					case 'shdatefield':
						$st .= DateModules::miladi_to_shamsi($tempValue);
						break;
					case 'currencyfield':
						$st .= number_format($tempValue) . " ( به حروف " . 
							CurrencyModulesclass::CurrencyToString($tempValue) . " ) ";
						break;
					default : 
						$st .= nl2br($tempValue);
				}

			} else {
				$st .= $res[$i];
			}
		}
		
		return $st;
	}
	
	public function Remove()
	{
		if(!DMS_documents::DeleteAllDocument($this->ContractID, "contract"))
		{
			ExceptionHandler::PushException("خطا در حذف مدارک");
	 		return false;
		}
		
		return parent::Remove();
	}
}

class CNT_ContractItems extends OperationClass {

    const TableName = "CNT_ContractItems";
    const TableKey = "ContractItemID";

    public $ContractItemID;
    public $ContractID;
    public $TemplateItemID;
    public $ItemValue;

    public function __construct($id = "") {
        parent::__construct($id);
    }

    public static function RemoveAll($ContractID, $pdo = null) {
		
        return parent::delete(static::TableName, "ContractID=:ContractID", array(":ContractID" => $ContractID), $pdo);
    }

    public static function GetContractItems($ContractID) {
        
		$CntObj = new CNT_contracts($ContractID);
		
        $res_cnt = array(
			array("TemplateItemID" => 1, "ItemValue" => $CntObj->StartDate),
            array("TemplateItemID" => 2, "ItemValue" => $CntObj->EndDate),
            array("TemplateItemID" => 3, "ItemValue" => $CntObj->_PersonName)
           
        );
        $res = parent::runquery("select * from " . static::TableName . " where ContractID=:ContractID", array(":ContractID" => $ContractID));
        //  echo PdoDataAccess::GetLatestQueryString();
        return array_merge($res_cnt, $res);
    }

}

class CNT_ContractSigns extends OperationClass
{
	const TableName = "CNT_ContractSigns";
	const TableKey = "SignID";
	
	public $SignID; 
	public $ContractID;
	public $PersonID;
	public $SignerPost;
	public $SignerName;

	public static function Get($where = '', $whereParams = array()) {
		
		$query = "select s.*,concat(fname,' ',lname) fullname
		from CNT_ContractSigns s
			left join BSC_persons using(PersonID)
		where 1=1 " . $where;
		
		return PdoDataAccess::runquery($query, $whereParams);
	}
}
?>
