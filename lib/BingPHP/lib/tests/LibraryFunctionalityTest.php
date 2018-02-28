<?php

/**
 * Microsoft Bing Search
 * 
 * PHP library to support the Microsoft Bing Search API.
 * Unit Tests
 *
 * PHP Version 5
 *  
 * @category  Msft
 * @package   Msft
 * @author    Marco Tabini 
 * @copyright 2010 Microsoft Corporation
 * @license   New BSD License (BSD) http://bingphp.codeplex.com/license
 * @version   SVN: $Id: LibraryFunctionalityTest.php 41 2010-03-17 01:08:50Z marco $
 * @link      http://bingphp.codeplex.com
 */

require_once "PHPUnit/Framework.php";

require_once dirname(__FILE__) . "/../Msft/Exception.php";
require_once dirname(__FILE__) . "/../Msft/Bing/Exception.php";
require_once dirname(__FILE__) . "/../Msft/Bing/Search/Exception.php";
require_once dirname(__FILE__) . "/../Msft/Bing/Search.php";

class LibraryFunctionalityTest extends PHPUnit_Framework_TestCase {
	protected $searchObject;
	
	function setUp() {
		if (!file_exists(dirname(__FILE__) . '/appid.php')) {
			$this->markTestSkipped("No APPID found. Check the README file in the tests directory to find out how to enable functionality testing");
			return;
		}
		
		require_once dirname(__FILE__) . '/appid.php';
	}
	
	function testSearch() {
		// Expect failure with a bad App ID
		$searchObject = new Msft_Bing_Search("123");
		$searchObject->setQuery("Blue Parabola");
		$result = json_decode($searchObject->search());
		
		$this->assertTrue($result instanceof StdClass, "Search results do not appear to be JSONized");
		
		$this->assertObjectHasAttribute('SearchResponse', $result, "Search results do not contain a SearchResponse property");
		$this->assertObjectHasAttribute('Version', $result->SearchResponse, "Search results do not contain a Version");
		$this->assertObjectHasAttribute('Query', $result->SearchResponse, "Search results do not contain a Query");
		$this->assertObjectHasAttribute('Errors', $result->SearchResponse, "Search results do not contain an expected Errors array");
		$this->assertTrue(is_array($result->SearchResponse->Errors), "The errors property of the search results is not an array on failure");

		$searchObject = new Msft_Bing_Search(BingAppId::APPID);
		$searchObject->setQuery("BlueParabola");
		$result = json_decode($searchObject->search());

		$this->assertTrue($result instanceof StdClass, "Search results do not appear to be JSONized");
		
		$this->assertFalse(isset($result->Errors), "An expectedly successful search returned an error");
		$this->assertObjectHasAttribute('Web', $result->SearchResponse, "Successful search results do not contain a Web property");
	}
	
	function testSearchStreamError() {
		$searchObject = new Msft_Bing_Search("123");
		$searchObject->setBaseUrl("http://nonexistent-site");
		$searchObject->setQuery("Blue Parabola");
		
		try {
			$searchObject->search();
		} catch (Msft_Bing_Search_Exception $e) {
			return;
		}
		
		$this->fail("Stream failure does not throw an exception");
	}
	
	function testSiteSpecific() {
		$searchObject = new Msft_Bing_Search(BingAppId::APPID);
		$searchObject->setQuery("Blue Parabola");
		$searchObject->setSite("blueparabola.com");
		
		$result = json_decode($searchObject->search());
		
		foreach($result->SearchResponse->Web->Results as $v) {
			$url = parse_url($v->Url);
			
			if ($url['host'] != 'blueparabola.com') {
				$this->fail("Site-specific search is returning results from unexpected site (note: this may just be search engine heuristics at work)");
			}
		}
	}
	
	function testLimitResults() {
		$searchObject = new Msft_Bing_Search(BingAppId::APPID);
		$searchObject->setQuery("Blue Parabola");
		$searchObject->setWebCount(3);
		
		$result = json_decode($searchObject->search());
		$this->assertEquals(count($result->SearchResponse->Web->Results), 3, "Result limiting does not seem to work");
	}
	
	function testResultOffset() {
		$searchObject = new Msft_Bing_Search(BingAppId::APPID);
		$searchObject->setQuery("Blue Parabola");
		$searchObject->setWebOffset(3);
		
		$result = json_decode($searchObject->search());
		$this->assertEquals($result->SearchResponse->Web->Offset, 3, "Result offsetting doesn't seem to work");
	}

	function testGetUrl() {
		$searchObject = new Msft_Bing_Search(BingAppId::APPID);
		$searchObject->setQuery("Blue Parabola");
		$searchObject->setWebOffset(3);
		
		$searchObject->search();
		
		$expectedUrl = "http://api.bing.net/json.aspx?AppId=" . 
						BingAppId::APPID . 
						"&Adult=Moderate&Version=2.2&Sources=web&Web.Count=10&Image.Count=10&Web.Offset=3&JsonType=raw&Query=Blue+Parabola";
		
		$this->assertEquals($searchObject->getUrl(), $expectedUrl, "The getUrl() method is not returning the expected URL (this error can be safely ignored)");
	}
}
