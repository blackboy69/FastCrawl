<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class tscpa extends baseScrape
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

		$html = $this->get("http://www.tscpa.com/Public/Referral/findcpa.aspx");
		$page = new HtmlParser($html);		
		$data = $page->loadViewState();

		$data = "__VIEWSTATE=%2FwEPDwUJMzM2ODQ1NTAyD2QWAmYPZBYMAgEPZBYCZg9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkAgMPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIFD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCCw9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkAg0PZBYCAgQPZBYIAgkPZBYCAgEPZBYCAgEPEGRkFgFmZAILD2QWAgIBD2QWAgIBDxAPFgYeDkRhdGFWYWx1ZUZpZWxkBQRjb2RlHg1EYXRhVGV4dEZpZWxkBQhjb2RlZGVzYx4LXyFEYXRhQm91bmRnZBAV3QEDQWxsBUFkYW1zCkFkYW1zdmlsbGUFQWZ0b24FQWxhbW8FQWxjb2ENQW5kZXJzb252aWxsZQdBbnRpb2NoBkFwaXNvbgdBcmRtb3JlCUFybGluZ3RvbglBcnJpbmd0b24HQXNobGFuZAxBc2hsYW5kIENpdHkGQXRoZW5zBUF0b2thCEJhcnRsZXR0BkJlbnRvbghCaWcgUm9jawtCbG91bnR2aWxsZQpCbHVmZiBDaXR5B0JvbGl2YXIJQnJlbnR3b29kCEJSSUdIVE9OB0JyaXN0b2wLQnJvd25zdmlsbGUFQnVybnMGQnV0bGVyCUJ5cmRzdG93bgZDYW1kZW4IQ2FydGhhZ2UGQ2VsaW5hC0NlbnRlcnZpbGxlC0NoYXBlbCBIaWxsCUNoYXJsb3R0ZQtDaGF0dGFub29nYQpDaHJpc3RpYW5hB0NodWNrZXkLQ2h1cmNoIEhpbGwLQ2xhcmtzdmlsbGUJQ2xldmVsYW5kB0NsaW50b24LQ29sbGVnZWRhbGUMQ29sbGllcnZpbGxlCENvbHVtYmlhCkNvb2tldmlsbGUKQ29wcGVyaGlsbAdDb3Jkb3ZhCENvcnJ5dG9uCUNvdmluZ3RvbgVDb3dhbgpDcm9zc3ZpbGxlCEN1bGxlb2thCURhbmRyaWRnZQZEYXl0b24MRGVjYXR1cnZpbGxlB0RlY2hlcmQHRGlja3NvbgZEb3Zlci4HRHJlc2RlbgZEdW5sYXAERHllcglEeWVyc2J1cmcERWFkcwxFbGl6YWJldGh0b24JRWxsZW5kYWxlCUVuZ2xld29vZAVFcndpbg5Fc3RpbGwgU3ByaW5ncwZFdG93YWgPRmFpcmZpZWxkIEdsYWRlCEZhaXJ2aWV3C0ZhbGwgQnJhbmNoDEZheWV0dGV2aWxsZQhGcmFua2xpbgxGcmllbmRzdmlsbGUHR2Fkc2RlbgpHYWluZXNib3JvCEdhbGxhdGluCkdlcm1hbnRvd24OR29vZGxldHRzdmlsbGUMR29yZG9uc3ZpbGxlCUdyYW5kdmlldwRHcmF5CUdyZWVuYmFjawpHcmVlbmJyaWVyC0dyZWVuZXZpbGxlCEhhcnJpc29uCUhhcnJvZ2F0ZQpIYXJ0c3ZpbGxlCEhlaXNrZWxsCUhlbGVud29vZAlIZW5kZXJzb24OSGVuZGVyc29udmlsbGUJSGVybWl0YWdlBkhpeHNvbglIb2hlbndhbGQISHVtYm9sZHQKSHVudGluZ2RvbgdKYWNrc29uCUphbWVzdG93bgZKYXNwZXIJSmVmZmVyc29uDkplZmZlcnNvbiBDaXR5B0plbGxpY28HSm9lbHRvbgxKb2huc29uIENpdHkMSm9uZXNib3JvdWdoCUtpbmdzcG9ydAhLaW5nc3RvbhBLaW5nc3RvbiBTcHJpbmdzCUtub3h2aWxsZQVLb2RhawtMYSBGb2xsZXR0ZQlMYWZheWV0dGUJTGFrZSBDaXR5CExhVmVyZ25lDExhd3JlbmNlYnVyZwdMZWJhbm9uBkxlbm9pcglMZXdpc2J1cmcJTGV4aW5ndG9uBkxpbmRlbgpMaXZpbmdzdG9uEExvb2tvdXQgTW91bnRhaW4HTG9yZXR0bwZMb3Vkb24KTG91aXN2aWxsZQlMeW5jaGJ1cmcHTWFkaXNvbgxNYWRpc29udmlsbGUKTWFuY2hlc3RlcgZNYXJ0aW4JTWFyeXZpbGxlDE1heW5hcmR2aWxsZQhNY0RvbmFsZAhNY0tlbnppZQtNY01pbm52aWxsZQdNZW1waGlzBU1pbGFuCk1pbGxpbmd0b24JTW9udGVhZ2xlCE1vbnRlcmV5Ck1vcnJpc3Rvd24HTW9zaGVpbQxNb3VudCBKdWxpZXQOTW91bnQgUGxlYXNhbnQNTW91bnRhaW4gQ2l0eQxNdXJmcmVlc2Jvcm8JTmFzaHZpbGxlCk5ldyBNYXJrZXQHTmV3cG9ydAtOb2xlbnN2aWxsZQZOb3JyaXMJT2FrIFJpZGdlBU9jb2VlC09sZCBIaWNrb3J5Dk9saXZlciBTcHJpbmdzBk9uZWlkYQhPb2x0ZXdhaAVQYXJpcwdQYXJzb25zBlBlZ3JhbQlQZXRlcmJ1cmcKUGV0ZXJzYnVyZwxQaWdlb24gRm9yZ2UJUGlrZXZpbGxlC1BpbmV5IEZsYXRzDVBsZWFzYW50IFZpZXcIUG9ydGxhbmQGUG93ZWxsB1B1bGFza2kTUmVkIEJvaWxpbmcgU3ByaW5ncwZSaXBsZXkIUm9ja2ZvcmQIUm9ja3dvb2QLUm9nZXJzdmlsbGUMUnVzc2VsbHZpbGxlCFJ1dGxlZGdlDFNhaW50IEpvc2VwaAhTYXZhbm5haAZTZWxtZXILU2V2aWVydmlsbGUHU2V3YW5lZQdTZXltb3VyC1NoZWxieXZpbGxlD1NpZ25hbCBNb3VudGFpbgpTbWl0aHZpbGxlBlNteXJuYQtTb2RkeSBEYWlzeQpTb21lcnZpbGxlD1NvdXRoIFBpdHRzYnVyZwlTb3V0aHNpZGUGU3BhcnRhC1NwcmluZyBDaXR5C1NwcmluZyBIaWxsC1NwcmluZ2ZpZWxkClN3ZWV0d2F0ZXIHVGFsYm90dAhUYXpld2VsbAdUZWxmb3JkDlRlbGxpY28gUGxhaW5zCFRlbiBNaWxlEVRob21wc29ucyBTdGF0aW9uC1RpcHRvbnZpbGxlCFRvd25zZW5kClRyYWN5IENpdHkHVHJlbnRvbgRUcm95CVR1bGxhaG9tYQpVbmlvbiBDaXR5CFdhcnRidXJnB1dhdmVybHkKV2F5bmVzYm9ybwxXZXN0bW9yZWxhbmQKV2hpdGVob3VzZQxXaGl0ZXMgQ3JlZWsKV2hpdGV2aWxsZQpXaWxsaWFtc29uCldpbmNoZXN0ZXIIV29vZGJ1cnkV3QEDQWxsA0FEQQNBRE0DQUZUA0FBTwNBTEEDQU5EA0FOVANBUEkDQURFA0FSTANBUlIDQVNEA0FTSANBVFMDQVRPA0JBUgNCRU4DQklHA0JMRQNCTFUDQk9SA0JSRQNCRzEDQlJJA0JXRQNCVVIDQlVUA0JZTgNDQU4DQ1RFA0NJMQNDRUUDQ0hQA0NIUgNDSEEDQ0hJA0NIVQNDVUwDQ0xBA0NMRQNDTEkDQ09FA0NPTANDTUEDQ09PA0NQTANDT1IDQ08xA0NWTgNDV04DQ1JFA0NVRQNEQUUDREFZA0RFQwNERUgDRElOA0RPMQNEUjEDRFVQA0RFMQNEWUcDRUFEA0VJTgNFTEwDRU5HA0VSTgNFU1QDRVRIA0ZBSQNGSTEDRkFMA0ZZRQNGUkEDRlJJA0dBRANHSTEDR0FMA0dFUgNHT08DR09SA0dSQQNHUjEDR1JFA0dSTgNHTkUDSEFSA0hBRQNIUjEDSEVJA0hFTANITk4DSEVOA0hFUgNISVgDSE9EA0hVTQNIVE4DSkFOA0pNTgNKU1IDSkVOA0pGWQNKTE8DSk9FA0pDWQNKT04DS0lOA0tJMQNLSUcDS05PA0tPRANMRkUDTFlFA0xBSwNMQVYDTEFXA0xCTgNMTlIDTEVXA0xFWANMRDEDTElOA0xPTwNMT1IDTE9OA0xPVQNMQzEDTUFEA01ERQNNTlIDTVJOA01BUgNNVjEDTUNEA01DSwNNTUUDTUVNA01JTANNTE4DTU9OA01ZMQNNU04DTU9TA01PVQNNUFQDTVRZA01VUgNOQVMDTkVXA05XVANOT0wDTk9SA09SRQNPQ08DT0xEA09TUwNPTkEDT09MA1BBUwNQUlMDUEVHA1BFVANQRUUDUEYxA1BJRQNQSU4DUExFA1BURANQT1cDUFVMA1JCMQNSSVkDUk9DA1JPRANSR0UDUlVTA1JUMQNTSkgDU0FIA1NFUgNTVkUDRVcxA1NFWQNTSEUDU0lHA1NJRQNTTVkDU09EA1NMMQNTVUcDU09VA1NSQQNTUEkDU1BSA1NORANTV1IDVEFCA1RBTANURUYDVEVMA1RFTgNUSE8DVEkxA1RPVwNUUkEDVFJOA1RSTwNUVUEDVUNZA1dBRwNXVlkDV1lPA1dFUwNXSEUDV0hJA1dIVANXSUwDV0lSA1dPWRQrA90BZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2cWAWZkAg0PEA8WBh8CZx8ABQRjb2RlHwEFCGNvZGVkZXNjZBAVGgNBbGwMYWdyaWJ1c2luZXNzFmF1dG9tb2JpbGUgZGVhbGVyc2hpcHMbYmFua3MvIGNyZWRpdCB1bmlvbnMgLyBTJkxzGWNvbGxlZ2VzIGFuZCB1bml2ZXJzaXRpZXMMY29uc3RydWN0aW9uC2NvbnRyYWN0b3JzDGRpc3RyaWJ1dGlvbg1lbnRlcnRhaW5tZW50BGZvb2QLZnJhbmNoaXNpbmcaZ292ZXJubWVudCAoZmVkZXJhbC9zdGF0ZSkSZ292ZXJubWVudCAobG9jYWwpC2hlYWx0aCBjYXJlE2hvc3BpdGFsaXR5L3RvdXJpc20JaW5zdXJhbmNlDW1hbnVmYWN0dXJpbmcFbWVkaWEIbWlsaXRhcnkObm90IGZvciBwcm9maXQLb2lsIGFuZCBnYXMNcHJvZmVzc2lvbmFscwtyZWFsIGVzdGF0ZQZyZXRhaWwOdHJhbnNwb3J0YXRpb24JdXRpbGl0aWVzFRoDQWxsAkExAkIxAkMxAkQxAkUxAkYxAkcxAkgxAkkxAkoxAksxAkwxAk0xAk4xAk8xAlAxAlExAlIxAlMxAlQxAlUxAlYxAlcxAlgxAlkxFCsDGmdnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAIPDxAPFgYfAmcfAAUEY29kZR8BBQhjb2RlZGVzY2QQFTkDQWxsE0FjY291bnRpbmcgU2VydmljZXMMQWdyaWJ1c2luZXNzCEF1ZGl0aW5nCkF1dG9tb3RpdmUVQmFua3J1cHRjeS9JbnNvbHZlbmN5F0JhbmtzL0NyZWRpdCBVbmlvbnMvUyZMC0Jvb2trZWVwaW5nDUJyb2tlci9EZWFsZXISQnVkZ2V0IEZvcmVjYXN0aW5nHUJ1c2luZXNzIFZhbHVhdGlvbnMvQXBwcmFpc2FsD0Nhc2ggTWFuYWdlbWVudBVDb21waWxhdGlvbnMgJiBSZXZpZXcMQ29uc3RydWN0aW9uDkNvbnRyb2xsZXJzaGlwDkRlYnQgRmluYW5jaW5nCUVkdWNhdGlvbg1FbnRlcnRhaW5tZW50EUVzdGF0ZSAmIEdpZnQgVGF4FkZpbmFuY2lhbCBJbnN0aXR1dGlvbnMTRm9yZW5zaWMgQWNjb3VudGluZxRHb3Zlcm5tZW50IC0gRmVkZXJhbBZHb3Zlcm5tZW50IC0gTm9ucHJvZml0GkdvdmVybm1lbnQgLSBTdGF0ZSAmIExvY2FsFUhlYWx0aCBDYXJlL0hvc3BpdGFscxRIb3NwaXRhbGl0eSBJbmR1c3RyeQ9IdW1hbiBSZXNvdXJjZXMJSW5zdXJhbmNlEUludGVybmFsIEF1ZGl0aW5nFkludGVybmF0aW9uYWwgQnVzaW5lc3MRSW52ZW50b3J5IENvbnRyb2wLSW52ZXN0bWVudHMSTGVnaXNsYXRpdmUgSXNzdWVzEUxpbWl0ZWQgTGlhYmlsaXR5EkxpdGlnYXRpb24gU3VwcG9ydBRNYW5hZ2VtZW50IC0gR2VuZXJhbB5NYW5hZ2VtZW50IENvbnN1bHRpbmcgU2VydmljZXMNTWFudWZhY3R1cmluZxRNZXJnZXJzL0FjcXVpc2l0aW9ucxZOb25wcm9maXQvQXNzb2NpYXRpb25zBU90aGVyC1BlZXIgUmV2aWV3GVBlbnNpb24vRW1wbG95ZWUgQmVuZWZpdHMbUGVyc29uYWwgRmluYW5jaWFsIFBsYW5uaW5nD1F1YWxpdHkgQ29udHJvbAtSZWFsIEVzdGF0ZRVSZWd1bGF0b3J5IEFjY291bnRpbmcNU0VDIFJlcG9ydGluZxdTbWFsbCBCdXNpbmVzcyBTZXJ2aWNlcxtTdHJhdGVnaWMvQnVzaW5lc3MgUGxhbm5pbmcUVGF4YXRpb24gLSBDb3Jwb3JhdGUfVGF4YXRpb24gLSBFeGVtcHQgT3JnYW5pemF0aW9ucxJUYXhhdGlvbiAtIEZlZGVyYWwVVGF4YXRpb24gLSBJbmRpdmlkdWFsGFRheGF0aW9uIC0gSW50ZXJuYXRpb25hbBBUYXhhdGlvbiAtIFNhbGVzClRlY2hub2xvZ3kVOQNBbGwCMDICMDECMDQCMDMCMDcCMDgCQk8CMDUCMDYCMDkCMTACQ1ICMTECMTICMTMCMTQCMTUCMTYCMTcCMTgCMTkCMjACMjECMjICMjMCMjQCMjgCMjUCMjYCMjcCMjkCMzACMzECMzICMzYCMzQCMzUCMzMCMzcCMzgCNDECMzkCNDACNDICNDQCNDMCNDYCNDUCNDcCNDgCNDkCNTACNTECNTMCNTICVEUUKwM5Z2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAIPD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgUFHGN0bDAwJFBhbmVsQ29udGVudCRidG5TZWFyY2gFG2N0bDAwJFBhbmVsQ29udGVudCRidG5DbGVhcgUeY3RsMDAkUGFuZWxDb250ZW50JGJveEluZHVzdHJ5BR5jdGwwMCRQYW5lbENvbnRlbnQkYm94U2VydmljZXMFHWN0bDAwJFBhbmVsQ29udGVudCRidG5TZWFyY2gyzrio3KzyiJNUMFVkDPLnwv1YUwo%3D&__VIEWSTATEGENERATOR=33E70A29&__EVENTVALIDATION=%2FwEWWAKiheT6DgKEksnUAwKjj6LABQLgqMmpCgKkypb5CQKG6r7%2FDwKH6r7%2FDwKE6r7%2FDwKF6r7%2FDwKC6r7%2FDwKD6r7%2FDwKA6r7%2FDwKx6r7%2FDwK%2B6r7%2FDwK%2F6r7%2FDwK86r7%2FDwK96r7%2FDwK66r7%2FDwK76r7%2FDwK46r7%2FDwKp6r7%2FDwK26r7%2FDwK36r7%2FDwK06r7%2FDwK16r7%2FDwKy6r7%2FDwKz6r7%2FDwKw6r7%2FDwKh6r7%2FDwKu6r7%2FDwLr5Y%2FpDgKGxaPvCAKGxafvCAKGxZvvCAKGxZ%2FvCAKGxY%2FvCAKGxcvsCALIxe%2FyCAKGxZfvCAKGxZPvCAKGxcfsCAKZxavvCALLxaPyCAKZxafvCAKZxaPvCAKZxZ%2FvCAKZxZvvCAKZxZfvCAKZxZPvCAKZxY%2FvCAKZxcvsCAKZxcfsCAKYxavvCAKYxafvCAKYxaPvCAKYxZ%2FvCAKYxZvvCAKYxcvsCAKYxZfvCAKYxZPvCAKYxY%2FvCAKYxcfsCAKbxavvCAKbxafvCAKbxaPvCAKbxZPvCAKbxZvvCAKbxZfvCAKbxZ%2FvCAKbxY%2FvCAKbxcvsCAKaxafvCAKbxcfsCAKaxavvCAKaxaPvCAKaxZvvCAKaxZ%2FvCAKaxZPvCAKaxZfvCAKaxY%2FvCAKaxcvsCAKaxcfsCAKdxavvCAKdxafvCAKdxZ%2FvCAKdxaPvCAL6xdfyCALhiPnYBfFvWeFfwgkLMCFF%2BOLKCeh0sYpA&ctl00%24PanelContent%24tbCompany=&ctl00%24PanelContent%24boxIndustry=All&ctl00%24PanelContent%24boxServices=All&ctl00%24PanelContent%24btnSearch2.x=35&ctl00%24PanelContent%24btnSearch2.y=3";

		log::info("Perform Search");
		$this->LoadPostUrl("http://www.tscpa.com/Public/Referral/findcpa.aspx",$data);
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


$r= new tscpa();
$r->parseCommandLine();

