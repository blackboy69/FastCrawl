<?

include_once("config.inc");
include_once("scrapeforce.inc");

$log = "D:/tmp/cron.log";
//if (is_file($log)) unlink($log);


log::info("Starting ScrapeForce Scheduled Scraper");
log::info("Writing to Log: $log");
//ob_start();
	
	while (true)
	{	
		//log::$errorLevel = ERROR_DEBUG_VERBOSE;

		ob_start();
		log::info ("Date: " .strftime("%c"));
		$sf = new ScrapeForce();
		if (!$sf->run())
		{
			// something bad happened
			log::error("Scrape Failed!");
			var_dump($sf->error);
		}

		// write the log file
		$fh = fopen($log,'a');


		$output = ob_get_contents();
		ob_end_clean();

		fputs($fh, $output);
		fclose($fh);	
		echo $output;
		flush();
		
		sleep(10); // sleep for 5 seconds
	}
