<?
require('c:/dev/lib/attach_mailer_class.php');

// This script runs the scheduled tasks from demand force.
// they should only be invoked by the scheduled task manager.


# auto repair
$title = "CustomerLink 'Auto Repair' Scrape Results.";
runSchedule('customerlink_auto_shops', 30,"byronwhitlock@gmail.com",$title);

$title = "CustomerLink 'Dentists' Scrape Results.";
runSchedule('customerlink_dentists', 30,"byronwhitlock@gmail.com",$title);


function runSchedule($name, $intervalDays, $to, $subject)
{
	$csv = "csv/$name.zip";
	$begin = false;

	if (file_exists($csv))
	{
		if ( (filemtime($csv) + ($intervalDays * 60 * 60 * 24)) < time() )
		{
			$begin = true;
		}
	}
	if ($begin)
	{		
		ob_start();
		include ("$name.php");
		$output = ob_get_contents();
		ob_end_clean();

		if (file_exists($csv))
		{
			if (filesize($csv) > 100)
			{
				
				$test = new attach_mailer($name="Byron Whitlock", $from = "byronwhitlock@gmail.com", $to , $cc = "", $bcc = "", $subject);
				$test->add_attach_file($csv);
				$test->text_body = "$subject\n\n$output";
				$test->process_mail();

				exec("zip -m -9 csv\$name.zip csv\$name.csv");
				unlink("csv/$name.zip");

				return true;
			}
		}
		
		unlink("csv/$name.zip");
		mail("byronwhitlock@gmail.com","FAILED: $subject","CSV generaton for demandforce/$name.php failed\n\n$output");
	}
}
