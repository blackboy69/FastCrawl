	var casper = require('casper').create({
		 verbose: false,
		 logLevel: 'debug',
		 userAgent: 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36',
		 pageSettings: {
			loadImages:  true,        // do not load images
			loadPlugins: false         // do not load NPAPI plugins (Flash, Silverlight, ...)
		}
	 });
	var x = require('casper').selectXPath;

	var zip = casper.cli.get('p1')
	var state = casper.cli.get('p2')

	casper.start("http://www.realtor.org/rofindrealtor.nsf/pages/FS_FOFFICE?OpenDocument");
			
	//fill out the form
	casper.then(function() {
		// capture the entire page.
		this.page.switchToChildFrame(0);		
		this.fill('form[name="searchOfficeForm"]', { 'zip': zip}, true);
	});
	
	//then fill out the captcha
	casper.then(function() {

		this.page.switchToChildFrame(0);		

		//this.captureSelector ("captures/captcha_"+zip+".png",'td img');
		//capture("captures/captcha_"+zip+".png")
		//this.echo("<h1>captcha_"+zip+"</h1><img src='captures/captcha_"+zip+".png'> <hr>");


		casper.capture("captures/page_captcha_"+zip+".png",{top:0,left:0, width: 500, height: 400});	
		casper.echo("<h1>page_captcha_"+zip+"</h1><img src='captures/captcha_"+zip+".png'> <hr>");


	});

	casper.run();