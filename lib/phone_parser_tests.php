<?

include_once("phone_parser.php");
include_once("scrape/db.inc");
db::init2("demandforce");

$p = new phone_parser();

$text = '<td width="50"><img src="/custom/Aacd/Images/icon_small_accred_spacer.gif" height="38" width="40">&nbsp;</td><td width="300"><table cellpadding="0" cellspacing="0"><tbody><tr valign="top"><td><a href="index.php?module=aacd.websiteforms&amp;cmd=memberreferralprintinfo&amp;mi=120359357019302" target="_blank"><b>Edward Jang, DDS&nbsp;</b></a><br>1 Santa Maria Way<br>Orinda, CA 94563 <br>(925) 254-5100<br> FAX: (925) 254-5103<br> Distance: 43 mi.<br></td></tr></tbody></table></td><td valign="top" width="280"><span style="color:#B54FB5;" onmouseover="showTip(\'An <span style=\'color:#B54FB5;font-weight:bold;\'>Accreditation Candidate</span> is a member who has passed the Accreditation Written Examination, as well as delivered and extensively documented one of the five required cosmetic treatment solutions for a patient to the standard of excellence as established by the American Board of Cosmetic Dentistry.\', 300)" onmouseout="hideTip()"><b>Accreditation Candidate</b></span> &nbsp;&nbsp;<span align="right"><a href="index.php?module=aacd.websiteforms&amp;cmd=memberreferralprintinfo&amp;mi=120359357019302" target="_blank" style="font-size:10px;font-weight:bold">Print Profile</a></span><br>Doctor • Member Since 2003<br><a href="index.php?module=aacd.websiteforms&amp;cmd=contactUs&amp;imisID=19302&amp;memberName=Edward%20Jang,%20DDS&amp;memberType=DOC" target="_blank" style="text-decoration:underline;font-weight:normal">Send an Email</a>&nbsp;&nbsp;•&nbsp;&nbsp;<a href="index.php?module=aacd.websiteforms&amp;cmd=visitwebsite&amp;url=http://www.edwardjangdds.com&amp;i=19302" target="_blank" style="text-decoration:underline;font-weight:normal">Visit Website</a><br><a href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=1+Santa+Maria+Way+Orinda+CA+94563+&amp;ie=UTF8&amp;hq=&amp;hnear=1+Santa+Maria+Way+Orinda+CA+94563+&amp;z=16" target="_blank" style="text-decoration:underline;font-weight:normal">View Map</a></td>';

print_r($p->parse($text));

print_r($p->parse("425A Lexington Ave., Chapin, SC 29036
Ph 803-345-5526
Web"));


$text=" Ph 701-824-2991";
 print_r($p->parse($text));



$text=" For urgent medical matters, please contact us during our regular office hours at our Parsippany office at (973) 263-2828, Morristown office at (973) 538-0029 or Randolph office at (973) 366-4411. In case of a medical emergency, call 911.";
 print_r($p->parse($text));
