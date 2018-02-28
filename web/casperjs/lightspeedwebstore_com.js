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
	function urldecode(str) {
	   return decodeURIComponent((str+'').replace(/\+/g, '%20'));
	}
	var links = [];
	var term = urldecode(casper.cli.get('p1'));
	var loc = urldecode(casper.cli.get('p2'));

	function getLinks() {
		var links = document.querySelectorAll('h3.r a');
		return Array.prototype.map.call(links, function(e) {
			return e.getAttribute('href');
	    });
	}



	casper.start("https:/www.google.com", function() {
		this.fill('form[action="/search"]', { q: term+" " +loc}, true);
	});

	var fs = require('fs');
	for (var i = 0; i<10 ; i++) // never go more than 10 pages deep
	{

		casper.then(function() {
			// aggregate results for the 'phantomjs' search
			links = links.concat(this.evaluate(getLinks));
			casper.capture("captures/lightspeedwebstore_com/google_"+loc+"_page_"+i+".png");
			fs.write("captures/lightspeedwebstore_com/google_"+loc+"_page_"+i+".html", casper.page.content, 'w');
		});

		casper.then(function() {
			if (casper.exists('#nav td:last-child a'))
			//if (casper.exists(x("//a[contains(text(), 'Next')]")))
			{
				casper.wait(Math.floor((Math.random() * 10) + 15)*1000); // 15 and 25 seconds
				casper.thenClick('#nav td:last-child a');
			}
		});

	} 

	casper.then(function() {
		this.echo(JSON.stringify(links));
	});
	casper.run();