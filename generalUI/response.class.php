<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	86.09.18
//-------------------------
class Response {

    public static function createResponse($succ,$data){
      	$res = array();
      	$res["success"] = $succ ? "true" : "false";
      	//if($succ) $res["data"]=$data; else $res["errors"]=$data;

      	$data = str_replace("\n",'" + "\n',$data);

      	if($data != "" && $data{0} == "{")
			echo '{"success":"' . $res["success"] . '","data":' . $data . '}';
		else
			echo '{"success":"' . $res["success"] . '","data":"' . $data . '"}';
    }

	 public static function createObjectiveResponse($succ,$data){
      	$res = array();
      	$res["success"] = $succ ? "true" : "false";

		$data = ($data == "") ? "{}" : $data;

      	$data = str_replace("\n",'" + "\n',$data);

      	if($data{0} == "{")
			echo '{success:' . $res["success"] . ',data:' . $data . '}';
		else
			echo '{success:' . $res["success"] . ',data:"' . $data . '"}';
    }
}
?>