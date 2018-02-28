<?
// An amazing class  to parse human names
// Written by Byron Whitlock
// byronwhitlock@gmail.com
//

include_once "scrape/utils.inc";
include_once "scrape/db.inc";
Class Name_Parser
{
	
	public function parse($fullname)
	{
		if (is_array($fullname))
		{
			$fullname = join(",", array_flatten($fullname));
		}
		$data =  array();
		$fullname = trim($fullname);

		// we need to normalize the delimiter
		// lets use , for a delimiter.

		$fullname = str_replace("<br></br>", " ", $fullname);
		$fullname = str_replace("<br>", " " , $fullname);
		$fullname = str_replace("</br>", " " , $fullname);
		$fullname = str_replace("<br/>", " " , $fullname);

		$fullname = preg_replace("/<[^>]+>+/"," ",$fullname);

		$fullname = str_replace("\n"  , " " , $fullname);				

		$regex = "/ (([IV]+|jr|sr|esq\.|J\.P|N\.P|CLA|CP|D\.R|ACP|ALS|PLS|PP|RP|PHD|MD|ESQ|CRP|PH\.D|B\.F\.A|M\.A|M\.D|D\.C|LL\.D|Esquire)\.?)$/i";
		while (preg_match($regex,$fullname,$matches))
		{
			$fullname=preg_replace($regex,"",$fullname);
			$suffix[] = $matches[1];
		}

		$regex = "/^(Dr|Atty|Doctor|Mr|Mrs|Ms)\.? /i";
		while (preg_match($regex,$fullname,$matches))
		{
			$fullname=preg_replace($regex,"",$fullname);
			$suffix[] = $matches[1];
		}


		$fullname = explode(" ", $fullname);		
		$end = sizeof($fullname);

		$data['FIRST_NAME'] = $fullname[0];
		if ($end > 1)
			$data['MIDDLE_NAME'] = join(" ", array_slice($fullname,1,$end-2));
		$data['LAST_NAME'] = $fullname[$end-1];
		if (isset($suffix))
			$data['SUFFIX_NAME'] = join(", ", $suffix);

		return $data;
	}


}

		