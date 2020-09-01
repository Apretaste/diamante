$(document).ready(function(){
	$('.tabs').tabs();
});

function short(username) {
	return (username.length > 9) ? username.substring(0, 6) + '...' : username;
}
