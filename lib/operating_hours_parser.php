<?

// Use this class to parse hours of operation
// byronwhitlock@gmail.com

Class Operating_Hours_Parser
{

	// mappings for all recognized types
	private $dayTokenMap = array(			
			"SU"=>1,			    "SUN" =>1, "SUNDAY"   =>1,
			"M" =>2,	"MO" =>2, "MON" =>2, "MONDAY"   =>2, 
			"T" =>3, "TU" =>3, "TUE" =>3, "TUESDAY"  =>3, "TUES"  => 3,
			"W" =>4,	"WE" =>4, "WED" =>4, "WEDNESDAY"=>4,
			"TH"=>5, "THU"=>5, "THUR"=>5, "THURSDAY" =>5, "THURS" => 5,
			"F" =>6,	"FR" =>6, "FRI" =>6, "FRIDAY"   =>6,
			"SA"=>7,			    "SAT" =>7, "SATURDAY" =>7
			
	);

	// normalizing to 3 letters
	private $dayIndexMap = array(
		2=> "MON",
		3=> "TUE",
		4=> "WED",
		5=> "THU",
		6=> "FRI",
		7=> "SAT",
		1=> "SUN");

	private $rangeTokens = array ("-", "THROUGH", "THRU", "TO");
	
	public function rangeToken($token)
	{
		return in_array($token, $this->rangeTokens);
	}

	// returns a normalized day
	public function dayToken($index)
	{
		if (isset($this->dayIndexMap[$index]))
		{
			return $this->dayIndexMap[$index];
		}
		return "";
	}

	public function dayIndex($str)
	{
		// remove anything that isn't in day token format
		$str = preg_replace("/[^A-Z]/i","", trim($str));
		if (isset($this->dayTokenMap[$str]))
		{
			return $this->dayTokenMap[$str];
		}
		return 0;
	}

	private function tokenize($text)
	{	
		$text = str_replace("<br />"," ", $text);
		$text = strip_Tags($text);

		$text = preg_replace("/[^0-9A-Z:\- ]/i","",$text);


		
		$text = strtoupper($text);
		
		// make sure the days and times have spaces between
		$text = preg_replace("/([A-Z]+)([0-9]+)/","\\1 \\2",$text);
		$text = preg_replace("/([0-9]+)([A-Z]+)/","\\1 \\2",$text);
		//$text = preg_replace("/(\w+)(\d+)/","\\1 \\2",$text);

		$text = str_replace("AM", " AM ",$text);
		$text = str_replace("PM", " PM ",$text);
		
		// make sure connected ranges such as m through f or m-f are tokenized properly as m - f 
		$text = str_replace($this->rangeTokens, " - ",$text);

		// space stuff like sat:closed or sat:7:40 properly 
		$text = preg_replace("/([A-Z]+):/i","\\1: ",$text);

		$tokens= preg_split("/\s+/",$text);
		
		return $tokens;
	}

	public function parse($text)
	{
		if (is_array($text))
			$text = join(" , ",$text);

		$tokens = $this->tokenize($text);
		
		// push days as we find them.
		// if we hit a range, push all the intermediate values
		// when we find a time, pop everything off the stack and assign time found

		$stack = array();
		// the idea here is to turn every day into a numeric key. 
		// mon = monday = m = 1
		$week = array();
		$parsingRange=false; // are we in the middle parsing a range? i.e. M-F 
		$parsingTime =false; // are we in the middle of parsing a time? i.e. 7-5:30

		$currentTime = array();
		$tokenCount = 0;

		// split the incomming text on whitespace
		foreach($tokens as $token)
		{	
			if ($tokenCount++ > 200)
			{
				// somthing went wrong. shouldn't have that many tokens!
				$stack = array(1,2,3,4,5,6,7);
				$currentTime = "";
			}
			// first check to see if this is a day like monday or m or TH etc.
			if ($dayIndex = $this->dayIndex($token))
			{
				// is this the start of a new unit of operation? each unit is in brakets: [m-f 9-3] [Sat closed] [Sunday 2-4]
				if ($parsingTime)
				{
					// get the time we have parsed so far.
					$time = $this->formatTime($currentTime);

					// iterate the stack and set our week array to the time we found
					foreach($stack as $day)
					{
						$week[$day] = $time;
					}
					$stack = array();
					$currentTime = array();
					$parsingTime = false;
				}

				if ($parsingRange)
				{
					// pop stack, for first day, 
					$firstDayIndex = array_pop($stack);
					$stackoverflow=0;
					// if we end on a sunday, we have to count backwards. if we change monday to = 1 we have the same problem!
					if ($dayIndex == 1)
					{
						
						//get days from last day of week untill we hit first day.
						for($i=7;$i>=$firstDayIndex;$i--)
						{
							array_push($stack,$i);

							if ($stackoverflow++>10 ) // should never iteratre more than 7 actually
								return array();
						}
					}
					else // get the difference between the two days
					{
						//get intermediate days, and push all on stack 
						for($i=$firstDayIndex;$i<$dayIndex;$i++)
						{
							// push intermediate days from first day to current day on the stack
							array_push($stack,$i);

							if ($stackoverflow++>10 ) // should never iteratre more than 7 actually
								return array();
						}
					}
					$parsingRange=false;
				}
				array_push($stack,$dayIndex);
			}
			
			// is this a range token? a range token is the dash in M-F 
			// if we are parsing time already,  we ignore stuff like 8am - 5pm
			else if (!$parsingTime && $this->rangeToken($token))
			{
				$parsingRange=true;
			}
			
			// is this a time?
			else if (preg_match("/([0-9:]+ *(a|am|pm|p)?)|(closed|open)/i",$token))
			{
				$parsingTime = true;
			}
			
			// if we are parsing time, we grab everthing until we arn't parsing time anymore
			if ($parsingTime)
			{
				$currentTime[] = $token;
			}
		}
		
		// get last token
		foreach($stack as $day)
		{
			$week[$day] = $this->formatTime($currentTime);
		}
		
		$found=0;
		$returnWeek = array();
		for($i= 1;$i<8;$i++)
		{
			if (!isset($week[$i]) || empty($week[$i]))
			{
				$week[$i] = "";
			}
			else 
				$found=1;

			$returnWeek["Operating Hours ".$this->dayToken($i)] = $week[$i];
		}

		if (!$found)
			$returnWeek = array();

		$returnWeek['Raw Operating Hours']=$text;

		return $returnWeek;
	}
	
	// takes a token list and turns it into a pretty time if we can.
	function formatTime($tokenList)
	{
		$time = join(" ",$tokenList);		

		// if am/pm denoted as a or p, remove space between digit and a/p char		
		$time = preg_replace("/([0-9]*[0-9]) (A|P) /i", " \\1\\2 ",$time);
		
		// compress small times like 8 - 5 or 8a - 5p

		$time = str_replace(" - ","-",$time);
		$time = str_replace("- ","-",$time);
		return $time;		
	}
}