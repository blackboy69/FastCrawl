<?



	static function parse($url,$html)
	{
		$host = parse_url($url,PHP_URL_HOST);
		log::info($url);
//										log::info($host);
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip

		if (preg_match("/bing/",$host))
		{
			if (empty($query['start']) || $query['start'] > 100) // only the first 10 pages
			{
				$toLoad=array();
				$hrefs = self::$bing->parse($html,true);			
				foreach ($hrefs as $href)
				{
					$hrefHost = parse_url($href,PHP_URL_HOST); 
					if (preg_match("/frontdeskhq/",$hrefHost))
						$toLoad[] = "http://$hrefHost";
					else if (preg_match("#bing.com/search#",$href))			
						$toLoad[] = $href;
				}
				log::info($toLoad);
				self::getInstance()->loadUrlsByArray($toLoad);
			}
		}
		/*else if (preg_match("/google/",$host))
		{
			$urls = self::$google->parse($html,true);
			log::info($urls);
			return;
			self::getInstance()->loadUrlsByArray($urls);
		}
		else if (preg_match("/yahoo/",$host))
		{
			self::getInstance()->loadUrlsByArray(self::$yahoo->parse($html));
		}*/
		else if (preg_match("#frontdeskhq#",$host))
		{
			$data = array();
			$x = new  XPath($html);	
			$ap = new Address_Parser();
			$pp = new Phone_Parser();
			$ep = new Email_Parser();
			
			foreach($x->query("//title") as $node)
			{
				if (strpos($node->textContent,":") !== false)
				{
					list($junk, $name) = explode(":", $node->textContent);
					$data['NAME'] = self::cleanup($name);
				}				
			}			

			for($i=1;$i<5;$i++)
			{
				foreach($x->query("//h$i[1]") as $node)
				{
					if (strpos($node->textContent,"{") === false)
					{

						$data['NAME'] =  self::cleanup($node->textContent);
						goto EXIT_LOOP;
					}
				}
			}			
			EXIT_LOOP:

			if (empty ($data['NAME']) )
			{
				$host = parse_url($url,PHP_URL_HOST); 
				list($name,$junk) = explode(".",$host);
				$data['NAME'] = ucfirst($name);
			}

			foreach($x->query("//footer") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()));
				$data = array_merge($data, $pp->parse($node->c14n()));
				$data = array_merge($data, $ep->parse($node->c14n()));

				if (!empty($data['EMAIL'])) break;
			}
			
			if (empty($data['ADDRESS']) || empty($data['PHONE']))
			{
				foreach($x->query("//*[contains(@id, 'footer')]") as $node)
				{
					$data = array_merge($data, $ap->parse($node->c14n()));
					$data = array_merge($data, $pp->parse($node->c14n()));
					$data = array_merge($data, $ep->parse($node->c14n()));
				}
			}
			
			unset($data['RAW_ADDRESS']);
			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('NAME', 'PHONE','EMAIL'));
		}
		else
		{
			log::error("Cannot parse $url");
		}


	}
}
$r = new frontdeskhq_com();
$r->parseCommandLine();
