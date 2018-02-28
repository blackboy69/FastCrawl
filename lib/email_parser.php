<?
// An amazing class  to parse phone numbers.
// Written by Byron Whitlock
// byronwhitlock@gmail.com
//

include_once "scrape/utils.inc";
Class Email_Parser
{
	
	public function parse($text, $findAll=false)
	{
		if (is_array($text))
		{
			$text = join(" ", array_flatten($text));
		}
		
		

		$data = array();
		if (preg_match_all("/[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+/", $text, $matches))
		{
			foreach($matches[0] as $match)
			{
				if (in_array($match, array_values($data)))
					continue;

				$key = "Email";
				if (isset($data[$key]))
				{	for ($count=1 ; isset($data[$key]); $count++ )
					{
						$key="Email $count";					

						if ($count > 10) break; // 10 emails only
					}
				}
				$data[$key] = $match;
			}			
		}

		return db::normalize($data);
	}


}

		