<?

$text = "TUES:8:30AM-5:00PMTHURS:8:30AM-5:00PMSAT:8:00AM-1:00PM";
echo strlen($text);
echo "\n";
echo preg_replace("/(\d+)(\w+)/","\\1 \\2",$text);
