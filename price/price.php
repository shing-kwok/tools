<?php
$calc = new ServerPriceCalulator();
$defaultSpec = array(
	'cpu' => 4,
	'ram' => 8,
	'storage' => 300,
	'storageType' => 'hdd',
);
$vendor = get('vendor', 'AWS');
$location = get('location', 'SG');
$cpu = get('cpu', $defaultSpec['cpu']);
$ram = get('ram', $defaultSpec['ram']);
$storage = get('storage', $defaultSpec['storage']);
$storageType = get('storageType', $defaultSpec['storageType']);
$debug = isset($_GET['debug']);
//$price = $calc->getPriceForVendor($vendor, $location, $cpu, $ram, $storage, $storageType);
$price = $calc->getPriceList($location, $cpu, $ram, $storage, $storageType);
if($debug){
	header('Content-type: text/plain');
	echo ($price);
}
else{
	header('Content-type: application/json');
	echo json_encode($price);
}

function get($name, $default){
	return isset($_GET[$name])? $_GET[$name] : $default;
}

class ServerPriceCalulator{
	const HOUR_PER_MONTH = 720;

	private $hourPerMonth = 720;
	private $numSuggestedPredefined = 5;
	private $posMatchingIndex = 1;
	private $negMatchingIndex = 2;
	private $matchingIndex = 2;

	private $exchangeRate = array(
		'USD' => 1,
		'JPY' => 0.009,
	);

	private $locations = array(
		'Asia' => array('TW', 'HK', 'JP', 'SG'),
		'US' => array('US', 'CA'),
	);

	private $priceDetails;

	private function _initiatePriceDetail(){
		$this->priceDetails = array(
			'AWS' => array(
				'SG' => array(
					'currency' => 'USD',
					//'hdd' => 0.08,
					'ssd' => 0.12,
					'netFree' => 1,
					'net' => 0.12,
					'predefined' => array (
						't2.nano' =>  array ( 'price' => 0.0075 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '0.5', ), 't2.micro' =>  array ( 'price' => 0.015 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '1', ), 't2.small' =>  array ( 'price' => 0.03 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '2', ), 't2.medium' =>  array ( 'price' => 0.06 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '4', ), 't2.large' =>  array ( 'price' => 0.12 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '8', ), 't2.xlarge' =>  array ( 'price' => 0.24 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '16', ), 't2.2xlarge' =>  array ( 'price' => 0.48 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '32', ), 'm4.large' =>  array ( 'price' => 0.125 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '8', ), 'm4.xlarge' =>  array ( 'price' => 0.25 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '16', ), 'm4.2xlarge' =>  array ( 'price' => 0.5 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '32', ), 'm4.4xlarge' =>  array ( 'price' => 1 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '64', ), 'm4.10xlarge' =>  array ( 'price' => 2.5 * $this->hourPerMonth, 'cpu' => '40', 'ram' => '160', ), 'm4.16xlarge' =>  array ( 'price' => 4 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '256', ), 'm3.medium' =>  array ( 'price' => 0.098 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '3.75', ), 'm3.large' =>  array ( 'price' => 0.196 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '7.5', ), 'm3.xlarge' =>  array ( 'price' => 0.392 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '15', ), 'm3.2xlarge' =>  array ( 'price' => 0.784 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '30', ), 'c4.large' =>  array ( 'price' => 0.115 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '3.75', ), 'c4.xlarge' =>  array ( 'price' => 0.231 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '7.5', ), 'c4.2xlarge' =>  array ( 'price' => 0.462 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '15', ), 'c4.4xlarge' =>  array ( 'price' => 0.924 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '30', ), 'c4.8xlarge' =>  array ( 'price' => 1.848 * $this->hourPerMonth, 'cpu' => '36', 'ram' => '60', ), 'c3.large' =>  array ( 'price' => 0.132 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '3.75', ), 'c3.xlarge' =>  array ( 'price' => 0.265 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '7.5', ), 'c3.2xlarge' =>  array ( 'price' => 0.529 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '15', ), 'c3.4xlarge' =>  array ( 'price' => 1.058 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '30', ), 'c3.8xlarge' =>  array ( 'price' => 2.117 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '60', ), 'g2.2xlarge' =>  array ( 'price' => 1 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '15', ), 'g2.8xlarge' =>  array ( 'price' => 4 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '60', ), 'x1.16xlarge' =>  array ( 'price' => 9.671 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '976', ), 'x1.32xlarge' =>  array ( 'price' => 19.341 * $this->hourPerMonth, 'cpu' => '128', 'ram' => '1952', ), 'r3.large' =>  array ( 'price' => 0.2 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '15', ), 'r3.xlarge' =>  array ( 'price' => 0.399 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'r3.2xlarge' =>  array ( 'price' => 0.798 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'r3.4xlarge' =>  array ( 'price' => 1.596 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'r3.8xlarge' =>  array ( 'price' => 3.192 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'r4.large' =>  array ( 'price' => 0.16 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '15.25', ), 'r4.xlarge' =>  array ( 'price' => 0.32 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'r4.2xlarge' =>  array ( 'price' => 0.64 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'r4.4xlarge' =>  array ( 'price' => 1.28 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'r4.8xlarge' =>  array ( 'price' => 2.56 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'r4.16xlarge' =>  array ( 'price' => 5.12 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '488', ), 'i3.large' =>  array ( 'price' => 0.187 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '15.25', ), 'i3.xlarge' =>  array ( 'price' => 0.374 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'i3.2xlarge' =>  array ( 'price' => 0.748 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'i3.4xlarge' =>  array ( 'price' => 1.496 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'i3.8xlarge' =>  array ( 'price' => 2.992 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'i3.16xlarge' =>  array ( 'price' => 5.984 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '488', ), 'd2.xlarge' =>  array ( 'price' => 0.87 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'd2.2xlarge' =>  array ( 'price' => 1.74 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'd2.4xlarge' =>  array ( 'price' => 3.48 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'd2.8xlarge' =>  array ( 'price' => 6.96 * $this->hourPerMonth, 'cpu' => '36', 'ram' => '244', ),
					), // End of AWS predefined
				),
				'US' => array(
					'currency' => 'USD',
					'ssd' => 0.1,
					'netFree' => 1,
					'net' => 0.09,
					'predefined' => array (
						't2.nano' =>  array ( 'price' => 0.0059 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '0.5', ), 't2.micro' =>  array ( 'price' => 0.012 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '1', ), 't2.small' =>  array ( 'price' => 0.023 * $this->hourPerMonth, 'cpu' => '1', 'ram' => '2', ), 't2.medium' =>  array ( 'price' => 0.047 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '4', ), 't2.large' =>  array ( 'price' => 0.094 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '8', ), 't2.xlarge' =>  array ( 'price' => 0.188 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '16', ), 't2.2xlarge' =>  array ( 'price' => 0.376 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '32', ), 'm4.large' =>  array ( 'price' => 0.1 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '8', ), 'm4.xlarge' =>  array ( 'price' => 0.2 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '16', ), 'm4.2xlarge' =>  array ( 'price' => 0.4 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '32', ), 'm4.4xlarge' =>  array ( 'price' => 0.8 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '64', ), 'm4.10xlarge' =>  array ( 'price' => 2 * $this->hourPerMonth, 'cpu' => '40', 'ram' => '160', ), 'm4.16xlarge' =>  array ( 'price' => 3.2 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '256', ), 'c4.large' =>  array ( 'price' => 0.1 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '3.75', ), 'c4.xlarge' =>  array ( 'price' => 0.199 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '7.5', ), 'c4.2xlarge' =>  array ( 'price' => 0.398 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '15', ), 'c4.4xlarge' =>  array ( 'price' => 0.796 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '30', ), 'c4.8xlarge' =>  array ( 'price' => 1.591 * $this->hourPerMonth, 'cpu' => '36', 'ram' => '60', ), 'p2.xlarge' =>  array ( 'price' => 0.9 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '61', ), 'p2.8xlarge' =>  array ( 'price' => 7.2 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '488', ), 'p2.16xlarge' =>  array ( 'price' => 14.4 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '732', ), 'g3.4xlarge' =>  array ( 'price' => 1.14 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'g3.8xlarge' =>  array ( 'price' => 2.28 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'g3.16xlarge' =>  array ( 'price' => 4.56 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '488', ), 'x1.16xlarge' =>  array ( 'price' => 6.669 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '976', ), 'x1.32xlarge' =>  array ( 'price' => 13.338 * $this->hourPerMonth, 'cpu' => '128', 'ram' => '1952', ), 'r3.large' =>  array ( 'price' => 0.166 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '15', ), 'r3.xlarge' =>  array ( 'price' => 0.333 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'r3.2xlarge' =>  array ( 'price' => 0.665 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'r3.4xlarge' =>  array ( 'price' => 1.33 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'r3.8xlarge' =>  array ( 'price' => 2.66 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'r4.large' =>  array ( 'price' => 0.133 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '15.25', ), 'r4.xlarge' =>  array ( 'price' => 0.266 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'r4.2xlarge' =>  array ( 'price' => 0.532 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'r4.4xlarge' =>  array ( 'price' => 1.064 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'r4.8xlarge' =>  array ( 'price' => 2.128 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'r4.16xlarge' =>  array ( 'price' => 4.256 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '488', ), 'i3.large' =>  array ( 'price' => 0.156 * $this->hourPerMonth, 'cpu' => '2', 'ram' => '15.25', ), 'i3.xlarge' =>  array ( 'price' => 0.312 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'i3.2xlarge' =>  array ( 'price' => 0.624 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'i3.4xlarge' =>  array ( 'price' => 1.248 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'i3.8xlarge' =>  array ( 'price' => 2.496 * $this->hourPerMonth, 'cpu' => '32', 'ram' => '244', ), 'i3.16xlarge' =>  array ( 'price' => 4.992 * $this->hourPerMonth, 'cpu' => '64', 'ram' => '488', ), 'd2.xlarge' =>  array ( 'price' => 0.69 * $this->hourPerMonth, 'cpu' => '4', 'ram' => '30.5', ), 'd2.2xlarge' =>  array ( 'price' => 1.38 * $this->hourPerMonth, 'cpu' => '8', 'ram' => '61', ), 'd2.4xlarge' =>  array ( 'price' => 2.76 * $this->hourPerMonth, 'cpu' => '16', 'ram' => '122', ), 'd2.8xlarge' =>  array ( 'price' => 5.52 * $this->hourPerMonth, 'cpu' => '36', 'ram' => '244', ),
					), // End of AWS predefined
				),
			),
			'Google' => array(
				'TW' => array(
					'currency' => 'USD',
					'cpu' => 19.93,
					'ram' => 2.67,
					'hdd' => 0.04,
					'ssd' => 0.17,
					'net' => 0.12,
				),
				'US' => array(
					'currency' => 'USD',
					'cpu' => 16.95,
					'ram' => 2.35,
					'hdd' => 0.04,
					'ssd' => 0.17,
					'netFree' => 1,
					'net' => 0.12,
				),
			),
			'Tsukaeru' => array(
				'JP' => array(
					'currency' => 'JPY',
					'cpu' => 1 * $this->hourPerMonth,
					'ram' => 0.8 * $this->hourPerMonth,
					'hdd' => 0.006 * $this->hourPerMonth,
					'netFree' => 3000,
					'net' => 8,
				)
			),
			/**/
			'Linode' => array(
				'Custom' => array(
					'currency' => 'USD',
					'net' => 0.2,
					'predefined' => array (
						'Linode 1GB' =>  array ( 'price' => '5', 'cpu' => '1', 'ram' => '1', 'ssd' => '20', 'netFree' => 1000, ), 'Linode 2GB' =>  array ( 'price' => '10', 'cpu' => '1', 'ram' => '2', 'ssd' => '30', 'netFree' => 2000, ), 'Linode 4GB' =>  array ( 'price' => '20', 'cpu' => '2', 'ram' => '4', 'ssd' => '48', 'netFree' => 3000, ), 'Linode 8GB' =>  array ( 'price' => '40', 'cpu' => '4', 'ram' => '8', 'ssd' => '96', 'netFree' => 4000, ), 'Linode 12GB' =>  array ( 'price' => '80', 'cpu' => '6', 'ram' => '12', 'ssd' => '192', 'netFree' => 8000, ), 'Linode 24GB' =>  array ( 'price' => '160', 'cpu' => '8', 'ram' => '24', 'ssd' => '384', 'netFree' => 16000, ), 'Linode 48GB' =>  array ( 'price' => '320', 'cpu' => '12', 'ram' => '48', 'ssd' => '768', 'netFree' => 20000, ), 'Linode 64GB' =>  array ( 'price' => '480', 'cpu' => '16', 'ram' => '64', 'ssd' => '1152', 'netFree' => 20000, ), 'Linode 80GB' =>  array ( 'price' => '640', 'cpu' => '20', 'ram' => '80', 'ssd' => '1536', 'netFree' => 20000, ), 'Linode 16GB' =>  array ( 'price' => '60', 'cpu' => '1', 'ram' => '16', 'ssd' => '20', 'netFree' => 5000, ), 'Linode 32GB' =>  array ( 'price' => '120', 'cpu' => '2', 'ram' => '32', 'ssd' => '40', 'netFree' => 6000, ), 'Linode 60GB' =>  array ( 'price' => '240', 'cpu' => '4', 'ram' => '60', 'ssd' => '90', 'netFree' => 7000, ), 'Linode 100GB' =>  array ( 'price' => '480', 'cpu' => '8', 'ram' => '100', 'ssd' => '200', 'netFree' => 8000, ), 'Linode 200GB' =>  array ( 'price' => '960', 'cpu' => '16', 'ram' => '200', 'ssd' => '340', 'netFree' => 9000, ),
					),
				),
			),
			/**/
			'Vultr' => array(
				'Custom' => array(
					'currency' => 'USD',
					'net' => 0.025,
					'predefined' => array (
						'20GB' =>  array ( 'price' => '2.50', 'cpu' => '1', 'ram' => 0.5, 'ssd' => '20', 'netFree' => '500', ), '25GB' =>  array ( 'price' => '5', 'cpu' => '1', 'ram' => 1, 'ssd' => '25', 'netFree' => '1000', ), '40GB' =>  array ( 'price' => '10', 'cpu' => '1', 'ram' => 2, 'ssd' => '40', 'netFree' => '2000', ), '60GB' =>  array ( 'price' => '20', 'cpu' => '2', 'ram' => 4, 'ssd' => '60', 'netFree' => '3000', ), '100GB' =>  array ( 'price' => '40', 'cpu' => '4', 'ram' => 8, 'ssd' => '100', 'netFree' => '4000', ), '200GB' =>  array ( 'price' => '80', 'cpu' => '6', 'ram' => 16, 'ssd' => '200', 'netFree' => '5000', ), '300GB' =>  array ( 'price' => '160', 'cpu' => '8', 'ram' => 32, 'ssd' => '300', 'netFree' => '6000', ), '400GB' =>  array ( 'price' => '320', 'cpu' => '16', 'ram' => 64, 'ssd' => '400', 'netFree' => '10000', ),
					),
				),
			),
			/**/
		);
	}

	function __construct($hourPerMonth = 720){
		$this->hourPerMonth = $hourPerMonth;
		$this->_initiatePriceDetail();
	}

	public function getPriceList($location, $cpu, $ram, $storage, $storageType = 'hdd', $traffic = 0, $os = 'linux'){
		$priceList = array();

		foreach($this->priceDetails as $vendor => $detail){
			$priceList[$vendor] = $this->getPriceForVendor($vendor, $location, $cpu, $ram, $storage, $storageType, $traffic);
		}

		return $priceList;
	}

	public function getPriceForVendor($vendor, $location, $cpu, $ram, $storage, $storageType = 'hdd', $traffic = 0){
		$priceDetails = $this->priceDetails;
		if(!isset($priceDetails[$vendor])){
			throw new Exception("Undefined vendor : $vendor");
		}

		// TODO: Find out the best location
		$vendorDetails = $priceDetails[$vendor];
		if(isset($vendorDetails[$location])){
			// Case 1 : The target location is supported by vendor
			// Then we use it directly
			$targetLocation = $location;
		}
		else{
			// Case 2 : The target location isn't supported by vendor
			// Then we first check other regions in the same zone
		 	foreach($this->locations as $bigLocation => $subLocations){
		 		if( ($bigLocation == $location) || in_array($location, $subLocations) ){
		 			foreach($subLocations as $subLocation){
		 				if(isset($vendorDetails[$subLocation])){
		 					$targetLocation = $subLocation;
		 					break;
		 				}
		 			}
		 			break;
		 		}
		 	}

		 	// If there are no suitable region in the saem zone, then use the first one in $vendorDetails as the default choice
		 	if(!isset($targetLocation)){
		 		reset($vendorDetails);
		 		$targetLocation = key($vendorDetails);
		 	}

		}

		$detail = $vendorDetails[$targetLocation];

		$price = $this->getPrice($detail, $cpu, $ram, $storage, $storageType, $traffic);
		return array(
			'location' => $targetLocation,
			'detail' => $price,
		);
	}

	/********************
	 * Helper functions *
	 ********************/
	private function getPrice($detail, $cpu, $ram, $storage, $storageType = 'hdd', $traffic = 0){
		//----- Process the predefined machine -----//
		$numBest = $this->numSuggestedPredefined;
		$best = array(); // Storing best n choices


		//----- Case 1 : Predefined machine -----//
		if(isset($detail['predefined'])){
			// Find out the best three choices
			foreach($detail['predefined'] as $type => $candidate){
				$candRating = $this->getRating($candidate, $cpu, $ram, $storage, $storageType, $traffic); // Rating indicating how good it match the requirement. The lower rating the better.
				
				for($i = 0; $i < $numBest; $i++){
					if(!isset($best[$i])){
						$best[$i] = array_merge(array('type' => $type, 'rating' => $candRating), $candidate);
						//$bestRating[$i] = $candRating;

						break;
					}
					else if($candRating < $best[$i]['rating']){
						for($j = min(sizeof($best), $numBest - 1); $j > $i; $j--){
							$best[$j] = $best[$j - 1];
							//$bestRating[$j] = $bestRating[$j - 1];
						}

						$best[$i] = array_merge(array('type' => $type, 'rating' => $candRating), $candidate);
						//$bestRating[$i] = $candRating;

						break;
					}
				}

			}

			// Fill in the missing part in predefined (due to default value), and calucluate the correpsonding price
			foreach($best as $key => &$candidate){
				$price = $candidate['price'];
				if(!isset($candidate['cpu'])){
					if(!isset($detail['cpu'])){
						throw new Exception('Missing definition for cpu');
					}
					$candidate['cpu'] = $cpu;
					$price += $detail['cpu'] * $cpu;
				}
				if(!isset($candidate['ram'])){
					if(!isset($detail['ram'])){
						throw new Exception('Missing definition for ram');
					}
					$candidate['ram'] = $ram;
					$price += $detail['ram'] * $ram;
				}
				if(!isset($candidate['hdd']) && !isset($candidate['ssd'])){
					if(!isset($detail['hdd']) && !isset($detail['ssd'])){
						throw new Exception('Missing definition for hdd/ssd');
					}
					if($storageType == 'hdd' && isset($detail['hdd'])){
						$candidate['hdd'] = $storage;
						$price += $detail['hdd'] * $stroage;
					}
					else if($storageType = 'ssd' && isset($detail['ssd'])){
						$candidate['ssd'] = $storage;
						$price += $detail['ssd'] * $storage;
					}
					else{
						throw new Exception('Unknown Uncaught Error : missing defintion for hdd/sdd');
					}
				}
				if(isset($candidate['hdd'])){
					$candidate['storage'] = $candidate['hdd'];
					$candidate['storageType'] = 'hdd';
				}
				else if(isset($candidate['ssd'])){
					$candidate['storage'] = $candidate['ssd'];
					$candidate['storageType'] = 'ssd';
				}

				// Traffic price
				$netFree = isset($candidate['netFree'])? $candidate['netFree'] : (isset($detail['netFree'])? $detail['netFree'] : 0);
				if(!isset($candidate['net']) && !isset($detail['net'])){
					throw new Exception('Missing definition for hdd/ssd');
				}
				$net = isset($candidate['net'])? $candidate['net'] : (isset($detail['net'])? $detail['net'] : 0);
				$price += max(0, $traffic - $netFree) * $net;

				$candidate['price'] = $price * $this->exchangeRate[$detail['currency']];
			}

			return $best;
		}
		

		//----- Case 2 : Flexible machine -----//
		if( !isset($detail['currency']) || !isset($detail['cpu']) || !isset($detail['ram']) || (!isset($detail['hdd'])&&!isset($detail['ssd'])) || !isset($detail['net']) ){
			throw new Exception("Missing necessary definition for flexible machine");
		}
		// CPU & Ram
		$price = $detail['cpu'] * $cpu + $detail['ram'] * $ram;
		$spec = array(
			'cpu' => $cpu,
			'ram' => $ram,
		);
		// Stroage
		if(!isset($detail['hdd']) || !isset($detail['ssd'])){
			$finalType = isset($detail['ssd'])? 'ssd' : 'hdd';
		}
		else{
			$finalType = $storageType == 'ssd'? 'ssd' : 'hdd';
		}

		$price += $detail[$finalType] * $storage;
		$spec += array(
			$finalType => $storage,
			'storage' => $storage,
			'storageType' => $finalType,
		);

		// Network
		if(isset($detail['netFree']))
			$traffic = max(0, $traffic - $detail['netFree']);
		$price += $detail['net'] * $traffic;

		// Finally count the exchange rate
		$price *= $this->exchangeRate[$detail['currency']];

		return array_merge(array('type' => 'custom', 'price' => $price), $spec);
	}

	private function getRating($candidate, $cpu, $ram, $storage, $storageType = 'hdd', $traffic = 0){
		// The lower rating, the better
		$rating = 0;
		$noItems = 0;
		$posMatchingIndex = $this->posMatchingIndex;
		$negMatchingIndex = $this->negMatchingIndex;
		$matchingIndex = $this->matchingIndex;

		if(isset($candidate['cpu'])){
			$provided = $candidate['cpu'];

			$rating += pow(abs($provided - $cpu), ($provided > $cpu)? $posMatchingIndex : $negMatchingIndex);
			$noItems++;
		}
		if(isset($candidate['ram'])){
			$provided = $candidate['ram'];

			$rating += pow(abs($provided - $ram)/2, ($provided > $ram)? $posMatchingIndex : $negMatchingIndex);
			$noItems++;
		}
		if(isset($candidate['ssd']) || isset($candidate['hdd'])){
			if(isset($candidate['ssd'])){
				$provided = $candidate['ssd'];

				$rating += pow(abs($provided - $storage)/50, ($provided > $storage)? $posMatchingIndex : $negMatchingIndex);
			}
			else{
				$provided = $candidate['hdd'];

				$rating += pow(abs($provided - $storage)/50*($storageType=='ssd'? 2:1), ($provided > $storage)? $posMatchingIndex : $negMatchingIndex);
			}
			$noItems++;
		}

		// Note that traffic price would not be calculated here, as it is a combination of variables of "netFree" and "net".

		return $rating / $noItems;
	}
}




