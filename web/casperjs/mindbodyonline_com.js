	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36',
		 pageSettings: {
			loadImages:  false,        // do not load images
			loadPlugins: false         // do not load NPAPI plugins (Flash, Silverlight, ...)
		}
	 });
	var x = require('casper').selectXPath;

	var studioid = casper.cli.get('p1')
	casper.start("https://clients.mindbodyonline.com/classic/home?studioid="+studioid);
	//	casper.start("http://www.mindbodyonline.com");


	casper.then(function() {
		// capture the entire page.
		this.page.switchToChildFrame(0);
		this.echo(this.getElementAttribute('#top-logo-container > a', 'href'));
	});

	if(casper.exists(x('//a[contains(text(),"Continue to site without logging in")]'))){
		casper.thenClick(x('//a[contains(text(),"Continue to site without logging in")]') );
	}
	
	if(casper.exists(x('//a[contains(text(),"HELP")]'))){
		casper.thenClick(x('//a[contains(text(),"HELP")]') );
	}



	// echo the phone number too
	casper.then(function() {

		this.echo(this.fetchText('#main-content > div.container > div.section'));
	});

	casper.run();