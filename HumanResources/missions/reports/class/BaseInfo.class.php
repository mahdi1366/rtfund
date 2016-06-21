<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.03
//-----------------------------

require_once "../../header.inc.php";

class BaseInfo extends PDODataAccess {
    
    public function SelectBaseTypes($where, $whereParams=array()){
        $query = "select InfoID , title from hrmstotal.Basic_Info where $where";
        return parent::runquery($query , $whereParams);        
    }
}

?>
