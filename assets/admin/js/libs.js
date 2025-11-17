


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
