(function () {
	"use strict";

	const DB_SCRIPT = "scripts/php/titles.php";
	const EBAY_SCRIPT = "scripts/php/ebaySearch.php";
	const TITLE_LIMIT = 10;
	const DEFAULT_PRICE = 3.20;

	// Publishers from DB
	var publishers = $.getJSON({
		url: DB_SCRIPT,
		data: {
			action: 'getPublishers'
		}
	});
	publishers.done(function(publishers) {
		// Populate the publisher drop down
		$(publishers).each(function(i){
			$('#publisher').append("<option value='" + publishers[i].id + "'>" + publishers[i].name + "</option>");
		});
	});
	publishers.fail(function(xhr) {
		console.log("AJAX error\n", xhr); // Handle this better
	});

	// Comic titles from DB
	var titles = $.getJSON({
		url: DB_SCRIPT,
		data: {
			action: 'getTitles'
		}
	});
	titles.done(function(titles) {
		populateLimits(titles.length);
		var limits = getLimits();
		var titleArray = titles.slice(limits[0],limits[1]);
		showTitles(titleArray);
	});
	titles.fail(function(xhr) {
		console.log("AJAX error\n", xhr);
		$('#errorMsg').show();
	});

	// Populate the limit drop down
	function populateLimits(titlesLen) {
		var increment = TITLE_LIMIT;
		var steps = parseInt(titlesLen/increment);
		var remainder = titlesLen%increment;

		for (var i = 0; i < steps; i++) {
			var limitMin = i*increment+1;
			var limitMax = (i+1)*increment;
			var limit = limitMin + "-" + limitMax;

			$('#limit').append("<option value='" + limitMax + "'>" + limit + "</option>");

			// Extra option for remainders
			if(i === steps-1){
				limit = (limitMax+1) + "-" + (limitMax+remainder);
				limitMax = limitMax+remainder;
				$('#limit').append("<option value='" + limitMax + "'>" + limit + "</option>");
			}	
		}
	}

	// Display the DB titles in the table
	function showTitles(data){
		var limits = getLimits();
		$(data).each(function(i){
			var title = $(this)[0];
			getEbayResults(title);
			// TIDY THIS UP (TEMPLATING?)
			$("#estimations tbody").append("<tr id='title_" + title.id + "'></tr>"); // Create a new table row
			$("#estimations tbody tr:last").append("<td>" + (limits[0]+i) + "</td>"); // Limit index
			$("#estimations tbody tr:last").append("<td>" + title.id + "</td>" + "<td><img class='cover' src='\\img\\title_" + title.id + ".jpg' alt=''></td>"); // ID and cover image
			$("#estimations tbody tr:last").append("<td class='keywords'><strong>No eBay results found</strong></td>"); // eBay keywords and results
			$("#estimations tbody tr:last").append("<td class='estimate'>&pound;" + DEFAULT_PRICE.toFixed(2) + "</td>"); // Estimated price
		});
	}

	// Clear titles table
	function clearTitles() {
		$('#estimations tbody').empty();
	}

	// Update titles using controls select
	function updateTitles() {
		var limits = getLimits();
		var titleArray = JSON.parse(titles.responseText);
		titleArray = titleArray.slice(limits[0],limits[1]);
		clearTitles();		
		showTitles(titleArray);
	}

	// Return upper and lower limits (from drop down) as an array
	function getLimits() {
		var limitMax = parseInt($("#limit").val());
		var limitMin = limitMax - (TITLE_LIMIT-1);
		var limits = [limitMin, limitMax+1]; // add 1 to account for array 0 index
		return limits;
	}

	// Returns ebay results
	function getEbayResults(title) {
		var id = title.id;
		var keywords = getEbayKeywords(title);
		$.getJSON({
			url: EBAY_SCRIPT,
			data: {
				keywords: keywords
			}
		})
		.done(function(data) {
			var items = data.searchResult.item || []; // Test if results were returned
			if(items.length > 0){
				// Add the keywords, estimate and results to the table (DB ID used to locate correct table row)
				var viewResults = "<p><a class='viewResults' href='#'>View eBay results</p>";
				$('#title_' + id + ' .keywords').html("<p>" + keywords + "</p>" + viewResults);
				var estimate = getPriceEstimate(items);
				$('#title_' + id + ' .estimate').html("&pound;" + estimate.toFixed(2));
				$("#estimations tbody tr#title_" + id).after(getEbayHTML(data));
			} else {
				// Append the ebay keywords to the table
				$('#title_' + id + ' .keywords').prepend("<p>" + keywords + "</p>");
			}
			$("#estimations table").show(); // Unhide table
		})
		.fail(function(xhr) {
			console.log("AJAX error\n", xhr); // Handle this better
		});	
	}

	// Constructs keywords for the eBay search (TIDY THIS UP)
	function getEbayKeywords(title) {
		// Year of issue release
		var year = title.issue_date.split("-");
		year = year[0];
		// Booleans (annual, special, variant)
		var annual = parseInt(title.annual) === 1 ? " Annual" : " -Annual";
		var special = parseInt(title.special) === 1 ? " Special" : " -Special -Edition";	
		var variant = parseInt(title.variant) === 1 ? " 'Variant' '" + title.cover + "'" : " -Variant -Variants";		
		// May be null (era, cgc, cover)
		var era = title.era !== null ? ", \"" + title.era + "\"" : "";
		var cgc = title.cgc_grading !== "" ? " CGC Grade " + title.cgc_grading : " -CGC -Grade";
		var cover = title.cover !== null ? " \"" + title.cover + "\"" : "";
		// Special cases: issue and signed
		var issue = "#" + title.issue;
		var signed = "Signed";
		var notesArray = title.notes.split(" ");
		if($.inArray(signed, notesArray) === -1){
			signed = "-" + signed;
		}

		var keywords = "\"" + title.title + "\" , \"" + issue + "\" , \"" + year + "\" , \"" + title.publisher + "\"" + variant + era + cgc + annual + special + " " + signed + " -Complete -Lot -Set -Full -Series -Printings -Prints -Issues";
		return keywords;
	}

	// Calculate average price from eBay results
	function getPriceEstimate(items) {
		var sumPrices = 0;
		$(items).each(function(i) {
			sumPrices += parseFloat(items[i].sellingStatus.convertedCurrentPrice);
		});
		return sumPrices/items.length; // Return average price
	}

	// Returns HTML string for eBay results
	function getEbayHTML(results) {
		var items = results.searchResult.item;
		var html = "<tr class='ebayResults'><td colspan='5'><h3>eBay results</h3><table><tbody>";
		$(items).each(function(i){
			var item = items[i];
			var title = item.title;
			var pic = item.galleryURL;
			var viewitem = item.viewItemURL;
			var price = parseFloat(items[i].sellingStatus.convertedCurrentPrice);
			if (null != title && null != viewitem) {
			  html  += ("<tr><td>" + "<img src='" + pic + "' border='0'>" + "</td>" +
			  "<td><a href='" + viewitem + "' target='_blank'>" + title + "</a></td><td>&pound;" + price.toFixed(2) + "</td></tr>");
			}
		});
		html += "</tbody></table></td></tr>";
		return html;		
	}

	$(document).on("click", ".viewResults", function() {
		$(this).closest("tr").next("tr").toggle();
		return false;
	});	

	// Controls select change - NEED TO INCORPORATE PUBLISHERS
	$(".controls select").change(function() {
		updateTitles();
	});	

})();