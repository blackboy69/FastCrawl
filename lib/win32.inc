<?

class win32
{
	# returns the pid of a process or false if it doesn't exist
	function getProcess($processName)
	{
		$rc = popen("tasklist /FO csv", "r");

		if ($rc)
		{
			while ($row = fgetcsv($rc))
			{
				if (preg_match("/$processName/i", $row[0]))
				{
					return $row[1];
				}
			}
		}
		return false;
	}

	# kills a process, returns resutls of taskkill command
	function killProcess($pid)
	{
		if (! is_int($pid))
		{
			throw new Exception ("PID must be an integer");
		}

		return `taskkill /F /PID $pid `;
	}

}