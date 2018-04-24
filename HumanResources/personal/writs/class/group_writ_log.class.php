<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
class manage_writ_group_issue_log 
{	
	private $s_count = 0 ;
	private $e_count = 0 ;
	private $u_count = 0 ;
	//..................
	private $s_content = "";
	private $e_content = "";
	private $u_content = "";
	   
	function manage_writ_group_issue_log()
	{
		
		$this->s_content = fopen("/tmp/s_content.txt", "w");
		$this->e_content = fopen("/tmp/e_content.txt", "w");
		$this->u_content = fopen("/tmp/u_content.txt", "w");
		
		fwrite($this->s_content , "<center><table style='display:none; width:90%;border-collapse: collapse;' id='issue_success'>");
		fwrite($this->e_content , "<center><table style='display:none; width:90%;border-collapse: collapse;' id='issue_esuccess'>");
		fwrite($this->u_content , "<center><table style='display:none; width:90%;border-collapse: collapse;' id='issue_usuccess'>");
	}
	
	function finalize()
	{
		fwrite($this->s_content, '</table></center>');
		fclose($this->s_content);
		fwrite($this->e_content, '</table></center>');
		fclose($this->e_content);
		fwrite($this->u_content, '</table></center>');
		fclose($this->u_content);
	}
	
	function make_success_row($person_rec)
	{
		if(ExceptionHandler::GetExceptionCount() == 0)
		{
			$this->s_count++;
			fwrite($this->s_content, "
			<tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
	            <td height=21px>شماره شناسايي :$person_rec[staff_id]</td>
	            <td>نام : $person_rec[pfname]</td>
	            <td>نام خانوادگي : $person_rec[plname]</td>
	            <td>شماره حکم : $person_rec[writ_id]</td>
	            </tr>");
		}
		else 
			self::make_esuccess_rows($person_rec);
		
	}
	
	function make_unsuccess_rows($person_rec)
	{
		$this->u_count++;
		fwrite($this->u_content, "
			<tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
	            <td height=21px>شماره خطا </td>
	            <td>شماره شناسايي :$person_rec[staff_id]</td>
	            <td>نام : $person_rec[pfname]</td>
	            <td>نام خانوادگي : $person_rec[plname]</td>
	            </tr>");
		$i = 0;
		while(ExceptionHandler::GetExceptionCount() != 0)
		{
			$i++;
			fwrite($this->u_content,
                  "<tr>
					<td height=21px style='font-family:tahoma;font-size:11px;color:red'>$i</td>
					<td colspan='3' style='font-family:tahoma;font-size:12px;color:red'>" . 
					ExceptionHandler::popExceptionDescription() . "</td>
                  </tr>");
			
		}
	}

	function make_esuccess_rows($person_rec)
	{
		$this->e_count++;
		fwrite($this->e_content, "
			<tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
	            <td height=21px>شماره خطا </td>
	            <td>شماره شناسايي :$person_rec[staff_id]</td>
	            <td>نام : $person_rec[pfname]</td>
	            <td>نام خانوادگي : $person_rec[plname]</td>
	            </tr>");
		$i = 0;
		while(ExceptionHandler::GetExceptionCount() != 0)
		{
			$i++;
			fwrite($this->e_content,
                  "<tr>
					<td height=21px style='font-family:tahoma;font-size:11px;color:red'>$i</td>
					<td colspan='3' style='font-family:tahoma;font-size:12px;color:red'>" . 
						ExceptionHandler::popExceptionDescription() . "</td>
                  </tr>");
			
		}
	}
		
	function make_result()
	{
		echo  "تعداد افرادي که با موفقيت براي آنها حکم صادر شده است : ";
		
		echo ($this->s_count == 0) ? " 0 <br>" :  
        	'<a href="javascript:void(0);" onclick="document.getElementById(\'issue_success\').style.display=\'\'">' .
				$this->s_count.' مشاهده... '.'</a></b><br><br>' . file_get_contents("/tmp/s_content.txt") . '<br><br>';
        //...........................................
		echo "<br>";
        echo ' تعداد افرادي که براي آنها حکم با خطا صادر شده است : ';
        
        echo ($this->e_count == 0) ? " 0 <br>" :  
        	'<a href="javascript:void(0);" onclick="document.getElementById(\'issue_esuccess\').style.display=\'\'">' .
				$this->e_count.' مشاهده... '.'</a></b><br><br>' . file_get_contents("/tmp/e_content.txt") . '<br><br>';
		//...........................................		
        echo "<br>";
        echo ' تعداد افرادي که صدور حکم براي آنها با شکست مواجه شده است : ';
        
        echo ($this->u_count == 0) ? '0 <br>' :  
        	'<a href="javascript:void(0);" onclick="document.getElementById(\'issue_usuccess\').style.display=\'\'">' .
				$this->u_count.' مشاهده... '.'</a></b><br><br>' . file_get_contents("/tmp/u_content.txt") . '<br><br>';   
	}
}

class manage_writ_group_cancel_log 
{	
	private $s_count = 0 ;
	private $u_count = 0 ;
	//..................
	private $s_content = "";
	private $u_content = "";
	
	function manage_writ_group_cancel_log()
	{
		$this->s_content = fopen("/tmp/s_content.txt", "w");
		$this->u_content = fopen("/tmp/u_content.txt", "w");
		
		fwrite($this->s_content, "<center><table style='display:none; width:90%;border-collapse: collapse;' id='cancel_success'>");
		fwrite($this->u_content, "<center><table style='display:none; width:90%;border-collapse: collapse;' id='cancel_usuccess'>");
	}
	
	function finalize()
	{
		fwrite($this->s_content, '</table></center>');
		fclose($this->s_content);
		fwrite($this->u_content , '</table></center>');
		fclose($this->u_content);
	}
	
	function make_success_row($person_rec)
	{
		if(ExceptionHandler::GetExceptionCount() == 0)
		{
			$this->s_count++;
			fwrite($this->s_content, "
			<tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
	            <td height=21px>شماره شناسايي :$person_rec[staff_id]</td>
	            <td>نام : $person_rec[pfname]</td>
	            <td>نام خانوادگي : $person_rec[plname]</td>
	            <td>شماره حکم : $person_rec[writ_id]</td>
	            </tr>");
		}
		else 
			self::make_unsuccess_rows($person_rec);
		
	}
	
	function make_unsuccess_rows($person_rec)
	{
		$this->u_count++;
		fwrite($this->u_content, "
			<tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
	            <td height=21px>شماره خطا </td>
	            <td>شماره شناسايي :$person_rec[staff_id]</td>
	            <td>نام : $person_rec[pfname]</td>
	            <td>نام خانوادگي : $person_rec[plname]</td>
	            <td>شماره حکم : $person_rec[writ_id]</td>
	            </tr>");
		$i = 0;
		while(ExceptionHandler::GetExceptionCount() != 0)
		{
			$i++;
			fwrite($this->u_content ,
                  "<tr>
					<td height=21px style='font-family:tahoma;font-size:11px;color:red'>$i</td>
					<td colspan='4' style='font-family:tahoma;font-size:12px;color:red'>" . 
					ExceptionHandler::popExceptionDescription() . "</td>
                  </tr>");
		}
	}

	function make_result()
	{
		echo "احکامی که با موفقيت ابطال شده اند : ";
		
		echo ($this->s_count == 0) ? " 0 <br>" :  
        	'<a href="javascript:void(0);" onclick="document.getElementById(\'cancel_success\').style.display=\'\'">' .
				$this->s_count.' مشاهده... '.'</a></b><br><br>' .  file_get_contents("/tmp/s_content.txt"). '<br><br>';
		//...........................................		
        echo "<br>";
       echo ' احکامی که ابطال آنها با شکست مواجه شده است : ';
        
        echo($this->u_count == 0) ? '0 <br>' :  
        	'<a href="javascript:void(0);" onclick="document.getElementById(\'cancel_usuccess\').style.display=\'\'">' .
				$this->u_count.' مشاهده... '.'</a></b><br><br>' .  file_get_contents("/tmp/u_content.txt"). '<br><br>';
	}
}
?>