	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.125 Safari/537.36',
		 pageSettings: {
			loadImages:  false,        // do not load images
			loadPlugins: false         // do not load NPAPI plugins (Flash, Silverlight, ...)
		}
	 });
	casper.options.waitTimeout = 60000;
var x = require('casper').selectXPath;
	function urldecode(str) {
	   return decodeURIComponent((str+'').replace(/\+/g, '%20'));
	}
	var url = urldecode(casper.cli.get('p1'));

	casper.start(url);

	casper.waitForText('This page is mobile-friendly', function() {		
		casper.echo("This page is mobile-friendly");
		
		this.exit();
	});
	
	casper.waitForText('Not mobile-friendly', function() {
		casper.echo("Not mobile-friendly: ");
		casper.echo(this.fetchText(x("//div[@class='result-group-body']")));
		 this.exit();
	});
	
	casper.waitForText('Failed ', function() {		
		casper.echo("Error");		
		this.exit();
	});
	
	casper.run();