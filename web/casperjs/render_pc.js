	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.125 Safari/537.36',
		 pageSettings: {
			loadImages:  true,        // do not load images
			loadPlugins: false         // do not load NPAPI plugins (Flash, Silverlight, ...)
		}
	 });
	casper.options.waitTimeout = 20000;

	function urldecode(str) {
	   return decodeURIComponent((str+'').replace(/\+/g, '%20'));
	}
	var url = urldecode(casper.cli.get('p1'));
	var filename = urldecode(casper.cli.get('p2'));
	

	casper.start(url);

	casper.waitForSelector('title', function() {
		casper.capture("sharepoint_finder/PC/"+filename+".png");
		casper.echo("Captured "+filename);
//		casper.echo(casper.page.content);
	});

	casper.run();