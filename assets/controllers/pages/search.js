
$(document).ready(function(){
    setAutocompleteLocation('searchFormLocalisation', locationSources);
 })


 function setAutocompleteLocation(input, sources){
	var researchInputTag = "#"+input;


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

		},
        cache: true,
		selector : researchInputTag,
		resultsList: {
		  element: (list, data) => {
		  },
		  noResults: true,
		  maxResults: 15,
		  tabSelect: true,
		},
		resultItem: {
		  element: (item, data) => {
			item.innerHTML = `
			<span class='match'>
			  ${data.match}
			</span>
			<span class='code'>
			  ${data.value.code}
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
              let selectSLUG = feedback.selection.value.slug;
              var selectionName = feedback.selection.value.name
              var selectionCode = feedback.selection.value.code
              $(researchInputTag).val(selectionName)
              $(researchInputTag).attr("placeholder", selectionName);
			},
		  },
		},
	  });
}
