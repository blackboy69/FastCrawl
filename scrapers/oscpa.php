<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class oscpa extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		//$this->maxRetries = 100;
		//$this->timeout = 15;
		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->debug=false;
//		$this->threads=2;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");

		*/
		// cananda top 100 cities by population
	//	db::query("DELETE FROM raw_data where type = '$type' ");
//		db::query("DELETE FROM load_queue where type='$type'");
		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		db::query("DROP TABLE $type ");
		//db::query("UPDATE load_queue SET processing = 1 where type = '$type' ");

		$webRequests= array();
		$this->noProxy= false;
		$this->proxy = "localhost:8888";

		$html = $this->get("http://www.oscpa.com/Public/Referral/findcpa.aspx");
		$page = new HtmlParser($html);		
		$data = $page->loadViewState();

		$data = "__VIEWSTATE=%2FwEPDwUKLTIzNDYwMTUxOA9kFgJmD2QWDAIBD2QWAgICD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCAw9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkAgkPZBYCAgQPZBYEAgsPEA8WBh4ORGF0YVZhbHVlRmllbGQFBGNvZGUeDURhdGFUZXh0RmllbGQFCGNvZGVkZXNjHgtfIURhdGFCb3VuZGdkEBUmA0FsbAxBZ3JpYnVzaW5lc3MQQXV0byBEZWFsZXJzaGlwcxdDb2xsZWdlcyAmIFVuaXZlcnNpdGllcwxDb25zdHJ1Y3Rpb24LQ29udHJhY3RvcnMTRGF5IENhcmUgQ2hpbGQgQ2FyZR5GaW5hbmNpYWwgSW5zdGl0dXRpb25zIC8gQmFua3MaRm9vZCBTZXJ2aWNlIC8gUmVzdGF1cmFudHMKRnJhbmNoaXNlcxxHb3Zlcm5tZW50IChGZWRlcmFsIC8gU3RhdGUpEkdvdmVybm1lbnQgKGxvY2FsKQ5IaWdoIE5ldCBXb3J0aAtIb21lIEhlYWx0aBhIb3NwaXRhbCAmIE51cnNpbmcgSG9tZXMVSG9zcGl0YWxpdHkgLyBUb3VyaXNtGEluZGlhbiBUcmliYWwgR292ZXJubWVudBlJbmRpcmVjdCBDb3N0IE5lZ290aWF0aW9uC0luZGl2aWR1YWxzCUluc3VyYW5jZQlMYXcgRmlybXMNTWFudWZhY3R1cmluZxFNZWRpY2FsIFByYWN0aWNlcxNNZWRpY2FyZSAvIE1lZGljYWlkE01pbmlzdHJ5IC8gQ2h1cmNoZXMWTmF0aXZlIEFtZXJpY2FuIFRyaWJlcxhOb24tUHJvZml0IE9yZ2FuaXphdGlvbnMJT2lsICYgR2FzClBoeXNpY2lhbnMLUmVhbCBFc3RhdGUGUmV0YWlsB1NjaG9vbHMSU2VydmljZSBJbmR1c3RyaWVzDlNtYWxsIEJ1c2luZXNzDlRyYW5zcG9ydGF0aW9uBlRyYXZlbAlVdGlsaXRpZXMgV3JpdGVycyAvIEFydGlzdHMgLyBFbnRlcnRhaW5lcnMVJgNBbGwCUTECQTECQ1UCQzECQ08CRTECQjECRlMCSDECRygCR08CSE4CSEgCSCYCSE8CSVQCSUMCSU4CTTECTEYCTjECTVACTUUCTTICTkECTzECUDECUEgCUjECUzECU0MCU0kCVjECVzECVFICWTECV1IUKwMmZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2cWAWZkAg0PEA8WBh8ABQRjb2RlHwEFCGNvZGVkZXNjHwJnZBAVPANBbGwSQWNjb3VudGluZyBTeXN0ZW1zCEF1ZGl0aW5nCkJhbmtydXB0Y3kLQm9va2tlZXBpbmcZQnVkZ2V0aW5nIGFuZCBGb3JlY2FzdGluZx9CdXNpbmVzcyBhbmQgU3RyYXRlZ2ljIFBsYW5uaW5nEkJ1c2luZXNzIFN0YXJ0LXVwcxNCdXNpbmVzcyBTdWNjZXNzaW9uEUJ1c2luZXNzIFRheGF0aW9uE0J1c2luZXNzIFZhbHVhdGlvbnMPQ2FzaCBNYW5hZ2VtZW50FUNsb3NlbHktaGVsZCBCdXNpbmVzcxBDb2xsZWdlIFBsYW5uaW5nFkNvbXBpbGF0aW9uIGFuZCBSZXZpZXcRQ29tcHV0ZXIgU2VydmljZXMxQ29tcHV0ZXIgU29mdHdhcmUgRGV2ZWxvcG1lbnQgU2VsZWN0aW9uIGFuZCBTYWxlcw5Db25zb2xpZGF0aW9ucyJDb3Jwb3JhdGlvbiAmIFBhcnRuZXJzaGlwIFRheGF0aW9uD0Nvc3QgQWNjb3VudGluZwpFbGRlciBDYXJlEUVsZWN0cm9uaWMgRmlsaW5nEUVtcGxveWVlIEJlbmVmaXRzD0VzdGF0ZSBQbGFubmluZxtFc3RhdGUgVHJ1c3QgYW5kIEdpZnQgVGF4ZXMJRmluYW5jaW5nE0ZvcmVuc2ljIEFjY291bnRpbmcKRnJhbmNoaXNlcxZHb3Zlcm5tZW50IENvbnRyYWN0aW5nMkdvdmVybm1lbnRhbCBBY2NvdW50aW5nIGFuZCBBdWRpdGluZyAoWWVsbG93IEJvb2spDkhpZ2ggTmV0IFdvcnRoDkluZGl2aWR1YWwgVGF4JUludGVybmF0aW9uYWwgQWNjb3VudGluZyBhbmQgQXVkaXRpbmcRSW50ZXJuYXRpb25hbCBUYXgRSW52ZW50b3J5IENvbnRyb2wLSW52ZXN0bWVudHMSSVJTIFJlcHJlc2VudGF0aW9uFExlZ2FsIFJlcHJlc2VudGF0aW9uLkxpbWl0ZWQgTGlhYmlsaXR5IENvcnBvcmF0aW9ucyBvciBQYXJ0bmVyc2hpcHMdTGl0aWdhdGlvbiBTdXBwb3J0IG9yIERpdm9yY2UcTWFuYWdlbWVudCBBZHZpc29yeSBTZXJ2aWNlcyVNZXJnZXJzIEFjcXVpc2l0aW9ucyBhbmQgTGlxdWlkYXRpb25zFk11bHRpIFN0YXRlIFRheCBJc3N1ZXMhTmF0aXZlIEFtZXJpY2FuIExhbmQgRGVwcmVjaWF0aW9uFE9mZmVycyBpbiBDb21wcm9taXNlB1BheXJvbGwaUGVuc2lvbiBhbmQgUHJvZml0IFNoYXJpbmcbUGVyc29uYWwgRmluYW5jaWFsIFBsYW5uaW5nHlB1cmNoYXNlIG9yIFNhbGUgb2YgYSBCdXNpbmVzcw9SZWFsIEVzdGF0ZSBUYXgTUmV0aXJlbWVudCBQbGFubmluZxFTYWxlcyBhbmQgVXNlIFRheAxTRUMgUHJhY3RpY2UYU2VsZiBFbXBsb3llZCAtIFRheGF0aW9uF1NtYWxsIEJ1c2luZXNzIFBsYW5uaW5nEFNwYW5pc2ggU3BlYWtpbmcMVGF4IFBsYW5uaW5nIlRoaXJkIFBhcnR5IG9yIFBsYW4gQWRtaW5pc3RyYXRpb24cVHJpYmFsIGFuZCBDYXNpbm8gQWNjb3VudGluZwlXZWIgVHJ1c3QVPANBbGwCQVMCQVUCQkECQk8CQkQCQkkCQk4CQlMCQlQCQlYCQ00CQ0ICQ08CQyYCQ1MCQ1ACQ04CQ1ICQ0ECRUMCRUYCRUICRVMCRVQCRkkCRk8CRlICR0MCR0ECSE4CSVQCSUECSUUCSUMCSU4CSVICTFICTEwCTFQCTU4CTVICTVMCTkECT0kCUEECUCYCUEYCUE8CUkUCUlQCUyYCU0UCU1ACU0ICU0ECVEECVFACVFICV1QUKwM8Z2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAILD2QWAgICD2QWBGYPFgIeB1Zpc2libGVoZAICDxYEHglpbm5lcmh0bWwF2wgNCjxkaXYgY2xhc3M9ImFycm93bGlzdG1lbnUiPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InRoaXNQYWdlSWQiIHZhbHVlPSI1ODU4MyIgLz48aW5wdXQgdHlwZT0iaGlkZGVuIiBjbGFzcz0idGhpc1BhdGhJZCIgdmFsdWU9Ijs1ODU4MzsiIC8%2BPGgzIGNsYXNzPSJtZW51aGVhZGVyIGxhc3QiPjxhIGNsYXNzPSJwdWJsaWNBY2Nlc3MgbGFzdCIgaHJlZj0iL0NvbnRlbnQvNTg1ODcuYXNweCIgdGl0bGU9IkZpbmQgYSBDUEEgRGlzY2xhaW1lciI%2BRmluZCBhIENQQSBEaXNjbGFpbWVyPGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InBhZ2VJZCIgdmFsdWU9IjU4NTg3IiAvPjwvYT48L2gzPg0KPGRpdiBpZD0iY3RsMDBfUGFuZWxTZWNvbmRhcnlOYXZpZ2F0aW9uX29QYW5lbF81ODU4M18xNV81X3BhcmVudE5vZGUiIHN0eWxlPSJkaXNwbGF5Om5vbmU7Ij48aDI%2BPGEgY2xhc3M9InB1YmxpY0FjY2VzcyBsYXN0IiBocmVmPSIvcHVibGljL3JlZmVycmFsL2ZpbmRjcGEuYXNweCIgdGl0bGU9IkZpbmQgYSBDUEEiPkZpbmQgYSBDUEE8aW5wdXQgdHlwZT0iaGlkZGVuIiBjbGFzcz0icGFnZUlkIiB2YWx1ZT0iNTg1ODMiIC8%2BPC9hPjwvaDI%2BDQo8L2Rpdj4NCjwvZGl2Pg0KDQo8c2NyaXB0Pg0KICAgIA0KLy87NTg1ODM7DQpkZGFjY29yZGlvbi5pbml0KHsNCgloZWFkZXJjbGFzczogImV4cGFuZGFibGUiLA0KCWNvbnRlbnRjbGFzczogImNhdGVnb3J5aXRlbXMiLA0KCXJldmVhbHR5cGU6ICJtb3VzZW92ZXIiLA0KCW1vdXNlb3ZlcmRlbGF5OiA1MDAsDQoJY29sbGFwc2VwcmV2OiB0cnVlLA0KCWRlZmF1bHRleHBhbmRlZDogWy0xXSwNCglvbmVtdXN0b3BlbjogZmFsc2UsDQoJYW5pbWF0ZWRlZmF1bHQ6IGZhbHNlLA0KCXBlcnNpc3RzdGF0ZTogZmFsc2UsDQoJdG9nZ2xlY2xhc3M6IFsiIiwgIm9wZW5oZWFkZXIiXSwNCgl0b2dnbGVodG1sOiBbInByZWZpeCIsICIiLCAiIl0sDQoJYW5pbWF0ZXNwZWVkOiAiZmFzdCIsDQoJb25pbml0OmZ1bmN0aW9uKGhlYWRlcnMsIGV4cGFuZGVkaW5kaWNlcyl7IH0sDQoJb25vcGVuY2xvc2U6ZnVuY3Rpb24oaGVhZGVyLCBpbmRleCwgc3RhdGUsIGlzdXNlcmFjdGl2YXRlZCl7IH0NCn0pOw0KDQo8L3NjcmlwdD4NCh8DZ2QCDQ9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGQWAmYPZBYCAgEPZBYCZg9kFgYCDQ8WAh4EVGV4dAXVBg0KICAgIDxkaXYgaWQ9ImFydGljbGVMaXN0Q29udGFpbmVyIj4NCgk8dWw%2BDQoNCiAgICAgICAgPGxpPjxhIGhyZWY9Ii9jb250ZW50LzYzNDY3LmFzcHgiIHRpdGxlPSJUb3AgSG9ub3JzIGZvciBUd28gT2tsYWhvbWEgQ1BBcyI%2BVG9wIEhvbm9ycyBmb3IgVHdvIE9rbGFob21hIENQQXM8L2E%2BPC9saT4NCg0KICAgICAgICA8bGk%2BPGEgaHJlZj0iL2NvbnRlbnQvNjA1ODAuYXNweCIgdGl0bGU9IkNhbGN1bGF0b3IgTGF1bmNoZXMgdG8gSW1wcm92ZSBQdWJsaWMgVW5kZXJzdGFuZGluZyBvZiBUYXggT2JsaWdhdGlvbiI%2BQ2FsY3VsYXRvciBMYXVuY2hlcyB0byBJbXByb3ZlIFB1YmxpYyBVbmRlcnN0YW5kaW5nIG9mIFRheCBPYmxpZ2F0aW9uPC9hPjwvbGk%2BDQoNCiAgICAgICAgPGxpPjxhIGhyZWY9Ii9jb250ZW50LzYzNDgzLmFzcHgiIHRpdGxlPSJJUlMgUmVsZWFzZXMgTmV3IFN0YW5kYXJkIE1pbGVhZ2UgUmF0ZXMgZm9yIDIwMTUiPklSUyBSZWxlYXNlcyBOZXcgU3RhbmRhcmQgTWlsZWFnZSBSYXRlcyBmb3IgMjAxNTwvYT48L2xpPg0KDQogICAgICAgIDxsaT48YSBocmVmPSIvY29udGVudC82MDc0OS5hc3B4IiB0aXRsZT0iT1NDUEEgUHJlc2VudHMgTmV3IE9pbCAmIEdhcyBDb25mZXJlbmNlIHRoaXMgWWVhciI%2BT1NDUEEgUHJlc2VudHMgTmV3IE9pbCAmIEdhcyBDb25mZXJlbmNlIHRoaXMgWWVhcjwvYT48L2xpPg0KDQogICAgICAgIDxsaT48YSBocmVmPSIvY29udGVudC82MDYwMy5hc3B4IiB0aXRsZT0iTmV3IENQQSBTcG90bGlnaHQgb24gTWVyaWRpdGggV2FycmVuIj5OZXcgQ1BBIFNwb3RsaWdodCBvbiBNZXJpZGl0aCBXYXJyZW48L2E%2BPC9saT4NCg0KCTwvdWw%2BDQoJPC9kaXY%2BDQpkAhEPFgQeC18hSXRlbUNvdW50AgUfA2gWCgIBD2QWAmYPFQFrPGEgaHJlZj0iL2NvbnRlbnQvNjM0NjcuYXNweCIgdGl0bGU9IlRvcCBIb25vcnMgZm9yIFR3byBPa2xhaG9tYSBDUEFzIj5Ub3AgSG9ub3JzIGZvciBUd28gT2tsYWhvbWEgQ1BBczwvYT5kAgIPZBYCZg8VAbUBPGEgaHJlZj0iL2NvbnRlbnQvNjA1ODAuYXNweCIgdGl0bGU9IkNhbGN1bGF0b3IgTGF1bmNoZXMgdG8gSW1wcm92ZSBQdWJsaWMgVW5kZXJzdGFuZGluZyBvZiBUYXggT2JsaWdhdGlvbiI%2BQ2FsY3VsYXRvciBMYXVuY2hlcyB0byBJbXByb3ZlIFB1YmxpYyBVbmRlcnN0YW5kaW5nIG9mIFRheCBPYmxpZ2F0aW9uPC9hPmQCAw9kFgJmDxUBiwE8YSBocmVmPSIvY29udGVudC82MzQ4My5hc3B4IiB0aXRsZT0iSVJTIFJlbGVhc2VzIE5ldyBTdGFuZGFyZCBNaWxlYWdlIFJhdGVzIGZvciAyMDE1Ij5JUlMgUmVsZWFzZXMgTmV3IFN0YW5kYXJkIE1pbGVhZ2UgUmF0ZXMgZm9yIDIwMTU8L2E%2BZAIED2QWAmYPFQGNATxhIGhyZWY9Ii9jb250ZW50LzYwNzQ5LmFzcHgiIHRpdGxlPSJPU0NQQSBQcmVzZW50cyBOZXcgT2lsICYgR2FzIENvbmZlcmVuY2UgdGhpcyBZZWFyIj5PU0NQQSBQcmVzZW50cyBOZXcgT2lsICYgR2FzIENvbmZlcmVuY2UgdGhpcyBZZWFyPC9hPmQCBQ9kFgJmDxUBczxhIGhyZWY9Ii9jb250ZW50LzYwNjAzLmFzcHgiIHRpdGxlPSJOZXcgQ1BBIFNwb3RsaWdodCBvbiBNZXJpZGl0aCBXYXJyZW4iPk5ldyBDUEEgU3BvdGxpZ2h0IG9uIE1lcmlkaXRoIFdhcnJlbjwvYT5kAhsPFgIfA2cWAmYPFgQeBGhyZWYFNS9QdWJsaWMvUmVmZXJyYWwvZmluZGNwYS5hc3B4P2k9MCZtPUFsbCZpZD0tMjg2ODA3Mjg5HwQFBk1vcmUgPmQCDw9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkGAEFHl9fQ29udHJvbHNSZXF1aXJlUG9zdEJhY2tLZXlfXxYFBRxjdGwwMCRQYW5lbENvbnRlbnQkYnRuU2VhcmNoBR5jdGwwMCRQYW5lbENvbnRlbnQkYm94SW5kdXN0cnkFHmN0bDAwJFBhbmVsQ29udGVudCRib3hTZXJ2aWNlcwUdY3RsMDAkUGFuZWxDb250ZW50JGJ0blNlYXJjaDIFG2N0bDAwJFBhbmVsQ29udGVudCRidG5DbGVhctGc2n%2B20gAudqt67zXzRkM47h5N&__VIEWSTATEGENERATOR=6A831C82&__EVENTVALIDATION=%2FwEWbgKy2NTABwKEksnUAwLgqMmpCgKSuNv6CQLkmL%2F%2FDwLgmP%2F8DwLhmMP8DwLkmP%2F8DwLG2ZirCAL9ucvfCAKkypb5CQK26r7%2FDwKG6r7%2FDwKE6o7iDwKE6r7%2FDwKE6vbiDwKC6r7%2FDwKH6r7%2FDwKD6obiDwKx6r7%2FDwKA6pL%2FDwKA6vbiDwKx6uriDwKx6pLiDwKx6sr%2FDwKx6vbiDwK%2B6oLiDwK%2B6sbiDwK%2B6uriDwK66r7%2FDwK96sriDwK76r7%2FDwK66rLiDwK66s7iDwK66rr%2FDwK76v7iDwK46r7%2FDwKp6r7%2FDwKp6pLiDwK36r7%2FDwK06r7%2FDwK06sbiDwK06p7iDwKz6r7%2FDwKw6r7%2FDwK16rriDwKu6r7%2FDwKw6rriDwLr5Y%2FpDgLJxZ%2FyCALJxZfyCALIxefyCALIxe%2FyCALIxdvyCALIxYfyCALIxfPyCALIxZ%2FyCALIxZvyCALIxZPyCALLxffyCALLxePyCALLxe%2FyCALLxdPvCALLxZ%2FyCALLxavyCALLxfPyCALLxaPyCALLxefyCALNxd%2FyCALNxdPyCALNxePyCALNxZ%2FyCALNxZvyCALMxYfyCALMxe%2FyCALMxaPyCALPxd%2FyCALPxefyCAL%2BxfPyCALxxZvyCALxxefyCALxxdfyCALxxd%2FyCALxxfPyCALxxaPyCALyxaPyCALyxfvyCALyxZvyCAL1xfPyCAL1xaPyCAL1xZ%2FyCAL0xefyCAL3xYfyCALmxefyCALmxdPvCALmxdPyCALmxe%2FyCAL4xdfyCAL4xZvyCAL7xdPvCAL7xdfyCAL7xavyCAL7xePyCAL7xefyCAL6xefyCAL6xavyCAL6xaPyCAL%2FxZvyCALhiPnYBQKjj6LABXOlelMlpYsO%2Bqy99kE8lAdC1Svv&ctl00%24PanelContent%24tbCompany=&ctl00%24PanelContent%24ddDistance=All&ctl00%24PanelContent%24tbZip=&ctl00%24PanelContent%24boxIndustry=All&ctl00%24PanelContent%24boxServices=All&ctl00%24PanelContent%24btnSearch2.x=35&ctl00%24PanelContent%24btnSearch2.y=9";




		log::info("Perform Search");
		$this->LoadPostUrl("http://www.oscpa.com/Public/Referral/findcpa.aspx",$data);
	}
/*
	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"The Three Laws of Robotics are as follows:"))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("ERROR! ERROR! FORBIDDEN ACCESS!");
					
			$html=null;
		}

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
	}*/




	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); 

	//	file_put_contents("$type.html",$html);	

		$links = array();
		$data = array();
		if (preg_match("#FindCPAList.aspx#",$url))
		{

			$x =  new  XPath($html);	
			foreach($x->query("//a[contains(@href, 'FindCPADetails')]") as $node)
			{
				  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (!empty($links))
				$thiz->loadUrlsByArray($links);
		}
		else
		{
			$x =  new  XPath($html);	
			$keys = array();
			$values = array();
			$data = array();
			
			foreach($x->query("//*[contains(@id,'FirmName')]") as $node)
			{
				$data['COMPANY'] =  self::cleanup($node->textContent);
			}

			foreach($x->query("//tr[contains(@id,'firmProfile')]//td[@class = 'ProfileDetailLabel']") as $node)
			{
				$keys[] = self::cleanup($node->textContent);
			}

			foreach($x->query("//tr[contains(@id,'firmProfile')]//td[@class = 'ProfileDetail']") as $node)
			{
				$values[] =  self::cleanup($node->textContent);
			}

			$key = "ADDRESS";

			for($i=0;$i< sizeof($values) ; $i++ )
			{
				if (!empty($keys[$i]))
					$key = $keys[$i];


				if (empty($data[$key]))
					$data[$key] = $values[$i];
				else
				{
					// if it is an email treat special
					if (preg_match("#.+\@.+\..+#", $values[$i]))
						@$data["EMAIL"] .= $values[$i]." ";
					else
						$data[$key] = "{$data[$key]}, {$values[$i]}";
				}


			}
			
			$data['COUNTRY'] = 'United States';

			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL'));			
		}
	}
}


$r= new oscpa();
$r->parseCommandLine();

