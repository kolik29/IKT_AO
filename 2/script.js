$(document).ready(function() {
	$('#selectVillage').load('CSVtoJSON.php');

	$('#selectVillage').change(function() {
		var valueTransfer = '&getVillageName='
			 + (($(this).children('option:selected').text() == 'Все *')? '' : $(this).children('option:selected').text());
		$('#selectStreet').load('CSVtoJSON.php', valueTransfer);

		if ($(this).children('option:selected').text() == 'Все *') {
			$('#selectStreet').attr('disabled', true);
		}
		else{
			$('#selectStreet').attr('disabled', false);
		}
	});

	$('#selectStreet').change(function() {
		var valueTransfer = '&getVillageName='
			 + (($('#selectVillage').children('option:selected').text() == 'Все *')? '' : $('#selectVillage').children('option:selected').text())
			 + '&getStreetName='
			 + (($(this).children('option:selected').text() == 'Все *')? '' : $(this).children('option:selected').text());

		$.ajax({
    		url: 'CSVtoJSON.php',
    		data: valueTransfer
		});
	});
});