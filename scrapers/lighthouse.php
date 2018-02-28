<?
include_once "config.inc";

$wsdl = file_get_contents("lighthouse.wsdl");
$x = new Xpath($wsdl);

foreach ($x->query("//complextype") as $complexType)
{
	$typeName = $complexType->getAttribute("name");
	
	
	
  if (!preg_match("/__c/",$typeName))
  {
	  $xEl =  new Xpath($complexType);
	foreach ($xEl->query("//element") as $element)	
	{
		$elementName = $element->getAttribute("name");
		
		
		if (preg_match("/__c/",$elementName)  )
		{
			echo "$typeName.$elementName\r\n";
		}
	}
}
}