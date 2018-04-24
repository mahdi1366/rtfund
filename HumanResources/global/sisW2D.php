<?
class sisW2DFormatConvertor {
	// ÂÑÇíå ÊÈÏíá ˜Ñ˜ÊÑ ÓíäÇ Èå ÏÇÓ (ÏÑ ÓÇÒäÏå Ñ ãí ÑÏÏ)
	var $sina2DosMap = array();
	
	// ÌÏæá äÇÔÊ ˜ÏåÇí ÍÑæÝ ÝÇÑÓí ÇÒ ÏÇÓ Èå ˜ÏíÌ ÓíäÇ	
	var $dos2SinaMap = array(0,1,2,3,4,5,6,7,8,9,
						   10,11,12,13,14,15,16,17,18,19,
						   20,21,22,23,24,25,26,27,28,29,
						   30,31,32,33,34,35,36,37,38,39,
						   40,41,42,43,44,45,46,47,48,49,
						   50,51,52,53,54,55,56,57,58,59,
						   60,61,62,63,64,65,66,67,68,69,
						   70,71,72,73,74,75,76,77,78,79,
						   80,81,82,83,84,85,86,87,88,89,
						   90,91,92,93,94,95,96,97,98,99,
						   100,101,102,103,104,105,106,107,108,109,
						   110,111,112,113,114,115,116,117,118,119,
						   120,121,122,123,124,125,126,127,48,49,
						   50,51,52,53,54,55,56,57,138,220,
						   140,193,199,195,194,194,200,200,201,201,
						   202,202,204,204,205,205,206,206,207,207,
						   208,208,209,210,211,212,213,214,214,216,
						   216,217,217,218,218,219,176,177,178,179,
						   180,181,182,183,184,185,186,187,188,189,
						   190,191,192,193,194,195,196,197,198,199,
						   200,201,202,203,204,205,206,207,208,209,
						   210,211,212,213,214,215,216,217,218,219,
						   220,221,222,223,221,222,222,222,222,223,
						   223,223,223,224,224,225,225,226,226,227,
						   227,228,228,228,229,229,230,230,231,232,
						   232,232,233,233,233,255);
						   
	// ˜ÇÑ˜ÊÑåÇíí ˜å ÈÚÏ ÇÒ ÂäåÇ äíÇÒ Èå ÝÇÕáå ÎÇáíÓÊ					   
	var $charNeedSpaceAfter = array(
								   146 => 146,  148 => 148,  150 => 150,  152 => 152,  154 => 154,  156 =>156,
								   158 => 158,  160 => 160,  167 => 167,  169 => 169,  171 => 171,  173 => 173,
								   225 => 225,  226 => 226,  229 => 229,  230 => 230,  233 => 233,  235 => 235,
								   237 => 237,  239 => 239,  241 => 241,  244 => 244,  246 => 246,  249 => 249,
								   252 => 252,  253 => 253);
								   
	// ÍÏÇ˜ËÑ ÊÚÏÇÏ ÏÑ ÌÏæá ÍÑæÝ ÝÇÑÓí
	var $maxCahr = 42;

	var $sinaCharStartStick = array('È', 'É', 'Ê', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ö', 'Ø', 'Ù',
	'Ú', 'Û', 'Ý', 'â', 'ã', 'à', 'á', 'ä', 'å', 'æ', 'è', 'é', 'Þ', 'ß', 'Ç', 'Ü');

//	'È', 'Ê', 'Ì', 'Í', 'Î', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ý', 'ã',
//									'á', 'ä', 'å', 'Þ', 'ß', 'Æ');
									
	var $sinaCharEndStick =   array('Á', 'Â', 'È', 'É', 'Ê', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ó', 'Ô', 'Õ', 'Ñ', 'Ò', 'Ö', 'Ø', 'Ù',
	'Ú', 'Û', 'Ý', 'â', 'ã', 'à', 'á', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'Þ', 'ß', 'Ç', 'Ä', 'Å');
	
//	'Â', 'È', 'É', 'Ê', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ó', 'Ô', 'Õ', 'Ñ', 'Ò', 'Ö',
//									'Ø', 'Ù', 'Ú', 'Û', 'Ý', 'ã', 'á', 'ä', 'å', 'æ', 'Þ', 'ß', 'Ç', 'Ä', 'Æ');
									
	//ÌÏæá ÍÑæÝ ÝÇÑÓí ÏÑ ˜ÏíÌ ÓíäÇ
	var $sinaCharMap = array(   'Á',//a
							    'Â',//alef
							    'È',//be							    
							    'É',//pe							    
							    'Ê',//te
							    'Ì',//se							    
							    'Í',//jim
							    'Î',//che							    
							    'Ï',//he							    
							    'Ð',//khe						    
							    'Ó',//re							    
							    'Ô',//ze							    
							    'Õ',//zhe							    
							    'Ñ',//dal							    
							    'Ò',//zal							    
							    'Ö',//sin							    
							    'Ø',//shin							    
							    'Ù',//sad							    
							    'Ú',//zad							    
							    'Û',//ta							    
							    'Ý',//za							    
							    'â',//kaf							    
							    'ã',//gaf							    
							    'à',//faf							    
							    'á',//ghaf							    
							    'ä',//lam							    
							    'å',//mim							    
							    'æ',//noon							    							    
							    'ç',//vav							    
							    'è',//he							    
							    'é',//ye							    
							    'Þ',//ein							    
							    'ß',//ghein							    
							    'Ç',//ye hamzeh							    
							    '¿',//?							    
							    'Ä',//alef hamzeh bala							    
							    'Å',//vav hamzeh							    
							    'Ã',//hamzeh
							    'Æ',//alef hamzeh paeen
							    'Ü',//keshesh
							    '¡',//kama
							    '×'//zarb
							    );

	//ÌÏæá ÍÑæÝ ÝÇÑÓí ÏÑ ÌÏæá ãÇí˜ÑæÓÇÝÊ
	var $microSoftCharMap = array(  'Â',//a
								    'Ç',//alef
								    'È',//be
								    '',//pe
								    'Ê',//te
								    'Ë',//se
								    'Ì',//jim
								    '',//che
								    'Í',//he
								    'Î',//khe
								    'Ñ',//re
								    'Ò',//ze
								    'Ž',//zhe
								    'Ï',//dal
								    'Ð',//zal
								    'Ó',//sin
								    'Ô',//shin
								    'Õ',//sad
								    'Ö',//zad
								    'Ø',//ta
								    'Ù',//za
								    'ß',//kaf
								    '',//gaf
								    'Ý',//faf
								    'Þ',//ghaf
								    'á',//lam
								    'ã',//mim
								    'ä',//noon
								    'æ',//vav
								    'å',//he
								    'í',//ye
								    'Ú',//ein
								    'Û',//ghein
								    'Æ',//ye hamzeh
								    '¿',//?
								    'Ã',//alef hamzeh bala
								    'Ä',//vav hamzeh
								    'Á',//hamzeh
								    'Å',//alef hamzeh paeen
								    'Ü',//keshesh
								    '¡',//kama
								    '×'//zarb
    								);
								
	function sisW2DFormatConvertor() {
		reset($this->dos2SinaMap);
		foreach ($this->dos2SinaMap as $key => $value) {
			if($key != $value || $key <= 58)
				$this->sina2DosMap[$value][] = $key;
		}
		$this->sina2DosMap[157][] = 32; //for shift + space
		
	}
//---------------------------------------------------------------------------------------------	
    function convertStringDos2Sina($dosString) {
	    $sinaString = '';

		
		for ($i = strlen($dosString); $i >= 1; $i--) {
    		$dosByte = ord($dosString[$i - 1]);
    		$sinaChar = chr($this->dos2SinaMap[$dosByte]);
    		$sinaString .= $sinaChar;
    		if ($dosByte==242)
      			$sinaString .= chr(194);
    		if (isset($this->charNeedSpaceAfter[$dosByte]))
      			$sinaString .= ' ';
  		}

  		$len = strlen($sinaString);
  		$i = 1;
  		while ($i <= $len) {
			if ((('0' <= $sinaString[$i - 1]) && ($sinaString[$i - 1] <= '9')) || ($sinaString[$i - 1] == '.')) {    
      			$begIdx = $i - 1;
      			$endIdx = $i - 1;
	      		while (($endIdx < $len) && ((('0' <= $sinaString[$endIdx]) && ($sinaString[$endIdx] <= '9')) || ($sinaString[$endIdx] == '.'))) 
	    			$endIdx++;
	  			$i = $endIdx + 1;
	  			while ($begIdx < $endIdx) {
	    			$tempChar = $sinaString[$endIdx - 1];
	    			$sinaString[$endIdx - 1] = $sinaString[$begIdx - 1];
	    			$sinaString[$begIdx - 1] = $tempChar;
	    		 	$begIdx++;
	    			$endIdx--;
	  			}
      		}
    		$i++;
		}
		return $sinaString;
	}
	
	function convertStringDos2Ms($dosString) {
		return $this->convertStringSina2MS($this->convertStringDos2Sina($dosString));
	}
	
	function convertFileDos2Ms($sourceFileName, $destFileName) {
		$sourceHandle = fopen($sourceFileName, 'r');
		$destHandle = fopen($destFileName, 'w');
  		while (!feof($sourceHandle)) {
		    $str = fgets($sourceHandle);
		    fwrite($destHandle, $this->convertStringDos2Ms($str));
  		}
  		fclose($sourceHandle);
  		fclose($destHandle);
  		
  		return true;
	}
	
	function convertFileBatchDos2Ms($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileDos2Ms($sourcePath . $file);
			}
			closedir($dirHandler);
    	}		
	}
	
//-----------------------------------------------------------------------------------------	
	function convertStringSina2Dos($sinaString) {
		$dosString = '';		
		$strLen = strlen($sinaString);
		
		for ($i = $strLen; $i >= 1; $i--) {
    		$sinaByte = ord($sinaString[$i - 1]);
    		$dosChar = $this->sina2DosMap[$sinaByte];					
    		$dosString .= chr($this->determineDosChar($sinaByte, 
    						($i < $strLen)?$sinaString[$i]:'',
    						($i - 2 >= 0)?$sinaString[$i - 2]:'',
    						$dosChar));
		}
  		$len = strlen($dosString);
  		$i = 0;
  		while ($i < $len) {  			
			if ((('0' <= $dosString[$i]) && ($dosString[$i] <= '9')) || ($dosString[$i] == '.') || 
				(ord($dosString[$i]) > 64 && ord($dosString[$i]) < 91) || 
	      		(ord($dosString[$i]) > 96 && ord($dosString[$i]) < 123)) {    
      			$begIdx = $i;
      			$endIdx = $i;
	      		while (($endIdx < $len) && ((('0' <= $dosString[$endIdx + 1]) && ($dosString[$endIdx + 1] <= '9')) || 
	      			($dosString[$endIdx + 1] == '.')) || (ord($dosString[$endIdx + 1]) > 64 && ord($dosString[$endIdx + 1]) < 91) || 
	      			(ord($dosString[$endIdx + 1]) > 96 && ord($dosString[$endIdx + 1]) < 123)) 
	    			$endIdx++;
	  			$i = $endIdx + 1;
	  			while ($begIdx < $endIdx) {
	    			$tempChar = $dosString[$endIdx];
	    			$dosString[$endIdx] = $dosString[$begIdx];
	    			$dosString[$begIdx] = $tempChar;
	    		 	$begIdx++;
	    			$endIdx--;
	  			}
      		}
    		$i++;
		}
		return $dosString;
  		
	}
	
	function determineDosChar($currentChar, $nextChar, $previousChar, $dosChars) {
		$chr1 = $dosChars[0];
		$chr2 = (count($dosChars) > 1)?$dosChars[1]:$chr1;
		$chr3 = (count($dosChars) > 2)?$dosChars[2]:$chr2;
		$chr4 = (count($dosChars) > 3)?$dosChars[3]:$chr3;
		$chr5 = (count($dosChars) > 4)?$dosChars[4]:$chr4;
		$char = '';
		
		if($currentChar == ord('Â')) { //Ç
			if(in_array($previousChar, $this->sinaCharStartStick))
				return $chr2;
			else
				return $chr1;
		}
		if($currentChar == ord('Þ')) { //Ú
			if(in_array($previousChar, $this->sinaCharStartStick) &&
				!in_array($nextChar, $this->sinaCharEndStick) )
				return $chr2;
		}
		
		if( ($currentChar > 64 && $currentChar < 91) || ($currentChar > 96 && $currentChar < 123))
			return $currentChar;
		 
		if (in_array($nextChar, $this->sinaCharEndStick) && 
			in_array($previousChar, $this->sinaCharStartStick) && 
			in_array(chr($currentChar), $this->sinaCharStartStick) && 
			in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr3;//$chr2;
		}
		else if (in_array($nextChar, $this->sinaCharEndStick) && 
			!in_array($previousChar, $this->sinaCharStartStick) && 
			!in_array(chr($currentChar), $this->sinaCharStartStick) && 
			in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr1;
		}
		else if (!in_array($nextChar, $this->sinaCharEndStick) && 
			!in_array($previousChar, $this->sinaCharStartStick) && 
			in_array(chr($currentChar), $this->sinaCharStartStick) && 
			in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr1;
		}
		else if (in_array($nextChar, $this->sinaCharEndStick) && 
				in_array($previousChar, $this->sinaCharStartStick) && 
				!in_array(chr($currentChar), $this->sinaCharStartStick) && 
				!in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr1;
		}
		else if (in_array($nextChar, $this->sinaCharEndStick) && 
				in_array($previousChar, $this->sinaCharStartStick) && 
				!in_array(chr($currentChar), $this->sinaCharStartStick) && 
				in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr3;//$chr2;
		}
		else if (in_array($nextChar, $this->sinaCharEndStick) && 
				in_array($previousChar, $this->sinaCharStartStick) && 
				in_array(chr($currentChar), $this->sinaCharStartStick) && 
				!in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr1;
		}
		else if (!in_array($nextChar, $this->sinaCharEndStick) && 
				in_array($previousChar, $this->sinaCharStartStick) && 
				in_array(chr($currentChar), $this->sinaCharStartStick) && 
				in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr1;//$chr3; ÝÞØ ÈÑÇ? Ú ÈÇ?Ï 2 ÈÇÔÏ
		}
		else if (in_array($nextChar, $this->sinaCharEndStick) && 
				!in_array($previousChar, $this->sinaCharStartStick) && 
				in_array(chr($currentChar), $this->sinaCharStartStick) && 
				in_array(chr($currentChar), $this->sinaCharEndStick)) {
			$char = $chr4;//$chr3;
		}
		else {
			$char = $chr1; //fix
		}
			
		return $char;
	}
	
	
	function convertStringMs2Dos($msString) {
		return $this->convertStringSina2Dos($this->convertStringMs2Sina($msString));		
	}

	function convertFileMs2Dos($sourceFileName, $destFileName) {
		$sourceHandle = fopen($sourceFileName, 'r');
		$destHandle = fopen($destFileName, 'w');
  		while (!feof($sourceHandle)) {
		    $str = fgets($sourceHandle);
		    fwrite($destHandle, $str);
  		}
  		fclose($sourceHandle);
  		fclose($destHandle);
  		
  		return true;
	}
	
	function convertFileBatchMs2Dos($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileDos2Ms($sourcePath . $file);
			}
			closedir($dirHandler);
    	}		
	}
//-----------------------------------------------------------------------------------------	

	function convertCharSina2Ms($aChar) {
		$result = $aChar;
	  	for ($i = 1; $i <= $this->maxCahr; $i++)
	    	if ($aChar == $this->sinaCharMap[$i-1]) {
	      		$result = $this->microSoftCharMap[$i-1];
	      		Break;
	    	}
	    return $result;
	}
	
	function convertStringSina2Ms($aString) {
		$len = strlen($aString);
		for($i = 1; $i <= $len; $i++)
			$aString[$i-1] = $this->convertCharSina2MS($aString[$i-1]);
		return $aString;
	}
	
	function convertFileSina2Ms($sourceFileName, $destFileName) {
		  $sourceHandle = fopen($sourceFileName, 'r');
		  $destHandle = fopen($destFileName, 'w');
		  while (!feof($sourceHandle)) {
		  	$str = fgets($sourceHandle);
		    fwrite($destHandle, $this->convertStringSina2Ms($str));
		  }
		  fclose($sourceHandle);
		  fclose($destHandle);
	}
	
	function convertFileBatchSina2Ms($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileSina2Ms($sourcePath . $file);
			}
			closedir($dirHandler);
    	}    	
	}	

//-----------------------------------------------------------------------------------------
	
	function convertCharMs2Sina($aChar) {
		$result = $aChar;
	  	for ($i = 1; $i <= $this->maxCahr; $i++)
	    	if ($aChar == $this->microSoftCharMap[$i-1]) {
	      		$result = $this->sinaCharMap[$i-1];
	      		Break;
	    	}
	    return $result;
	}	
	
	function convertStringMs2Sina($aString) {
		$len = strlen($aString);
		for($i = 1; $i <= $len; $i++)
			$aString[$i-1] = $this->convertCharMs2Sina($aString[$i-1]);
		return $aString;
	}	
	
	function convertFileMs2Sina($sourceFileName, $destFileName) {
		  $sourceHandle = fopen($sourceFileName, 'r');
		  $destHandle = fopen($destFileName, 'w');
		  while (!feof($sourceHandle)) {
		  	$str = fgets($sourceHandle);
		    fwrite($destHandle, $this->convertStringMs2Sina($str));
		  }
		  fclose($sourceHandle);
		  fclose($destHandle);
	}
	
	function convertFileBatchMs2Sina($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileMs2Sina($sourcePath . $file);
			}
			closedir($dirHandler);
    	}    	
	}

	function convertDigitDosEnToFa($digitStr) {
		for($i=0; $i<strlen($digitStr); $i++) {
			if($digitStr[$i] >= '0' && $digitStr[$i] <= '9')
				$digitStr[$i] = chr(ord($digitStr[$i])+80);
		}
		return $digitStr;
	}
	
	function correctDigitDir($dstr) {
		$stack_array = array();
		$digit = null;
		for($i=0; $i<strlen($dstr); $i++) {
			if($dstr[$i] >= '0' && $dstr[$i] <= '9') {
				$digit .= $dstr[$i];
			}
			else {
				array_push($stack_array,$digit);
				array_push($stack_array,$dstr[$i]);
				$digit = null;
			}			
		}
		if($digit !== null)
			array_push($stack_array,$digit);
	
		reset($stack_array);
		$dstr = null;	
		while($item = array_pop($stack_array)) {
			$dstr .= $item;
		}
		return $dstr;					
	}

}

?>