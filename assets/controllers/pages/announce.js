$(document).ready(function(){
    let askForContact = $('.annonce-contact a');

    let emailRow = " ";
    if(isEmailAvailable != false) emailRow = " "
    else emailRow = "<div class='form-row'><label>Email : </label> <input required type='text' name='email' placeholder='Email'></div>"

    let form = "<form method='post' action='"+ANNOUNCE_CONTACT_PATH+"'> ".concat(emailRow)
    .concat(
    "<div class='form-row'><label>Nom : </label> <input required type='text' name='name' placeholder='Nom'></div>" +
    "<div class='form-row'><label>Prénom : </label> <input required type='text' name='firstname' placeholder='Prénom'></div>" +
    "<div class='form-row'><label>Message : </label> <textarea name='message'></textarea></div>" +
    "<div class='form-submit'><button type='submit'><span class='material-symbols-outlined'>mail</span>Envoyer</button></div>" +
"</form>");
    askForContact.on('click', function(){

        showDialog({
            title: "Établir le contact avec le rédacteur de cette annonce",
            text: form
        });
    })
})




function disableScrolling(){
	var x=window.scrollX;
	var y=window.scrollY;
	window.onscroll=function(){window.scrollTo(x, y);};
}

function enableScrolling(){
	window.onscroll=function(){};
}


function showDialog(options) {

	options = $.extend({
		id: 'dialog',
		title: null,
		text: null,
		negative: false,
		positive: false,
		cancelable: true,
		contentStyle: null,
		onLoaded: false
	}, options);

	// remove existing dialogs
	$('.dialog-container').remove();
	$(document).unbind("keyup.dialog");

	$('<div id="' + options.id + '" class="dialog-container"><div class="mdl-card mdl-shadow--16dp"></div></div>').prependTo("body");
	var dialog = $('#dialog');
	var content = dialog.find('.mdl-card');
	if (options.contentStyle != null) content.css(options.contentStyle);
	if (options.title != null) {
		$('<h5>' + options.title + '</h5>').appendTo(content);
	}
	if (options.text != null) {
		$('<p>' + options.text + '</p>').appendTo(content);
	}
	if (options.negative || options.positive) {
		var buttonBar = $('<div class="mdl-card__actions dialog-button-bar"></div>');
		if (options.negative) {
			options.negative = $.extend({
				id: 'negative',
				title: 'Annuler',
				onClick: function () {
					return false;
				}
			}, options.negative);
			var negButton = $('<button class="mdl-button mdl-js-button mdl-js-ripple-effect" id="' + options.negative.id + '">' + options.negative.title + '</button>');
			negButton.click(function (e) {
				e.preventDefault();
				if (!options.negative.onClick(e))
					hideDialog(dialog)
			});
			negButton.appendTo(buttonBar);
		}
		if (options.positive) {
			options.positive = $.extend({
				id: 'positive',
				title: 'Confirmer',
				onClick: function () {
					return false;
				}
			}, options.positive);
			var posButton = $('<button class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" id="' + options.positive.id + '">' + options.positive.title + '</button>');
			posButton.click(function (e) {
				e.preventDefault();
				if (!options.positive.onClick(e))
					hideDialog(dialog)
			});
			posButton.appendTo(buttonBar);
		}
		buttonBar.appendTo(content);
	}
	//componentHandler.upgradeDom();
	if (options.cancelable) {
		dialog.click(function () {
			hideDialog(dialog);
		});
		$(document).bind("keyup.dialog", function (e) {
			if (e.which == 27)
				hideDialog(dialog);
		});
		content.click(function (e) {
			e.stopPropagation();
		});
	}
	setTimeout(function () {
		dialog.css({opacity: 1});
		if (options.onLoaded)
			options.onLoaded();
	}, 1);

	disableScrolling();
	if($(dialog).find('.close')[0]) $(dialog).find('.close').click(function(){hideDialog(dialog)})
}

function hideDialog(dialog) {
	$(document).unbind("keyup.dialog");
	$(dialog).css({opacity: 0});
	setTimeout(function () {
		$(dialog).remove();
	}, 400);
	enableScrolling();
}

function getDialog(){
	return $('.dialog-container');
}