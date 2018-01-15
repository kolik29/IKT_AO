$(document).ready(function() {
	$('select').change(function() {
		$(this).css('color', '#000');
	});

	$(".phone").mask("+7 (999) 999-99-99");
});