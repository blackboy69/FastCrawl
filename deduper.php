<?

// Deduper

$file1 = $argv[1];
$file2 = $argv[2];

if (!is_file($file1) || !is_file($file2))
{
	Echo "\nFile not found \n\nUsage: Deduper <file1> <file2>\n\n";
	exit;
}

$offset = 9;
$f1Hash = array();
$f2Hash = array();
foreach( file($file1) as $line)
{
	$l = str_getcsv($line);
	
	$hash=  $l[$offset];
	$f1Hash[ $hash ] = $line;		
}


foreach(file($file2) as $line)
{
	$l = str_getcsv($line);
	
	$hash=  $l[$offset];
	$f2Hash[$hash ] = trim($line);

}

$new = array();
$deleted = array();

foreach( $f2Hash as $k=>$v )
{
	if (! isset($f1Hash[$k]))
		$new[]=$f2Hash[$k];	
}

foreach( $f1Hash as $k=>$v )
{
	if (! isset($f2Hash[$k]))
		$deleted[]= $k;	
}

echo sizeof($new)." New \n";
echo sizeof($deleted)." Deleted \n";

file_put_contents("new.csv", join("\n",$new));
file_put_contents("deleted.csv", join("\n",$deleted));
	