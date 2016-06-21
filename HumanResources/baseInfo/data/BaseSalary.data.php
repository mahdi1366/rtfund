<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/BaseSalary.class.php';
//require_once '../class/evaluation_list_items.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {
		case "SelectRetList" :
              SelectRetList();

        case "Save" :
              Save();

        case "deleteEval" :
              deleteEval();

        case "SelectMemberEvalList" :
              SelectMemberEvalList();

        case "AddAllPrn" :
              AddAllPrn();

        case "SaveMember" :
              SaveMember();

        case "deleteMember" :
              deleteMember();
	    
	case "DelAllPrn" :
	      DelAllPrn() ; 

                    }

function SelectRetList()
{
	
	$where = " (1=1) " ;
	$whereParam = array();
	
	$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "";
	
	if (isset($_GET ["query"]) && $_GET ["query"] != "") {
		switch ($field) {
			case "pfname" :
				$where .= " AND p.pfname LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";

				break;
			case "plname" :
				$where .= " AND p.plname LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";

				break;

			case "ledger_number" :
				$where .= " AND rs.ledger_number = :qry ";
				$whereParam[":qry"] = $_GET["query"] ; 

				break;
			
		}
	}
	
       $qry = " SELECT salary_94 , rs.ledger_number,p.pfname,p.plname

				FROM hrmr.retirees_salary_inc_1394 rs
								inner join staff s on rs.ledger_number = s.ledger_number
								inner join persons p on s.PersonID = p.PersonID
				where $where 
				UNION ALL

				SELECT salary_94 , rs.ledger_number,p.pfname,p.plname
					FROM hrmr.mo_salary_inc_1394 rs
								inner join staff s on rs.ledger_number = s.ledger_number
								inner join persons p on s.PersonID = p.PersonID
				where $where 
				" ; 
	   
	   $temp = PdoDataAccess::runquery($qry,$whereParam); 
	
       // $temp = manage_evaluation_lists::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function Save(){

	$obj = new Retired_Base_Salary(); 
	
	if(isset($_REQUEST['ledger_number']))
		$obj->ledger_number = $_REQUEST['ledger_number'];
	else 		
	 PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	
	$qry = " select ledger_number
				from hrmr.retirees_salary_inc_1394
							where ledger_number =".$obj->ledger_number."

			union all

			select ledger_number
				from hrmr.mo_salary_inc_1394
							where ledger_number = ".$obj->ledger_number ; 
	
	$res2 = PdoDataAccess::runquery($qry);
	
	if( count($res2) > 0 && $res2[0]['ledger_number'] > 0 )
	{
		 /*echo Response::createResponse(false ,'این شماره دفتر کل  قبلا ثبت شده است.');
         die();*/
	}  
	
	
		$query = " select staff_id
						from staff 
							where ledger_number = ".$obj->ledger_number ." and ( die_date is null or die_date = '0000-00-00' ) " ; 
		$res = PdoDataAccess::runquery($query) ; 

		if(count($res) && $res[0]['staff_id'] > 0 )
		{
			if( count($res2) == 0  )
				$return = PdoDataAccess::runquery(" insert into hrmr.retirees_salary_inc_1394 (salary_94 , ledger_number ) values (".$obj->salary_94.",".$obj->ledger_number .") "); 						
			elseif( count($res2) > 0 && $res2[0]['ledger_number'] > 0 ) 
			{
				$return = PdoDataAccess::runquery(" update hrmr.retirees_salary_inc_1394 set salary_94 = ".$obj->salary_94."  where  ledger_number = ".$obj->ledger_number ); 
			}
		}
		else {
			$query = " select staff_id
						from staff 
							where ledger_number = ".$obj->ledger_number ." and ( die_date is not null and die_date != '0000-00-00' ) " ; 
			$res = PdoDataAccess::runquery($query) ; 
			if($res[0]['staff_id'] > 0 )
			{
				
				if( count($res2) == 0 )
					$return = PdoDataAccess::runquery(" insert into hrmr.mo_salary_inc_1394 (salary_94 , ledger_number ) values (".$obj->salary_94.",".$obj->ledger_number .") "); 				
				elseif( count($res2) > 0 && $res2[0]['ledger_number'] > 0 ) 
				{
					$return = PdoDataAccess::runquery(" update hrmr.mo_salary_inc_1394 set salary_94 = ".$obj->salary_94."  where  ledger_number = ".$obj->ledger_number ); 
				}
			}

		}
	
	
	//........................................
	
        if($return)
            echo Response::createResponse(true,'ذخیره سازی با موفقیت صورت گرفت.');
        else
            echo Response::createResponse(false ,'');
        die();
}

function AddAllPrn(){
 
        $PersonList = manage_evaluation_list_items::SelectListOfPrn( $_REQUEST['ouid'] , $_REQUEST['person_type']);
        $return = "";
        $msg = "";
        $obj = new manage_evaluation_list_items();

        for($i=0 ; $i< count($PersonList); $i++)
        {  
                        
            $obj->list_id = $_REQUEST['list_id'] ;
            $obj->staff_id = $PersonList[$i]['staff_id'] ;

            $return = $obj->Add();

             if(!$return)
                break ; 
                 
        }

            if(count($PersonList) == 0 )
                $msg = 'این واحد شامل این گروه افراد نمی باشد.';

            if(!$return)
            {
                echo Response::createObjectiveResponse($return, $msg);
                die();
            }
            echo Response::createObjectiveResponse(true, "");
            die();

}

function SaveMember(){

     $obj = new manage_evaluation_list_items();
     PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

     $obj->list_id = $_POST['list_id'];

     
        if($obj->ListItemID == ""){
            $return = $obj->Add();
        }
        else
            $return = $obj->Edit();

        if($return)
            echo Response::createResponse(true,$obj->list_id);
        else
            echo Response::createResponse(false ,'');
        die();
}

function deleteEval()
{   
	$obj = new manage_evaluation_lists();
    $obj->list_id = $_POST["list_id"];

    $return =  $obj->Remove();

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}
function deleteMember()
{
	$obj = new manage_evaluation_list_items();
    
    $obj->list_id = $_POST["list_id"];
    $obj->ListItemID = $_POST["ListItemID"];

    $return =  $obj->Remove();

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}
function DelAllPrn()
{
    $obj = new manage_evaluation_list_items();
    $obj->list_id = $_POST["list_id"];

    $return =  $obj->Remove("true");

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}
function SelectMemberEvalList()
{
        $where = " where list_id = ".$_GET['list_id'] ;
        $where .= dataReader::makeOrder();
        $temp = manage_evaluation_lists::GetAllMembers($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();
}
?>