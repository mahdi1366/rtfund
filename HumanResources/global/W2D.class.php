<?
class manage_W2DFormatConvertor {
	
	 	// آرايه تبديل کرکتر سينا به داس (در سازنده پر مي گردد)
	 public $sina2DosMap = array();
	
	// جدول نگاشت کدهاي حروف فارسي از داس به کدپيج سينا	
	 public $dos2SinaMap = array(0,1,2,3,4,5,6,7,8,9,
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
						   
	// کارکترهايي که بعد از آنها نياز به فاصله خاليست					   
	 public $charNeedSpaceAfter = array(
								   146 => 146,  148 => 148,  150 => 150,  152 => 152,  154 => 154,  156 =>156,
								   158 => 158,  160 => 160,  167 => 167,  169 => 169,  171 => 171,  173 => 173,
								   225 => 225,  226 => 226,  229 => 229,  230 => 230,  233 => 233,  235 => 235,
								   237 => 237,  239 => 239,  241 => 241,  244 => 244,  246 => 246,  249 => 249,
								   252 => 252,  253 => 253);
								   
	// حداکثر تعداد در جدول حروف فارسي
	 public $maxCahr = 42;

	 public $sinaCharStartStick = array('ب', 'ة', 'ت', 'ج', 'ح', 'خ', 'د', 'ذ', 'ض', 'ط', 'ظ',
	'ع', 'غ', 'ف', 'â', 'م', 'à', 'ل', 'ن', 'ه', 'و', 'è', 'é', 'ق', 'ك', 'ا', 'ـ');

//	'ب', 'ت', 'ج', 'ح', 'خ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'م',
//									'ل', 'ن', 'ه', 'ق', 'ك', 'ئ');
									
	 public $sinaCharEndStick =   array('ء', 'آ', 'ب', 'ة', 'ت', 'ج', 'ح', 'خ', 'د', 'ذ', 'س', 'ش', 'ص', 'ر', 'ز', 'ض', 'ط', 'ظ',
	'ع', 'غ', 'ف', 'â', 'م', 'à', 'ل', 'ن', 'ه', 'و', 'ç', 'è', 'é', 'ق', 'ك', 'ا', 'ؤ', 'إ');
	
//	'آ', 'ب', 'ة', 'ت', 'ج', 'ح', 'خ', 'د', 'ذ', 'س', 'ش', 'ص', 'ر', 'ز', 'ض',
//									'ط', 'ظ', 'ع', 'غ', 'ف', 'م', 'ل', 'ن', 'ه', 'و', 'ق', 'ك', 'ا', 'ؤ', 'ئ');
									
	//جدول حروف فارسي در کدپيج سينا
	 public $sinaCharMap = array(   'ء',//a
							    'آ',//alef
							    'ب',//be							    
							    'ة',//pe							    
							    'ت',//te
							    'ج',//se							    
							    'ح',//jim
							    'خ',//che							    
							    'د',//he							    
							    'ذ',//khe						    
							    'س',//re							    
							    'ش',//ze							    
							    'ص',//zhe							    
							    'ر',//dal							    
							    'ز',//zal							    
							    'ض',//sin							    
							    'ط',//shin							    
							    'ظ',//sad							    
							    'ع',//zad							    
							    'غ',//ta							    
							    'ف',//za							    
							    'â',//kaf							    
							    'م',//gaf							    
							    'à',//faf							    
							    'ل',//ghaf							    
							    'ن',//lam							    
							    'ه',//mim							    
							    'و',//noon							    							    
							    'ç',//vav							    
							    'è',//he							    
							    'é',//ye							    
							    'ق',//ein							    
							    'ك',//ghein							    
							    'ا',//ye hamzeh							    
							    '؟',//?							    
							    'ؤ',//alef hamzeh bala							    
							    'إ',//vav hamzeh							    
							    'أ',//hamzeh
							    'ئ',//alef hamzeh paeen
							    'ـ',//keshesh
							    '،',//kama
							    '×'//zarb
							    );

	//جدول حروف فارسي در جدول مايکروسافت
	 public $microSoftCharMap = array(  'آ',//a
								    'ا',//alef
								    'ب',//be
								    'پ',//pe
								    'ت',//te
								    'ث',//se
								    'ج',//jim
								    'چ',//che
								    'ح',//he
								    'خ',//khe
								    'ر',//re
								    'ز',//ze
								    'ژ',//zhe
								    'د',//dal
								    'ذ',//zal
								    'س',//sin
								    'ش',//shin
								    'ص',//sad
								    'ض',//zad
								    'ط',//ta
								    'ظ',//za
								    'ك',//kaf
								    'گ',//gaf
								    'ف',//faf
								    'ق',//ghaf
								    'ل',//lam
								    'م',//mim
								    'ن',//noon
								    'و',//vav
								    'ه',//he
								    'ي',//ye
								    'ع',//ein
								    'غ',//ghein
								    'ئ',//ye hamzeh
								    '؟',//?
								    'أ',//alef hamzeh bala
								    'ؤ',//vav hamzeh
								    'ء',//hamzeh
								    'إ',//alef hamzeh paeen
								    'ـ',//keshesh
								    '،',//kama
								    '×'//zarb
    								);
	
	public function  __construct() {
	
									
		$this->sisW2DFormatConvertor() ;
		return ; 
	}
								
	public function sisW2DFormatConvertor() {
			
		reset($this->dos2SinaMap);
		
	
		foreach ($this->dos2SinaMap as $key => $value) {
			if($key != $value || $key <= 58)
				$this->sina2DosMap[$value][] = $key;				
		}
		$this->sina2DosMap[157][] = 32; //for shift + space		
		$this->sina2DosMap[237][] = 32; //for shift + space		
		$this->sina2DosMap[129][] = 129 ; // حرف پ
		$this->sina2DosMap[198][] = 198 ; // حرف ئ 
		$this->sina2DosMap[144][] = 144 ; // حرف گ
	}
//---------------------------------------------------------------------------------------------	
    public function convertStringDos2Sina($dosString) {
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
	
	public function convertStringDos2Ms($dosString) {
		return $this->convertStringSina2MS($this->convertStringDos2Sina($dosString));
	}
	
	public function convertFileDos2Ms($sourceFileName, $destFileName) {
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
	
	public function convertFileBatchDos2Ms($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileDos2Ms($sourcePath . $file);
			}
			closedir($dirHandler);
    	}		
	}
	
//-----------------------------------------------------------------------------------------	
	public function convertStringSina2Dos($sinaString) {
	
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
	
	public function determineDosChar($currentChar, $nextChar, $previousChar, $dosChars) {
		
		$chr1 = $dosChars[0];
		$chr2 = (count($dosChars) > 1)?$dosChars[1]:$chr1;
		$chr3 = (count($dosChars) > 2)?$dosChars[2]:$chr2;
		$chr4 = (count($dosChars) > 3)?$dosChars[3]:$chr3;
		$chr5 = (count($dosChars) > 4)?$dosChars[4]:$chr4;
		$char = '';
		
		if($currentChar == ord('آ')) { //ا
			if(in_array($previousChar, $this->sinaCharStartStick))
				return $chr2;
			else
				return $chr1;
		}
		if($currentChar == ord('ق')) { //ع
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
			$char = $chr1;//$chr3; فقط برا? ع با?د 2 باشد
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
	
	
	public function convertStringMs2Dos($msString) {		
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
	
	public function convertFileBatchMs2Dos($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileDos2Ms($sourcePath . $file);
			}
			closedir($dirHandler);
    	}		
	}
//-----------------------------------------------------------------------------------------	

	public function convertCharSina2Ms($aChar) {
		$result = $aChar;
	  	for ($i = 1; $i <= $this->maxCahr; $i++)
	    	if ($aChar == $this->sinaCharMap[$i-1]) {
	      		$result = $this->microSoftCharMap[$i-1];
	      		Break;
	    	}
	    return $result;
	}
	
	public function convertStringSina2Ms($aString) {
		$len = strlen($aString);
		for($i = 1; $i <= $len; $i++)
			$aString[$i-1] = $this->convertCharSina2MS($aString[$i-1]);
		return $aString;
	}
	
	public function convertFileSina2Ms($sourceFileName, $destFileName) {
		  $sourceHandle = fopen($sourceFileName, 'r');
		  $destHandle = fopen($destFileName, 'w');
		  while (!feof($sourceHandle)) {
		  	$str = fgets($sourceHandle);
		    fwrite($destHandle, $this->convertStringSina2Ms($str));
		  }
		  fclose($sourceHandle);
		  fclose($destHandle);
	}
	
	public function convertFileBatchSina2Ms($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileSina2Ms($sourcePath . $file);
			}
			closedir($dirHandler);
    	}    	
	}	

//-----------------------------------------------------------------------------------------
	
	public function convertCharMs2Sina($aChar) {
		$result = $aChar;
	  	for ($i = 1; $i <= $this->maxCahr; $i++)
	    	if ($aChar == $this->microSoftCharMap[$i-1]) {
	      		$result = $this->sinaCharMap[$i-1];
	      		Break;
	    	}
	    return $result;
	}	
	
	public function convertStringMs2Sina($aString) {
		$len = strlen($aString);
		for($i = 1; $i <= $len; $i++)
			$aString[$i-1] = $this->convertCharMs2Sina($aString[$i-1]);
		return $aString;
	}	
	
	public function convertFileMs2Sina($sourceFileName, $destFileName) {
		  $sourceHandle = fopen($sourceFileName, 'r');
		  $destHandle = fopen($destFileName, 'w');
		  while (!feof($sourceHandle)) {
		  	$str = fgets($sourceHandle);
		    fwrite($destHandle, $this->convertStringMs2Sina($str));
		  }
		  fclose($sourceHandle);
		  fclose($destHandle);
	}
	
	public function convertFileBatchMs2Sina($sourcePath, $destPath, $wildCard) {
    	if ($dirHandler = opendir($sourcePath . $wildCard)) {
			while (($file = readdir($dirHandler)) !== false) {
				$this->convertFileMs2Sina($sourcePath . $file);
			}
			closedir($dirHandler);
    	}    	
	}

	public function convertDigitDosEnToFa($digitStr) {
		for($i=0; $i<strlen($digitStr); $i++) {
			if($digitStr[$i] >= '0' && $digitStr[$i] <= '9')
				$digitStr[$i] = chr(ord($digitStr[$i])+80);
		}
		return $digitStr;
	}
	
	public function correctDigitDir($dstr) {
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