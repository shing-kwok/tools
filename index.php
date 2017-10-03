<?php
// Setting environment
set_include_path( '.:' . __DIR__ . '/library/php');
if(isset($_ENV["PATH"])){
	$path = $_ENV["PATH"]. ':';
}
else{
	$path = '';
}

putenv("PATH=" . $path . '/Users/shing/tools/aws-cli');

// Execute appropiate file
$path = isset($_GET['q']) ? $_GET['q'] : '';

if(strpos(basename($path), '.') === false){
	$path = rtrim($path, '/') . '/index.php';
}

if(file_exists($path)){
	require_once $path;
}
else{
	header('HTTP/1.1 404 Not found');
}


// Debug functions
function _get($name, $default){
	return isset($_GET[$name])? $_GET[$name] : $default;
}

function dp(){
	header('content-type: text/plain; charset=utf-8;');

	$args = func_get_args();

	foreach($args as $arg){
		if($arg){
			print_r($arg);
			echo "\n";
		}
		else
			var_dump($arg);

	}

	exit;
}

function dps(){
	header('content-type: text/plain; charset=utf-8;');

	$args = func_get_args();

	foreach($args as $arg){
		if($arg){
			print_r($arg);
			echo "\n";
		}
		else
			var_dump($arg);

	}
}

function dpob(){
	header('content-type: text/plain; charset=utf-8;');
	
	$args = func_get_args();
	
	foreach($args as $arg){
		if($arg){
			print_r($arg);
			echo "\n";
		}
		else
			var_dump($arg);
	
	}
	
	ob_end_flush();
	exit;
}

function dpf(){
	header('content-type: text/plain; charset=utf-8;');
	
	$args = func_get_args();
	
	$format = array_shift($args);
	
	vprintf($format, $args);
	
	echo PHP_EOL;
}

function dpvf(){
	header('content-type: text/plain; charset=utf-8;');

	$args = func_get_args();

	$format = array_shift($args);

	vprintf($format, $args[0]);

	echo PHP_EOL;
}

function echotable($arr){
	echo '<table><tbody>';
	foreach($arr as $row){
		echo '<tr>';
		foreach($row as $item)
			echotd($item);
		echo '</tr>';
	}
	echo '</tbody></table>';
}

function echotd($str){
	echo "<td>$str</td>\n";
}