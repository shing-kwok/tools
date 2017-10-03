<?php


/**
//echo __DIR__;
$filename = __DIR__  . "/ec2.json";
$contents = file_get_contents($filename);
/**/
/*
$handle = fopen( $filename, "r" );
$contents = fread($handle, filesize($filename));
fclose($handle);
echo $contents;
*/

// echo $contents;

$json = '{"t2.nano":{"cpu":"1","ram":"0.5","price":"0.0075"},"t2.micro":{"cpu":"1","ram":"1","price":"0.015"},"t2.small":{"cpu":"1","ram":"2","price":"0.03"},"t2.medium":{"cpu":"2","ram":"4","price":"0.06"},"t2.large":{"cpu":"2","ram":"8","price":"0.12"},"t2.xlarge":{"cpu":"4","ram":"16","price":"0.24"},"t2.2xlarge":{"cpu":"8","ram":"32","price":"0.48"},"m4.large":{"cpu":"2","ram":"8","price":"0.125"},"m4.xlarge":{"cpu":"4","ram":"16","price":"0.25"},"m4.2xlarge":{"cpu":"8","ram":"32","price":"0.5"},"m4.4xlarge":{"cpu":"16","ram":"64","price":"1"},"m4.10xlarge":{"cpu":"40","ram":"160","price":"2.5"},"m4.16xlarge":{"cpu":"64","ram":"256","price":"4"},"m3.medium":{"cpu":"1","ram":"3.75","price":"0.098"},"m3.large":{"cpu":"2","ram":"7.5","price":"0.196"},"m3.xlarge":{"cpu":"4","ram":"15","price":"0.392"},"m3.2xlarge":{"cpu":"8","ram":"30","price":"0.784"},"c4.large":{"cpu":"2","ram":"3.75","price":"0.115"},"c4.xlarge":{"cpu":"4","ram":"7.5","price":"0.231"},"c4.2xlarge":{"cpu":"8","ram":"15","price":"0.462"},"c4.4xlarge":{"cpu":"16","ram":"30","price":"0.924"},"c4.8xlarge":{"cpu":"36","ram":"60","price":"1.848"},"c3.large":{"cpu":"2","ram":"3.75","price":"0.132"},"c3.xlarge":{"cpu":"4","ram":"7.5","price":"0.265"},"c3.2xlarge":{"cpu":"8","ram":"15","price":"0.529"},"c3.4xlarge":{"cpu":"16","ram":"30","price":"1.058"},"c3.8xlarge":{"cpu":"32","ram":"60","price":"2.117"},"g2.2xlarge":{"cpu":"8","ram":"15","price":"1"},"g2.8xlarge":{"cpu":"32","ram":"60","price":"4"},"x1.16xlarge":{"cpu":"64","ram":"976","price":"9.671"},"x1.32xlarge":{"cpu":"128","ram":"1952","price":"19.341"},"r3.large":{"cpu":"2","ram":"15","price":"0.2"},"r3.xlarge":{"cpu":"4","ram":"30.5","price":"0.399"},"r3.2xlarge":{"cpu":"8","ram":"61","price":"0.798"},"r3.4xlarge":{"cpu":"16","ram":"122","price":"1.596"},"r3.8xlarge":{"cpu":"32","ram":"244","price":"3.192"},"r4.large":{"cpu":"2","ram":"15.25","price":"0.16"},"r4.xlarge":{"cpu":"4","ram":"30.5","price":"0.32"},"r4.2xlarge":{"cpu":"8","ram":"61","price":"0.64"},"r4.4xlarge":{"cpu":"16","ram":"122","price":"1.28"},"r4.8xlarge":{"cpu":"32","ram":"244","price":"2.56"},"r4.16xlarge":{"cpu":"64","ram":"488","price":"5.12"},"i3.large":{"cpu":"2","ram":"15.25","price":"0.187"},"i3.xlarge":{"cpu":"4","ram":"30.5","price":"0.374"},"i3.2xlarge":{"cpu":"8","ram":"61","price":"0.748"},"i3.4xlarge":{"cpu":"16","ram":"122","price":"1.496"},"i3.8xlarge":{"cpu":"32","ram":"244","price":"2.992"},"i3.16xlarge":{"cpu":"64","ram":"488","price":"5.984"},"d2.xlarge":{"cpu":"4","ram":"30.5","price":"0.87"},"d2.2xlarge":{"cpu":"8","ram":"61","price":"1.74"},"d2.4xlarge":{"cpu":"16","ram":"122","price":"3.48"},"d2.8xlarge":{"cpu":"36","ram":"244","price":"6.96"}}';

$products = json_decode($json, true);

$us = array();
$sg = array();
foreach($products as $key => $product){
	$us[$key] = array(
		'price' => "{$product['price']} * \$this->hourPerMonth",
		'cpu' => $product['cpu'],
		'ram' => $product['ram'],
	);

	/**
	$sg['Linode 1GB'] = array(
		'price' => 5,
		'cpu' => 1,
		'ram' => 1,
		'SSD' => 20,
		'netFree' => 1000,
	);
	/**/
}

// Print array as PHP code
$str = var_export($us, true);
$str = preg_replace("/'([^']+hourPerMonth)'/", '$1', $str);
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