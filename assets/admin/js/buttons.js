$(window).on('load', function(){
	setButtonsHandlers();
});
setButtonsHandlers();


function setButtonsHandlers(){
	let listButtons = ['add-mail', 'emailToVerify', 'getMailPreview', "export_table_users", 'export_table_announces', 'export_table_announces_requests', 'seeAnnouncesRequests', "disableAdmin"];


	$.each(listButtons, function(index, value){
		if(!$('#'+value)[0] && !$('.'+value)[0]) return;

		let selector = !$('#'+value)[0] ? $('.'+value) : $('#'+value);
		console.log(selector)

		selector.on('click', function(){
			let type = value;
			switch(true){
				case type == "add-mail":
					addMail();
					break;
				case type == 'emailToVerify':
					userEmailToVerify(this);
					break;
				case type == 'getMailPreview':
					getMailPreview(this);
					break;
				case type == 'export_table_users':
					exportTableUsers(this);
					break;
				case type == 'export_table_announces':
					exportTableAnnounces(this);
					break;
				case type == 'export_table_announces_requests':
					exportTableAnnouncesRequests(this);
					break;
				case type == 'disableAdmin':
					disableAdmin(this);
					break;
				case type == 'seeAnnouncesRequests':
					seeAnnounceRequestInfos(this);
					break;
			}

		})
	});
};


function addMail(){
	let getMailFormPath = ADD_MAIL_FORM_PATH;
	$.post(getMailFormPath,{}, function(response){
		if(response.success == true){
			let content = response.model
			showDialog({
				title: "Envoyer un nouveau mail",
				text: content
			});
			CKEDITOR.replace('admin_mails_content');
		}
	});
}




function userEmailToVerify(el){
	let id = $(el).parent().attr('user-id');
	let name = $(el).parent().find('.name a').text();
	let isVerified = $(el).attr('isVerified');
	if(isVerified == "true"){
		showDialog({
			title: "Désactiver l'email de "+name,
			text: "",
			positive: {title: "Désactiver", onClick: function (e) {unapprouveUserEmail(id);}},
			negative: {title: "Annuler", onClick: function (e) {}},
		});
	}
	else{
		
		showDialog({
			title: "Vérifier l'email de "+name,
			text: "",
			positive: {title: "Vérifier", onClick: function (e) {approuveUserEmail(id);}},
			negative: {title: "Annuler", onClick: function (e) {}},
		});
	}
	
}

function approuveUserEmail(id){
	let path = APROUVE_USER_EMAIL;
	$.post(path,{id: id}, function(response){
		if(response.success == true) addFlash('success', 'Email vérifié')
	});
}

function unapprouveUserEmail(id){
	let path = UNAPROUVE_USER_EMAIL;
	$.post(path,{id: id}, function(response){
		if(response.success == true) addFlash('success', 'Email désapprouvé')
	});
}


function getMailPreview(el){
	let path = GET_MAIL_PREVIEW;
	let id = $(el).parent().parent().attr('mail-id');

	$.post(path,{id: id}, function(response){
		let preview = response.model;
		showDialog({
			title: "",
			text: preview,
		});
	});
}



function exportTableUsers(el){
	let path = EXPORT_USER_TABLE;
	$.post(path,{}, function(response){
		if(response.success == true){
			addFlash('success', 'Exportation de la liste des utilisateurs');
			window.location.href = window.location.protocol + "//" + window.location.host + "/" + response.data.name
		}
	});
}

function exportTableAnnounces(el){
	let path = EXPORT_ANNOUNCES_TABLE;
	$.post(path,{}, function(response){
		if(response.success == true){
			addFlash('success', 'Exportation de la liste des annonces');
			window.location.href = window.location.protocol + "//" + window.location.host + "/" + response.data.name
		}
	});
}



function exportTableAnnouncesRequests(el){
	let path = EXPORT_ANNOUNCES_REQUESTS_TABLE;
	$.post(path,{}, function(response){
		if(response.success == true){
			addFlash('success', 'Exportation de la liste des réponses aux annonces');
			window.location.href = window.location.protocol + "//" + window.location.host + "/" + response.data.name
		}
	});
}

function disableAdmin(el){

	let name = $(el).parent().find('> a').text();
	showDialog({
		title: "Retirer les droits d'Administration à " + name,
		text: "Êtes-vous sûr de vouloir retirer les droits d'Administration",
		positive: {title: "Retirer les droits", onClick: function (e) {disableAdminUser(el);}},
		negative: {title: "Annuler", onClick: function (e) {}},
	});

	
}

function disableAdminUser(el){
	let dataId = $(el).attr('data-id');
	let path = DISABLE_ADMIN_PATH;
	$.post(path,{id: dataId}, function(response){
		if(response.success == true){
			addFlash('success', 'Administrateur désactivé');
			$(el).remove();
		}
	});
}


function seeAnnounceRequestInfos(el){
	let dataId = $(el).attr('announceRequestId');
	let path = GET_ANNOUNCE_REQUEST_INFOS;
	console.log('est')
	$.post(path,{id: dataId}, function(response){
		if(response.success == true){
			let title = response.data.title;
			let name = response.data.name;
			let email = response.data.email;
			let firstname = response.data.firstname;
			let message = response.data.message;

			let model = `
				<form>
					<div class='form-row'><label class='active'>Email : </label> <input required='' disabled type='text' name='email' placeholder='Email' value='`+email+`'></div>
					<div class='form-row'><label class='active'>Nom : </label> <input required='' disabled type='text' name='name' placeholder='Nom' value='`+name+`'></div>
					<div class='form-row'><label class='active'>Prénom : </label> <input required='' disabled type='text' name='firstname' placeholder='Prénom' value='`+firstname+`'></div>
					<div class='form-row'><label class='active'>Message : </label> <textarea disabled>`+message+`</textarea></div>
				</form>
			`;
			showDialog({
				title: "Informations concernant la réponse à l'annonce " + title,
				text: model
			});
		}
	});
}





function addFlash(type, message){
	let model = `<div class='flash type-`+type+`'>`+message+`</div>`;
	if(!jQuery('.flashes-content')[0]) jQuery('#header').after('<div class="flashes-content"></div>');
	let container = jQuery('.flashes-content');

	container.append(model)
	let last = container.find('.flash').last()
	setFlashesPosition();
	setTimeout(function(){
		removeFlash(last);
	},5000)
}

function removeFlash(el){
	jQuery(el).slideUp(400, function(){
		jQuery(el).remove();
	});
}

function setFlashesHandlers(){
	jQuery('.flash').click(function(){
		removeFlash(this);
	});
	let defaultTimer = 2500;
	$('.flash').each(function(index, value){
		defaultTimer += 1500;
		let el = value;
		setTimeout(function(){
			el.remove();
		}, defaultTimer, el)
	})
	setFlashesPosition();
}

function setFlashesPosition(){
	let content = jQuery('.flashes-content');
	let flashes = content.find(' > .flash');

	let header = jQuery('#header');
	let topScroll = 0;
	if(header[0]){
		let headerH = header[0].getBoundingClientRect().height;
		topScroll = (headerH+10);
	} 
	
	let windowW = jQuery(window).width();
	let contentW = content[0].getBoundingClientRect().width;
	let flashesPosition = (windowW - contentW)/2;

	content.css({
		"top" : topScroll+"px",
		"left": flashesPosition+"px"
	});
}



function setAnchorsHandlers(){
	let headerHeight = $('#header')[0].getBoundingClientRect().height;
	$('.anchor').css('bottom', headerHeight+'px')
	$('a').on('click', function(e){
		let currentTarget = e.currentTarget;
		let pathTo = $(currentTarget).attr('href');
		if(pathTo == undefined) return;
		if(pathTo.slice(0,'1') == "#"){
			e.preventDefault();
			$('html, body').animate({
				scrollTop: $(pathTo).offset().top
			}, 500);
		}
	})
}







function px2em(elem) {
	var W = window,
		D = document;
	if (!elem || elem.parentNode.tagName.toLowerCase() == 'body') {
		return false;
	}
	else {
		var parentFontSize = parseInt(W.getComputedStyle(elem.parentNode, null).fontSize, 10),
			elemFontSize = parseInt(W.getComputedStyle(elem, null).fontSize, 10);

		var pxInEms = Math.floor((elemFontSize / parentFontSize) * 100) / 100;
		elem.style.fontSize = pxInEms + 'em';
	}
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

