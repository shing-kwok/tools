var targetCol = {
	2 : 'ram',
	3 : 'cpu',
	4 : 'ssd',
	5 : 'netFree',
	8 : 'price'
};

var results = {};
$('table.table-pricing').each(function(){
	$(this).find('tr').each(function(){
		var key, data = {};
		var index = 1;
		$(this).find('td').each(function(){
			$td = $(this);
			var value = $td.text();

			if(index == 1){
				key = value;
			}
			else{
				if(typeof targetCol[index] !== 'undefined'){
					var spacePos = value.indexOf(' ');
					if(index <=5)
						value = spacePos >= 0? value.substr(0, spacePos) : value;
					if(index == 5)
						value *= 1000;
					if(index == 8)
						value = spacePos >= 0? value.substr(1, spacePos-1) : value;

					data[targetCol[index]] = value;
				}
			}
			index++;
		});

		if(key)
			results[key] = data;
	});
});

console.log(JSON.stringify(results));