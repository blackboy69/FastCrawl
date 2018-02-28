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

DEFINE("PATH_TO_OCR",'"D:/Program Files/Tesseract-OCR/"' );

class captcha_parser
{
	public $invalidCharsRegex = "/[^0-9A-Z]/";

	// bare tessearct ocr scan
	// $path_to_image: url or filesystem path to captcha image
	// returns : text of captcha
	public function parse($source_image)
	{
		if (! file_exists($source_image))
			throw new exception ("captcha_parser::parse( $source_image not present");

		$tesseract = PATH_TO_OCR.'tesseract.exe';
		$working_image = tempnam(null,".jpg");

		copy($source_image,$working_image);

		`$tesseract $working_image $working_image -psm 7`;
		unlink($working_image);

		$text = trim(file_get_contents("${working_image}.txt"));
		unlink("${working_image}.txt");

		// remove invalid chars. usuall anything that isn't 0-9, a-z
		$text = preg_replace($this->invalidCharsRegex,"",$text);

		return $text;
	}
}