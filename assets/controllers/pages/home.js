
$(document).ready(function(){

   /* let inputLocation = $('#search_location');
    inputLocation.on('focus', function(){
        let results = $('.location-results')
        results.addClass('show');
    })
    inputLocation.focusout(function(){
        let results = $('.location-results')
        results.removeClass('show');
    })
*/

    let inputCategory = $('#search_category');
    inputCategory.on('focus', function(){
        let results = $('.category-results')
        results.addClass('show');
    })
    inputCategory.focusout(function(){
        let results = $('.category-results')
        results.removeClass('show');
    })

    setAutocompleteLocation('search_location', locationSources);
})



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
