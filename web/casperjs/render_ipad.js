	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (iPad; CPU OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B206 Safari/7534.48.3',
		 pageSettings: {
			loadImages:  true,        // do not load images
			loadPlugins: false         // do not load NPAPI plugins (Flash, Silverlight, ...)
		},
		viewportSize: { width: 768, height: 1024 }
	 });
	casper.options.waitTimeout = 20000;

	function urldecode(str) {
	   return decodeURIComponent((str+'').replace(/\+/g, '%20'));
	}
	var url = urldecode(casper.cli.get('p1'));
	var filename = urldecode(casper.cli.get('p2'));
	

	casper.start(url);

	casper.waitForSelector('title', function() {
		casper.capture("sharepoint_finder/iPad/"+filename+".png");
		casper.echo("Captured "+filename);
//		casper.echo(casper.page.content);
	});

	casper.run();