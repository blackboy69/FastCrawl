<?

//just a few functions to help make building html quick n dirty stuff easier.

// build a simple html table.
function table($items,$callback=null)
{
	$html = "<table>";
	foreach($items as $row=>$item)
	{
		$html .= "<tr id='$row'>";
		foreach($item as $name=>$value)
		{
			if ($row == 0)
			{
				$html .= "<td id='header_$name'><b>$name</b></td>";
			}
			else
			{
				$html .= "<td id='${name}_$value'>$value</td>";
			}
		}
		$html .= "</tr>";		
	}
	$html .= "<table>";

	return $html;
}