<?php
$keyOrder = array(
	'price',
	'cpu',
	'ram',
	'ssd',
	'netFree',
	
);

$json = '{"20GB":{"cpu":"1","ram":0.5,"ssd":"20","netFree":"500","price":"2.50"},"25GB":{"cpu":"1","ram":1,"ssd":"25","netFree":"1000","price":"5"},"40GB":{"cpu":"1","ram":2,"ssd":"40","netFree":"2000","price":"10"},"60GB":{"cpu":"2","ram":4,"ssd":"60","netFree":"3000","price":"20"},"100GB":{"cpu":"4","ram":8,"ssd":"100","netFree":"4000","price":"40"},"200GB":{"cpu":"6","ram":16,"ssd":"200","netFree":"5000","price":"80"},"300GB":{"cpu":"8","ram":32,"ssd":"300","netFree":"6000","price":"160"},"400GB":{"cpu":"16","ram":64,"ssd":"400","netFree":"10000","price":"320"}}';

$products = json_decode($json, true);
foreach($products as &$product){
	$temp = array();
	foreach($keyOrder as $key){
		$temp[$key] = $product[$key];
	}
	$product = $temp;
}


// Print array as PHP code
$str = var_export($products, true);
// Format output to high readability
/**
$str = preg_replace("/  /", "\t", $str);
$str = preg_replace("/=> \n\t+/", "=> ", $str);
/**/
//  Format output to one line code
/**/
$str = preg_replace("/\n\s*/", " ", $str);
/**/
echo "<pre>";
//var_export($us);
echo $str;
echo "</pre>";

?>