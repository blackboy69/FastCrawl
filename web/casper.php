<?
// This script handles IPC between casper and fastcrawl

ini_set("max_execution_time","300000");

DEFINE("CASPERJS", "..\\lib\\casperjs\\bin\\casperjs.exe");
DEFINE("SCRIPTPATH", ".\\casperjs\\");

if (isset($_REQUEST['type']))
{
	// hacky stuffss	
	$type =  $_REQUEST['type'];
	$p1=  urlencode(@$_REQUEST['p1']);
	$p2=  urlencode(@$_REQUEST['p2']);
	if ($type=='render_iphone')
	{
		$filename="sharepoint_finder/iPhone/$p2.png";
		if (is_file($filename))
		{
			echo "sharepoint_finder/iPhone/$p2.png is CACHED!";
			exit;
		}
		else
		{
			//$script = "..\\lib\\wkhtmltopdf\\bin\\wkhtmltoimage.exe --width 320 --crop-h 480 --disable-smart-width --custom-header \"User-Agent\" \"Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3)\" {$_REQUEST['p1']} {$_REQUEST['p2']}";
			
			
			//echo $script;
			
		}
	}		
	//else
	{		
		$script = CASPERJS . " ". SCRIPTPATH."$type.js --p1=$p1 --p2=$p2";

		if (isset($_REQUEST['proxy']))
		{
			$proxy = $_REQUEST['proxy'];
			$script .= " --proxy=$proxy";
		}

		$script .= " --ignore-ssl-errors=yes ";
	}
	//echo $script;
	
	echo `$script`;
}
else
	echo "{ERROR}";