<?

include_once "email_parser.php";

$ep = new Email_Parser();

print_r( $ep->parse("Can you find all of the byron@gmail.com addreses in @24 hours? jo@aol.com or byronw.whitlock@cci.channelmanagement.com or even david@cribs.org"  ));