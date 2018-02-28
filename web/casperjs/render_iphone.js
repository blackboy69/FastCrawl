	var fs = require('fs');
	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3)',
		 pageSettings: {
			loadImages:  true,        // do not load images
			loadPlugins: false         // do not load NPAPI plugins (Flash, Silverlight, ...)
		},
		viewportSize: { width: 320, height: 480 }
	 });
	casper.options.waitTimeout = 20000;

	function urldecode(str) {
	   return decodeURIComponent((str+'').replace(/\+/g, '%20'));
	}
	var url = urldecode(casper.cli.get('p1'));
	var filename = urldecode(casper.cli.get('p2'));
	
	if (fs.exists(filename+".png"))
	{
		casper.echo("Cached.")
	}
	else
	{
		casper.start(url);

		casper.waitForSelector('title', function() {
			casper.capture("sharepoint_finder/iPhone/"+filename+".png", 
				{
					top:0,
					left:0,
					width:320,
					height: 480
				}
			);
			casper.echo("Captured "+filename);
			//casper.echo(casper.page.content);
			 this.exit();
		});
		casper.run();
	}
	