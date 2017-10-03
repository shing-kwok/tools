var targetMatch = {
	'type' : /(\w+) SSD/,
	'cpu' : /(\d+) CPU/,
	'ram' : /(\d+)MB Memory/,
	'ssd' : /(\d+)GB SSD/,
	'netFree' :  /(\d+)GB Bandwidth/,
	'price' : /\$([0-9.]+)/,
};

var results = {};
$('.package').each(function(){
	var str = $(this).text();
	var key, data = {};

	for(k in targetMatch){
		var value = str.match(targetMatch[k])[1];
		if(k == 'type'){
			key = value;
		}
		else{
			if(k == 'ram'){
				value /= 1024;
			}

			data[k] = value;
		}
	}

	if(key)
		results[key] = data;
});

console.log(JSON.stringify(results));