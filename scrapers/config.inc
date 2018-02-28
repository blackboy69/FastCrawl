<?
DEFINE('YAHOO_APPID','OhomF67V34FyWUcKF1zv0ktgcLBYZwc8zSYER2wLfZyV.1DJs2eDXZbxv14R88xnNDNxzE2Y');

set_include_path('..\lib\;..\lib\scrape'.get_include_path());


require_once "load_queue.inc";
require_once "baseScrape.inc";
require_once "log.inc";
require_once "db.inc";
require_once "utils.inc";
require_once "win32.inc";
require_once "captcha_parser.php";
require_once "captcha_parser_noise.php";
require_once "address_parser.php";
require_once "phone_parser.php";
require_once "email_parser.php";
require_once "operating_hours_parser.php";
require_once "keyvalue_parser.php";
require_once "name_parser.php";
require_once "HtmlParser.php";
require_once "AddressLookup.php";
require_once "search_engine_google.php";
require_once "search_engine_bing.php";
require_once "search_engine_yahoo.php";
require_once "search_engine_mapquest_api.php";
require_once "PHPExcel.php";
require_once "Encoding.php";
require_once "proxy.inc";
require_once "Html2Text.php";
require_once "vcard.php";



/*$host = "localhost";
$user = 'root';
$pass = '';
$dbname = 'demandforce';
*/

$host = "localhost";
$user = 'root';
$pass = '';
$dbname = 'fastcrawl';
db::init($host,$user,$pass,$dbname);


log::error_level(ERROR_INFO );
error_reporting  (E_ALL & ~E_STRICT);
ini_set("display_errors",true);

//xdebug_enable();
