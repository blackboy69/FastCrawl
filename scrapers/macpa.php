<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class macpa extends baseScrape
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

		$html = $this->get("https://www.macpa.org/Public/Referral/findcpa.aspx");
		$page = new HtmlParser($html);		
		$data = $page->loadViewState();

//macpa
		$data['__VIEWSTATEGENERATOR'] ='D3B5AAD8';
		$data['ctl00$PanelContent$boxCity'] ='All';
		$data['ctl00$PanelContent$boxIndustry'] ='All';
		$data['ctl00$PanelContent$boxSE_Audit'] ='All';
		$data['ctl00$PanelContent$boxSE_Tax'] ='All';
		$data['ctl00$PanelContent$boxSE_Consulting'] ='All';
		$data['ctl00$PanelContent$boxSE_Language'] ='All';
		$data['ctl00$PanelContent$btnSearch2.x'] ='0';
		$data['ctl00$PanelContent$btnSearch2.y'] ='0';

		$data = "__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKLTEyMjcwNzg3MQ9kFgJmD2QWEAIBD2QWAgICD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCAw9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkAgUPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkFgJmD2QWAgIJD2QWAmYPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIHD2QWAgIED2QWDAIJDxAPFgYeDkRhdGFWYWx1ZUZpZWxkBQRjb2RlHg1EYXRhVGV4dEZpZWxkBQhjb2RlZGVzYx4LXyFEYXRhQm91bmRnZBAVHANBbGwMQW5uZSBBcnVuZGVsEEJhbHRpbW9yZSAoQ2l0eSkSQmFsdGltb3JlIChDb3VudHkpB0NhbHZlcnQIQ2Fyb2xpbmUHQ2Fycm9sbAVDZWNpbAdDaGFybGVzCERlbGF3YXJlCkRvcmNoZXN0ZXIJRnJlZGVyaWNrB0dhcnJldHQHSGFyZm9yZAZIb3dhcmQES2VudApNb250Z29tZXJ5DFBlbm5zeWx2YW5pYQ9QcmluY2UgR2VvcmdlJ3MMUXVlZW4gQW5uZSdzCFNvbWVyc2V0ClN0LiBNYXJ5J3MGVGFsYm90CFZpcmdpbmlhE1dhc2hpbmd0b24gKENvdW50eSkRV2FzaGluZ3RvbiAoRC5DLikIV2ljb21pY28JV29yY2VzdGVyFRwDQWxsAzAwMgMwMDMDMDA0AzAwNQMwMDYDMDA3AzAwOAMwMDkDREVMAzAxMAMwMTEDMDEyAzAxMwMwMTQDMDE1AzAxNgNQRU4DMDE3AzAxOAMwMTkDMDIwAzAyMQNWSVIDMDIyAzAyMwMwMjQDMDI1FCsDHGdnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2cWAWZkAgsPEA8WBh8ABQRjb2RlHwEFCGNvZGVkZXNjHwJnZBAVKANBbGwNQWdyaS1idXNpbmVzcxJBdXRvbW9iaWxlIGRlYWxlcnMHQmFua2luZwxDb25zdHJ1Y3Rpb24PQ29udGludWluZyBDYXJlDUNyZWRpdCBVbmlvbnMURGF5IGNhcmUvIGNoaWxkIGNhcmUYRWR1Y2F0aW9uYWwgSW5zdGl0dXRpb25zFkVtcGxveWVlIEJlbmVmaXQgUGxhbnMLRW5naW5lZXJpbmcWRmluYW5jaWFsIGluc3RpdHV0aW9ucwpGcmFuY2hpc2VzDUZ1bmVyYWwgSG9tZXMSR0FTQiAtIFllbGxvdyBCb29rF0hlYWx0aCBDYXJlIC0gSG9zcGl0YWxzG0hlYWx0aCBDYXJlIC0gTnVyc2luZyBob21lcxdIb21lb3duZXJzIEFzc29jaWF0aW9ucxJIb3RlbHMvUmVzdGF1cmFudHMKSFVEIEF1ZGl0cw1JbXBvcnQvRXhwb3J0CUluc3VyYW5jZQxMYWJvciBVbmlvbnMRTG9jYWwgZ292ZXJubWVudHMNTWFudWZhY3R1cmluZwtOb24tcHJvZml0cwlPaWwgJiBnYXMFT3RoZXIYUHJpdmF0ZWx5IEhlbGQgQ29tcGFuaWVzI1Byb2Zlc3Npb25hbHMgKERvY3RvciwgTGF3eWVyLCBldGMpC1JlYWwgZXN0YXRlFlJlbGlnaW91cyBJbnN0aXR1dGlvbnMRU2F2aW5ncyBhbmQgTG9hbnMNU0VDIGNvbXBhbmllcxlTZWxmLWVtcGxveWVkIGNvbnRyYWN0b3JzDlNtYWxsIEJ1c2luZXNzEFN0YXRlIEdvdmVybm1lbnQOVHJhbnNwb3J0YXRpb24JVXRpbGl0aWVzH1dyaXRlcnMsIGFydGlzdHMgJiBlbnRlcnRhaW5lcnMVKANBbGwCMDECMTECMTYCMjICMTcCMTgCMzICMTMCMTkCMjACMzMCMzUCRkgCMjMCMzcCMzgCSEECMjUCMjYCMjcCMDMCTFUCMzYCMDQCMDUCMDYCMzACUEgCUCgCMDcCMTUCU0ECMDgCMDkCU0ICMjkCMTACVVQCMTIUKwMoZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZxYBZmQCDQ8QDxYGHwJnHwAFBGNvZGUfAQUIY29kZWRlc2NkEBUJA0FsbAxBLTEzMyBBdWRpdHMcQWNjdG5nIC0gRm9yZW5zaWMgQWNjb3VudGluZxRBdHRlc3RhdGlvbiBTZXJ2aWNlcwVBdWRpdBRDb21waWxhdGlvbiAmIHJldmlldwxFUklTQSBBdWRpdHMKRkFSIEF1ZGl0cw5JbnRlcm5hbCBBdWRpdBUJA0FsbAJBMQJBQwIwMQIwMgIwMwJFQQJGQQJJRRQrAwlnZ2dnZ2dnZ2cWAWZkAg8PEA8WBh8CZx8ABQRjb2RlHwEFCGNvZGVkZXNjZBAVDgNBbGwNQ29ycG9yYXRlIFRheBdFc3RhdGVzLCB0cnVzdHMgJiBnaWZ0cw5JbmRpdmlkdWFsIFRheBFJbnRlcm5hdGlvbmFsIFRheBJJUlMgcmVwcmVzZW50YXRpb24FTExDJ3MRTWlsaXRhcnkgVGF4YXRpb24LUGFydG5lcnNoaXAHUGF5cm9sbA1QZW5zaW9uIHBsYW5zEFJldGlyZW1lbnQgUGxhbnMHUyBjb3JwcwlTdGF0ZSBUYXgVDgNBbGwCQ1QCMzkCSVQCSU4CNDACNDMCTVQCNDQCNDUCNDYCNDcCNDgCU1QUKwMOZ2dnZ2dnZ2dnZ2dnZ2cWAWZkAhEPEA8WBh8CZx8ABQRjb2RlHwEFCGNvZGVkZXNjZBAVJQNBbGwWQXNzdXJhbmNlIC0gRWxkZXIgQ2FyZQpCYW5rcnVwdGN5F0J1ZGdldGluZyAmIGZvcmVjYXN0aW5nEkJ1c2luZXNzIHZhbHVhdGlvbhdDRk8vQ29udHJvbGxlciBTZXJ2aWNlcxNDb21wdXRlciBDb25zdWx0aW5nB0Rpdm9yY2UKRWxkZXIgQ2FyZRZFbXBsb3llZSBiZW5lZml0IHBsYW5zHkZpbmFuY2lhbCBQbGFubmluZyAtIENlcnRpZmllZB1GaW5hbmNpYWwgUGxhbm5pbmcgLSBQZXJzb25hbBZGaW5hbmNpbmcgYWx0ZXJuYXRpdmVzGEZ1bGwgU2VydmljZSBCb29ra2VlcGluZxZHb3Zlcm5tZW50IGNvbnRyYWN0aW5nFUhvbWUtYmFzZWQgYnVzaW5lc3NlcxZJbmZvcm1hdGlvbiBUZWNobm9sb2d5FUludGVybmV0IC8gRS1Db21tZXJjZQxMaXF1aWRhdGlvbnMSTGl0aWdhdGlvbiBzdXBwb3J0GU1hbmFnZW1lbnQvYWRtaW5pc3RyYXRpb24UTWVyZ2Vycy9hY3F1aXNpdGlvbnMOTm90LWZvci1wcm9maXQWT3V0c291cmNlZCBCb29ra2VlcGluZxdQZXJmb3JtYW5jZSBNZWFzdXJlbWVudA1Qcm9mZXNzaW9uYWxzFVF1aWNrQm9va3MgQ29uc3VsdGluZw1SaXNrIEFkdmlzb3J5FlJpc2sgQWR2aXNvcnkgU2VydmljZXMOU2FyYmFuZXMtT3hsZXkhU21hbGwgQnVzaW5lc3MgR2VuZXJhbCBDb25zdWx0aW5nHlNtYWxsIEJ1c2luZXNzIFN0YXJ0LXVwIElzc3VlcxRTb2Z0d2FyZSBEZXZlbG9wbWVudBJTdHJhdGVnaWMgUGxhbm5pbmcHV2ViIERldglXZWIgVHJ1c3QWV2hvbGVzYWxlIERpc3RyaWJ1dGlvbhUlA0FsbAJBLQIyNwIxOQIzNAJDUwIyOAJESQJFQwJFQgIwNAIwNQIyMgJGUwIyMwIzNgIyNAJJLwIyOQIzMAIyNQIzMQJOTwJPQgJQTQJQUgJRQwJBQQJSQQJTQQJTTQJTQgIzMwIyNgJUQwJXVAJXRBQrAyVnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAITDxAPFgYfAmcfAAUEY29kZR8BBQhjb2RlZGVzY2QQFRwDQWxsBkFyYWJpYwlDYW50b25lc2UHQ2hpbmVzZQVGYXJzaQhGaWxpcGlubwZGcmVuY2gGR2VybWFuBUdyZWVrCEd1amFyYXRpBkhlYnJldwVIaW5kaQdJdGFsaWFuCEphcGFuZXNlBktvcmVhbghNYW5kYXJpbgVPdGhlcgZQb2xpc2gHUHVuamFiaQdSdXNzaWFuBVNob25hDVNpZ24gTGFuZ3VhZ2UHU3BhbmlzaAZUYWdhbG8GVGVsdWd1BFVyZHUKVmlldG5hbWVzZQZZYXJ1YmEVHANBbGwCQVICQ0ECMDYCMDgCMDkCMTACMTICR1ICR1UCMTMCSEkCSUECMTQCMTUCTUECMTYCUE8CUFUCUlUCU0gCMTcCMTgCVEECVEUCVVICVkkCWUEUKwMcZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZxYBZmQCCw9kFgICAg9kFgRmDxYCHgdWaXNpYmxlaGQCAg8WBB4JaW5uZXJodG1sBasFDQo8ZGl2IGNsYXNzPSJhcnJvd2xpc3RtZW51Ij4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIGNsYXNzPSJ0aGlzUGFnZUlkIiB2YWx1ZT0iMjY0ODUiIC8%2BPGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InRoaXNQYXRoSWQiIHZhbHVlPSI7OzI1MTMwOzI2NDI1OzI2NDg1OyIgLz4NCjwvZGl2Pg0KDQo8c2NyaXB0Pg0KICAgIA0KLy87OzI1MTMwOzI2NDI1OzI2NDg1Ow0KZGRhY2NvcmRpb24uaW5pdCh7DQoJaGVhZGVyY2xhc3M6ICJleHBhbmRhYmxlIiwNCgljb250ZW50Y2xhc3M6ICJjYXRlZ29yeWl0ZW1zIiwNCglyZXZlYWx0eXBlOiAibW91c2VvdmVyIiwNCgltb3VzZW92ZXJkZWxheTogNTAwLA0KCWNvbGxhcHNlcHJldjogdHJ1ZSwNCglkZWZhdWx0ZXhwYW5kZWQ6IFstMV0sDQoJb25lbXVzdG9wZW46IGZhbHNlLA0KCWFuaW1hdGVkZWZhdWx0OiBmYWxzZSwNCglwZXJzaXN0c3RhdGU6IGZhbHNlLA0KCXRvZ2dsZWNsYXNzOiBbIiIsICJvcGVuaGVhZGVyIl0sDQoJdG9nZ2xlaHRtbDogWyJwcmVmaXgiLCAiIiwgIiJdLA0KCWFuaW1hdGVzcGVlZDogImZhc3QiLA0KCW9uaW5pdDpmdW5jdGlvbihoZWFkZXJzLCBleHBhbmRlZGluZGljZXMpeyB9LA0KCW9ub3BlbmNsb3NlOmZ1bmN0aW9uKGhlYWRlciwgaW5kZXgsIHN0YXRlLCBpc3VzZXJhY3RpdmF0ZWQpeyB9DQp9KTsNCg0KPC9zY3JpcHQ%2BDQofA2dkAg0PZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkFgJmD2QWBAIBD2QWAmYPZBYCAgEPPCsACwIADxYGHhNBdXRvR2VuZXJhdGVDb2x1bW5zZx4KU2hvd0hlYWRlcmgfA2hkAxYEHglGb250X0JvbGRoHgRfIVNCAoAQZAIDD2QWAmYPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIPD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCEQ9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkGAEFHl9fQ29udHJvbHNSZXF1aXJlUG9zdEJhY2tLZXlfXxYJBRxjdGwwMCRQYW5lbENvbnRlbnQkYnRuU2VhcmNoBRtjdGwwMCRQYW5lbENvbnRlbnQkYnRuQ2xlYXIFGmN0bDAwJFBhbmVsQ29udGVudCRib3hDaXR5BR5jdGwwMCRQYW5lbENvbnRlbnQkYm94SW5kdXN0cnkFHmN0bDAwJFBhbmVsQ29udGVudCRib3hTRV9BdWRpdAUcY3RsMDAkUGFuZWxDb250ZW50JGJveFNFX1RheAUjY3RsMDAkUGFuZWxDb250ZW50JGJveFNFX0NvbnN1bHRpbmcFIWN0bDAwJFBhbmVsQ29udGVudCRib3hTRV9MYW5ndWFnZQUdY3RsMDAkUGFuZWxDb250ZW50JGJ0blNlYXJjaDJHJTpowaXl844dPxawDjd9wO3A9Q%3D%3D&__VIEWSTATEGENERATOR=D3B5AAD8&__EVENTVALIDATION=%2FwEdAKABhFh%2BR%2BUl5lK0SEv9lfe%2F4VspY774O2CAtS%2Fr79sDHK865vXjqVOlvUwCb0%2FZKYFCCbuEbyHsIDLl%2FtLcn2KJyvMeLoqGIRI2CZUSnJOOZFVdJbY64iScxMso6itWL%2BIvhUoKlcDz2AqtYRXJKY3gUPSjuo%2BWvRxvHn3OhMUJ6BVNSwOwwNi94fBshPjmrvmG0lsBpXo8%2FZGybRxcBCCU6pExIdwxgj9syA5QIIV5HhjH2beLQJLcwbu0dPVXNxfcJTeoTkE6dnw6W3KuhPzfHakpfzks6ZXl50KHR68VagRPTGJJrnNaDNcsQFGQaJc5CKh4pdbov1MeN92sRmQZnYoBkCKkxPeuT9tg4ofOE0iaBoxA%2FyKNHViN9POlgA%2FPUoRp8WIEawJwFvIq8JJYxKX8MDGzadkxrSwheJFzxv3BT%2FJNMnMDj97QkelIZ8%2BtjgttGWgXBGJinTGvzjIzrY4TekPjnbiCcjvTzU2d%2Bky3XagJV1b4DLy6rbd2UdXIef688Xn2cnlIjs38kUTN4X5DufgG4ERnflKthAMc95jd5icXRL54KjHn4IYJNMm7wqRQFE6xPaB2Eq9VBKHM8WmSWxzXEj9ODIruxs3XB9rB49HmpaHUTf9Vkg4opkNcD0LJlwjyjxi%2B%2FhyqpngBH%2BMK15Invq7Hz%2BN99qYNxhxQT5HTIensfpw8sH88nOvtAOzYB64xuMCqMGzjhWSvT6sDO1cqUutI1I9zdDxXIyqmOBiyK1mjsLG%2FUAcULKTYZhb%2FXIuBXz3NvFjuP3C4ZIkBqEOzikxrybYqAqtqqnfuJWsoIHYBEblnxBmSvZAr%2B0fv1eaczpBNdcjwPUC6c%2FqRYOxVtvndCNJZ%2Bh41YE6BkMpneAf36lviqCkUe8TlksKlbLYwpEKTDddlGpdFLl%2FUVIpPDHGlz60G6y05fuea6ygqqamd6v49m1RTj4HpvldRLZPIQgnkBM7tDxQ2vAtDgUc9P5P9aCmHrV8b9AmaXpu3sI%2BHjxv9%2BP3Fea5k%2BYAXKZr3Y1GGy5W2IWg%2BzYoXQIjYl%2Bfs6NhXn2yoYrYj9V%2Bqslz6Dku2Dp0DFhS%2B8egxogALBWEbsoQ9sok6HS0F0bHaja5ewsIPj2jNA6gSa9wqkICLpyq8zua%2FSQizSqRFI6imS11FI3xN%2BJKGZYrx3TEz2RBld2AMr3wHQDzzpyK11EteDll3wYvf52Yll9fjOrqcqN6zIOear2Bep13iM%2F%2BNVZDgdZC%2BDwAMI9d1sd3g7eV22%2F0egVYCuM6GfGYmi%2BIiSbhCD7sS09pjkvn0uTiheBEMM9l85zSsEqqLTIAXd0leSElKoUZallbsmDwMe7Njh9KBCiVbRfxGc5uREBL04iDK0vAPJhMSNSWu%2F%2FwM0PLuLzGvz4v2iwqfnpEKcUpEpJBQ2IYGavog3xm9UqknCvpbfdpN2XnJR1SN3DXCr%2BUkeBFZ6jTkV4bR%2BPUwf7CQSzMbJq6BAry%2FSwM7inkJdSiqA0Og0Resesk%2BBigb660knOSaneo1%2FDRlb30Kh510eMnLBoduu9ryoUdHjQ13qkqrE99429oEHQwCMbMq9YbmUJYFrnOyYaKeXWd9ATtj5SSc7LpxKV4zEcKTSVsOU1NLJWBZZvBxXj%2BziQMJDZHW3%2B7u30E2MD5dLFnaqtrAAavhAlG2MRUJ%2B5gTBcUTGviTh%2F%2F0LSngOrig6lT7HjYOZCQ7noyCePEtgOmwbtL63GajGeuSntV9J4pFmyKuBIy%2BS73jY7sZcrh4yAve70EOsoIpuFhHaFFh7ZZQnNN6urQB1EPvlA3VghyPSMvzh8dhWIwGb1WuI1%2FRpfRFie2CbywMtWqDbcaM3w6f1%2Fu5EcJbyiICJZAlEZdO6QPtBDuO7NhYJBOtBcX4OPH5At6f2jtCRCzgWg72j7PjU0yyE7G50Q%2FYq6riRWDgXJ2db1KlMkKZtg8fm1ujvfwx9Y%2BGPCjJ57TnZnqA%2F1lB1UWQCQg5HczhnOGJvxNwxdSntEZkP1nStkdsOTKwg2wb19vBG9Qn1KUWjsyvFdq5iEp%2Bv60YAdyD2eanr4tam5VoSHmKJ5QdPnbQtoesTcIlUro%2BWG89xhg8L%2FC5GLvHgswh06wzoNvVZb%2B6OXC9oh9XBEBHU2lAYYfbaSfOW2tRqds56s0anq8OFhjmSWXV8C%2BQrMDOYTEshy9xaHnGvgPly%2B4KfQRMTarWJ0QJMj0M6DiiIVqImQE8FnK6dtPEa6GnSpcZU0f3Z4bS0onxjQ7LSBHarQSS%2FVcvtUkRUH2NEhYY8VxoxbUbJpFZXNPmwA4eQTVYozLWmjGBFeBGIP4S7%2Fb4pV4EYEEc06o%2FgLNP%2FzSgFRcw440J4DcVB2%2BShYbLFq6eG6jQP665NvHnqC2ukB3OR6w6GMVfD8xoqpJo0VbHuovA5oq7tANRnoiZD%2FgIprdn%2FxYK9%2FxDSu8f5bPK3th%2BZCwC0rdum0oB7jnrB%2BHQX80fjdK45PsdkObetIwk9sPqzYaKpkv4kopzS3VR6K%2B%2FWGGTwMD4D5hJ0B8vHaVGxN5HRHqt4aw%2By9VKiavTrDw1i0EI7VSIVdoGnfvgp7v5btYHQE6l6lkUcuSTdzpFI9nfmUBWVNcDHK5d8bCGBMyEarH%2FoQWbPIt%2BuHFueltKGK6iuZ6cJWMFXQfafe4YqBYrWJy2owFYJpV4VwbgqjP6g1zwTdIYwDVSWie9nfo1t7Zmg7Je6ckeRGOJ6lPBrOgk%2Bsg12nNH4qvr4%2Brhvdnj7hbFTFIHDEMAfD3Z0BnDGmnLZ3bAMICxgqwsmk9uSz6zUiXZDPXtNY7LW%2Bn9diARDImNHtbdZcJrNa86sdfYi8GeGFPPW0JZwSWJPsUKyg3Mbi7ac3iGWD4dM%2FBMnwROv3ZsrIYNqpwcHUKIh0%2B9iNul1%2Fyq8hFYQ8CPErENftaXz7WQClCFFLdmYsYF9Z9XfF%2BElDUPwjYCO9SPEajwEJLrRYGSLKH3m2TrmPr4YJpvCJxgVpl4znVFR%2FkxySlvtujTpwWj0mxxrF8nAZwifUsL9z2E6mWslhd%2BOZhgCf%2BHquN6EeswNJD67GAX6rlX2rrh7sc%2FADgRozARfF2tY7zhyjfQKVFZgxliNT8VSgklcIqjkd7FwS9WdjubWlRTLuNArpm9rSwJz0lJJK%2BjRXZe14o5y1bsqAdaRekaatV3z%2BtyaG40KI65Mbkg%2FEcy%2B%2BzPJVfHRHgOKZUsH03luKLMPi83kCliwByjx1E%2BiH6DswpU0ptIrVCAwy8fkZbhoDa9p2rZlQTJgYjHN6we9I8%2BKbpjrKN6iVk8FGI6c039L7wU5E2Uc8TUlI5b7rRlnABmm5LbQUIYc%2F%2FhAIpwcwLX3uL97kuw9hic%2FUU5hg98qWsRUABJ8PgzwTZFYtC4BOgOXuLDmsGnRecllEJsKGmST097T%2FWGQBe9&ctl00%24PanelContent%24boxCity=All&ctl00%24PanelContent%24boxIndustry=All&ctl00%24PanelContent%24boxSE_Audit=All&ctl00%24PanelContent%24boxSE_Tax=All&ctl00%24PanelContent%24boxSE_Consulting=All&ctl00%24PanelContent%24boxSE_Language=All&ctl00%24PanelContent%24btnSearch2.x=0&ctl00%24PanelContent%24btnSearch2.y=0";




		log::info("Perform Search");
		$this->LoadPostUrl("https://www.macpa.org/Public/Referral/findcpa.aspx",$data);
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


$r= new macpa();
$r->parseCommandLine();

