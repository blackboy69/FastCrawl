<?
include_once "address_parser.php";
include_once "phone_parser.php";
include_once "email_parser.php";
include_once "phpwhois/whois.main.php";
include_once "scrape/utils.inc";

class whois_parser
{

	public function parse($domain)
	{

		$whois = new Whois();
		$res = $whois->Lookup($domain);	//	print_r($res);

		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$ep = new Email_Parser();

		if (!isset($res['regrinfo']['owner']))
		{
			$d = db::normalize($res);
			$data = array();
			if (isset($d['DOMAIN_NAME']))
			{
				$data['domain'] = $d['DOMAIN_NAME'];

				return db::normalize($data);
			}
			return array();
		}

		$owner = db::normalize($res['regrinfo']['owner']);		
		
		$data = array();
		
		$data['Domain'] = $res['regrinfo']['domain']['name'];
		
		if (isset($owner['ADDRESS']))
			$data = array_merge($data, $ap->parse( $owner['address']) );

		$data = array_merge($data, $pp->parse( $res) );
		$data = array_merge($data, $ep->parse( $res));

		return db::normalize($data);
	}
}
