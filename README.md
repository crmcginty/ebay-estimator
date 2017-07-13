# eBay comic price estimator
## <a target="_blank" href="http://anthonymcginty.com/comics/estimator/">View it working here</a>

## Overview
This is still work in progress... here's an overview on the basics of how it's put together:
1. cbd-scripts.js calls titles.php ($.getJSON) to retrieve comic titles and publishers
2. PHP connects to a MySQL database and queries two tables (titles and publishers) for all records
3. Using the data returned, the JS file populates the drop-downs and #estimations table (limiting the number of comics shown to the #limit drop-down)
4. For each comic/title, a string of keywords is constructed to query the eBay API
5. ebaySearch.php is called to build the full eBay API call and return the results (PHP used to remove eBay API key from the front-end)
6. The JS file then populates the table with the keyword string and if eBay results were returned, a price is estimated (average price of completed items, NOTE: these may have ended without sale) and an additional row is added to the table to allow the user to visually compare the eBay results against the DB title

## What's next
* Finding the optimal eBay query/keywords: some of the current results aren't reliable for calculating the price
* Remove reliance on the Comics DB: looking to call JSON from the server to improve performance and reliability
* Extending/Improving the front-end: implementing the publisher drop-down and other search mechanisms, reviewing the layout on devices, the list goes on...
* Updating the DB with price estimates: my plan is to save the estimated prices to a file (probably CSV). This would take place as eBay is queried from the page and a bulk action will also be built. 

