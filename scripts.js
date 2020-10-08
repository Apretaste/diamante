// start tabs and models
//
$(document).ready(function(){
	$('.tabs').tabs();
	$('.modal').modal();
});

// shorten the username
//
function short(username) {
	return (username.length > 9) ? username.substring(0, 6) + '...' : username;
}

// send a new chat
//
function chat() {
	// get message and user features
	var message = $('#message').val().trim();
	var username = $('#message').attr('username');
	var gender = $('#message').attr('gender');
	var avatar = $('#message').attr('avatar');
	var avatarColor = $('#message').attr('avatarColor');

	// do not allow short messages
	if (message.length < 2 || message.length > 200) {
		M.toast({html: 'Debe escribir un texto'});
		return false;
	}

	// send the chat
	apretaste.send({
		command: 'DIAMANTE ESCRIBIR',
		data: {message: message},
		redirect: false
	});

	// append the bubble to teh screen
	$('#chat').append(
		'<li id="last" class="right">' +
		'	<div class="person-avatar circle" face="'+ avatar +'" color="'+ avatarColor +'" size="30"></div>' +
		'	<div class="head">' +
		'		<a href="#!" class="' + gender + '">@' + username + '</a>' +
		'		<span class="date">' +  moment().format('MMM D, YYYY h:mm A') + '</span>' +
		'	</div>' +
		'	<span class="text">' + message + '</span>' +
		'</li>');

	// re-create avatar
	setElementAsAvatar($('#last .person-avatar').get());

	// clean the chat field
	$('#message').val('');

	// scroll to the lastest chat
	$('html, body').animate({
		scrollTop: $("#last").offset().top
	}, 1000);
}

// calculate remaining characteres
//
function checkLength(size=200) {
	var message = $('#message').val().trim();
	if (message.length <= size) $('.helper-text').html('Restante: ' + (size - message.length));
	else {
		message = message.substring(0, size);
		$('#message').val(message);
		$('.helper-text').html('Límite excedido');
	}
}
