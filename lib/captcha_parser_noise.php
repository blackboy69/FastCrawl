<?
/* 
Byron Whitlock byronwhitlock@gmail.com
January 2012
Captcha Parser uses Tesseract OCR and other methods to read any generic captcha image and return ascii text.
http://code.google.com/p/tesseract-ocr/

Currently this class only works with very simple captchas.
*/

// path to open source library 
// Tesseract-OCR
// convert Captcha3.jpg -gamma 10 -paint 2 -monochrome c.png & tesseract C.png C -psm 7 ./car_captcha.conf & cat C.txt

include_once("captcha_parser.php");
DEFINE("PATH_TO_IMAGE_MAGIC",'"c:/Program Files/ImageMagick-6.9.1-Q16/"' );

class captcha_parser_noise extends captcha_parser
{

	public $tesseractConfigContents = "tessedit_char_whitelist abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	// bare tessearct ocr scan
	// $path_to_image: url or filesystem path to captcha image
	// returns : text of captcha
	public function parse($source_image)
	{
		if (! file_exists($source_image))
			throw new exception ("captcha_parser::parse( $source_image not present");

		$tesseract = PATH_TO_OCR.'tesseract.exe';
		$convert = PATH_TO_IMAGE_MAGIC.'convert.exe';
		$working_image = tempnam(null,".png");
		$config = tempnam(null,".confg");
		file_put_contents($config,$this->tesseractConfigContents);

		`$convert "$source_image" -gamma 10 -paint 2 -monochrome "$working_image"`;
//		copy($source_image,$working_image);

		`$tesseract $working_image $working_image -psm 7 "$config"`;
		unlink($working_image);
		unlink($config);

		$text = trim(file_get_contents("${working_image}.txt"));
		unlink("${working_image}.txt");

		// remove invalid chars. usuall anything that isn't 0-9, a-z
		//$text = preg_replace($this->invalidCharsRegex,"",$text);

		return $text;
	}
}