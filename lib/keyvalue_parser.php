<?php
// A class  to parse key value pairs in html or text.
// Written by Byron Whitlock
// byronwhitlock@gmail.com

//
include_once "scrape/utils.inc";
include_once "scrape/db.inc";
include_once "operating_hours_parser.php";

Class KeyValue_Parser
{
	
	private $seperator=":";
	private $value_quote_strip="'";

	// takes an address html string and returns an array with normalized keys and values.	
	// key: value
	public function parse($text, $allowLongKeys=false)
	{
		//$oh = new operating_Hours_parser();
		//$hours = $oh->parse($text);
		//if (sizeof(array_keys($hours)) > 3 )
		//	return $hours;

		$delimiter = "__FIELD_DELIMITER__";

		$q = $this->value_quote_strip;

		if (is_array($text))
		{
			$text = strip_tags(html_entity_decode(join($delimiter,$text)));
		}
		else
		{
			// remove a/strong/b/i tags
			$text = preg_replace("/<(a|strong|b|i) [^>]+>/i","",$text);
			$text = str_ireplace("</a>","",$text);
			$text = str_ireplace("</strong>","",$text);
			$text = str_ireplace("</b>","",$text);
			$text = str_ireplace("</i>","",$text);


			// turn all tags into field delimiters
			// turn newlines into delimiters
			$text = preg_replace("/<[^>]+>|\n/",$delimiter,$text);
			$text = str_replace("http://","",$text);
		}
		
		
		$fields = explode($delimiter,$text);
		$data = array();
		
		for ($i=0;$i<sizeof($fields);$i++)
		{			
			$field = $fields[$i];
			
			if (strpos($field, $this->seperator) !== false)
			{
				
				list($key,$value) = explode($this->seperator,$field);
				$key = trim($key);
				$value = trim($value);
				
				
				if (empty($value)) // keep compounding value until we find another sepeartor.
				{
					$nextValues = array();
					// this means the value might be on the next few values. make value equal
					// keep looking for a seperator and accumalate the values 
					for($j=$i+1;$j<sizeof($fields);$j++)
					{
						$nextField = $fields[$j];
						if (empty(trim($nextField))) continue;
						if (strpos($nextField, $this->seperator) === false)						
							$nextValues[] =trim( $nextField );						
						else
							break; // have to break or we go to far. need to stop as soon as we see seperator.	
					}
					
					$value = join(", ",$nextValues);
				}
				
				if (!empty($key))
				{
					if($allowLongKeys)  // something went wrong, shouldn't have keys that long
						$data[$key] = trim(html_entity_decode(preg_replace("/^'(.*)',?$/","\\1",$value)));
					else if (strlen($key) < 40)
						$data[$key] = trim(html_entity_decode(preg_replace("/^'(.*)',?$/","\\1",$value)));
				}
			}
		}
		return db::normalize($data);

	}
}