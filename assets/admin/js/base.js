$(document).ready(function(){
	$('.sidenav').sidenav();
	
	if($('.table-search')[0]) setSearchHandlers();
	if($('.table-container')[0]) setTableHandlers();

	jQuery('select').formSelect();
  });

function setTableHandlers(){
	makeAllSortable();
}

function setSearchHandlers(){
	$('.table-search input[type=text]').on('keyup', function(){
		let colNumber = $(this).attr('colNumber');
		let thSortable = $('.table-container table thead th.sortable');
		var trValuables = [];

		$.each(thSortable, function(index, value){
			colNumber = $(value).index()
			colValuables = filterTable($('.table-search input[type=text]')[0], $('.table-container table')[0], colNumber);
			trValuables = trValuables.concat(colValuables)
		});

		$('.table-container table tbody tr').css('display', 'none');
		$.each(trValuables, function(index, value){
			$('.table-container table tbody tr:nth-child('+value+')').css('display', 'table-row')
		});
	});
}



function sortTable(table, col, reverse) {
	var tb = table.tBodies[0],
	tr = Array.prototype.slice.call(tb.rows, 0),
	i;
	reverse = -((+reverse) || -1);
	tr = tr.sort(function (a, b) {
		return reverse
		* (a.cells[col].textContent.trim()
		.localeCompare(b.cells[col].textContent.trim())
		);
	});
	for(i = 0; i < tr.length; ++i) tb.appendChild(tr[i]);
}

function makeSortable(table) {
	var th = table.tHead, i;
	th && (th = th.rows[0]) && (th = th.cells);
	if (th) i = th.length;
	else return;
	while (--i >= 0) (function (i) {
		var dir = 1;
		// i just added +1 for 0 - 1 selector
		//th[i].addEventListener('click', function () {sortTable(table, i+1, (dir = 1 - dir))});
		th[i].addEventListener('click', function () {sortTable(table, i, (dir = 1 - dir))});
	}(i));
}

function makeAllSortable(parent) {
	parent = parent || document.body;
	var t = $('.table-container > table'), i = t.length;
	while (--i >= 0) makeSortable(t[i]);
}




function filterTable(input, table, colNumber) {
	var filter, tr, td, i, txtValue, trValuables;
	filter = input.value.toUpperCase();
	tr = table.getElementsByTagName("tr");
	trValuables = [];
	for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[colNumber];
		if (td) {
			txtValue = td.textContent || td.innerText;
			if (txtValue.toUpperCase().indexOf(filter) > -1) {
				//tr[i].style.display = "";
				if(trValuables.indexOf(i) == -1) trValuables.push(i);
			} else {
				//tr[i].style.display = "none";
			}
		}
	}

	return trValuables;
}


