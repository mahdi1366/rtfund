<?php

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	    if ( strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 15 )
	  	  echo substr($_SERVER['HTTP_X_FORWARDED_FOR'] , 0,strpos($_SERVER['HTTP_X_FORWARDED_FOR'],','));
	    else
	      echo ($_SERVER['HTTP_X_FORWARDED_FOR']);
	  else
	      echo $_SERVER['REMOTE_ADDR'];

?>