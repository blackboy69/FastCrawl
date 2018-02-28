<?php 
require($_SERVER['DOCUMENT_ROOT']."/classes/attach_mailer/attach_mailer_class.php");

$test = new attach_mailer($name = "Olaf", $from = "you@gmail.com", $to = "she@sites.com", $cc = "", $bcc = "", $subject = "html mail with two images and att.");
$test->add_html_image("image.gif");
$test->add_html_image("vcss.png"); 
$test->add_attach_file("ip2nation.zip");
$test->html_body = file_get_contents("html_attachment.htm");
$test->text_body = strip_tags($test->html_body, "<a>");
$test->process_mail();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Attachment Mailer example script</title>
</head>

<body>
<h2>Attachment Mailer HTML example</h2>
<p>This is a HTML example mail script using html code with inline attachments (images) and one regular attatchment. </p>
<p style="color:#FF0000;"><?php echo $test->get_msg_str(); ?></p>
</body>
</html>
