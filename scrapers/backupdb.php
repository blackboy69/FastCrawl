<?


// Backs up database

include_once "config.inc";
/*
$tables = db::oneCol("SHOW TABLES");

$new_database = "demandforce_".date("n_j_Y");
mysql_query("CREATE DATABASE $new_database");
foreach ($tables as $table)
{
	$cmd = "RENAME TABLE demandforce.$table to $new_database.$table";
	log::info($cmd);
	mysql_query($cmd);
}
echo "\nTables Renamed\n;";
*/

// read the schema file
function backup($name='mysql')
{
	$db = "g:\\backup\\$name.%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%.sql";
	log::info("Backing up $name");
	`mysqldump -uroot $name > $db`;

	if (file_exists($db))
		return true;
	else
		return false;
}


function RefreshDemandforce()
{
	log::info("Creating new tables from schema\n");
	`mysql -uroot demandforce < schema.sql`;
	`mysql -uroot demandforce < schema.sp.sql`;
}

//`mysqldump -uroot --all-databases > $db`;
backup("byrotube");

if (!backup('demandforce'))
{
	log::error("Could not create backup file");	
}
else
{
	RefreshDemandforce();
}