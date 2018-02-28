	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.125 Safari/537.36',
		 pageSettings: {
			loadImages:  false,        // do not load images
			loadPlugins: true         // do not load NPAPI plugins (Flash, Silverlight, ...)
		}
	 });
	 var x = require('casper').selectXPath;
	 
	var terminate = function() {
		this.echo("Exiting..").exit();
	};

	// Return the current page by looking for the disabled page number link in the pager
	function getSelectedPage() {
		var el = document.querySelector('li[class="selected"]');
		return parseInt(el.textContent);
	}

	function getUrls() {
		var rows = document.querySelectorAll('table#peopleResults a');
		var urls = [];

		for (var i = 0, row; row = rows[i]; i++) {
			var url = row.getAttribute('href');
			if (url.indexOf("mailto:") <0)
				urls.push(url);
		} 

		return urls;       
	}

	var processPage = function() {
		city = casper.cli.get('p1')
		
		/*casper.capture("captures/"+city+"."+currentPage+".png",
			{
				top: 0,
				left: 0,
				width: 800,
				height: 600
			}
		);*/
		urls = this.evaluate(getUrls);
		require('utils').dump(urls);

		if (currentPage >= 320 || !this.exists(x("//a[@title='Move to next page']"))) {
			return terminate.call(casper);
		}

		currentPage++;

		this.thenClick(x("//a[@title='Move to next page']")).then(function() {
			this.waitFor(function() {
				return currentPage === this.evaluate(getSelectedPage);
			}, processPage, terminate);
		});
	};
	
	var url = decodeURIComponent(casper.cli.get('p1'));
	var urls = [];
	var currentPage = 1;
	

	casper.start(url);
	casper.waitForSelector('table#peopleResults', processPage, terminate);
	casper.run()
	
	//var links = [];
	//var city = casper.cli.get('p1');



	
	casper.run();