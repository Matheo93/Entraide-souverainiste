//import AJS from 'ajs';

jQuery(document).ready(function(){
    jQuery('select').formSelect();


	window.addEventListener('load', () => {
		const $recaptcha = document.querySelector('#g-recaptcha-response');
		if ($recaptcha) {
			$recaptcha.setAttribute('required', 'required');
		}
	})


	if(isMobile.any()) setMobileHandlers();

});



function setAutocompleteLocation(input, sources){
	var researchInputTag = "#"+input;

	/*const autoCompleteJS = new autoComplete({
		data: {
			src: async function () {
				const data = sources;
				console.log(data)
                return data;
			},
			key: ["name"],
		},
		searchEngine: "strict",
		highlight: true,
		selector : researchInputTag,
		maxResults: 10,
		resultItem: {
			content: (data, element) => {
                console.log(data)
			},
		},
		noResults: (dataFeedback, generateList) => {
			generateList(autoCompleteJS, dataFeedback, dataFeedback.results);
			const result = document.createElement("li");
			result.setAttribute("class", "no_result");
			result.setAttribute("tabindex", "1");
			let noResultsText = getNoResults(dataFeedback.query);
			result.innerHTML = noResultsText;
			document.querySelector(`#${autoCompleteJS.resultsList.name}`).appendChild(result);
		},
		onSelection: (feedback) => {
			let selectSLUG = feedback.selection.value.slug;
			let hiddenField = $('input[type=hidden]#speciality_slug_input').attr('value', selectSLUG)
			var selectionName = feedback.selection.value.name
			document.querySelector(researchInputTag).value = selectionName;
			registerNewSpeciality(selectSLUG, selectionName);
			$(researchInputTag).val("");
			$(researchInputTag).attr("placeholder", selectionName);
		},
	});*/

	const autoCompleteJS = new autoComplete({
		data: {
		  src: async () => {
			try {
				const data = sources
				return data;
			} catch (error) {
				return error;
			}
		  },
		keys: ["name"],
		cache: true,
		selector : researchInputTag,
		},
		resultsList: {
		  element: (list, data) => {
			const info = document.createElement("p");
			if (data.results.length) {
			  info.innerHTML = `Displaying <strong>${data.results.length}</strong> out of <strong>${data.matches.length}</strong> results`;
			} else {
			  info.innerHTML = `Found <strong>${data.matches.length}</strong> matching results for <strong>"${data.query}"</strong>`;
			}
			list.prepend(info);
		  },
		  noResults: true,
		  maxResults: 15,
		  tabSelect: true,
		},
		resultItem: {
		  element: (item, data) => {
			// Modify Results Item Style
			item.style = "display: flex; justify-content: space-between;";
			// Modify Results Item Content
			item.innerHTML = `
			<span style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
			  ${data.match}
			</span>
			<span style="display: flex; align-items: center; font-size: 13px; font-weight: 100; text-transform: uppercase; color: rgba(0,0,0,.2);">
			  ${data.key}
			</span>`;
		  },
		  highlight: true,
		},
		events: {
		  input: {
			focus() {
			  if (autoCompleteJS.input.value.length) autoCompleteJS.start();
			},
			selection(event) {
			  const feedback = event.detail;
			  autoCompleteJS.input.blur();
			  // Prepare User's Selected Value
			  const selection = feedback.selection.value[feedback.selection.key];
			  // Render selected choice to selection div
			  document.querySelector(".selection").innerHTML = selection;
			  // Replace Input value with the selected value
			  autoCompleteJS.input.value = selection;
			  // Console log autoComplete data feedback
			  console.log(feedback);
			},
		  },
		},
	  });
}



var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i) || navigator.userAgent.match(/WPDesktop/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

function setMobileHandlers(){
    menuMake();
	$('#menu-button').click(function(){
		var ul  = $(this).parent().find('.menu-header-container > ul')
		if(ul.hasClass('open')){
			$(this).animate({'transition': '900ms 175ms cubic-bezier(.6,-0.46,.21,1.3)','transform': 'rotate(-270deg)'},900)
			ul.css({'position': 'absolute', 'z-index': '99999999999'})
		}
	})
}

function menuMake(){
	let title ='<svg id="i1" class="icon" viewBox="0 0 100 100"><path id="top-line-1" d="M30,37 L70,37"></path><path id="middle-line-1" d="M30,50 L70,50 Z" style="opacity: 1;"></path><path id="bottom-line-1" d="M30,63 L70,63"></path></svg><p>MENU</p>'

	var cssmenu = $("#header-mobile");
	var settings = {
		title: title,
		format: "multitoggle",
		sticky: false
	  };

		//cssmenu.prepend('<div id="menu-button">' + settings.title + '</div>');
		cssmenu.find("#menu-button").on('click', function(){
			
		  $(this).toggleClass('menu-opened');
		  var mainmenu = $("#header-mobile-menu .menu-header-container > ul");
		  if (mainmenu.hasClass('open')) { 
			mainmenu.slideUp('slow').removeClass('open');
		  }
		  else {
			mainmenu.slideDown("fast").addClass('open');
			if (settings.format === "dropdown") {
			  mainmenu.find('ul').slideDown('slow');
			}
		  }
		});

		cssmenu.find('li ul').parent().addClass('has-sub');

		multiTg = function() {
		  cssmenu.find(".has-sub").prepend('<span class="submenu-button"></span>');
		  cssmenu.find('.submenu-button').on('click', function() {
			$(this).toggleClass('submenu-opened');
			if ($(this).siblings('ul').hasClass('open')) {
			  $(this).siblings('ul').removeClass('open').hide();
			}
			else {
			  $(this).siblings('ul').addClass('open').slideDown("slow");
			}
		  });
		};

		if (settings.format === 'multitoggle') multiTg();
		else cssmenu.addClass('dropdown');

		if (settings.sticky === true) cssmenu.css('position', 'fixed');

		resizeFix = function() {
		  if ($( window ).width() > 768) {
			cssmenu.find('ul').show();
		  }

		  if ($(window).width() <= 768) {
			cssmenu.find('ul').hide().removeClass('open');
		  }
		};
		resizeFix();
		return $(window).on('resize', resizeFix);

  }
  
  function menuDisappearAnimation_1() {
	  currentFrame_1++;
	  if ( currentFrame_1 <= menuDisappearDurationInFrames_1 ) {
		  window.requestAnimationFrame( ()=> { 
			  //top line
			  topLineY_1 = AJS.easeInBack( 37, 50, menuDisappearDurationInFrames_1, currentFrame_1 );
			  topLine_1.setAttribute( "d", "M30,"+topLineY_1+" L70,"+topLineY_1 );
			  //bottom line
			  bottomLineY_1 = AJS.easeInBack( 63, 50, menuDisappearDurationInFrames_1, currentFrame_1 );
			  bottomLine_1.setAttribute( "d", "M30,"+bottomLineY_1+" L70,"+bottomLineY_1 );
			  //recursion
			  menuDisappearAnimation_1();
		  });
	  } else {
		  middleLine_1.style.opacity = "0";
		  currentFrame_1 = 1;
		  menuDisappearComplete_1 = true;
		  openMenuAnimation_1();
	  }
  }
  
  ///Cross Appear
  function arrowAppearAnimation_1() {
	  currentFrame_1++;
	  if ( currentFrame_1 <= arrowAppearDurationInFrames_1 ) {
		  window.requestAnimationFrame( ()=> { 
			  //top line
			  topLeftX_1 = AJS.easeOutBack( 30, 35, arrowAppearDurationInFrames_1, currentFrame_1 );
			  topLeftY_1 = AJS.easeOutBack( 50, 35, arrowAppearDurationInFrames_1, currentFrame_1 );
			  bottomRightX_1 = AJS.easeOutBack( 70, 65, arrowAppearDurationInFrames_1, currentFrame_1 );
			  bottomRightY_1 = AJS.easeOutBack( 50, 65, arrowAppearDurationInFrames_1, currentFrame_1 );
			  topLine_1.setAttribute( "d", "M" + topLeftX_1 + "," + topLeftY_1 + " L" + bottomRightX_1 + "," + bottomRightY_1 );
			  //bottom line
			  bottomLeftX_1 = AJS.easeOutBack( 30, 35, arrowAppearDurationInFrames_1, currentFrame_1 );
			  bottomLeftY_1 = AJS.easeOutBack( 50, 65, arrowAppearDurationInFrames_1, currentFrame_1 );
			  topRightX_1 = AJS.easeOutBack( 70, 65, arrowAppearDurationInFrames_1, currentFrame_1 );
			  topRightY_1 = AJS.easeOutBack( 50, 35, arrowAppearDurationInFrames_1, currentFrame_1 );
			  bottomLine_1.setAttribute( "d", "M" + bottomLeftX_1 + "," + bottomLeftY_1 + " L" + topRightX_1 + "," + topRightY_1 );
			  //recursion
			  arrowAppearAnimation_1();
		  });
	  } else {
		  currentFrame_1 = 1;
		  arrowAppearComplete_1 = true;
		  openMenuAnimation_1();
	  }
  }
  
  ///Combined Open Menu Animation
  function openMenuAnimation_1() {
	  if ( !menuDisappearComplete_1 ) { 
		  menuDisappearAnimation_1();
	  } else if ( !arrowAppearComplete_1) {
		  arrowAppearAnimation_1();
	  }
  }
  
  ///Cross Disappear
  function arrowDisappearAnimation_1() {
	  currentFrame_1++;
	  if ( currentFrame_1 <= arrowDisappearDurationInFrames_1 ) {
		  window.requestAnimationFrame( ()=> {
			  //top line
			  topLeftX_1 = AJS.easeInBack( 35, 30, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  topLeftY_1 = AJS.easeInBack( 35, 50, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  bottomRightX_1 = AJS.easeInBack( 65, 70, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  bottomRightY_1 = AJS.easeInBack( 65, 50, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  topLine_1.setAttribute( "d", "M" + topLeftX_1 + "," + topLeftY_1 + " L" + bottomRightX_1 + "," + bottomRightY_1 );
			  //bottom line
			  bottomLeftX_1 = AJS.easeInBack( 35, 30, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  bottomLeftY_1 = AJS.easeInBack( 65, 50, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  topRightX_1 = AJS.easeInBack( 65, 70, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  topRightY_1 = AJS.easeInBack( 35, 50, arrowDisappearDurationInFrames_1, currentFrame_1 );
			  bottomLine_1.setAttribute( "d", "M" + bottomLeftX_1 + "," + bottomLeftY_1 + " L" + topRightX_1 + "," + topRightY_1 );
			  //recursion
			  arrowDisappearAnimation_1();
		  });
	  } else {
		  middleLine_1.style.opacity = "1";
		  currentFrame_1 = 1;
		  arrowDisappearComplete_1 = true;
		  closeMenuAnimation_1();
	  }
  }
  
  ///Menu Appear
  function menuAppearAnimation_1() {
	  currentFrame_1++;
	  if ( currentFrame_1 <= menuAppearDurationInFrames_1 ) {
		  window.requestAnimationFrame( ()=> {
			  //top line
			  topLineY_1 = AJS.easeOutBack( 50, 37, menuDisappearDurationInFrames_1, currentFrame_1 );
			  topLine_1.setAttribute( "d", "M30,"+topLineY_1+" L70,"+topLineY_1 );
			  //bottom line
			  bottomLineY_1 = AJS.easeOutBack( 50, 63, menuDisappearDurationInFrames_1, currentFrame_1 );
			  bottomLine_1.setAttribute( "d", "M30,"+bottomLineY_1+" L70,"+bottomLineY_1 );
			  //recursion
			  menuAppearAnimation_1();
		  });
	  } else {
		  currentFrame_1 = 1;
		  menuAppearComplete_1 = true;
		  closeMenuAnimation_1();
	  }
  }
  
  ///Close Menu Animation
  function closeMenuAnimation_1() {
	  if ( !arrowDisappearComplete_1 ) {
		  arrowDisappearAnimation_1();
	  } else if ( !menuAppearComplete_1 ) {
		  menuAppearAnimation_1();
	  }
  }
  
  function setVars(){
	  var icon_1 = document.getElementById("menu-button");
	  var topLine_1 = document.getElementById("top-line-1");
	  var middleLine_1 = document.getElementById("middle-line-1");
	  var bottomLine_1 = document.getElementById("bottom-line-1");
	  var state_1 = "menu";
	  var topLineY_1;
	  var middleLineY_1;
	  var bottomLineY_1;
	  var topLeftY_1;
	  var topRightY_1;
	  var bottomLeftY_1;
	  var bottomRightY_1;
	  var topLeftX_1;
	  var topRightX_1;
	  var bottomLeftX_1;
	  var bottomRightX_1;
  
	  ///Animation Variables
	  var segmentDuration_1 = 15;
	  var menuDisappearDurationInFrames_1 = segmentDuration_1;
	  var arrowAppearDurationInFrames_1 = segmentDuration_1;
	  var arrowDisappearDurationInFrames_1 = segmentDuration_1;
	  var menuAppearDurationInFrames_1 = segmentDuration_1;
	  var menuDisappearComplete_1 = false;
	  var arrowAppearComplete_1 = false;
	  var arrowDisappearComplete_1 = false;
	  var menuAppearComplete_1 = false;
	  var currentFrame_1 = 1;
  }
  
  
		  var icon_1 = document.getElementById("menu-button");
		  var topLine_1 = document.getElementById("top-line-1");
		  var middleLine_1 = document.getElementById("middle-line-1");
		  var bottomLine_1 = document.getElementById("bottom-line-1");
	  setTimeout(function(){topLine_1 = jQuery('#top-line-1')[0]}, 100)
		  var state_1 = "menu";  // can be "menu" or "arrow"
		  var topLineY_1;
		  var middleLineY_1;
		  var bottomLineY_1;
		  var topLeftY_1;
		  var topRightY_1;
		  var bottomLeftY_1;
		  var bottomRightY_1;
		  var topLeftX_1;
		  var topRightX_1;
		  var bottomLeftX_1;
		  var bottomRightX_1;
  
		  ///Animation Variables
		  var segmentDuration_1 = 15;
		  var menuDisappearDurationInFrames_1 = segmentDuration_1;
		  var arrowAppearDurationInFrames_1 = segmentDuration_1;
		  var arrowDisappearDurationInFrames_1 = segmentDuration_1;
		  var menuAppearDurationInFrames_1 = segmentDuration_1;
		  var menuDisappearComplete_1 = false;
		  var arrowAppearComplete_1 = false;
		  var arrowDisappearComplete_1 = false;
		  var menuAppearComplete_1 = false;
		  var currentFrame_1 = 1;
  
  
  ///Events
  setTimeout(function(){
  
	  topLine_1 = document.getElementById("top-line-1");
	  middleLine_1 = document.getElementById("middle-line-1");
	  bottomLine_1 = document.getElementById("bottom-line-1");
	  jQuery('#menu-button').click(function(){
		//setVars();
		  if ( state_1 === "menu" ) {
			  openMenuAnimation_1();
			  state_1 = "arrow";
			  arrowDisappearComplete_1 = false;
			  menuAppearComplete_1 = false;
		  } else if ( state_1 === "arrow" ) {
			  closeMenuAnimation_1();
			  state_1 = "menu";
			  menuDisappearComplete_1 = false;
			  arrowAppearComplete_1 = false;
		  }
	  })
  }, 500);
  
