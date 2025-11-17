$(document).ready(function(){
    setUserHandlers();
})


function setUserHandlers(){

    let announcesList = $('.announces-list')

    announcesList.find('li').each(function(index, value){
        let changeActive = $(value).find('.changeActiveAnnounce');
        let editForm = $(value).find('.editAnnounce');

        changeActive.on('click', function(){
            let announceId = $(this).parent().parent().attr('data-id');
            changeActiveAnnounceState(announceId);
        })

        editForm.on('click', function(){
            let announceId = $(this).parent().attr('data-id');
            editFormInformations(announceId);
        })
    })

    let announcesRequestsList = $('.announces-requests-list')

	announcesRequestsList.find('li').each(function(index, value){
		let showAnnounceRequestButton = $(value).find('.showAnnounceRequest')

		showAnnounceRequestButton.on('click', function(){
            let announceRequestId = $(this).parent().attr('data-id');
			showAnnounceRequest(announceRequestId);
		})
	})
	
}


function changeActiveAnnounceState(id){
    let path = CHANGE_ACTIVE_ANNOUNCE_PATH;

	$.post(path,{id: id}, function(response){
        if(response.success == true){
            window.location.reload()
        }
    });

}

function editFormInformations(id){
    let path = GET_INFORMATIONS_ANNONCE_PATH

    $.post(path,{id: id}, function(response){
        if(response.success == true){
            showDialog({
                title:"Votre annonce",
                text:response.data.content
            });
	        jQuery('select').formSelect();

        }
    });
}


function showAnnounceRequest(id){
	let path = GET_ANNOUNCE_REQUEST_PATH;

    $.post(path,{id: id}, function(response){
        if(response.success == true){
            showDialog({
                title:"La réponse de " + response.data.name + " à votre annonce",
                text:response.data.content
            });

        }
    });
}









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