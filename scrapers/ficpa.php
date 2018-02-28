<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class ficpa extends baseScrape
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
		//db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		//db::query("DROP TABLE $type ");
		//db::query("UPDATE load_queue SET processing = 1 where type = '$type' ");

		$webRequests= array();
//		$this->noProxy= false;
	//	$this->proxy = "localhost:8888";

		$html = $this->get("http://www.ficpa.org/Public/Referral/findcpa.aspx");
		$page = new HtmlParser($html);		
		
		$counties = array('ALA', 'BAK', 'BAY', 'BRA', 'BRE', 'BRO', 'CAL', 'CHA', 'CIT', 'CLA', 'CLL', 'CLU', 'DES', 'DIX', 'DUV', 'ESC', 'FLA', 'FRA', 'GAD', 'GIL', 'GLA', 'GUL', 'HAM', 'HAR', 'HEN', 'HER', 'HIG', 'HIL', 'HOL', 'IND', 'JAC', 'JEF', 'LAF', 'LAK', 'LEE', 'LEO', 'LEV', 'LIB', 'MAD', 'MAN', 'MRI', 'MRT', 'DAD', 'MON', 'NAS', 'OKA', 'OKE', 'ORA', 'OSC', 'PAL', 'PAS', 'PIN', 'POL', 'PUT', 'SAN', 'SAR', 'SEM', 'STJ', 'STL', 'SUM', 'SUW', 'TAY', 'UNI', 'VOL', 'WAK', 'WAL', 'WAS');
		foreach($counties as $county)
		{
			$data = "__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKMTE0MDA5NTQ5Nw9kFgJmD2QWEmYPZBYCAgIPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIBD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCAw9kFgICAg9kFgRmDxYCHgdWaXNpYmxlaGQCAg8WBB4JaW5uZXJodG1sBaMPDQo8ZGl2IGNsYXNzPSJhcnJvd2xpc3RtZW51Ij4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIGNsYXNzPSJ0aGlzUGFnZUlkIiB2YWx1ZT0iNjYyNDkiIC8%2BPGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InRoaXNQYXRoSWQiIHZhbHVlPSI7NjYyNDk7IiAvPjxoMyBjbGFzcz0ibWVudWhlYWRlciI%2BPGEgY2xhc3M9InB1YmxpY0FjY2VzcyIgaHJlZj0iL3B1YmxpYy9yZWZlcnJhbC9maW5kY3BhLmFzcHgiIHRpdGxlPSJGaW5kIGEgQ1BBIj5GaW5kIGEgQ1BBPGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InBhZ2VJZCIgdmFsdWU9IjY2MjQ5IiAvPjwvYT48L2gzPg0KPGgzIGNsYXNzPSJtZW51aGVhZGVyIj48YSBjbGFzcz0icHVibGljQWNjZXNzIiBocmVmPSIvQ29udGVudC9QdWJsaWMvQ1BBLmFzcHgiIHRpdGxlPSJCZWNvbWUgYSBGbG9yaWRhIENQQSI%2BQmVjb21lIGEgRmxvcmlkYSBDUEE8aW5wdXQgdHlwZT0iaGlkZGVuIiBjbGFzcz0icGFnZUlkIiB2YWx1ZT0iNjU1MDQiIC8%2BPC9hPjwvaDM%2BDQo8aDMgY2xhc3M9Im1lbnVoZWFkZXIiPjxhIGNsYXNzPSJwdWJsaWNBY2Nlc3MiIGhyZWY9Ii9jb250ZW50L0FkdmVydGlzZS5hc3B4IiB0aXRsZT0iQWR2ZXJ0aXNlIGFuZCBTcG9uc29yIj5BZHZlcnRpc2UgYW5kIFNwb25zb3I8aW5wdXQgdHlwZT0iaGlkZGVuIiBjbGFzcz0icGFnZUlkIiB2YWx1ZT0iNjU0MDIiIC8%2BPC9hPjwvaDM%2BDQo8aDMgY2xhc3M9Im1lbnVoZWFkZXIiPjxhIGNsYXNzPSJwdWJsaWNBY2Nlc3MiIGhyZWY9Ii9Db250ZW50L1B1YmxpYy9GaW5hbmNpYWxMaXRlcmFjeS9FbGRlci5hc3B4IiB0aXRsZT0iRWxkZXIgUGxhbm5pbmcgYW5kIFN1cHBvcnQgU2VydmljZXMiPkVsZGVyIFBsYW5uaW5nIGFuZCBTdXBwb3J0IFNlcnZpY2VzPGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InBhZ2VJZCIgdmFsdWU9Ijc4MDM5IiAvPjwvYT48L2gzPg0KPGgzIGNsYXNzPSJtZW51aGVhZGVyIGxhc3QiPjxhIGNsYXNzPSJwdWJsaWNBY2Nlc3MgbGFzdCIgaHJlZj0iL0NvbnRlbnQvUHVibGljL0ZpbmFuY2lhbExpdGVyYWN5LmFzcHgiIHRpdGxlPSJFbmhhbmNlIFlvdXIgRmluYW5jaWFsIExpdGVyYWN5Ij5FbmhhbmNlIFlvdXIgRmluYW5jaWFsIExpdGVyYWN5PGlucHV0IHR5cGU9ImhpZGRlbiIgY2xhc3M9InBhZ2VJZCIgdmFsdWU9IjY1NDA1IiAvPjwvYT48L2gzPg0KPGRpdiBpZD0iY3RsMDBfUGFuZWxTZWNvbmRhcnlOYXZpZ2F0aW9uX29QYW5lbF82NjI0OV8xNV81X3BhcmVudE5vZGUiIHN0eWxlPSJkaXNwbGF5Om5vbmU7Ij48aDI%2BPGEgY2xhc3M9InB1YmxpY0FjY2VzcyBsYXN0IiBocmVmPSIvQ29udGVudC9QdWJsaWMuYXNweCIgdGl0bGU9IkZvciB0aGUgUHVibGljIj5Gb3IgdGhlIFB1YmxpYzxpbnB1dCB0eXBlPSJoaWRkZW4iIGNsYXNzPSJwYWdlSWQiIHZhbHVlPSI2NTQwMCIgLz48L2E%2BPC9oMj4NCjwvZGl2Pg0KPC9kaXY%2BDQoNCjxzY3JpcHQ%2BDQogICAgDQovLzs2NjI0OTsNCmRkYWNjb3JkaW9uLmluaXQoew0KCWhlYWRlcmNsYXNzOiAiZXhwYW5kYWJsZSIsDQoJY29udGVudGNsYXNzOiAiY2F0ZWdvcnlpdGVtcyIsDQpyZXZlYWx0eXBlOiAibW91c2VvdmVyIiwNCiAgICBtb3VzZW92ZXJkZWxheTogNTAwLA0KCWNvbGxhcHNlcHJldjogdHJ1ZSwNCglkZWZhdWx0ZXhwYW5kZWQ6IFstMV0sDQoJb25lbXVzdG9wZW46IGZhbHNlLA0KCWFuaW1hdGVkZWZhdWx0OiBmYWxzZSwNCglwZXJzaXN0c3RhdGU6IGZhbHNlLA0KCXRvZ2dsZWNsYXNzOiBbIiIsICJvcGVuaGVhZGVyIl0sDQoJdG9nZ2xlaHRtbDogWyJwcmVmaXgiLCAiIiwgIiJdLA0KCWFuaW1hdGVzcGVlZDogImZhc3QiLA0KCW9uaW5pdDpmdW5jdGlvbihoZWFkZXJzLCBleHBhbmRlZGluZGljZXMpeyB9LA0KCW9ub3BlbmNsb3NlOmZ1bmN0aW9uKGhlYWRlciwgaW5kZXgsIHN0YXRlLCBpc3VzZXJhY3RpdmF0ZWQpeyB9DQp9KTsNCg0KPC9zY3JpcHQ%2BDQofAGdkAgQPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkFgJmD2QWBAIBD2QWAmYPZBYCAgEPPCsACwIADxYGHhNBdXRvR2VuZXJhdGVDb2x1bW5zZx4KU2hvd0hlYWRlcmcfAGhkAxYEHglGb250X0JvbGRnHgRfIVNCAoAQZAIDD2QWAmYPZBYCAgEPPCsACwIADxYGHwJnHwNnHwBoZAMWBB8EZx8FAoAQZAIFDxYCHwEFCkZpbmQgYSBDUEFkAgYPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIHD2QWAgIED2QWCgIFDw8WBB4EVGV4dAVGTm8gQ1BBcyBsb2NhdGVkIGluIG91ciByZWZlcnJhbCBzeXN0ZW0gLSBwbGVhc2Ugd2lkZW4gc2VhcmNoIGNyaXRlcmlhIB8AZ2RkAgkPEA8WBh4ORGF0YVZhbHVlRmllbGQFBGNvZGUeDURhdGFUZXh0RmllbGQFCGNvZGVkZXNjHgtfIURhdGFCb3VuZGdkEBVEA0FueQdBbGFjaHVhBUJha2VyA0JheQhCcmFkZm9yZAdCcmV2YXJkB0Jyb3dhcmQHQ2FsaG91bglDaGFybG90dGUGQ2l0cnVzBENsYXkHQ29sbGllcghDb2x1bWJpYQZEZXNvdG8FRGl4aWUFRHV2YWwIRXNjYW1iaWEHRmxhZ2xlcghGcmFua2xpbgdHYWRzZGVuCUdpbGNocmlzdAZHbGFkZXMER3VsZghIYW1pbHRvbgZIYXJkZWUGSGVuZHJ5CEhlcm5hbmRvCUhpZ2hsYW5kcwxIaWxsc2Jvcm91Z2gGSG9sbWVzDEluZGlhbiBSaXZlcgdKYWNrc29uCUplZmZlcnNvbglMYWZheWV0dGUETGFrZQNMZWUETGVvbgRMZXZ5B0xpYmVydHkHTWFkaXNvbgdNYW5hdGVlBk1hcmlvbgZNYXJ0aW4KTWlhbWktRGFkZQZNb25yb2UGTmFzc2F1CE9rYWxvb3NhCk9rZWVjaG9iZWUGT3JhbmdlB09zY2VvbGEKUGFsbSBCZWFjaAVQYXNjbwhQaW5lbGxhcwRQb2xrBlB1dG5hbQpTYW50YSBSb3NhCFNhcmFzb3RhCFNlbWlub2xlCVN0LiBKb2hucwlTdC4gTHVjaWUGU3VtdGVyCFN1d2FubmVlBlRheWxvcgVVbmlvbgdWb2x1c2lhB1dha3VsbGEGV2FsdG9uCldhc2hpbmd0b24VRANBbGwDQUxBA0JBSwNCQVkDQlJBA0JSRQNCUk8DQ0FMA0NIQQNDSVQDQ0xBA0NMTANDTFUDREVTA0RJWANEVVYDRVNDA0ZMQQNGUkEDR0FEA0dJTANHTEEDR1VMA0hBTQNIQVIDSEVOA0hFUgNISUcDSElMA0hPTANJTkQDSkFDA0pFRgNMQUYDTEFLA0xFRQNMRU8DTEVWA0xJQgNNQUQDTUFOA01SSQNNUlQDREFEA01PTgNOQVMDT0tBA09LRQNPUkEDT1NDA1BBTANQQVMDUElOA1BPTANQVVQDU0FOA1NBUgNTRU0DU1RKA1NUTANTVU0DU1VXA1RBWQNVTkkDVk9MA1dBSwNXQUwDV0FTFCsDRGdnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgECAWQCCw8QDxYGHwcFBGNvZGUfCAUIY29kZWRlc2MfCWdkEBUbA0FueQlBZXJvc3BhY2UMQWdyaWJ1c2luZXNzGEFzc29jaWF0aW9ucy9Ob24tUHJvZml0cxZBdXRvbW9iaWxlIERlYWxlcnNoaXBzGEJhbmtzL0NyZWRpdCBVbmlvbnMvUyZMcxVDb2xsZWdlcy9Vbml2ZXJzaXRpZXMpQ29uZG9taW5pdW0vSG9tZW93bmVyIEFzc29jaWF0aW9ucyAoQ0lSQSkYQ29uc3RydWN0aW9uL0NvbnRyYWN0b3JzFkRpc3RyaWJ1dGlvbi9XaG9sZXNhbGUNRW50ZXJ0YWlubWVudARGb29kC0ZyYW5jaGlzaW5nGkdvdmVybm1lbnQgKEZlZGVyYWwvU3RhdGUpEkdvdmVybm1lbnQgKExvY2FsKQtIZWFsdGggQ2FyZR5Ib3NwaXRhbGl0eS9Ub3VyaXNtL1Jlc3RhdXJhbnQJSW5zdXJhbmNlDU1hbnVmYWN0dXJpbmcRTWVkaWEvQWR2ZXJ0aXNpbmcKTm9uLVByb2ZpdA5PaWwvR2FzL01pbmluZw1Qcm9mZXNzaW9uYWxzC1JlYWwgRXN0YXRlBlJldGFpbA5UcmFuc3BvcnRhdGlvbglVdGlsaXRpZXMVGwNBbGwCMDECMDICMDQCMDMCMDUCMDYCMDcCMDgCMDkCMTACMTECMTICMTMCMTQCMTUCMTYCMTcCMTgCMTkCMjACMjECMjICMjMCMjQCMjUCMjYUKwMbZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgECAWQCDQ8QDxYGHwgFCGNvZGVkZXNjHwcFBGNvZGUfCWdkEBUeA0FueQhBdWRpdGluZw5BdWRpdGluZyBBLTEzMx1CYW5rcnVwdGN5L0luc29sdmVuY3kgU3VwcG9ydAtCb29ra2VlcGluZxxCdXNpbmVzcyBDb25zdWx0aW5nL1BsYW5uaW5nE0J1c2luZXNzIFZhbHVhdGlvbnMUQ29tcGlsYXRpb25zL1Jldmlld3MPRGlzYXN0ZXIgQ2xhaW1zIkRpdm9yY2UvRG9tZXN0aWMgUmVsYXRpb25zIFN1cHBvcnQcRWxlY3Ryb25pYyBDb21tZXJjZS9JbnRlcm5ldBZFbXBsb3llZSBCZW5lZml0IFBsYW5zDkVzdGF0ZXMvVHJ1c3RzEkZpbmFuY2lhbCBQbGFubmluZxxGaW5hbmNpYWwgUmVwb3J0cy9TdGF0ZW1lbnRzHkZyYXVkIEludmVzdGlnYXRpb25zL0ZvcmVuc2ljcxxJbnRlcm5hdGlvbmFsIEJ1c2luZXNzL1RyYWRlC0ludmVzdG1lbnRzEklSUyBSZXByZXNlbnRhdGlvbhNMaXRpZ2F0aW9uIFNlcnZpY2VzFk1lcmdlcnMgJiBBY3F1aXNpdGlvbnMXTmV3IEJ1c2luZXNzIENvbnN1bHRpbmcKTm9uLVByb2ZpdBxQZW5zaW9uL1Byb2ZpdCBTaGFyaW5nIFBsYW5zHlF1aWNrYm9va3MvQWNjb3VudGluZyBzb2Z0d2FyZRFSZWFsIEVzdGF0ZSBUYXhlcxdTbWFsbCBCdXNpbmVzcyBQbGFubmluZw5UYXggKEJ1c2luZXNzKRBUYXggKEluZGl2aWR1YWwpIlRlY2hub2xvZ3kgQ29uc3VsdGF0aW9uLyBDb21wdXRlcnMVHgNBbGwCMDICQUECMDMCMDQCMDUCMDYCMDcCMDkCMDgCMTACMTECMTICMTMCMTQCMTUCMTYCMTcCMTgCMTkCMjACMjECMjICMjMCMjQCMjUCMjYCMjcCMjgCMjkUKwMeZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgECAWQCDw8QDxYGHwgFCGNvZGVkZXNjHwcFBGNvZGUfCWdkEBUeA0FueQZBcmFiaWMHQ2hpbmVzZQVDemVjaAVEdXRjaAVGYXJzaQZGcmVuY2gNRnJlbmNoIENyZW9sZQZHZXJtYW4FR3JlZWsJR3VqYXJhdGhpBkhlYnJldwVIaW5kaQlIdW5nYXJpYW4HSXRhbGlhbghKYXBhbmVzZQZLb3JlYW4QS3J1LCBJYm8sIFlvcnViYQlNYWxheWFsYW0GUG9saXNoClBvcnR1Z3Vlc2UIUm9tYW5pYW4HUnVzc2lhbg5TZXJiby1Dcm9hdGlhbgdTcGFuaXNoB1RhZ2Fsb2cEVGhhaQRVcmR1ClZpZXRuYW1lc2UHWWlkZGlzaBUeA0FsbAIzMAIzMQIzMgIzMwIzNAIzNQIzNgIzNwIzOAIzOQI0MAI0MQI0MgI0MwI0NAI0NQI0NgI0NwI0OAI0OQI1MAI1MQI1MgI1MwI1NAI1NQI1NgI1NwI1OBQrAx5nZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2cWAWZkAggPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIKD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgcFHGN0bDAwJFBhbmVsQ29udGVudCRidG5TZWFyY2gFG2N0bDAwJFBhbmVsQ29udGVudCRidG5DbGVhcgUcY3RsMDAkUGFuZWxDb250ZW50JGJveENvdW50eQUeY3RsMDAkUGFuZWxDb250ZW50JGJveEluZHVzdHJ5BR5jdGwwMCRQYW5lbENvbnRlbnQkYm94U2VydmljZXMFIWN0bDAwJFBhbmVsQ29udGVudCRib3hTRV9MYW5ndWFnZQUdY3RsMDAkUGFuZWxDb250ZW50JGJ0blNlYXJjaDJCVz2aehSppyIv1UJ7F%2F4zJ%2F19dw%3D%3D&__VIEWSTATEGENERATOR=0F8CBF0E&__EVENTVALIDATION=%2FwEdAKABTFoILFcHlUIYYZSUca25ClspY774O2CAtS%2Fr79sDHK865vXjqVOlvUwCb0%2FZKYFC1WLrXOB9RqXIUTAoLPdksiPaN%2Bd%2BmH1Xzr58oxCn6w%2Fk1qA%2F7BX%2F4fo9kbfTGGU400c41NETYDm4JLH9MJ9eYmD6dD0JGnjAqhcrIeuNGeJymb2QleyPaMBcbcNDqkw62sUo56clNI8yao%2F%2FJbtmxlzsiCgy%2FYvVnmiLNuvAFu1ihpc3uK23VLjJ8LbiTwiAxEgKRuHoWa%2BZiqrgkemcCnwdYzmd3%2B6IsAPRt5PtxzGzkdsKQSEtAvH9ETroV1sWYn%2BS%2B0u25dtMokbPXcAssz0lkgdofTtUtfi5jqk3o%2BHh3jrQ13SoW9n8ZMMVVK9g62eQehvrYp1qUL88qgiba%2FmZJnxv8GmROg%2Fn%2BJlrfKS4ArV0%2FLgMHuEHNquHJzyC7TpUmryEpgfU7PVuquJPFzhzoQqts1%2B1GjaC58%2FxFM8dvT8TnDiQmTXWb8JJsYbvDNp%2FU16lqtmKMBL7XHQ5PIw0X4DtmDnvUt3BjhjXfSOtuoIoTrm2gTSCqbrg8mK59PumexKRcj%2Bodd0ueZeWdeX%2F2%2F7ukmwpjKyI%2FsKV2wZn0vhXmp6ftfi4gGpjXSskbd31TY2v4EurWWiewdn70%2B%2BcnMYNRPUNErbFUrhIzTY0W3Dy6dsqmGkAzWGI2aW3AnA92lgeVHm6R0AQS29MkYw%2F2ZJRtqK6s86Yhxj2TbIuzPU9ajXLP7oegogrQ%2FMN7LaIGx8uGR5Sw24zlcUrOn%2BfZbaSUzsi7KY3e6jTX%2BXQ29WpW8p9XwOdrX9NDALhhcE4GoWfu%2BWk3hBfadK1yb5be9qKP4Uupv0uUBe1EWS39soCEP3U7ZXR%2F5%2F6ayIf9pfKCeTAEcbeiYmFGoK%2FCPQdkX6xwksT9gDW6P7uldyHBJJT3kc2IrasU6SqwL5M4IIDTcgVfhtf2%2BmduuaIuVVYV7fKW3fXLU03pdAja%2Fg7YEQ%2FUe8QUlFBwcOthV1TuCOG3G1%2FxJrOkV7nngFBC1qLtRJ2pgRZ8yc9W9EwYmYcso6yX%2Bgh4B5JBrZ18rPS8mLclwoIbOSi3MaxsVN2tdJe5QJO2u9ZL2tkgGzBMYIjZDrTmgSbo0%2BuXsf59mMyR3nm71Wo%2Bf1Dp4znADw4Ivo5uBItHJsqnW%2BAji86C878k%2BCYgoqAyYHlSuAQ%2B7KmTC4Y7z78TZaz38nIxX9EPGTOhFjAzrQoB2V%2F4FxcPYJXb21MYO3n3ylIjFXAVGyxlhkbFMhzhLd0vkJh%2BxDrvT1Q91A2bPv9ovTdvKmz8%2FNoag6zn2iypSO0HfgHr1mA4Kbf7gyaMpkraHKRCYtBh1ItnFOW4CEnJNrDI3jJDG3qSRAfe8qP3Kv1OuTwePRcQvZB8h4KkPxdazRblIZv1DiOdTUXX1tS7sIYFGHqZ262tjLAd%2Bzx4HrH2KNIi%2FWsG0VrFhxyo8mGCYCUZhEShO6%2FMdoJ%2BlsftU0y%2Bb0wgNIQb%2BcU%2FI%2BRl1AFxd7f7KTf4wrXkie%2BrsfP4332pg3GHFBPkdMh6ex%2BnDywfzyc6%2B1cHxFkfO0jd1zdHjThp40pivHdMTPZEGV3YAyvfAdAPC0F0bHaja5ewsIPj2jNA6jzpyK11EteDll3wYvf52Yll9fjOrqcqN6zIOear2Bep%2Fn0uTiheBEMM9l85zSsEqqbkRAS9OIgytLwDyYTEjUlrv%2F8DNDy7i8xr8%2BL9osKn43cNcKv5SR4EVnqNORXhtEA7NgHrjG4wKowbOOFZK9PAzuKeQl1KKoDQ6DRF6x6yftH79XmnM6QTXXI8D1AunMMa16i21NmANJA%2BHOF8sDii0yAF3dJXkhJSqFGWpZW7KsDO1cqUutI1I9zdDxXIypmFv9ci4FfPc28WO4%2FcLhkiQGoQ7OKTGvJtioCq2qqd%2FqRYOxVtvndCNJZ%2Bh41YE6BkMpneAf36lviqCkUe8TlYY0%2F6ukyyl%2BaH11EHMDz%2FqY4GLIrWaOwsb9QBxQspNi%2BV1Etk8hCCeQEzu0PFDa8EXPEO6L%2B2%2BR7nR2qGpaWEIoXQIjYl%2Bfs6NhXn2yoYrYj9V%2Bqslz6Dku2Dp0DFhS%2BsWnErR3wyQGx1cUJnZHXU3ZlClfcqvu7YAoT1VMoU%2BsYOeNLxJEjvG9uK6JVGJ6BwI7VetcfIWlkRsoXGzYoh47M0w%2FJ55efWB3pLtwRPbRbPfjUCldz%2B0skOoAl3uOnqjoWY4JfqKEr%2FVMNzlytX2LNRkPLLVi98spQSnxZSV3%2FTrbyjx8fC3riKS0HO2Vci5Bo69DkEFo09tFPU2p9qb6B1vT%2Bkh6UevbODDPTitbH0HxAPx1nuZ8sEc2CqqJLV5mRkrCcqPu4%2Bhj2L%2BIPFkKhomReAro3lHO3x6q7SUPEtDpc%2FIxYIOgG5%2FDjyy1jNKlRMqLS9qBZhjnR3rECvw51LcrPvE5azntIAdyBg2KroP0Pj%2FwjsbLk1uss0ylAwqPe%2FF601jBrtQerlYu85%2BUr6RT48RAESCMBwjyrO5k2o308og9dsh3hK%2BFVKyW4rC5Hqnty%2FpiqElufcMuEx%2BjTKLMSExB5TYtlvpGJHEdacPqDjYjBPxtTmqAI%2BeqqegOwu13VCoqeWqlMLVy2uxBx7mPdYUizNx52tUBaLaTyMkADakMwuo1vyXAJkoRVACATwxRLOZLCLGD8Dx%2F1wdYw2CGq5fZLWtAnJXsw0wNTl%2FK%2BYfa94nDIgR79%2BbaAsYKsLJpPbks%2Bs1Il2Qz17YmISxm%2FclkvNfsio1%2BeyDJuVLUigsz6cjPu%2FKM9k8IMQMjl7L4BPmKJ81Zq3nrHoTbdq%2BknALK6D7FBnfPRGEUbteeS%2BDLqidwmTkGmSMou%2B7lM4pH3MlQllLwfvsxZiJAath0XQwl27V7atV5016FJjcT%2Fo3wCYfE0nOPQdHcPzMMJtccd11rWqKjp%2BfJPKPyN5lNFdCs6HdUOAIQeitt1qba7WI9Ay2toLZ3Y3oQG6raOPTaVQvyDBFOP1jBxUAIdHfjewOjj1S49b%2BytQ6v%2FHS1Wu49mzGs0EZHU%2BefBAgTyheiHmUuuPNDaVasebNaXShqMI2B%2FZ0zAFna%2BIC3d6OfVlbx7rZg1jXEtZgsrGI4qjubu5Vrl3BE8vmvgTtGeywaBcWZ6NJ0LP8Gd4Fu0QUS5heMVserudEnTPpE5%2BnauP%2BHY4nQVL%2F6K0HFsS4KsS5dvnEoyyv9XimBcwHoMajO2WKJ2jzFCjwdY4xBgBCqHtbFsgPOcj7HK3suL3zESCLbFRJsLuBOGGmuYpNPxDxyAYSzOGgV8hKTTslqzStuPhpjUg2pnqp63VcmPjIOqcBjyQjyILNCcjfqd5%2B%2Bbyf0DJkDsC6512NnUk6pC8PgzwTZFYtC4BOgOXuLDmtaQzRzjewTxj8dR88t60VxjsGFO&ctl00%24PanelContent%24tbComp=&ctl00%24PanelContent%24boxCounty=".$county."&ctl00%24PanelContent%24boxIndustry=All&ctl00%24PanelContent%24boxServices=All&ctl00%24PanelContent%24boxSE_Language=All&ctl00%24PanelContent%24btnSearch2.x=17&ctl00%24PanelContent%24btnSearch2.y=3";
			$this->LoadPostUrl("http://www.ficpa.org/Public/Referral/findcpa.aspx?a=$county",$data,true);
			$this->queuedFetch();
			$this->parseData();
		}


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


$r= new ficpa();
$r->parseCommandLine();

