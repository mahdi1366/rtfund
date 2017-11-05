<?php
/*
 * contact me to improve this class
 * zakiehalizadeh@gmail.com
 * 
 * improved by Shabnam.Jafarkhani
 * Date : 1395/09/22
 */

require_once getenv("DOCUMENT_ROOT") . "/generalClasses/htmlpurifier-4.8.0/HTMLPurifier.auto.php";

class InputValidation {
    /* we used const in methodes also in all over System,Programmers must use this Patterns */
    
    /* Patterns */
	/**
	 * فقط اعداد
	 */
    const Pattern_Num =				'/^-?(?:\d+|\d*\.\d+)$/u';
	
	/* Patterns */
	/**
	 * رشته اعداد شامل عدد و کاما
	 */
    const Pattern_NumComma =		'/^([0-9]|[\. \-\,])*$/i';
	 
	/**
	 * اعداد و حروف انگلیسی
	 */
    const Pattern_EnAlphaNum =		'/^([A-Za-z0-9]|[\._ \-])*$/i';
    /**
	 * اعداد و حروف فارسی و علامت های _ و - و . و %
	 */
	const Pattern_FaAlphaNum =		'/^([0-9]|[\p{Arabic}]|[\._ \-])*$/u';
	/**
	 *اعداد و حروف فارسی و انگلیسی و علامت های _و - و . و %
	 */
    const Pattern_FaEnAlphaNum =	'/^([a-zA-Z0-9]|[\p{Arabic}]|["\'\/]|[\\\]|[\s،,\._ \-%\(\)\*\+=÷:;؛-])*$/u';
	/**
	 html value or text values with a lot of range of special characters
	 */
    const Pattern_Html =			'';
	/**
	 زمان به فرمت 00:00 یا 00:00:00
	 */
    const Pattern_Time =			'/^([0-9]|[:])*$/';
	/**
	 *تاریخ فارسی یا انگلیسی به فرمت 0000/00/00 یا 00-00-0000  
	 */
    const Pattern_Date =			'/^[0-9]{4}[-\/][0-9]{1,2}[-\/][0-9]{1,2}$/';
	
	const Pattern_DateTime =			'/^[0-9]{4}[-\/][0-9]{1,2}[-\/][0-9]{1,2}[ ][0-9]{2}[:][0-9]{2}[:][0-9]{2}$/';
	
	/**
	 *تاریخ فارسی با فرمت 0000/00/00 یا 00-00-0000  
	 */
    const Pattern_JDate =			'/^(1[3-4][0-9]{2}[-\/][0-9]{1,2}[-\/][0-9]{1,2})|([0]{4}[-\/][0]{2}[-\/][0]{2})$/';
	/**
	 *تاریخ انگلیسی به فرمت 0000/00/00 یا 00-00-0000  
	 */
    const Pattern_GDate =			'/^([12][09][0-9]{2}[-\/][0-9]{1,2}[-\/][0-9]{1,2})|([0]{4}[-\/][0]{2}[-\/][0]{2})$/';
	
    const Pattern_FileName =		'/^([a-zA-Z0-9]|[\p{Arabic}]|[_ -])*\.([a-zA-Z0-9]{1,5})*$/u';
    //const Pattern_url =			'(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})';
	
	const Pattern_IP =			'ip'; //Use the filter function is better
	const Pattern_Email =		'email'; //Use the filter function is better
	const Pattern_Url =			'url'; //Use the filter function is better
	const Pattern_Boolean =		'boolean'; //Use the filter function is better
	
	/**
	 * این تابع ورودی را با الگو مورد نظر اعتبار سنجی می کند
	 * @param string $value مقدار ورودی
	 * @param string $pattern الگوی مورد نظر
	 * @param boolean $DieOnError در صورت خطا اجرا خاتمه یابد
	 * @param string $ErrorMessage خطای رخ داده
	 * @return boolean
	 */
    public static function validate(&$value, $pattern, $DieOnError = true, &$ErrorMessage = "") {
		
		if(empty($value))
			return true;
		
		if($value == "%pdonull%")
			return true;
		
		if($value == "%pdonull%")
			return true;
		
		if(($pattern == self::Pattern_Date || $pattern == self::Pattern_JDate || $pattern == self::Pattern_GDate) && $value == "now()")
			return true;
		
		switch($pattern)
		{
			case self::Pattern_IP :			$result = self::validateIp($value);				break;
			case self::Pattern_Email :		$result = self::validateEmail($value);			break;
			case self::Pattern_Url :		$result = self::validateUrl($value);			break;
			case self::Pattern_Boolean :	$result = self::validateBoolean($value);		break;
			case self::Pattern_Html	:		
				$value = self::htmlEncode($value); 
				$result= true;	
				break;
			default:						$result = preg_match($pattern, $value);				
		}        
		
		if(!$result)
		{
			self::LogToDB($value, $pattern);
			$ErrorMessage = "ورودی " . "'" . $value . "' مطابق با الگوی " . self::GetPatternName($pattern) . " نمی باشد.";
			if($DieOnError)
			{
				echo $ErrorMessage;
				die();
			}
			else
			{
				if(class_exists("ExceptionHandler"))
					ExceptionHandler::PushException($ErrorMessage);
				return false;
			}
		}
		return true;
    }
	
    /* Validates that the specified value does not have null or empty value. */
    public static function validateRequired($value) {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        }
        return true;
    }

    public static function validateNumeric($value) {
        return is_numeric($value);
    }

    public static function validateLengthBetween($value, $max, $min) {

        $length = self::stringLength($value);
        // Length between
        return $length >= $min && $length <= $max;
    }

    public static function validateLengthEqual($value, $len) {
        $length = self::stringLength($value);
        // Length between
        return $length == $len;
    }

    public static function stringLength($value) {

        if (!is_string($value)) {
           return false;
        } elseif (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }
        return strlen($value);
    }

	/****************** validate limitted option by white list ********* */
	/**
	 * این تابع چک می کند که مقدار ورودی در آرایه مجاز وجود دارد یا نه
	 * @param array $acceptable آرایه مقادیر مجاز
	 * @param string $value مقدار مورد نظر
	 * @return boolean
	 */
    static public function validateAccepted($acceptable, $value) {
        return in_array($value, $acceptable, true);
    }

    /********************* XSS Validation ******************************* */
	/**
	 *  این تابع کلیه آیتم های آرایه را به صورت بازگشتی انکود می کند
	 * @param type $InputArr
	 */
	public static function ArrayEncoding(&$InputArr){
		
		foreach ($InputArr as $key => &$value) 
		{
			if(is_array($value))
				self::ArrayEncoding($value);
			else
				$InputArr[$key] = htmlspecialchars($value);
		}
	}
	
	/**
	 *  این تابع کلیه آیتم های آرایه را به صورت بازگشتی خالص سازی می کند
	 * @param array $InputArr
	 */
	public static function ArrayPurifier(&$InputArr){
		
		foreach ($InputArr as $key => &$value) 
		{
			if(is_array($value))
				self::ArrayPurifier($value);
			else
				$InputArr[$key] = self::filteyByHTMLPurifier($value);
		}
	}
	
    /* encode HTML special chars */
    /* Use this function,When the user is not allowed to enter the HTML Tags  */

    public static function htmlEncode($str) {

        return htmlspecialchars($str);
    }

	/**
	 * این تابع ورودی را خالص سازی می کند
	 * @param string $dirty_html مقدار ورودی 
	 * @return string مقدار ورودی معتبر شده
	 */
    public static function filteyByHTMLPurifier($dirty_html) {

		//return self::htmlEncode($dirty_html);
		
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return $clean_html = $purifier->purify($dirty_html);
    }

    /******************* sql injection sanitation ******************************* */

    /* Warning: you can't validate where phares by this function. */
    /* sanitises that the value does not have malicios value for sql injection. */
    /* Use this function,When the Programmer is not allowed to change main functions in system */
    /* Warning:mysql_real_escape_string was deprecated in PHP 5.5.0,
     *  and it was removed in PHP 7.0.0. Instead, the MySQLi or PDO_MySQL extension should be used*/
         
    public static function validationSqlParam($value) {
        $value = stripcslashes($value);
        return $value = mysql_real_escape_string($value);
        /* return $value= mysqli_real_escape_string($value); //for modern php versions */
        /* return $value= mysqli::escape_string($value); //for modern php versions */
    }

	//****************************************************************
	private static function GetPatternName($pattern){
		
		$tmp = new ReflectionClass(get_called_class());
		$a = $tmp->getConstants();
        $b = array_flip($a);

		$titles = array(
			"Pattern_Num" => "اعداد",
			"Pattern_NumComma" => "رشته اعداد",
			"Pattern_EnAlphaNum" => "اعداد و حروف لاتین",
			"Pattern_FaAlphaNum" => "اعداد و حروف فارسی",
			"Pattern_FaEnAlphaNum" => "اعداد و حروف",
			"Pattern_Time" => "زمان",
			"Pattern_Date" => "تاریخ",
			"Pattern_JDate" => "تاریخ شمسی",
			"Pattern_GDate" => "تاریخ میلادی",
			"Pattern_FileName" => "نام فایل",
			"Pattern_IP" => "آدرس آی پی",
			"Pattern_Email" => "پست الکترونیک",
			"Pattern_Url" => "آدرس الکترونیک",
			"Pattern_Boolean" => ""
		);
		return $titles[  $b[$pattern] ];
       // return $b[$pattern];
	}
	
	private static function LogToDB($value, $pattern){
		
		$pdo = new PDO("mysql:host=" . config::$db_servers['master']['host'] . ";dbname=" . 
				config::$db_servers['master']['dataanalysis_db'], 
				config::$db_servers['master']['dataanalysis_user'], 
				config::$db_servers['master']['dataanalysis_pass'], 
				array(PDO::MYSQL_ATTR_INIT_COMMAND =>  "SET NAMES utf8"));
				
		$query = "insert into InputViolation(InputType,InputValue,SysCode,PageName,IPAddress,ActionTime,parameters)
				values (:InputType,:InputValue,:SysCode,:PageName,:IPAddress,now(),:parameters)";
		
		$stm = $pdo->prepare($query);
		$whereParam = array(
			':InputType' => self::GetPatternName($pattern),
			':InputValue' => $value,
			':SysCode' => $_SESSION['SystemCode'],
			':PageName' => $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']. " " .
					(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['SCRIPT_FILENAME']),
			':IPAddress' => $_SESSION['LIPAddress'],
			':parameters' => json_encode($_REQUEST)
		);
		$keys = array_keys($whereParam);
		for($i=0; $i < count($keys); $i++)
			$stm->bindParam($keys[$i], $whereParam[$keys[$i]]);
		
		$stm->execute();
	}
		
    private static function validateIp($value) {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }
	
    private static function validateEmail( $value) {
        return filter_var($value, \FILTER_VALIDATE_EMAIL) !== false;
    }

    private static function validateUrl( $value) {
            return filter_var($value, \FILTER_VALIDATE_URL) !== false;
    }

    private static function validateBoolean($value) {
        return (is_bool($value)) ? true : false;
    }

}

?>
