<?php
$keyOrder = array(
	'price',
	'cpu',
	'ram',
	'ssd',
	'netFree',
	
);

$json = '{"Linode 1GB":{"ram":"1","cpu":"1","ssd":"20","netFree":1000,"price":"5"},"Linode 2GB":{"ram":"2","cpu":"1","ssd":"30","netFree":2000,"price":"10"},"Linode 4GB":{"ram":"4","cpu":"2","ssd":"48","netFree":3000,"price":"20"},"Linode 8GB":{"ram":"8","cpu":"4","ssd":"96","netFree":4000,"price":"40"},"Linode 12GB":{"ram":"12","cpu":"6","ssd":"192","netFree":8000,"price":"80"},"Linode 24GB":{"ram":"24","cpu":"8","ssd":"384","netFree":16000,"price":"160"},"Linode 48GB":{"ram":"48","cpu":"12","ssd":"768","netFree":20000,"price":"320"},"Linode 64GB":{"ram":"64","cpu":"16","ssd":"1152","netFree":20000,"price":"480"},"Linode 80GB":{"ram":"80","cpu":"20","ssd":"1536","netFree":20000,"price":"640"},"Linode 16GB":{"ram":"16","cpu":"1","ssd":"20","netFree":5000,"price":"60"},"Linode 32GB":{"ram":"32","cpu":"2","ssd":"40","netFree":6000,"price":"120"},"Linode 60GB":{"ram":"60","cpu":"4","ssd":"90","netFree":7000,"price":"240"},"Linode 100GB":{"ram":"100","cpu":"8","ssd":"200","netFree":8000,"price":"480"},"Linode 200GB":{"ram":"200","cpu":"16","ssd":"340","netFree":9000,"price":"960"}}';

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