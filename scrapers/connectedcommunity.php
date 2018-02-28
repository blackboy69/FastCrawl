<?
include_once "config.inc";
R::freeze(false);
		
class corenetglobal extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
	    
		$type = get_class();		
		
		//log::info("This script cannot be run multi threaded. Best to run one 'all' and one 'parse' make sure to cleanAll the first time.");
		//db::query("DELETE FROM load_queue where type ='$type' and processing = 0");
		
		$this->threads=1;		
		//$this->timeout = 90;
		//$this->debug=1; http://network.corenetglobal.org/network/members/profile/?UserKey=490eb84e-d245-4ff2-897d-f6aec1278f2f
		
		//$this->noProxy=true;
		//$this->proxy = "localhost:8888";
		
		$this->noProxy=false;
		$this->useCookies=true;
		$thiz = self::getInstance();
		
		// get an sso token by logging into thesite, 
		// https://crewnetwork.org/home
		// and copy ssoToken here.
		$this->ssoToken = "e4ba4c9b-65fc-4e08-aeb1-801badf089d9";
	
		log::info("Login OK!");
		
		$url = "https://crewnetwork.connectedcommunity.org/network/members?ssoToken=1a525b84-4ac5-49c0-ae7c-3d76e440359b";
		log::info("loading $url");
		$html = $thiz->get($url);
		
		$thiz->get($url); // do an sso login
		$x= new HtmlParser($html);
		$searchDatas = $x->getForm();		
		$searchData=$searchDatas[0];
		
		$searchData = array_merge($searchData, $x->loadViewState());
		
		foreach (range('A', 'Z') as $c1){
			foreach (range('A', 'Z') as $c2){
			$webRequests = array();
				$namekey = "$c1$c2";
				
				$url = "https://crewnetwork.connectedcommunity.org/network/members?firstname=".$namekey;					
				$postData = $this->getPostDataForSearch($namekey,"","");
				$webRequest = new WebRequest($url,$type,"POST", $postData);  // loading these with a low priority means each pages children are parsed before moving on to start a new search.
				log::info("Loading firstname $namekey")	;
				$thiz->loadWebRequest($webRequest); // force this load at least
							
				$url = "https://crewnetwork.connectedcommunity.org/network/members?lastname=".$namekey;					
				$postData = $this->getPostDataForSearch("",$namekey,"");
				$webRequest = new WebRequest($url,$type,"POST", $postData);  // loading these with a low priority means each pages children are parsed before moving on to start a new search.
				log::info("Loading lastname $namekey")	;
				$thiz->loadWebRequest($webRequest); // force this load at least
				
				
				$url = "https://crewnetwork.connectedcommunity.org/network/members?company=".$namekey;					
				$postData = $this->getPostDataForSearch("","",$namekey);
				$webRequest = new WebRequest($url,$type,"POST", $postData);  // loading these with a low priority means each pages children are parsed before moving on to start a new search.
				log::info("Loading company $namekey")	;
				$thiz->loadWebRequest($webRequest); // force this load at least
		
			}		
		}	
		$webRequest = new WebRequest("https://crewnetwork.org/2017-crew-network-convention-and-marketplace/attendee-list",$type,"GET", null,1000);  // loading this with a high priority so it gets processed last
		log::info("Loading attendee-list")	;
		$thiz->loadWebRequest($webRequest); // force this load at least
	}
	
	static function getPostDataForSearch($firstname,$lastname,$companyname) {
		return'ScriptManager1_TSM=%3B%3BAjaxControlToolkit%2C+Version%3D17.1.1.0%2C+Culture%3Dneutral%2C+PublicKeyToken%3D28f01b0e84b6d53e%3Aen-US%3A838279e1-14af-4865-b78d-64f914dee41b%3Acd9be5ef%3Af2800037%3A2761bb61%3A10439726%3Ac99a1521%3Adffb332%3A891e6cc6%3A9833e5c%3Af06639ea%3A98f9cc63%3Adda46be5%3A7b2ffb77%3Ad6567903&StyleSheetManager1_TSSM=&__EVENTTARGET=&__EVENTARGUMENT=&__VSTATE=H4sIAAAAAAAEAO1ZW1MbyRVejTSSEGDhG3vLjtoKDpBldBeS1sCuEGA7C17KYDtFKkW1ZlrSrEfTylyEtan8gjzlB%2BQlr%2FkJ%2B5aX%2FIr8kDwlp7tnpJFlFta7lXVSwS7ROn36nNPd5%2FKd5l%2BR9AcRWTk%2Fb1HLtanpPCW%2F8wybHFPH3cXayy%2FJ6Px8eV6%2Bp7lmobByQrCt9XzelWOb6p7mHhqOu1K4mqV4NUvpapby1SyVq1mqV7NsXs1Suw7LfcFyhA2rRQejFfa1trL%2FakBtd%2F%2BVRsynWDeo%2FPPvYGudPOdMaTmlNjaLlVKjUqim9WWpk04vS0rqsfOI9skx7pKevhyTImwKPtlc4rnhGG0TJnQpCnQFPoGcPnGx7RpW9wnVyTPblD%2FKt57uv3iyf%2Friq6df5jWbXFjEvaD2y3xRl2KwJCN9N498FU9Zl%2BJX8VR0KXEVT0mXkown0ltOik3CkO9uPJRhGFMWjgzNpo7hEvDitfVfn4wcl%2FRzDz1D30B9R6O2abQ30HNiOwa1tiu5Avu3gVqe6Xo22baI59rY3EDHXts0NBBySl8Sa7tdq%2BGqVt0sNsoVUqg3VoodXCqQTkUtlms1tVJpd9R2Ua%2BpnbZeKlQ7pXq5UxSGJZhhmejap7HvsWjOv05%2B32wosRuMnZJXrvyP2Jaj2cbARVoP2w5xt7Oe21Hr2Z2VtazR7%2F7GsbVfbq%2FukQ6GXYFjdgyTrP42u57Dut4yseOsZXUxCdyM7Lr2WhZWZTeyPdcdOJ%2Fl8z2j2yO2SbuGptMLy6RYzznlHO7jb6iFL5ycRvtT13WdveWfDZggoj%2Fug986%2BUPapXnH6A9MovZJv01sFQ8xeKnqGGaPesR1Se7rAdj4YDE1xDYa2H20jeBScy9I%2B4DafSfHIoAlMOK4R9iCL3auS9zHluNiSyNrbCWsYls%2FH8DsIde%2FNsAWMcWYcXQ8S3PBIVCIvuYQSyf2BsJ211n%2F%2FWLqf%2Fp0%2F7CVF061o0sp8LhkJwgtP63ElZvj2HpoU2%2FAAgx8GpeqmrbZ2FRr7XZdrRRLutqolcpquU0aBVyq4zquKQst2u97luGO%2FFUF%2F0d9w0fwI5QL94%2FyT3kSFVMUKc1SoDRJgZmYnD4keEjQWLEuUogia%2ByOCPBHfc4bv6KGNcWYmI6%2BeEeMlnR9%2BT09oPvfpAk5vXxLSfkewfLrQT7IX%2BL4nfxATOY%2Ff%2BYQG45im2ilUrVcr6t1Ui2plXK1oWKil1VcqWwWO0XS0RslJcVu0oPaYr2U%2FxzJP%2BLOc8icJ39CNM8Go%2FOCJYedwavPnxLIZdazp4fb3N%2Ful5v3SwfwP5RRc9TuAsUZEM3Apsoiw4Hv2HOpySXdLx%2FYQoxt3i%2FvTQu6uLjIzQrrQT1S5oQhbPvvuqm3gys3iMPOlifX%2BRBRmTvi9wYXJa%2Fst6rNUrFWVFuNYkOttA4qar1ZrKnVSr1cPmjsNfZrDUXm1UJe2WxVW3v7lbJaqh5sqpVSoaHWi3ubanW%2FXi41DpqVeqmlLDy22vQVGM0vdv1SZ%2BmPsKZRz3LzBluQV26EbGQHXb10qUYti%2FDM5sB4vMgCAJBXkic9evEYWHo83O9GPnx0%2BIKYwEfQePSIQEaxmXeL8iuJUYzofgSyr8r8%2Bb3HUGdbzEopIqLm1hQmSfKkxKz9YyTIguF78S0lemDmiF3VlAPpgFE1l9qjPJe1ZzgDE4%2BEH7FYqmC9VqyWOpCEcFmtbFYglmq4oOo1XStgrQYhVf%2BF7m5Xq43NarFSrlXrmwAZ4j0CSlxZqhQU%2BcLQ3R4bCmwBs6eQ%2FolLlPkneGh0sftjRnaA5jK33rVTydyWpc1C5g779HEepNQjbBsYLEJf6T2LeUVCTNzsU074wiWW%2BooVrwDBRP0iIs1zFLT8Y50cKN3ccqBaI57Ot1e75mjQM%2BC80HikeiBwdQcKG%2FDtID8xQ3pf9G15u7gB1TtXqjahD1Cx6U7Uh2IWTEizigomXD%2FqQe2DK9USa0hMOiATtQXkWTbEsICVN%2F2dv%2BOpGfYaF6ZwYB9kEbCcTcnMaEtnpXeM%2FmOiJvvOtsAGCfm2TS8QwHndYDcJBlp46Nf1%2BDg5BZ%2Bshbob4QU9IPFWA2aUeQgpjfSoCalQjju88YNcb7gmCb6G0yHPjnJgTMKXNMmfImjusPSLmvqQ4VQdiXbS79hiY65F1mASy0XuaMA9J5peYmAMMu4eYLld8BC9qyyyMatfBwYxdVl%2Bjk2PKDcYlQ8FOQrRpS%2FdTS42LQvWaaQPgh15F%2B7KCde9eUgimuewDsmJ7w8ZU%2FKhSR0H26OFE8B%2FyDdq7tBo25ATiHM3uRAWGmMy58aQKr7fZYhxsYVNwNTY5jLHIlN%2BZgV1CSFwdOfTaLLr%2F%2FgukBwfSQq2RZCNrS4JzkPKfAh8fhcI5eg4dF8xHdj9TBtTki3H4ShdTnWgf1ANa%2BC5Suz83smuJM000qeUmqfGQMgALe%2F70vxuM7FvYeiz9d6k05MyP5OTsEkU1pr5JKwto8xoymQmi7iaiQihKoPGOthRRLlfAl%2BqY9iOiywMeEZ2uUOGSHyxYJyDPQti5l7oCzdacCQg0UHKGMF8MGRZ3J9dJH1smAh6HJs4DvBME%2Fz3Aubvcnrr0%2FcWI%2B8FtwLpVrh%2BbBIEnVA0TMJQCgXjVPjEg5VBlIkgyHzke3rmY%2B7bTN1%2FnXsD9JIisPeoBJuW4lJCuHuc7REai0jHz4CXOpL0Bg%2B5Pnf0Os8q3%2Ff14q2ePG78303efTdZ%2BsndZO5tFs1PLF18m%2FXpyfq0wBZzGjXVvq4WS%2FwlYVL65b9EtqgZgLQ2w16a7fXbAMlMY2cLo55NOturAe5bRTxrb6%2Bu7uwFSH4rjwG%2FMe43rQiQ4nglc0OsuUgAEcSucnVHdK7oDTLz1NwRzpIaR9pPH%2F3RSfkIneXS69uYehkNIznxKp7wewim%2B3oN2B2hKiHH4QiNIQk6spi8sIsdQ5vGZePJpddwG9Kn4i8mf3AAEBU1rRGFdmmtqfcNC1HLHK3PRF%2BMhJ58p0nzs6TFWVJ6lnRzlnR7liSFn5f15YVxlMfCifS1e%2FEx2AEHGU8YhJjciX9wLYEegtnFdJBPJGVh37apfQSIAXoFuX9sEuwQBHkQLhmghA%2FUkQbdCIFekzkzcnvAEQYbOXTaI7BsyJK6g7BNkEVdhE2TXhD9M%2FTFxv2N3AZgmA1oKTZYO7rBmosgeHlB4e%2FpCa1HtJdEl4OBf3JTaH1JTy9HefJdji7J8%2FxejwkdANSKHHeX5BucIvYMNUCOtIA4x4m71O3Jkd3uMiwe9yjp5QXl45Zn27Br5q6PnXGF4G7Sy8jXflf9aEpO04OjslxDY7ixm4lf96FVmTuhtvuVzdBygl3aefNMuTUtmlvGfCQeqr6L3PWUBLU009Beyn%2BPfI2HWLwjf3YCRc6%2F6LXV4E9c5%2FxPXOehOWjLu%2BxSVzdmmLz21xD1rK3Zpa9m530Bl8776w9xm5iXrhaz6w%2BQ6FNRB5sOecAzPmwtsy7%2F6ZI9tVj4m%2F%2BRnc1alwxdQjSw9GHI0KauHwAisfQZA8czY%2FNmxcuByKM3ibxk59cRnHqT3WElT0mfDsklpocnr2P9ySWCL9nANcUvTcrjrem8x2NeVCzHz7gpkfcE%2B%2FvBnwo%2BhsHS3VSSxRpqqmdicKY254PUCcTxGOjJFmQHzskHQJk7cVkXDCR%2FJFZ7lmsHq8UY6HdTQVSL32fNlC8bSOPhWTPBhDMu%2FvusmeSSgeAP%2BDouVawTw7Mm4NRUd%2FzDsegnwVbviddf9le6E%2BMbIt0RnfzkSScmS3%2F7K%2FS%2F8OljD0aL5nI5ILJfY0TxOnXuNSnfcinfhjrOnx7W3Jy8gsbkf0a3vAAZZg%2Bwe0ApVLkjYnmIvxcalmlYJMtgX8DlwMEbWhbZFJBeFpoKDwpjPztGhdn8wDaGWBsNKGTgUTZYZ5IhMYtZ5PLX6%2B3sedvE1svszrHgRsecPQw0r69x%2FBY9NKjJG5Y3K98Z1zX0POD8QYrhsPoO7XgOYc%2BGbeObGY2njAPRDoKqhVTE%2FoIKbD9UG3vLvI4q9EQgdMTeLkOA22OAezmcB4LhvwHMzX3ViyMAAA%3D%3D&__VIEWSTATE=&__EVENTVALIDATION=R8ljoYAVN31ZmRU2f8v%2BFX40WN%2BmzsOKLWC58i%2BgWGFY%2Fc9ZEJ5TeWFVewd81Cdpc3eM4TVYPIm1J5ODXaiHovr2uF3PjCNJxpPw1VD461U3PFjBbb8ovfpWz04yGS84pBDjIV3Bn5AhaTsyue9PfQbuzy429hs6HDOK%2FkYylk4tGPfhPw%2BXbr1sB9Kw3PSS%2FFrfTQ4zrUkfxmQNzafRdgTNgqWaEaMIJvrXQq0pJXvUc8XlDIIzcKWNSbzAEvdiqIQ%2FZ1q7Rh%2BM9bB4pYUtD2wlRXb1d0NLPuM4OUpZS7wkfmtzWUbGQRNbM11sDtQa7Ei9rMVUDH0aUQjd8X4CHrZ36My8FfYolK%2BoQJhOtuCtkj7UlBm%2F6G7WEH7Bj9SvzHZQv1RWpmCcDfynjIq4pg5btW7cUA3EkGB0dJN8PYYpL4R9C1%2FD0LD7QCWyqLbxNfp5ZyupqWthQcZ3lOGMDOXfDOmkAZKPoqEhWSgF7ZPpmAvFCBahoauradfH4NI%2FdYNynYZuUs4rX5HATPs%2BBfGXTYzYycMnRjP3hQfLi9%2BSP3zmbvjbVu%2FQo6mfRPIYmF21Ng2r9iJA%2Fv3xnD%2FFfBgeLnwRvevfqspSFizZKj39mXtsKDjudQ4hTEdQYE%2FqnnxbVw5Hd2BQiPBwkwRJcvR14CKKpFdKFJ6zyUnt9rf348WobX0wiZGqwB22Nq4%2B4MeelCYgBwbeTRSOWy17qNn4nbFhxokRTWfG7fQlMakc2AMQj2aMULfV%2BHwTScyeFQ3GXBmC0xXXGhUorcbbcT3m3yfhVqsA7vO8ZqcQYfdjR5gWqGnsO%2FnGIEq6rvGbZLDam04kE%2B4yJEvGgkg%2B49L2UyZNohCIsNNbYkuYtPfBEsF6g2jC0JUwZuhWGsmBKNEu9A%3D%3D&ctl00%24SearchControl%24SearchTerm=&ctl00%24SearchControl%24ProductList%240=Announcement&ctl00%24SearchControl%24ProductList%241=Blog&ctl00%24SearchControl%24ProductList%242=Community&ctl00%24SearchControl%24ProductList%243=Egroup&ctl00%24SearchControl%24ProductList%244=CalendarEvent&ctl00%24SearchControl%24ProductList%245=Glossary&ctl00%24SearchControl%24ProductList%246=Navigation&ctl00%24SearchControl%24ProductList%247=Library&ctl00%24SearchControl%24DateRangeDDL=on+this+day&ctl00%24SearchControl%24StartDate%24ucTextBox=&ctl00%24SearchControl%24EndDate%24ucTextBox=&ctl00%24SearchControl%24FirstName=&ctl00%24SearchControl%24LastName=&ctl00%24SearchControl%24CompanyName=&ctl00%24SearchControl%24EmailAddress=&ctl00%24MainCopy%24ctl04%24FindFirstName=' . $firstname . '&ctl00%24MainCopy%24ctl04%24FindLastName=' . $lastname .'&ctl00%24MainCopy%24ctl04%24FindCompanyName='.$companyname.'&ctl00%24MainCopy%24ctl04%24EmailAddress=&ctl00%24MainCopy%24ctl07%24hdnTargetControl=&ctl00%24MainCopy%24ctl07%24SubjectTextBox=&ctl00%24MainCopy%24ctl07%24MessageTextBox=&ctl00%24MainCopy%24ctl07%24ExcelCSV=excel&ctl00%24MainCopy%24ctl07%24FindContacts=Find+Members';
	}

	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);
		$thiz = self::getInstance();
		if ($thiz->needLogin($html))
		{
			log::error("NEEDS LOGIN!!!");
			log::error("NEEDS LOGIN!!!");
			log::error("NEEDS LOGIN!!!");
			log::error("NEEDS LOGIN!!!");
			exit;
			
			return;
		}
		if (strlen($html) < 3000)
		{
			log::error("GOT tiny html?");
			log::info($html);
			
			return;
		}
		//sleep(2);
		
		baseScrape::loadCallBack($url,$html,$arg3);
	}
	
	
	public static function needLogin($html)
	{
	
		$thiz = self::getInstance();
		if (preg_match("/Account Login/",$html))			
		{
			log::info("Needs login");
			return true;
		}
		return false;
	}
	
	public static function parse($url,$html)
	{
		$type = get_class();    
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$np = new Name_Parser();
		$kvp = new KeyValue_Parser(); 

		log::info($url);

		$webRequests = array();
		  
		$x = new HtmlParser($html);		
		
		// load listings
		
		if (preg_match("#/2017-crew-network-convention-and-marketplace/attendee-list#",$url))
		{
			foreach($x->query("//div[contains(@class, 'member-container')]") as $node)
			{
				$x2 = new HtmlParser($node->c14n());
				$data = array();
				foreach($x2->query("//span[@class='memberName')]") as $node)
				{
					list($last,$first) = explode(",", $node->textContent);
					$data = array_merge($data,$np->parse("$first $last"));
				}
				foreach($x2->query("//span[contains(@id, 'LinkEmail')]") as $node)
				{
					$email = preg_replacE("/mailto:/","",$node->getAttribute("href"));
					$data = array_merge($data,$np->parse($email));
				}
				foreach($x2->query("//span[contains(@id, '_lblCompanyName')]") as $node)
				{
					$data['COMPANY'] = self::cleanup($node->textContent);
				}
				
				foreach($x2->query("//span[contains(@id, '_lblAddress')]") as $node)
				{
					$data = array_merge($data,$ap->parse($node->textContent));
				}
				
				$data['2017 CREW Network Convention'] = 1;
				log::info($data);
				db::store($type,$data,array('EMAIL'));
			}
			return;
		}
		
		
		if (!preg_match("#network/members/profile#",$url))
		{
			$links = array();
			foreach($x->query("//a[contains(@id, 'Contacts_DisplayName')]") as $node)
			{
			  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			log::info($links);
			$thiz->loadUrlsByArray($links);

			// get next page links?			
		}
		else
		{
			$data= array();
			
			//log::info($nodeTop->c14n());
			
			
			foreach($x->query("//span[contains(@id, '_lblName')]") as $node)
			{
				$data=  array_merge($data, $np->parse($node->textContent));		
			}
			
			
			foreach($x->query("//div[contains(@id, '_JobDepartmentPanel')]") as $node)
			{
				$data['TITLE'] = self::cleanup($node->textContent);		
				break;				
			}			


			foreach($x->query("//div[contains(@id, '_CompanyNamePanel')]") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);	
				break;				
			}			

			foreach($x->query("//div[contains(@id, '_presentJob_EmailAddressPanel')]") as $node)
			{
				$data['EMAIL'] = self::cleanup($node->textContent);		
				break;				
			}					

			$phone = "";	
			foreach($x->query("//div[contains(@id, 'Job_Phone')]") as $node)
			{
				$phone = self::cleanup($node->textContent).", \r\n ";
			}
			
			$data= array_merge($data, $pp->parse($phone));		
			
			/*$address = "";
			foreach($x->query("//div[@class='list-address-panel']//div") as $node)
			{
				$address.= $node->textContent. " , "	;			
			}	*/
			foreach($x->query("//a[contains(@id, 'presentJob_MapHyperlink')]") as $node)
			{
				$href = $node->getAttribute("href");
				parse_str(parse_url($href,PHP_URL_QUERY),$q); 
				$data= array_merge($data, $ap->parse($q['q']));
			}						
			
			$data['SOURCE_URL'] = $url;
			
			if (!empty($data))
			{
				//$citystate = urlencode();
				log::info($data);
				db::store($type,$data,array('EMAIL'));
			}
		}		
	}
}



$r= new corenetglobal();



$r->parseCommandLine();

