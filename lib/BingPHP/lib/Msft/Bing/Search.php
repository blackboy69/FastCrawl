<?PHP
/**
 * Microsoft Bing Search
 * 
 * PHP library to support the Microsoft Bing Search API.
 *
 * PHP Version 5
 *  
 * @category  Msft
 * @package   Msft
 * @author    Cal Evans 
 * @copyright 2010 Microsoft
 * @license   New BSD License (BSD) http://bingphp.codeplex.com/license
 * @version   SVN: $Id: Search.php 50 2010-03-24 14:31:53Z cal $
 * @link      http://bingphp.codeplex.com
 *
 */


/**
 * Search class for the Bing Search API.
 * 
 * @todo Implement PhonebookSearch sourceType
 * @todo Implement InstanceAnswer sourceType
 * @todo Implement MobileWeb sourceType
 * @todo Implement News sourceType
 * @todo Implement RelatedSearch sourceType
 * @todo Implement Translation sourceType
 * @todo Implement Video sourceType
 * @todo Implement new returnType that is a PHP class.
 * 
 * @category   Msft
 * @package    Msft
 * @subpackage Bing
 * @author     Cal Evans 
 * @license    New BSD License (BSD) http://bingphp.codeplex.com/license
 * @link       http://bingphp.codeplex.com
 */
class Msft_Bing_Search
{

    /**
     * Adult filter setting
     * @access protected
     * @var string Off|Moderate|Strict
     */
    protected $adult = 'Moderate';

    /**
     * Bing Application ID - http://www.bing.com/developers/createapp.aspx
     * @access protected
     * @var string 
     */
    protected $appid = '';

    /**
     * Base bing URL
     * @access protected
     * @var string http://api.bing.net/
     */
    protected $baseUrl = 'http://api.bing.net/';

    /**
     * Flags that have been set.
     * @access protected
     * @var array
     */
    protected $flags = array();

    /**
     * List of preferred file types to return
     * @access protected
     * @var array
     */
    protected $filetype = array();

    /**
     * Output format (json|xml)
     * @access protected
     * @var string
     */
    protected $format = 'json';

    /**
     * Number of image results to return (1-100)
     * @access protected
     * @var integer
     */
    protected $imagecount = 10;

    /**
     * JSON format option (raw|callback)
     * @access protected
     * @var string
     */
    protected $jsontype = 'raw';

    /**
     * Market (see http://msdn.microsoft.com/en-us/library/dd251064.aspx)
     * @access protected
     * @var string
     */
    protected $market = '';

    /**
     * The query string
     * @access protected
     * @var string
     */
    protected $query = '';

    /**
     * Site to limit the search to
     * @access protected
     * @var string
     */
    protected $site = '';

    /**
     * Sources to draw from when searching [web|image|spell]
     * @access protected
     * @var array
     */
    protected $sources = array();

    /**
     * Valid file types that can be specified.
     * @access protected
     * @var array
     */
    protected $validFileTypes = array();

    /**
     * Valid flags that can be specified.
     * @access protected
     * @var array
     */
    protected $validFlags = array();

    /**
     * Valid formats that can be specified.
     * @access protected
     * @var array
     */
    protected $validFormats = array();

    /**
     * Valid formats options that can be specified.
     * @access protected
     * @var array
     */
    protected $validFormatOptions = array();

    /**
     * Valid sources that can be specified.
     * @access protected
     * @var array
     */
    protected $validSources = array();

    /**
     * API version (2.0|2.2)
     * @access protected
     * @var number
     */
    protected $version = 2.2;

    /**
     * URL used in the last search performed by the class
     * @access protected
     * @var string
     */
    protected $url = null;

    /**
     * Number of web results to return (1-100)
     * @access protected
     * @var integer
     */
    protected $webcount = 10;

    /**
     * Offset from 0 to begin the list of urls returned. (0-100)
     * @access protected
     * @var integer
     */
    protected $weboffset = null;

    /**
     * XML output type specified
     * @access protected
     * @var string
     */
    protected $xmltype = '';
    

    /**
     * constructor
     * 
     * @param string $appId The Bing APP ID
     */
    public function __construct($appId='')
    {
        $this->validFileTypes     = array('DOC','DWF','FEED','HTM','HTML',
                                          'PDF','PPT','PS','RTF','TEXT','TXT',
                                          'XLS');
        $this->validFlags         = array('DisableHostCollapsing',
                                          'DisableQueryAlterations');
        $this->validSources       = array('Image','InstantAnswer','MobileWeb',
                                          'News','PhoneBook','RelatedSearch',
                                         'Spell','Translation','Video','Web');
        $this->validFormats       = array('json','xml');
        $this->validFormatOptions = array('json' => array('raw','callback'),
                                           'xml'  => array('elementbased',
                                                           'attributebased'));
        if (!empty($appId)) {
            $this->appid = $appId;
        }
        return;
    } // function __construct

    
    /**
     * Attempts to execute the currently-specified search
     * 
     * @return string The results from the query
     * @throws Msft_Bing_Search_Exception
     */
    public function search()
    {
        if (empty($this->appid)) {
            throw new Msft_Bing_Search_Exception('Empty AppId');
        }
        
        if (empty($this->sources)) {
            $this->sources[]='web';
        }
        
             
        $this->url = $this->buildUrl();
        
        if ($stream = @fopen($this->url, 'r')) {
            $output = stream_get_contents($stream);
            fClose($stream);
        } else {
            throw new Msft_Bing_Search_Exception('Problem opening stream');
        }
        // print all the page starting at the offset 10
        return $output;
    } // public function search()


    /**
     * Construct the URL used in the search
     * 
     * @return string the url
     */
    protected function buildUrl()
    {
        $url = $this->baseUrl . $this->getFormat() . '.aspx';
        $url .= "?"
             . $this->getParameter('AppId', true)
             . $this->getParameter('Adult')
             . $this->getParameter('Version')
             . $this->getParameter('Market')
             . $this->getParameter('Sources')
             . $this->getParameter('Web.Count')
             . $this->getParameter('Image.Count')
             . $this->getParameter('Web.Offset')
             . $this->getParameter('FileType')
             . $this->getParameter('Flags')
             . $this->getParameter('JsonType')
             . $this->getParameter('XmlType')            
             . '&Query='.$this->getQuery(true);
             return $url;
    }

    /*
     * Helper methods
     */

    /**
     * Return either the value passed in if it's between the min and max or the
     * min or max.
     *
     * @param number $value The value to check
     * @param number $min   The minimum allowable value
     * @param number $max   The maximum allowable value
     * 
     * @return number
     */
    protected function between($value, $min, $max)
    {
        $value = (int)$value;
        $value = max($min, $value);
        $value = min($max, $value);
        return $value;        
    }


    /**
     * Properly format and urlencode the key=value pair requested.
     * 
     * @param string  $name            The name of the parameter
     * @param boolean $ignoreAmpersand If true, does not prepend the output with
     *                                 an ampersand. Default behaviour is to
     *                                 prepend the ampersand.
     *                                 
     * @return mixed
     * 
     * @throws Msft_Bing_Search_Exception
     */
    protected function getParameter($name, $ignoreAmpersand=false)
    {
        
        $paramName    = str_replace('.', '', strtolower($name));        
        $returnValue = '';
        
        if (!property_exists($this, $paramName)) {
            throw new Msft_Bing_Search_Exception($paramName.
                                                 ' is an invlaid parameter');
        }
        
        If (is_array($this->$paramName)) {
            $allValues = implode(' ', $this->$paramName);
            $returnValue .= (!empty($allValues)?($name.'='):'').
                            urlencode(trim($allValues));
        } else {

            if (isset($this->$paramName) and !empty($this->$paramName)) {
                $returnValue .= $name.'='.urlencode($this->$paramName);
            }
        }
        if (!empty($returnValue)) {
            $returnValue  = ((boolean)$ignoreAmpersand?'':'&').$returnValue;
        }
        return $returnValue;
    } // protected function getParameter($name)


    /*
     * Getters and Setters
     */
    /**
     * Returns the value of the adult filter
     * 
     * @return string
     */
    public function getAdult()
    {
        return $this->adult;
    } // public function getAdult()
    
    /**
     * Sets the value for the adult filter
     * 
     * @param string $value The new value (Off|Moderate|Strict)
     * 
     * @return Msft_Bing_Search
     */
    public function setAdult($value)
    {
        $this->adult = $value;
        return $this;
    } // public function setAdult($value)
    
    
    /**
     * Returns the AppID currently set in the object
     * 
     * @return string
     */
    public function getAppId()
    {
        return $this->appid;
    } // public function getAppId()
    
    /**
     * Set the AppID for this object
     * 
     * @param string $value The Bing application id
     * 
     * @return Msft_Bing_Search
     */
    public function setAppId($value)
    {
        if (!is_null($value)) {
            $this->appid = $value;
        }
        return $this;
    } // public function setAppId($value)
    
    
    /**
     * Returns the value of the base Bing URL
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    } // public function getBaseUrl()
    
    /**
     * Sets the value of the base Bing URL
     * 
     * @param string $value The new base url
     * 
     * @return Msft_Bing_Search
     */
    public function setBaseUrl($value)
    {
        if (!is_null($value)) {
            $this->baseUrl = $value . (substr($value, -1, 1)!='/'?'/':'');
        } // if (!is_null($value))
        return $this;
    } // public function setBaseUrl($value)
    
    
    /**
     * Returns the maximum number of web results to be requested
     * 
     * @return integer
     */
    public function getWebCount()
    {
        return $this->webcount;
    } // public function getCount()
    
    /**
     * set the maximum number of web results to be requested
     * 
     * @param int $value 1-100 The new value of WebSource.count
     * 
     * @return Msft_Bing_Search
     */
    public function setWebCount($value)
    {
        $this->webcount = $this->between($value, 1, 100);
        return $this;
    } // public function setCount($value)


    /**
     * Returns the maximum number of image results to be requested
     * 
     * @return integer
     */
    public function getImageCount()
    {
        return $this->imagecount;
    } // public function getImageCount()
    
    /**
     * Sets the maximum number of image results to be requested
     * 
     * @param int $value 1-100 The new value of ImageSource.count
     * 
     * @return Msft_Bing_Search
     */
    public function setImageCount($value)
    {
        $this->imagecount = $this->between($value, 1, 100);
        return $this;
    } // public function setImageCount($value)

        
    /**
     * Returns the requested result format
     * 
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    } // public function getFormat()
    
    /**
     * Sets the value of the requested result format. format is validated
     * against validFormats. formatOption is reset to the first listed
     * validFormatOption. Other options are cleared.
     * 
     * @param string $value The new value
     * 
     * @return Msft_Bing_Search
     * 
     * @throws Msft_Bing_Search_Exception
     */
    public function setFormat($value)
    {
        $value = strtolower($value);
        if (in_array($value, $this->validFormats)) {
            $this->format = $value;
            $this->_fromatOption = $this->validFormatOptions[$value][0];
            foreach ($this->validFormats as $key) {
                $property = '_'.$key.'type';
                $this->$property = '';
            }
        } else {
            throw new Msft_Bing_Search_Exception($value.' is an invalid format.');
        }
        return $this;
    } // public function setFormat($value)


    /**
     * Returns the value of the specified format options.
     * 
     * @return string
     */
    public function getFormatOption()
    {
        return $this->formatOption;
    }

    /**
     * Sets the specified format option. option is validated against
     * the validFormats array
     * 
     * @param string $value The new value
     * 
     * @return Msft_Bing_Search
     */
    public function setFormatOption($value)
    {
        $value = strtolower($value);
        if (in_array($value, $this->validFormatOptions[$this->format])) {
            $this->formatoption = $value;
        } else {
            throw new Msft_Bing_Search_Exception($value.
                       ' is not a valid option for the format '.$this->format);
        }
        return $this;        
    } // public function setFormatOption($key)
    
    
    /**
     * Returns the language of the results
     * 
     * @return string
     */
    public function getMarket()
    {
        return $this->market;
    } // public function getMarket()

    /**
     * Sets the language of the results
     * 
     * @param string $value The new value
     * 
     * @return Msft_Bing_Search
     */
    public function setMarket($value)
    {
        $this->market = $value;
        return $this;
    } // public function setMarket($value)
    
    
    /**
     * Returns the value of the requested result offset
     * 
     * @return integer
     */
    public function getWebOffset()
    {
        return $this->weboffset;
    }
    
    /**
     * Sets the value of the requested result offset
     * 
     * @param integer $value The new value
     * 
     * @return Msft_Bing_Search
     */
    public function setWebOffset($value)
    {
        $this->weboffset = $this->between($value, 0, 1000);
        return $this;
    }


    /**
     * Returns the value of the Bing API version used by the object
     * 
     * @return float
     */
    public function getVersion()
    {
        return $this->version;        
    }
    
    /**
     * Sets the value of the Bing API version to use
     * 
     * @param float $value The new value
     * 
     * @return Msft_Bing_Search
     */
    public function setVersion($value)
    {
        $this->version = (float)$value;
        return $this;
    }

    
    /**
     * Returns the array of preferred file types for the object
     * 
     * @return array
     */
    public function getFileTypes()
    {
        return $this->filetype;
    } // public function getFileType()
    
    /**
     * Toggles a value in the list of preferred file types. 
     * 
     * @param string $value The value to be toggled
     * 
     * @return Msft_Bing_Search
     * 
     * @throws Msft_Bing_Search_Exception
     */
    public function setFileType($value)
    {
        $value   = strtoupper($value);
        $testKey = array_search($value, $this->validFileTypes);
        
        if ($testKey!==false) {
            $key = array_search($value, $this->filetype);
            if ($key===false) {
                    $this->filetype[] = $value;
            } else {
                unset($this->filetype[$key]);
            }
        } else {
            throw new Msft_Bing_Search_Exception($value.
                                                 ' is not a valid File Type');
        }
        return $this;
    } // public function setFileType($key)
    
    
    /**
     * Returns the array of flags specified for the object
     * 
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    } // public function getFlagss()
    
    /**
     * Toggles a value in the list of acceptable flags.
     * 
     * @param string $value The value to be toggled
     * 
     * @return Msft_Bing_Search
     * 
     * @throws Msft_Bing_Search_Exception
     */
    public function setFlag($value)
    {
        $testKey = array_search($value, $this->validFlags);
        
        if ($testKey!==false) {
            $key = array_search($value, $this->flags);
            if ($key===false) {
                    $this->flags[] = $value;
            } else {
                unset($this->flags[$key]);
            }
        } else {
            throw new Msft_Bing_Search_Exception($value.
                                                 ' is not a valid Flag.');
        }
        return $this;
    } // public function setOption($key, $value)


    /**
     * Returns an array of the sources specified for the object
     * 
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    } // public function getSource($key)
    
    
    /**
     * Toggles a value in the list of requested sources
     * 
     * @param string $value The value to be toggled
     * 
     * @return Msft_Bing_Search
     * 
     * @throws Msft_Bing_Search_Exception
     */
    public function setSource($value)
    {
        $testKey = array_search($value, $this->validSources);
        
        if ($testKey!==false) {
            $key = array_search($value, $this->sources);
            if ($key===false) {
                    $this->sources[] = $value;
            } else {
                unset($this->sources[$key]);
            }
        } else {
            throw new Msft_Bing_Search_Exception($value.
                                                 ' is not a valid Source.');
        }
        return $this;
    } // public function setSource($key, $value)
    
    
    /**
     * Returns the value of the search query
     * 
     * @param bool $encode true if the return value should be urlencoded.
     * 
     * @return string
     */
    public function getQuery($encode=false)
    {
		$returnValue = "";
        if ($this->isSiteSpecific()) {
            $returnValue = 'site:'.$this->getSite()." ";
        }

        $returnValue .= $this->query;    
        
        if ($encode) {
            $returnValue = urlencode($returnValue);
        } // if ($encode)
        
        return $returnValue;
    }

    /**
     * Sets the search query
     * 
     * @param string $value the new value
     * 
     * @return Msft_Bing_Search
     */
    public function setQuery($value)
    {
        $this->query = $value;
        return $this;
    } // public function setSource($key, $value)
        

    /**
     * Returns the value of the site specified for a site-specific search.
     * 
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Sets the site address for a site-specific search
     * 
     * @param string $value The site address (no http:// or https://)
     * 
     * @return Msft_Bing_Search
     */
    public function setSite($value)
    {
        $this->site = $value;
        return $this;
    }

    
    /*
     * READ ONLY
     */
    /**
     * Returns true if the search is site specific
     * 
     * @return boolean
     */
    public function isSiteSpecific()
    {
        return (isset($this->site) and
                !empty($this->site));
    }


    /**
     * Returns the URL of the last search executed by the object
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
}