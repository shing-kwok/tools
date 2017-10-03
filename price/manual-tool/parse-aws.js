var targetCol = {
	2 : 'cpu',
	4 : 'ram',
	6 : 'price'
};

var results = {};
$('table.tan-table:visible tr').each(function(){
	var key, data = {};
	var index = 1;
	$(this).find('td').each(function(){
		$td = $(this);
		var value = $td.html();

		if(index == 1){
			key = value;
		}
		else{
			
			if(typeof targetCol[index] !== 'undefined'){
				var spacePos = value.indexOf(' ');
				data[targetCol[index]] = spacePos >= 0? value.substr(1, spacePos-1) : value;
			}
		}
		index++;
	});

	if(key)
		results[key] = data;
});
console.log(JSON.stringify(results));