<?

include ("captcha_parser.php");
include ("captcha_parser_noise.php");
$captcha_parser = new captcha_parser();
$captcha_parser_noise = new captcha_parser_noise();

foreach(scandir("./captcha_tests") as $test_image)
{
	if ($test_image == '.' || $test_image == '..') continue;
	$actual_text = preg_replace("/\.[a-z]{3}$/i","", basename($test_image));

	$parsed_text = $captcha_parser->parse("./captcha_tests/$test_image");
	if ($actual_text == $parsed_text)
	{
		echo "MATCH: $test_image => $parsed_text (captcha_parser) \n";
		continue;
	}

	$parsed_text = $captcha_parser_noise->parse("./captcha_tests/$test_image");
	if ($actual_text == $parsed_text)
	{
		echo "MATCH: $test_image => $parsed_text (captcha_parser_noise)\n";
		continue;
	}

	echo "FAIL:  $test_image => $parsed_text (captcha_parser_noise) \n";
	

}
