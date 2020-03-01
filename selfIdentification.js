/*jslint browser: true, devel: true, for: true, long: true, single: true, white: true */

// Function to facilitate self-identification features
var selfIdentificationHandler = function (selfIdentification, selfIdentificationStatus)
{
	// Define a function to disable form elements; see: https://stackoverflow.com/a/23428851/180733
	var disableFormElements = function (className, isEnabled, showWarning)
	{
		// Get the select box(es) in this table
		var selects = document.querySelectorAll ('table.' + className + ' select');
		
		// If disabling, state this to the user
		if (!isEnabled && showWarning) {
			var hasSelection = false;
			var n;
			for (n = 0; n < selects.length; n++) {
				if (selects[n].value != '0') {
					hasSelection = true;
				}
			}
			if (hasSelection) {
				alert ('As you have unticked the box, the selection(s) you have made for this post will now be removed.');
			}
		}
		
		// Loop through each select in the table
		var options;
		var i;
		var j;
		for (i = 0; i < selects.length; i++) {
			
			// Reset the value to default
			selects[i].value = '0';
			
			// Select all non-default options to disabled; this has to be done at this level to ensure the select itself is submitted
			options = selects[i].querySelectorAll ('option');
			for (j = 0; j < options.length; j++) {
				if (j != 0) {
					options[j].disabled = (!isEnabled);
				}
			}
		}
	};
	
	// Define a function for the checkbox handler
	var onCheckboxChange = function (e)
	{
		var thisCheckbox = e.target;
		
		// Dim out / light up the response table for this vote
		var opacity = (thisCheckbox.checked ? 1 : 0.25);
		document.querySelector ('table.' + thisCheckbox.value).style.opacity = opacity;
		
		// Disable/enable the form elements
		disableFormElements (thisCheckbox.value, thisCheckbox.checked, true);
	};
	
	// Function to alert the user about values clearance for a post
	var onTableClick = function (vote)
	{
		//alert ('The selections are disabled, as you first need to declare that you wish to vote for this post.');
		var relatedCheckbox = document.querySelector ('input#selfidentify' + vote);
		if (!relatedCheckbox.checked) {
			var paragraph = document.querySelector ('p.selfidentify' + vote);
			paragraph.classList.add ('flash');
		}
	};
	
	// Loop through each vote
	var vote;
	var label;
	var textCheckbox;
	var textCheckboxContent;
	var table;
	for (vote in selfIdentification) {
		if (selfIdentification.hasOwnProperty (vote)) {
			label = selfIdentification[vote];
			
			// Define text and checkbox
			textCheckbox = document.createElement ('div');
			textCheckboxContent  = '<p>We ask that only those who self-define as <strong>' + label + '</strong> vote for this position.</p>';
			textCheckboxContent += '<p class="selfidentify' + vote + '"><label><input type="checkbox" id="selfidentify' + vote + '" name="selfidentify' + vote + '" value="v' + vote + '"' + (selfIdentificationStatus[vote] ? ' checked="checked"' : '') + '>I wish to vote for this position.</p>';
			textCheckbox.innerHTML = textCheckboxContent;
			
			// Add text and checkbox before table
			table = document.querySelector ('table.v' + vote);
			table.parentNode.insertBefore (textCheckbox, table);
			
			// On initial page load, fade out this table by default and disable its controls
			if (!selfIdentificationStatus[vote]) {
				disableFormElements ('v' + vote, false, false);
				table.style.opacity = '0.25';
			}
			
			// Register the event handler for this checkbox
			document.querySelector ('#selfidentify' + vote).addEventListener ('change', onCheckboxChange);
			
			// Register an event handler for this table
			(function () {	// IIFE, creating closure to ensure vote is the current number in the loop
				var voteNumber = vote;
				document.querySelector ('table.v' + vote).addEventListener ('click', function () {onTableClick (voteNumber);} );
//				table.addEventListener ('touchstart', onTableClick);
			}());
		}
	}
};
