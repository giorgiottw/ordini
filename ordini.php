<?php
	$curl_parameters = array( 'op'=>'getordersbyids', 'data'=>array('id'=>"15") );
	$data_string=json_encode($curl_parameters);
	$c = curl_init('http://www.gipys.it/ordini/wl.php');
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($c, CURLOPT_POSTFIELDS, "JSONData=".$data_string);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json', 'Content-Length: ' . strlen($data_string)));   
	curl_setopt($c, CURLOPT_POSTFIELDS, $data_string);
	$page = curl_exec($c);
	echo $page;
	curl_close($c);
?>