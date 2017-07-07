<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//---------------------------

class FRG_forms extends OperationClass {

    const TableName = "FRG_forms";
    const TableKey = "FormID";
	
	const TplItemSeperator = "#";

	public $FormID;
	public $ParentID;
    public $FormTitle;
    public $FormContent;
    public $IsActive;

    public function Remove($pdo = null){
        		
		parent::runquery("delete from FRG_FormElems where FormID=?", array($this->FormID), $pdo);
        return parent::Remove($pdo);
    }    
    
	static function CorrectFormContentItems($content, $mode = "id") {
		
        $contentsArr = explode(self::TplItemSeperator, $content);
        $CorrectContent = '';
        if (substr($content, 0, 3) === self::TplItemSeperator) {
            $contentsArr = array_merge(array(''), $contentsArr);
        }

        for ($i = 0; $i < count($contentsArr); $i++) {
            $ArrCell = $contentsArr[$i];
            if ($i % 2 == 0) {
                $CorrectContent .= $ArrCell;
            } else {
                $arr = explode('--', $ArrCell);
				if($mode == "id")
					$CorrectContent .= self::TplItemSeperator . $arr[0] . self::TplItemSeperator;
				if($mode == "div")
					$CorrectContent .= "<div style='float:right' id=elem_" . $arr[0] . " ></div>";
            }
        }
        return $CorrectContent;
    }
}

class FRG_FormElems extends OperationClass{
	
	const TableName = "FRG_FormElems";
	const TableKey = "ElementID";
	
	public $FormID;
	public $ElementID;
	public $ParentID;
	public $ElementTitle;
	public $ElementType;
	public $alias;
	public $properties;
	public $EditorProperties;
	public $ElementValues;
	public $IsActive;
	
	function Remove($pdo = null) {
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);
	}
}

?>
