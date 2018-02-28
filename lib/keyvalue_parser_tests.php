<?
include_once("keyvalue_parser.php");
$test1_1[] = '<li><strong>Website:</strong> <br></br><a href="http://www.empirecityvetcare.com" onclick="window.open(this.href); return false;" title="Visit www.empirecityvetcare.com">www.empirecityvetcare.com</a></li>';
$test1_1[] ='<li><a href="#" id="email_link" title="Email the Doctor">Email Empire City Veterinary Care!</a> <br></br></li>';
$test1_1[] ='<li><strong>Phone Number:</strong> <br></br> 646-801-7387</li>';
$test1_1[] ='<li><strong>Fax Number:</strong> <br></br> n/a</li>';
$tests[] = $test1_1;

$tests[] = '
<div class="padTopBot10">Website: <a href="http://www.vcaspecialtyvets.com/sonoma-county/our-hospital" target="_blank">http://www.vcaspecialtyvets.com/sonoma-county/our-hospital</a><br>Phone: (707)584-4343<br>Email: <a href="mailto:kathy.yerger@vcahospitals.com">kathy.yerger@vcahospitals.com</a></div>
';


$tests[] =  "
Specialty: General dentistry, Public Health
Dental Degree(s): DMD
Dental School: Univ. of Pittsburgh
";

$tests[] =  "
Specialty: General dentistry, Public Health
Dental Degree(s): DMD
Dental School: Univ. of Pittsburgh
";

$tests[] =  '
						<legend class="eb9">Dr. Carolyn E. Kelly-Mueller</legend>
					Bureau Of Health Promotion 
Division Of Health Risk Reduction<br/>
					625 Forster St Rm 1008<br/> Harrisburg, PA <br/>
					Phone: (717) 787-5900<br/>
					Fax: (717) 599-8155 (CELL)<br/>

					Website: No information on file<br/>
					<a target="_blank" href="http://www.mapquest.com/maps/map.adp?city=Harrisburg&amp;state=PA&amp;zip=&amp;address=625 Forster St Rm 1008&amp;zoom=7">[Map]</a><br/>
					<br/>
					<ul>
						
						
						Specialty: General dentistry, Public Health<br/>
						Dental Degree(s): DMD<br/>
						Dental School: Univ. of Pittsburgh<br/>

						
						County: Chester<br/>
						Gender: F<br/>
						Practice Type: Solo<br/>
                        Accepts Insurance: N/A<br/> 
						   The PDA encourages patients to ask questions to learn details about the dentist’s participation in dental insurance programs.<br/>

						Handicap_Accessible:  N/A<br/>

						Accepts Credit Cards: N/A<br/>
						Financing Arrangements: N/A<br/>
						Early Appointments: N/A<br/>
						Evening Appointments: N/A<br/>
						Saturday Appointments: N/A<br/>
						Public Trans Access: N/A<br/>

						
						
						<table cellspacing="0" cellpadding="0" border="0">
							<tbody><tr>
								<td>Languages Spoken: </td>
								<td>No information on file</td>
							</tr>
						</tbody></table>


						
					</ul>

					';
					
					
$tests[] = '
<p><strong>Tel</strong>:&nbsp;212-260-1978</p>
<p><strong>Location</strong>:</p>
<p>USA<br>
102 Madison Ave,<br>
New York, NY</p>
<p><strong>Mail</strong>:&nbsp;<a href="mailto:info@bluefountainmedia.com" target="_blank">info@bluefountainmedia.com</a></p>
<p><strong>Contact Name</strong>:</p>
<p>Brian Byer<br>
<a href="mailto:bbyer@bluefountainmedia.com">bbyer@bluefountainmedia.com</a></p>
<p><strong>Clients</strong>: Procter &amp; Gamble, National Football League, HarperCollins, Canon, Oppenheimer Funds, NASA, European Delegation to the United States, etc…</p>
<p><strong>Services</strong>:&nbsp;Website Design, Development Services,&nbsp;Online Marketing,&nbsp;E-commerce, Mobile Development, Logo Design,&nbsp;Video Production &amp; Animations, Print Design, Copywriting, Proactive Website Support.</p>
<p><strong>Staff</strong>: 101 – 200</p>
<p><strong>Opinion and profiles:</strong><br>
Gabriel Shaoolan, Blue Fountain Media FOUNDER &amp; CEO:<br>
<a href="http://www.topinteractiveagencies.com/digital/agency/articles/meet-ceo-founder-blue-fountain-media-digital-agency/" target="_blank">“Meet Blue Fountain Media CEO and founder”</a></p>
<p>Tatyana Khamdamova, Blue Fountain Media DESIGN DIRECTOR:<br>
<a href="http://www.topinteractiveagencies.com/digital/agency/articles/tatyana-khamdamova-the-woman-who-took-a-risk-for-creativity/">“Tatyana Khamdamova, the woman who took a risk for creativity”</a></p><a href="http://www.topinteractiveagencies.com/digital/agency/articles/tatyana-khamdamova-the-woman-who-took-a-risk-for-creativity/">
';
					
					


$kvp = new KeyValue_Parser();
foreach($tests as $i=>$test)
{
	echo "\nTEST $i:";
	print_r($kvp->parse($test));
}