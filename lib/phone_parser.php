<?
// An amazing class  to parse phone numbers.
// Written by Byron Whitlock
// byronwhitlock@gmail.com
//

include_once "scrape/utils.inc";
include_once "scrape/db.inc";
Class Phone_Parser
{
	
	/// strictNaming will force all keys to be phone, phone 1, phone 2 etc.
	/// when false, the name of the Keyvalue will be used IE Toll Free:xxx , Fax:xxxx
	public function parse($address, $strict=true)
	{
		if (is_array($address))
		{
			$address = join(",", array_flatten($address));
		}
		$data =  array();
		$address = trim($address);

		// we need to normalize the delimiter
		// lets use , for a delimiter.

		$address = str_replace("<br></br>", ",", $address);
		$address = str_replace("<br>", "," , $address);

		$address = preg_replace("/<[^>]+>+/",",",$address);

		$address = preg_replace("/([a-z])\\s+([a-z])/","\\1,\\2",$address);

		$address = str_replace("\n"  , "," , $address);		


		$address = preg_replace("/\\s+/"," ",$address);

		$address = preg_replace("/\\s+/"," ",$address);
		$address = preg_replace("/\\s+/"," ",$address);
	

		$pieces = explode(',',$address);

				// iterate through it backwards. item 0 is the string
		for ($i = sizeof($pieces)-1; $i>-1 ; $i--)
		{			

			$piece = str_replace(" ","", ($pieces[$i]));

			if (empty($piece))
			{
				continue;
			}

			preg_match("/(([A-Za-z0-9 \-_]+): )?(\([0-9]{3}\) *[0-9]{3}-[0-9]{4})/", $piece, $matches);
			if (empty($matches))
				preg_match("/(([A-Za-z0-9 \-_]+):? )?([0-9]{3}-[0-9]{3}-[0-9]{4})/",$piece, $matches);
			if (empty($matches))
				preg_match("/(([A-Za-z0-9 \-_.]+):? )?([0-9]{3}.[0-9]{3}.[0-9]{4})/",$piece, $matches);

			
			if (!empty($matches))
			{
				$phone = trim($matches[3]);
				if ($strict)
				{
					if (preg_match('/^[0-9]+$/', $phone)) // make sure there is some phone punctuation like - or () or . or somthing.
						continue;
				}
				
				if (preg_match("/PH:|PHONE|FAX|VOICEMAIL|VOICE|CELL|PAGER/i", $piece, $matches2))
					$key = $keyname = strtoupper(trim($matches2[0]));
				else
					$key = $keyname = "PHONE";

				for ($count=1 ; isset($data[$key]) ; $count++ )
				{
					$key="$keyname $count";			
				}
				if (! in_array($phone, array_values($data)))
				{
					$data[$key] = $phone;
				}
				
				if ($count > 10) // 10 phone numbers only
					break;
			}
		}
		return db::normalize($data);
	}


}

		