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
 * @version   SVN: $Id: LibraryIntegrityTest.php 41 2010-03-17 01:08:50Z marco $
 * @link      http://bingphp.codeplex.com
 */

require_once "PHPUnit/Framework.php";

require_once dirname(__FILE__) . "/../Msft/Exception.php";
require_once dirname(__FILE__) . "/../Msft/Bing/Exception.php";
require_once dirname(__FILE__) . "/../Msft/Bing/Search/Exception.php";
require_once dirname(__FILE__) . "/../Msft/Bing/Search.php";

class LibraryIntegrityTest extends PHPUnit_Framework_TestCase {
	protected $searchObject;
	
	function setUp() {
		$this->searchObject = new Msft_Bing_Search;
	}
	
	function checkGetterSetter($property, $testValue = "abc") {
		$setter = "set" . $property;
		$getter = "get" . $property;
		
		$this->assertTrue(method_exists($this->searchObject, $getter), "Getter for expected property «{$property}» does not exist in the main library");
		$this->assertTrue(method_exists($this->searchObject, $setter), "Setter for expected property «{$property}» does not exist in the main library");
		
		$this->searchObject->$setter($testValue);
		$this->assertEquals($this->searchObject->$getter(), $testValue, "The setter and getter for property «{$property}» are not congruent");
	}
	
	function testGettersAndSetters() {
		$this->checkGetterSetter("Adult");
		$this->checkGetterSetter("AppId");
		$this->checkGetterSetter("BaseUrl", "http://blueparabola.com/");
		$this->checkGetterSetter("WebCount", 10);
		$this->checkGetterSetter("ImageCount", 10);
		$this->checkGetterSetter("Market");
		$this->checkGetterSetter("WebOffset", 10);
		$this->checkGetterSetter("Version", 1.5);
		$this->checkGetterSetter("Query");
		$this->checkGetterSetter("Site", "abc");
	}
	
	function testJsonFormat() {
		$this->searchObject->setFormat("json");
		
		$this->searchObject->setFormatOption("raw");
		$this->assertEquals($this->searchObject->getFormatOption(), "raw", "The format option getter and setter are not congruent");
		$this->searchObject->setFormatOption("callback");
		
		$this->setExpectedException('Msft_Bing_Search_Exception');
		$this->searchObject->setFormatOption("invalidFormatOption");
	}
	
	function testXmlFormat() {
		$this->searchObject->setFormat("xml");
		
		$this->searchObject->setFormatOption("elementbased");
		$this->searchObject->setFormatOption("attributebased");
		
		$this->setExpectedException('Msft_Bing_Search_Exception');
		$this->searchObject->setFormatOption("invalidFormatOption");
	}
	
	function testInvalidFormat() {
		$this->setExpectedException('Msft_Bing_Search_Exception');
		$this->searchObject->setFormat("invalidFormatOption");
	}
	
	function checkArrayGetterSetter($getter, $setter, $array, $name, $caseSensitive = true) {
		foreach ($array as $v) {
			$this->searchObject->$setter($v);
		}
		
		$this->assertEquals($this->searchObject->$getter(), $array, "The $name getter and setter are incongruent");
		
		$this->searchObject->$setter($array[1]);
		$this->assertFalse(in_array($array[1], $this->searchObject->$getter()), "The $name setter does not toggle values as expected");
		
		if ($caseSensitive) {
			$fail = true;
			
			try {
				$this->searchObject->$setter(ucfirst(strtolower($array[1])));
			} catch (Msft_Bing_Search_Exception $e) {
				$fail = false;
			}
			
			$this->assertFalse($fail, "The $name setter is not case sensitive");
		} else {
			$this->searchObject->$setter(ucfirst(strtolower($array[1])));
			$this->assertTrue(in_array($array[1], $this->searchObject->$getter()), "The $name setter is case sensitive");
		}

		try {
			$this->searchObject->$setter("invalidValue");
		} catch (Msft_Bing_Search_Exception $e) {
			return;
		}
		
		$this->fail("The $name setter allows invalid values");
	}
	
	function testFileTypes() {
		$this->checkArrayGetterSetter(
			'getFileTypes',
			'setFileType',
			array('DOC','DWF','FEED','HTM','HTML','PDF','PPT','PS','RTF','TEXT','TXT','XLS'),
			'filetype',
			false);
	}
	
	function testFlags() {
		$this->checkArrayGetterSetter(
			'getFlags',
			'setFlag',
			array('DisableHostCollapsing','DisableQueryAlterations'),
			'flag');
	}
	
	function testSources() {
		$this->checkArrayGetterSetter(
			'getSource',
			'setSource',
			array('Image','InstantAnswer','MobileWeb','News','PhoneBook','RelatedSearch','Spell','Translation','Video','Web'),
			'source');
	}
	
	function testSite() {
		$searchObject = new Msft_Bing_Search;
		
		$this->assertFalse($searchObject->isSiteSpecific(), "The search object returns that it is site specific even when no site has been set");
		$searchObject->setSite("abc");
		$this->assertTrue($searchObject->isSiteSpecific(), "The search object does not return that it is site specific even after a site has been set");
	}
	
	function testEmptyAppId() {
		$searchObject = new Msft_Bing_Search;
		
		$this->setExpectedException('Msft_Bing_Search_Exception');
		$searchObject->search();
	}
}
