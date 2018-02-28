<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class scacpa extends baseScrape
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

		$html = $this->get("http://www.scacpa.org/Public/Referral/findcpa.aspx");
		$page = new HtmlParser($html);		
		$data = $page->loadViewState();

		$data = "__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKLTY3MTM5OTY2Ng9kFgJmD2QWEAIBD2QWAgICD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCAw9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkAgUPZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkFgJmD2QWAgIBDxYCHgdWaXNpYmxlaGQCCQ9kFgICAg9kFgJmDxQrAAIPBcUBQztDOkJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgVmVyc2lvbj0yLjEuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTMwZDIzZjEwODVkZjNlNzI7b0RDUEgWAGRkAg0PZBYCAgIPZBYCZg8UKwACDwXFAUM7QzpCcnljZVdlYkdyb3VwLldlYi5VSS5XZWJDb250cm9scy5EeW5hbWljQ29udHJvbHNQbGFjZWhvbGRlciwgQnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIFZlcnNpb249Mi4xLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj0zMGQyM2YxMDg1ZGYzZTcyO29EQ1BIFgBkZAIPD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQCEQ9kFgICBA9kFgYCCw8QDxYGHg5EYXRhVmFsdWVGaWVsZAUEY29kZR4NRGF0YVRleHRGaWVsZAUIY29kZWRlc2MeC18hRGF0YUJvdW5kZ2QQFfYCA0FsbAlBYmJldmlsbGUFQWJuZXkFQWlrZW4JQWxsZW5kYWxlCEFuZGVyc29uB0FuZHJld3MNQXJjYWRpYSBMYWtlcwVBcmlhbA5BdGxhbnRpYyBCZWFjaAdBd2VuZGF3BUF5bm9yB0JhbWJlcmcIQmFybndlbGwTQmF0ZXNidXJnLUxlZXN2aWxsZQhCZWF1Zm9ydAZCZWx0b24JQmVsdmVkZXJlDUJlbm5ldHRzdmlsbGUFQmVyZWEIQmVya2VsZXkHQmV0aHVuZQtCaXNob3B2aWxsZQpCbGFja3NidXJnCkJsYWNrdmlsbGUIQmxlbmhlaW0IQmx1ZmZ0b24KQmx5dGhld29vZA9Cb2lsaW5nIFNwcmluZ3MHQm9ubmVhdQZCb3dtYW4LQnJhbmNodmlsbGURQnJpYXJjbGlmZmUgQWNyZXMJQnJvb2tkYWxlB0JydW5zb24JQnVja3Nwb3J0B0J1ZmZhbG8KQnVybmV0dG93bgZCdXJ0b24HQ2FsaG91bg1DYWxob3VuIEZhbGxzBkNhbWRlbgdDYW1lcm9uCkNhbXBvYmVsbG8IQ2FybGlzbGUFQ2F5Y2ULQ2VudGVydmlsbGUHQ2VudHJhbA9DZW50cmFsIFBhY29sZXQGQ2hhcGluCUNoYXBwZWxscwZDaGFyYXcKQ2hhcmxlc3RvbglDaGFybG90dGUGQ2hlcmF3CENoZXJva2VlCkNoZXJyeXZhbGUHQ2hlc25lZQdDaGVzdGVyDENoZXN0ZXJmaWVsZAlDaXR5IFZpZXcJQ2xhcmVuZG9uCkNsZWFyd2F0ZXIHQ2xlbXNvbglDbGV2ZWxhbmQHQ2xpbnRvbgRDbGlvBkNsb3ZlcghDb2xsZXRvbghDb2x1bWJpYQZDb253YXkEQ29wZQdDb3Jkb3ZhDENvdHRhZ2V2aWxsZQZDb3dhcmQHQ293cGVucwpDcm9zcyBIaWxsB0RhbHplbGwNRGFuaWVsIElzbGFuZApEYXJsaW5ndG9uB0Rlbm1hcmsKRGVudHN2aWxsZQZEaWxsb24HRG9uYWxkcwpEb3JjaGVzdGVyCER1ZSBXZXN0BkR1bmNhbgZEdW5lYW4GRWFzbGV5DEVhc3QgR2FmZm5leQtFYXN0IFN1bXRlcghFYXN0b3ZlcglFZGdlZmllbGQGRWRpc3RvDEVkaXN0byBCZWFjaAhFaHJoYXJkdAVFbGdpbgRFbGtvB0VsbG9yZWUGRXN0aWxsC0V1cmVrYSBNaWxsCkV1dGF3dmlsbGUJRXZlcmdyZWVuB0ZhaXJmYXgIRmxvcmVuY2ULRm9sbHkgQmVhY2gMRm9yZXN0IEFjcmVzC0ZvcmVzdGJyb29rCUZvcnQgTGF3bglGb3J0IE1pbGwMRm91bnRhaW4gSW5uBkZ1cm1hbgdHYWZmbmV5D0dhbGl2YW50cyBGZXJyeQVHYW50dAtHYXJkZW4gQ2l0eQZHYXN0b24IR2FzdG9uaWEKR2F5bGUgTWlsbApHZW9yZ2V0b3duB0dpZmZvcmQHR2lsYmVydAtHbG92ZXJ2aWxsZQxHb2xkZW4gR3JvdmULR29vc2UgQ3JlZWsFR292YW4KR3JheSBDb3VydAtHcmVhdCBGYWxscwxHcmVlbGV5dmlsbGUKR3JlZW52aWxsZQlHcmVlbndvb2QFR3JlZXIHSGFtcHRvbgdIYW5haGFuC0hhcmRlZXZpbGxlC0hhcmxleXZpbGxlCkhhcnRzdmlsbGUNSGVhdGggU3ByaW5ncwlIZW1pbmd3YXkNSGlja29yeSBHcm92ZQVIaWxkYRJIaWx0b24gSGVhZCBJc2xhbmQGSG9kZ2VzCkhvbGx5IEhpbGwJSG9sbHl3b29kDUhvbWVsYW5kIFBhcmsKSG9uZWEgUGF0aAdIb3BraW5zBUhvcnJ5CkluZGlhIEhvb2sFSW5tYW4LSW5tYW4gTWlsbHMESXJtbwVJcndpbg1Jc2xlIG9mIFBhbG1zA0l2YQdKYWNrc29uDEphbWVzIElzbGFuZAlKYW1lc3Rvd24GSmFzcGVyCUplZmZlcnNvbgZKb2FubmEMSm9obnMgSXNsYW5kDEpvaG5zb252aWxsZQhKb2huc3RvbgpKb25lc3ZpbGxlBkp1ZHNvbgdLZXJzaGF3DUtpYXdhaCBJc2xhbmQJS2luZ3N0cmVlBUtsaW5lBkxhZHNvbglMYWtlIENpdHkJTGFrZSBWaWV3Ckxha2UgV3lsaWUFTGFtYXIJTGFuY2FzdGVyDkxhbmNhc3RlciBNaWxsB0xhbmRydW0ETGFuZQVMYXR0YQpMYXVyZWwgQmF5B0xhdXJlbnMDTGVlCUxleGluZ3RvbgdMaWJlcnR5DExpbmNvbG52aWxsZQpMaXRjaGZpZWxkD0xpdHRsZSBNb3VudGFpbgxMaXR0bGUgUml2ZXIKTGl2aW5nc3RvbgZMb2JlY28ITG9ja2hhcnQFTG9kZ2UFTG9uZ3MFTG9yaXMMTG93bmRlc3ZpbGxlBkxvd3J5cwZMdWdvZmYFTHVyYXkFTHltYW4JTHluY2hidXJnB01hZ2dldHQHTWFubmluZwZNYXJpb24ITWFybGJvcm8ITWFydGluZXoHTWF1bGRpbgpNYXllc3ZpbGxlBE1heW8FTWNCZWUOTWNDbGVsbGFudmlsbGUGTWNDb2xsCk1jQ29ubmVsbHMJTWNDb3JtaWNrCE1pbGx3b29kDE1vbmFyY2ggTWlsbA1Nb25ja3MgQ29ybmVyB01vbmV0dGEMTW91bnQgQ2FybWVsCk10IENyb2doYW4MTXQuIFBsZWFzYW50CE11bGJlcnJ5B011bGxpbnMOTXVycmVsbHMgSW5sZXQMTXlydGxlIEJlYWNoBk5lZXNlcwxOZXcgRWxsZW50b24ITmV3YmVycnkHTmljaG9scwpOaW5ldHkgU2l4Bk5vcnJpcwVOb3J0aA1Ob3J0aCBBdWd1c3RhEE5vcnRoIENoYXJsZXN0b24QTm9ydGggSGFydHN2aWxsZRJOb3J0aCBNeXJ0bGUgQmVhY2gJTm9ydGhsYWtlBk5vcndheQlPYWsgR3JvdmUHT2FrbGFuZAZPY29uZWUGT2xhbnRhBE9sYXIKT3JhbmdlYnVyZwZPc3dlZ28HUGFjb2xldA1QYWNvbGV0IE1pbGxzCFBhZ2VsYW5kCFBhbXBsaWNvBlBhcmtlcgpQYXJrc3ZpbGxlDVBhcnJpcyBJc2xhbmQHUGF0cmljaw5QYXdsZXlzIElzbGFuZAhQYXh2aWxsZQRQZWFrBlBlbGlvbgZQZWx6ZXIJUGVuZGxldG9uBVBlcnJ5B1BpY2tlbnMIUGllZG1vbnQKUGluZSBSaWRnZQhQaW5ld29vZAtQbHVtIEJyYW5jaAdQb21hcmlhClBvcnQgUm95YWwKUHJvc3Blcml0eQZRdWluYnkHUmF2ZW5lbAhSZWQgQmFuawhSZWQgSGlsbAtSZWV2ZXN2aWxsZQdSZW1iZXJ0CFJpY2hidXJnCFJpY2hsYW5kDFJpZGdlIFNwcmluZwlSaWRnZWxhbmQKUmlkZ2V2aWxsZQhSaWRnZXdheQlSb2NrIEhpbGwJUm9ja3ZpbGxlB1JvZWJ1Y2sKUm93ZXN2aWxsZQRSdWJ5BVNhbGVtBlNhbGxleQZTYWx1ZGEKU2FucyBTb3VjaQZTYW50ZWUFU2F4b24GU2NvdGlhCFNjcmFudG9uD1NlYWJyb29rIElzbGFuZAdTZWxsZXJzBlNlbmVjYQpTZXZlbiBPYWtzBlNoYXJvbgtTaGVsbCBQb2ludAxTaWx2ZXJzdHJlZXQMU2ltcHNvbnZpbGxlCFNpeCBNaWxlD1NsYXRlci1NYXJpZXR0YQZTbW9ha3MGU215cm5hCFNuZWxsaW5nCFNvY2FzdGVlDFNvY2lldHkgSGlsbA5Tb3V0aCBDb25nYXJlZQxTb3V0aCBTdW10ZXIOU291dGhlcm4gU2hvcHMLU3BhcnRhbmJ1cmcKU3ByaW5nZGFsZQtTcHJpbmdmaWVsZApTdCBBbmRyZXdzCVN0IEdlb3JnZRBTdCBIZWxlbmEgSXNsYW5kC1N0IE1hdHRoZXdzClN0IFN0ZXBoZW4KU3QuIEdlb3JnZQVTdGFycgdTdHVja2V5EVN1bGxpdmFuJ3MgSXNsYW5kCVN1bW1lcnRvbgtTdW1tZXJ2aWxsZQZTdW1taXQGU3VtdGVyDlN1cmZzaWRlIEJlYWNoB1N3YW5zZWEIU3ljYW1vcmUFVGF0dW0HVGF5bG9ycwhUZWdhIENheQxUaW1tb25zdmlsbGUJVG93bnZpbGxlDlRyYXZlbGVycyBSZXN0B1RyZW50b24EVHJveQpUdXJiZXZpbGxlBVVsbWVyBVVuaW9uBVV0aWNhEFZhbGVuY2lhIEhlaWdodHMMVmFsbGV5IEZhbGxzBVZhbmNlCVZhcm52aWxsZQxXYWRlIEhhbXB0b24HV2FnZW5lcghXYWxoYWxsYQpXYWx0ZXJib3JvBFdhcmQLV2FyZSBTaG9hbHMIV2F0ZXJsb28LV2F0dHMgTWlsbHMHV2VsY29tZQhXZWxsZm9yZA1XZXN0IENvbHVtYmlhC1dlc3QgUGVsemVyCldlc3QgVW5pb24LV2VzdG1pbnN0ZXIIV2hpdG1pcmURV2lsa2luc29uIEhlaWdodHMIV2lsbGlhbXMMV2lsbGlhbXNidXJnC1dpbGxpYW1zdG9uCVdpbGxpc3RvbgdXaW5kc29yCVdpbm5zYm9ybw9XaW5uc2Jvcm8gTWlsbHMIV29vZGZvcmQIV29vZHJ1ZmYIWWVtYXNzZWUEWW9yaxX2AgNBbGwDQUJCA0FCTgNBSUsDQUxMA0FORANBTlIDQVJDA0FSSQNBVEwDQVdFA0FZTgNCQU0DQkFSA0JBVANCRUEDQkVMA0JFVgNCRU4DQkVFA0JFUgNCRVQDQklTA0JMQQNCTEMDQkxFA0JMVQNCTFkDQk9JA0JPTgNCT1cDQlJBA0JSSQNCUksDQlJVA0JVQwNCVUYDQlVSA0JVVANDQUgDQ0FMA0NBTQNDQUUDQ0FQA0NBUgNDQVkDQ0VOA0NFVANDRVIDQ0hQA0NITANDSFIDQ0hBA0NITwJDVwNDSEsDQ0hZA0NIUwNDSFQDQ0hFA0NJVANDTEEDQ0xFA0NMTQNDTFYDQ0xOA0NMSQNDTE8DQ09FA0NPTANDT04DQ09QA0NPUgNDT1QDQ09XA0NPUwNDUk8DREFMA0RBTgNEQVIDREVOA0RFVANESUwDRE9OA0RPUgNEVUUDRFVDA0RVQQNFQVMDRUFUA0VBVQNFQU8DRURHA0VESQNFRFMDRUhSA0VMRwNFTEsDRUxMA0VTVANFVVIDRVVUA0VWRQNGQUkDRkxPA0ZPTANGT1IDRk9FA0ZPVANGT00DRk9VA0ZVUgNHQUYDR0FMA0dBTgNHQVIDR0FTA0dBVANHQVkDR0VPA0dJRgNHSUwDR0xPA0dPTANHT08DR09WA0dSQQNHUlQDR1JMA0dSRQNHUk4DR1JSA0hBTQNIQU4DSEFSA0hBTANIQVQDSEVBA0hFTQNISUMDSElEA0hJTANIT0QDSE9ZA0hPTANIT00DSE9OA0hPUANIT1IDSU5EA0lOTQNJTkEDSVJNA0lSVwNJU0wDSVZBA0pBQwNKQU0DSkFFA0pBUwNKRUYDSk9BA0pPSANKT04DSk9TA0pPRQNKVUQDS0VSA0tJQQNLSU4DS0xJA0xBRANMQUsDTEFFA0xBVwNMQU0DTEFOA0xBQwNMQVIDQU5FA0xBVANMQVUDTEFTA0xFRQNMRVgDTElCA0xJTgNMSVQDTElMA0xJRQNMSVYDTE9CA0xPQwNMT0QDTE9OA0xPUgNMT1cDTE9ZA0xVRwNMVVIDTFlNA0xZTgNNQUcDTUFOA01BUgNNQUwDTUFUA01BVQNNQVkDTUFPA01DQgNNQ0wDTUNPA01DTgNNQ0MDTUlMA01PQQNNT04DTU9FA01PVQNNVEMDTVQuA01VTANNVUkDTVVSA01ZUgNORUUDTkVMA05FVwNOSUMDTklOA05PUgNOT1QDTk9IA05PQwNOT0EDTk9NA05PTANOT1cDT0FLA09BTANPQ08DT0xBA09MUgNPUkEDT1NXA1BBQwNQQU8DUEFHA1BBTQNQQVIDUEFLA1BBSQNQQVQDUEFXA1BBWANQRUEDUEVJA1BFTANQRU4DUEVSA1BJQwNQSUUDUElOA1BJVwNQTFUDUE9NA1BPUgNQUk8DUVVJA1JBVgNSRUQDUkVIA1JFRQNSRU0DUklDA1JJSANSSUcDUklEA1JJRQNSSVcDUk9DA1JPSwNST0UDUk9XA1JVQgNTQUwDU0FFA1NBVQNTQU4DU0FUA1NBWANTQ08DU0NSA1NFQQNTRUwDU0VOA1NFVgNTSEEDU0hFA1NJTANTSU0DU0lYA1NMQQNTTU8DU01ZA1NORQNTT0MDU09JA1NPVANTT1MDU09IA1NQQQNTUFIDU1BJA1NUQQNTVEcDU1RIA1NUTQNTVFMDU1QuA1NUUgNTVFUDU1VMA1NVVANTVUUDU1VJA1NVTQNTVVIDU1dBA1NZQwNUQVQDVEFZA1RFRwNUSU0DVE9XA1RSQQNUUkUDVFJPA1RVUgNVTE0DVU5JA1VUSQNWQUwDVkFFA1ZBTgNWQVIDV0FEA1dBRwNXQUgDV0FUA1dBUgNXQUUDV0FPA1dBUwNXRUwDV0VGA1dFUwNXRVADV0VVA1dFVANXSEkDV0lLA1dJSQNXSUEDV0lMA1dJUwNXSUQDV0lOA1dJQgNXT08DV09EA1lFTQNZT1IUKwP2AmdnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAINDxAPFgYfAQUEY29kZR8CBQhjb2RlZGVzYx8DZ2QQFR8DQWxsDGFncmlidXNpbmVzcwlBdHRvcm5leXMWYXV0b21vYmlsZSBkZWFsZXJzaGlwcxtiYW5rcy8gY3JlZGl0IHVuaW9ucyAvIFMmTHMZY29sbGVnZXMgYW5kIHVuaXZlcnNpdGllcwxjb25zdHJ1Y3Rpb24LY29udHJhY3RvcnMMZGlzdHJpYnV0aW9uDWVudGVydGFpbm1lbnQEZm9vZAtmcmFuY2hpc2luZxpnb3Zlcm5tZW50IChmZWRlcmFsL3N0YXRlKRJnb3Zlcm5tZW50IChsb2NhbCkLaGVhbHRoIGNhcmUTaG9zcGl0YWxpdHkvdG91cmlzbQlpbnN1cmFuY2UNbWFudWZhY3R1cmluZwVtZWRpYQhtaWxpdGFyeQ5ub3QgZm9yIHByb2ZpdAtvaWwgYW5kIGdhcwdQZW5zaW9uDXByb2Zlc3Npb25hbHMLcmVhbCBlc3RhdGUKUmVzdGF1cmFudAZyZXRhaWwIVGF4YXRpb24OdHJhbnNwb3J0YXRpb24JdXRpbGl0aWVzCVdob2xlc2FsZRUfA0FsbAJBMQJBVAJCMQJDMQJEMQJFMQJGMQJHMQJIMQJJMQJKMQJLMQJMMQJNMQJOMQJPMQJQMQJRMQJSMQJTMQJUMQJQRQJVMQJWMQJSRQJXMQJUQQJYMQJZMQJXSBQrAx9nZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAIPDxAPFgYfAQUEY29kZR8CBQhjb2RlZGVzYx8DZ2QQFY0BA0FsbBVBY2NvdW50aW5nICYgQXVkaXRpbmcQQWNjb3VudGluZyAtIEFsbBZBY2NvdW50aW5nIC0gQ29ycG9yYXRlFUFjY291bnRpbmcgLSBQZXJzb25hbBdBY2NvdW50aW5nIC0gUmVndWxhdG9yeRRBY2NvdW50aW5nIC0gUmV2ZW51ZQxBY3F1aXNpdGlvbnMWQWN0aXZpdHkgQmFzZWQgQ29zdGluZwlBZnJpa2FhbnMMQWdyaWJ1c2luZXNzC0FncmljdWx0dXJlD0FydGlzdHMvV3JpdGVycxJBc3N1cmFuY2UgU2VydmljZXMIQXVkaXRpbmcKQXVzdHJhbGlhbhdBdXRvbW90aXZlL0F1dG8gRGVhbGVycwxCZW5jaG1hcmtpbmcLQm9va2tlZXBpbmcWQnVkZ2V0aW5nLCBGb3JlY2FzdGluZxFCdXNpbmVzcyBQbGFubmluZxFCdXNpbmVzcyBTdGFydCB1cBxCdXNpbmVzcyBWYWx1YXRpb24vQXBwcmFpc2FsE0J1c2luZXNzIFZhbHVhdGlvbnMWQ2FyZWVyICYgRmFtaWx5IElzc3Vlcw9DYXNoIE1hbmFnZW1lbnQKQ2hpbGQgQ2FyZQdDaGluZXNlC0NvbGxlY3Rpb25zEkNvbW1lcmNpYWwgTGVuZGluZxRDb21tdW5pY2F0aW9ucy9NZWRpYRRDb21waWxhdGlvbiAmIFJldmlldwxDb25zdHJ1Y3Rpb24TQ29uc3VsdGluZyBTZXJ2aWNlcw9Db250aW51aW5nIENhcmUJQ29udG9uZXNlDkNvbnRyb2xsZXJzaGlwD0Nvc3QgQWNjb3VudGluZw9Db3N0IE1hbmFnZW1lbnQSQ3JlZGl0L0NvbGxlY3Rpb25zBkRhbmlzaBNEYXkgQ2FyZS9DaGlsZCBDYXJlDkRlYnQgRmluYW5jaW5nDkRlcGVuZGVudCBDYXJlC0Rlcml2YXRpdmVzB0Rpdm9yY2UFRHV0Y2gKRWxkZXIgQ2FyZQlFbGRlcmNhcmUZRW1wbG95ZWUgQmVuZWZpdHMvUGVuc2lvbgtFbmdpbmVlcmluZw1FbnRlcnRhaW5tZW50DEVSSVNBIEF1ZGl0cw1Fc3RhdGUgJiBHaWZ0D0VzdGF0ZSBQbGFubmluZwZFdGhpY3MhRXh0cmFjdGl2ZSBJbmQuIE1pbmluZywgT2lsICYgR2FzE0Zhcm1pbmcgYW5kIFJhY2hpbmcFRmFyc2kERkFTQhNGZWRlcmFsIENvbnRyYWN0aW5nEkZpbmFuY2lhbCBQbGFubmluZxNGaW5hbmNpYWwgUmVwb3J0aW5nB0Zpbm5pc2gWRmlybSBDcmlzaXMgQXNzaXN0YW5jZQhGb3JlbnNpYwVGcmF1ZAZGcmVuY2gGR2FtaW5nEkdBU0IgLSBZZWxsb3cgQm9vawZHZXJtYW4KR292ZXJubWVudBhHb3Zlcm5tZW50IC0gQ29udHJhY3RpbmcUR292ZXJubWVudCAtIEZlZGVyYWwZR292ZXJubWVudCAtIFNjaG9vbCBEaXN0LhVHb3Zlcm5tZW50IENvdW5zZWxpbmcFR3JlZWsLSGVhbHRoIENhcmUJSG9zcGl0YWxzCkhVRCBBdWRpdHMKSW5kb25lc2lhbhZJbmZvcm1hdGlvbiBUZWNobm9sb2d5CUluc3VyYW5jZQ5JbnRlcm5hbCBBdWRpdBBJbnRlcm5hbCBDb250cm9sFkludGVybmF0aW9uYWwgQnVzaW5lc3MWSW50ZXJuYXRpb25hbCBTZXJ2aWNlcwtJbnZlc3RtZW50cwdJdGFsaWFuCEphcGFuZXNlBktvcmVhbgdMYW90aWFuBkxhdGlhbgdMYXR2aWFuB0xlYXNpbmcTTGl0aWdhdGlvbiBTZXJ2aWNlcxdNYW5hZ2VtZW50IC0gQWNjb3VudGluZxRNYW5hZ2VtZW50IC0gR2VuZXJhbBVNYW5hZ2VtZW50IENvbnN1bHRpbmcITWFuZGFyaW4NTWFudWZhY3R1cmluZxVNZXJnZXJzLCBBY3F1aXNpdGlvbnMKTm9uLVByb2ZpdAlOb3J3ZWdpYW4HUGF5cm9sbAtQZWVyIFJldmlldxBQZW5zaW9uIFBsYW5uaW5nFlBlcmZvcm1hbmNlIE1hbmFnZW1lbnQKUG9seW5lc2lhbgpQb3J0dWd1ZXNlFFJlZ3VsYXRvcnkgUmVwb3J0aW5nFlJlbGlnaW91cyBJbnN0aXR1dGlvbnMGUmV0YWlsD1Jpc2sgTWFuYWdlbWVudAdSdXNzaWFuDVNFQyBSZXBvcnRpbmcHU2lnbmluZwdTcGFuaXNoFFN1Y2Nlc3Npb25zIFBsYW5uaW5nB1N3ZWRpc2gIVGFoaXRpYW4JVGF4IC0gQWxsD1RheCAtIENvcnBvcmF0ZRVUYXggLSBFc3RhdGUgYW5kIEdpZnQaVGF4IC0gRXhlbXB0IE9yZ2FuaXphdGlvbnMPVGF4IC0gRmlkdWNpYXJ5EFRheCAtIEluZGl2aWR1YWwTVGF4IC0gSW50ZXJuYXRpb25hbBFUYXggLSBQYXJ0bmVyc2hpcAtUYXggLSBTYWxlcwtUYXggLSBTYWxlcxNUYXggLSBTdGF0ZSAmIExvY2FsF1RheCBJbnZlc3RtZW50IFBsYW5uaW5nCFRheGF0aW9uClRlY2hub2xvZ3kDVGhhBFRoYWkHVG9nb2xvZwZUb25nb24RVHJlYXN1cnkgU2VydmljZXMGVHJ1c3RzFY0BA0FsbAJBJgJBTgJBLQJBQwJBTwJBVQJBUQJBQgJBRgJBUgJBRwJBVAJBUwJBRAJBQQJBTQJCRQJCTwJCRgJCUAJCUwJCVQJCVgJDJgJDTQJDQwJDSAJDTwJDTAJDVQJDUAJDTgJDUwJDVAJPTgJDUgJDQQJDRwJDRQJEQQJEQwJERgJERQJEUgJESQJEVQJFQwJFTAJFQgJFTgJFVAJFQQJFJgJFUAJFSAJFSQJGQQJGSQJGUwJGQwJGUAJGUgJGTgJGTQJGTwJGVQJGRQJHQQJHLQJHTQJHTwJHVgJHRQJHUgJHQwJHSwJIQwJITwJIQQJJRAJJVAJJTgJJQQJJQwJJQgJJUwJJVgJJTAJKQQJLTwJMQQJMVAJMVgJMRQJMUwJNLQJNTgJNQwJNRAJNQQJNRQJOTwJOUgJQQQJQUgJQUAJQTQJQTwJQVAJSUgJSSQJSRQJSTQJSVQJTUgJTSQJTQQJTUAJTVwJUSAJBWAJULQJUWAJUTQJURgJUSQJUTgJUUAJUTAJUUwJUVAJUVgJUQQJURQJUMQJISQJUTwJURwJUVQJUUhQrA40BZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnFgFmZAITD2QWAgICD2QWAmYPFCsAAg8FxQFDO0M6QnJ5Y2VXZWJHcm91cC5XZWIuVUkuV2ViQ29udHJvbHMuRHluYW1pY0NvbnRyb2xzUGxhY2Vob2xkZXIsIEJyeWNlV2ViR3JvdXAuV2ViLlVJLldlYkNvbnRyb2xzLkR5bmFtaWNDb250cm9sc1BsYWNlaG9sZGVyLCBWZXJzaW9uPTIuMS4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49MzBkMjNmMTA4NWRmM2U3MjtvRENQSBYAZGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgYFHGN0bDAwJFBhbmVsQ29udGVudCRidG5TZWFyY2gFG2N0bDAwJFBhbmVsQ29udGVudCRidG5DbGVhcgUcY3RsMDAkUGFuZWxDb250ZW50JGJveENvdW50eQUeY3RsMDAkUGFuZWxDb250ZW50JGJveEluZHVzdHJ5BR5jdGwwMCRQYW5lbENvbnRlbnQkYm94U2VydmljZXMFHWN0bDAwJFBhbmVsQ29udGVudCRidG5TZWFyY2gybMPjhW0F4rouA%2FND4G1H9hXllQhopaABgJFyFd7qowI%3D&__VIEWSTATEGENERATOR=5F6AFACE&__EVENTVALIDATION=%2FwEdAK0EZ4ctBm3GQu0rBSZnf%2BZ9ElspY774O2CAtS%2Fr79sDHK865vXjqVOlvUwCb0%2FZKYFCTpmWR2CmqBVCmfqOR0tR4KkMTGwPrx4lt68H5tF%2BUoDyKjDEoMUCWYJpEYCGlk%2FkqrH3rWF6vNErpQUJ%2FTlrxTNnnkgUG0sRq1%2FJlFGDwcj%2B0WHxOE9P1hzkbj12e%2BYLctp8PRIR2HuftmoLPqQ8HCPaN%2Bd%2BmH1Xzr58oxCn6w9%2FOhvmElUkWiq40W1O3iGWFf%2F9Nx4i4upigJqT%2Bb5IF48VMTZL9ghUVLZ9LpontQVqNnhx%2F2UyJB8izHGUAuFwct1Fa5MufiNdM%2BjKlIMw%2Fk%2Fpmozcqgex3z%2FVAFyExV5%2BIrv7NBWxzKYFjbpwdzcxJtQzHRigh%2Fud8H5iANTeF%2B0L5H2iU10%2FBMWGch8O0WDbxH5vrqnb3oG1N9RawYQy09IELlcuJwzcFWFmhitMbEsCLRBAEj4qHQyj1eHO4m%2B%2FhabGePU1mB%2FMQ6XRpPTfafZepEaBeuXpPTSvh%2Fpse6J0b4J2yyoqymEEM7IwvKZfCKPQVRoV2wgNWge9DRbBea4oD5EvwYNQyZDKNoQXH08InKROlIeA7eexXUJkV%2FLI1cMm3Ka84c24Of5%2FupiLe00TJs6wnzQyB1Edikd78ibPb8f0ulfiDXtv42LWjzgKDMddGQgugt6n9xAbbznr9Pio2rIb6YB%2F7uDEftb3tNe7xAM0KzTZDPDTcey6pnQPFVPL3PcLwXdgXKEN2gwcx16ou4kX%2FewjXA6UF%2Bn588zCKBjR12FaYXfVqf3Q6WKsEcRS4xC5THiDADQ2a%2FbmdXK6ty3Y7OPvyOjqeNVj3jRnlfgibhVSr7YxJlSku59ymb2QleyPaMBcbcNDqkw6kH5%2BJdEHRDW7BxQlkZ0scRBm2lLSElzjl9RnJqJPYFJDQdR%2BbnPVDy0TmtkRXCRXx7A11lnGbvSO7N9HqkvEu3bgJWoswVt1sYPMZnp4NN7Nm5IUUx%2FdIffHSupTpaAUPPqXNvNlwQD4AiUKj7EVcupYS0%2B8zKXAhYUgAhKrllFihpc3uK23VLjJ8LbiTwiAgWb04cU154Tvq5FgLYHME%2FwkMXlMSp9phg7wHUv%2FhB9EKzzBbMRnT9ZMWIHpMIuqKV4dKCCbeto%2FzbiPeEEykWmGWVLmkTpPbD5VJrTVMOJXMd791JFnqWS376N0mXRW3JlDFpBDNMVJDyRLJVO4pF1GN7yy9qGZwZ2ReDHI1%2BiVaS3N5%2FSp2MCogYOvn6vlcx6iSzE0kgqMZObu7KHD92f8C%2B8FtTvhw7yXVrnoboPESApG4ehZr5mKquCR6ZwK3ROTMxIviQPtJ%2B%2BFhKhymwdxE1%2BNLnV9QdqIxz1QB48tAaNokz7fK8b4weaacYyfKPHYGX2qB7hjdtF3FgXJxNPhxglkDbhsX7DyvX3dfWbU2xYAhDJ%2FyMyF27PwvfZz6jrdJFtZbCLJLIOYxHzJ0XwdYzmd3%2B6IsAPRt5PtxzGzkdsKQSEtAvH9ETroV1sWXR6%2F%2BXqKEryK1jNrptdd8MmBCNiyWY6EiuzFjYpKFM4Vv7gYj724TunaPy0%2FVOl%2FC0jdQlFfFRG32y8Gqer8%2F%2FTWCVpLTdzT7wiZbQUUDL54K6gqTqB%2F8glajWIKgi4cF42FMsb712sz4nsIEoHMpuIieAqEswGMD4sen9kzY7UaW6kLXKbFYveXrJgoDeLf5H9g2rJBrDiCYM4W%2F49AQYe2%2FSZ%2FMFUdWMyaZSgx3qfv80Wi40meIGWjalJlol9ENTkRzCntLmScc69jE0IhE3ioibDMSZnLl6T3pQmJkiSEJ2SYvvM8Z0rHT%2FiQPpiZ32dK7T1jE7AxHLsQhw74%2FIizENTv3EhML5Sv82yoHO5oM0V4p%2B3TStUw3hWrY7hHGQ9E9mXAapqfF2A6t6X2p84LdhvBasUInDKCD%2FX4i1pBEXoBS8fbrJL4xGWClC2NpwNY4%2FT%2FX5%2FSfOyAIeE%2FEU%2BfA3jdG6HfWGyZPiU%2BtJDoWIKQNNqQC84hVT%2BMow07NlS2FzTMzo2XNhASqaYHgodhIezt412Fj365uJdxH%2FL4tVo8lS3xglfMPjDkXtN1WE2L%2Fc0DJdZ7K%2F%2FouZF6GtoTU5KkzPac3KfOUEE4Wlp5d9LbSMY14Rv8mn6bgWpo5zmiWe%2Fy9ktAqKVOe9dC3kn%2BrDUZkaBeGD2e%2F5qWhoYvMb7UvY2r2jhGCMDih8gKlnCn3cwyiAwlZscdiJvkU0xlH6I8Co%2BBg%2FO6rfmIt06IgOJsjzbBH5n6BpkYmjA0GJ2Y%2FqwQAlZArp0FIZSLIAoDy%2Fcy5Dhhv%2FiWJuPBw1yCpbLj5GQhn9CockBX9vIWT6lQD9OFT8VdvWFx8F3%2FKm0c6kkAqIFO6M9666olWt0Mc8txT4IzN0xaeiBnER1%2BqgnTzxqCGU4gvuQA48Y9e%2BnPU2uZuM8i6AvZZE5Y2e6wRPXjBHWS%2FWJFo7kUfSA9%2Fwck9vZwP1pFnPhdsMV%2BDO9M24WCQMMkZBwTnFpoCcJAdN84EQJTk6I6%2FWXvfltbZU2WIGlsp1ruazED7e0%2B0id8asVW7JTnovI5XIzOnE%2B79fMJJeiZVYT7QmUZeLin6H4d6NWkREMNM0NV%2BmkbPgT%2BvWLYDEkgLZSw5Od0m5DEo%2Fii5mmw1ecm9vDrjoMGpXurOR%2FssB6mdtSB3uioJLLtcNMYzBCi6gP2rEQJXB6EsX7iRmwx%2FfgKHFghjQN5S%2F0rtyhM%2Bnqs98Y%2FcdnqW2FIjtv67u9mM4qaUXrctssM2n9TXqWq2YowEvtcdDk8BDForxqEA9ZZtmvFDtimrZELmMBqy621%2FqgiKudmnk1RnHrvDLueWZYJTuKcFBfUSp41hX3s4k6pG%2BArBKhBRUIRvZcYKgXRh%2BcqfYVFUAbxEtHi0Cu26R0QPBMAmL260PqeMqtpXhbxiI22evJ3pzDZImBxGxQx%2FVygHXGpD9B625ruCPylC5bJ2axVJBiugICDmLvsd5poD25r%2B9Ny%2BvT7pnsSkXI%2FqHXdLnmXlnXkKAsnyGWMXepIDG%2Ff6sje5f%2Fb%2Fu6SbCmMrIj%2BwpXbBihy4J8Leeatr741zDkTkCxfEeCWOisUYyVQUtvaEz5odu5o2pO0%2B9MW1CMdR4VBLxtkY0z4e3yjDHc55%2BkhZwjqp4d8akh5JQipZIXgw%2B1AxMtd9aoy7RGqvYnucIDM6jRbcPLp2yqYaQDNYYjZpbd%2F%2BkmiuhPiq6bXR73Fl3qGfld1ghWYJdZrMSL4uHhLUwJwPdpYHlR5ukdAEEtvTJE5UTMfuxQ0kglOfYHvzzS8i3a4aEAt7he2sWxI2lAU%2Bbg1Sfcc26A5gOETUAgY5R%2BqD3LdNmc4ypWRZ%2Bx0ixd6jD%2FZklG2orqzzpiHGPZNsgCAPFzj1a%2Fc5DHSLqGTJIEoJyeZQfMmpJgXJw64%2FLRgoDLuVAgtMbYw45gMQSA7i8D5R7VyGkt%2FBS6hAkKaIWxRpB3ybH2pZPM2FJSlFPx9KapDbiGLjw3KG8TNzkNO6S7M9T1qNcs%2Fuh6CiCtD8w34Y96eg%2BQSV0wtAE4C%2FgAd6oHchJzwVia0f7pTwJWGPyLL418gD%2Fr2zZql9ZbkqSDstogbHy4ZHlLDbjOVxSs6JOwu1bl3%2B%2BtRNSDhv97EpVGO7%2B8UUUAS2zYLEeG%2FkcRT%2BRMpii2ADzQ7EtM9o%2BeUWDRn6oq5veWGJahox2TRaF%2FkL4jLxEeHxjudnEdNx0bXUu26HmTlN12YYPbFdhHK8f8D0dkbzXR%2FIi2fc1Bf%2B%2BAlL8fCCndAhPAz7AEhcBXjnlno7579HL7cDKPjpg8EoLIWjpoeSuOlXVKERAmSj067QUBBasaBpZAGeYEot5zQ29WpW8p9XwOdrX9NDALhMwIN5JRQ%2BQQ3GUrkNhCxI4bw0PnYv%2BIIdSIHXhCGj2PR%2BrYJatMsukpiH12mWEZAAJbErycOsTsm4VbiRmu0ApGHkmHbFlJ6MeBaIQW%2FH3VN1jnvSYuBNjvKlkjmTVGMalxMecuPWLRrVlTkXs%2BFwPTvfhUX7QUO2dA9mnvRBIcHRKGk0D4HjRzKVUbg9Nc52T8XEHtQDGgmNYyWx3J4TYXBOBqFn7vlpN4QX2nStckVqecitPwYGmVU4dv0ZcrN9pfKCeTAEcbeiYmFGoK%2FCI%2BWpkP%2BsOYKkzaPR%2BmjSBSMw1NYJ3J44iWoBdXQG4geKDyMtP3hRFRYafxXClYUcZSCoKsHNB0RwFgHhN6R1rfo89b8KY3qOm0MNjVQOtwrAnV7TEfVL0k4g695ffX41GyWUAbZObv49ylkP3de4fYuI6T7raVzgL0g7O21nfYBdAvcXz%2FQC4MYcQ1rOyv%2BxnwZFqXFif9UCSP2pXeGaWlfi05QNcMoGF8zaBfde8vSe870HxoAFqiCDAXT%2Bc1a%2BlZc8P3mKDPgWJUyZyxkhrCluYmVKHtZl%2FLSUO%2FYogp49MBTBao7A4NDX%2BY%2F03xJY0BrHCrEIG8hNQV2OMyvz0BPwpWLkBlWQA6%2BI8alFXEIhwSSU95HNiK2rFOkqsC%2BTJbcwbL0kC6Jezn9y%2BD65F85IPxGHHD38Ps5u%2FKYwIfvY9GSUqvXeWvtyH1WIg9GuqVEV2Q3c2E1%2BP8ZChAFtxCw2JNkVG5U1%2Br4t9yN3ldb93rE6h57r2S0cB2depYXk6ahXtIPS4%2F8oBgsw9QUwRh%2FcDrN2n0l7H2SfnwFv12%2Fw747HHUIGDujmyckYC1TaNyR22%2FdbUJFyTHQt9gaqt5doDXbJJRjohbFkKfyzolW5L8LFUq4xFFuXYjgEC4xyehe%2BR941nUElUwWl2vQM0C4I4bcbX%2FEms6RXueeAUELAXgjYbQRmJKtl%2FB2f6FTSwqhWx0cPZJVj5DavzUW7jFLneqRc7oVbxQ0jtuElSXhmoDLWYcgOWlgeeCVSaXQZXdLhSjwUtcSf563Q%2Fz4YkOfIqT%2BfqvYPqlR2V0zdSHLpX0EZAKJ0osv7tmfcSJkDiztVvkyxt8SNvYYzMqurH8IbrBsgNBF8CIvvqwvOjvOo%2FERxJLJwFB2ZButbPxQpERQKkjJMZEmITWUWWASF4FNpXNCaidixjL8FFIU3uvJc9CN5BUA7PTeaaI4Ht25Dfwss%2FhhAmiLMU%2FLwPJhQ5QqBN%2BQzQYk6QMTC6a%2Fq%2Bmm%2BvI1d4n%2Ftql6%2FcAl61VRTq2n6ZVAGABMXtZtRYtWtIaVbqWFlxjbpN0cjLQfGK2Itp391i8a5mdIWldcWAQd08zZ7tv9eMe6UwRBQ6MNjyHK5EJKdmZ7HmQyQz2kF7opC3xFTDwc8FYJrF3keqS%2B5bipyxGHzY5sh95dU78R1AzKiifcn6cT0a2hYemnCtFBbCG48Y305p1sIlzvgaBYzWL%2Bj7XivG62WN5jofUmvLbSXuUCTtrvWS9rZIBswTGC156LcPHqyFNPEk%2Fyoh3GFoNn79vn%2Bukc%2FZyCsRb%2B2axY%2B0Rf2DHT7yb%2Bm6EEQNdk1j31Loh1sUwFBKTILahfM5p17hmBZJ9iq5PD2rByFSz7GrrAWz80ZBMxJ9SkPXFe4V%2FO5D64NA%2BLmwnVbM6Is8bE2ozWW2B335RxjzJ4eiZH2qfuWNCom3Mi8Yrpg%2FBTfw%2FK6m2Ph3XPpSnUE1vL89F3tmMppgAPslFpL71hF8Fcovhl6%2F1gj%2Fhcdkqs7HVHVj5XWEn9b5NoRS5kSJ08oJCo60pp8JWHJNiTMC7eOcyxzYPerupcx6h9IwBble3OC%2FhSQyyN0td%2FHaPuZ1GoD2pC7NUVYTC4hrbD1KtvyKdGM3Yyn5%2BVfP6S3ur%2BxPRM%2FJPgmIKKgMmB5UrgEPuypugi6ZH9y45aP5oVOPODsQl0D5%2Bj3eVMt96%2Fg1liacVprxqXXxCesii%2F5lN%2FcM9rZHXH%2FShzt1NiF0TXHJVkXODPnmntGSEynoaGX0fHBIh8aJGwbedzf2MQnzscF6YJ%2BWdZHI7MRgfSY5ZwFSx8vksKIF%2FvJw3heZ0C1ynfFNgHEUeaXkXuNhTVngkJT3fMNXUjNtwulLWLtSy38GZWoItUskHNiZ4eZzudTZCR%2F6sgPDDQP4xKtD4e7JoNEhvNECHJWHAvkBCzJdb4v0BdzYlX939L41N1vdweoLLK5q8LjA0d3rYllGBm%2Fxuk%2FaMCUcucQWYICeBFv8wvA5bRzuWgHobBboV4DoY6duKedYNcaijeL1cKo4OGOZevwfzpegy%2FOwZqHGDKhN%2BtMv%2BG61o9iPKO9KDFMzfI22yoiiHRuNshecxWVCFkpoGkL9MMmnPNN6DUemOyqWm07cx5Va3y9KpVffz%2BXS%2BIHom%2Bj7ZZAsKqmb0XUHlrdoYbkXbZEV2v2jq8Cjs4AG8YNFwsUR9Xb21MYO3n3ylIjFXAVGyxR24RP2R7sE1Iyg8kU4ZEOHWGQs19Rg0M3t%2Fw1kwsdtj2IbyGdkoHK3uPCEXNItwOqdaFfh9BcC7Zj9iEJlwNdtpDCKKAs6iU4rxMVQB%2FtpuCryKbyqoX0NM4tqP2eLyX6HwMMWGkvEWA6D9mhYty7%2BQD6Q%2BM%2BOmW4vpMcKmrjRBleGhhZGLaEMTi4aPqICB69xTIqQHMvFJsH%2BL1zHsPnCYLGQBmNI1yx2yUAN0Wpl4dI8uql95ZvhTVff0W%2Fths1xrlmaDCbY5D6CW8LzyIKDKMJPeaYz3SowGMTi82dXBL4ZgIO0aIwWyW6faW6rV4DjCiRiIX5gyNmz%2BNOvzE1VfX6RP3ccgYRlfcV0ClGg6eAVPHlcI2ucFILDm2KPwNIzSncf9DJPQvviZzHOfb8u1RLFQRrhiJaqjq0OOazjRLrmgJTlQ2msKEOfXeAgTQ%2BWMa5NWmQVQbjQDj0eLneMS4MhPoe0Vr3MwnbRF131ryM2FAvyTMGTWKH9SR0zF%2FtIMx7TEHkRyqhA4ua00TnSpKEi7yVOyoJ0nw7t%2FzbFig2frb5TKBjnjDGVq0IQ%2BWiM6ZifrTCLUYiCjOyIKIhYRGhE0hKCTvvY9lkFuUgM2%2FOXHISCd9ZpUkQDTLKETBqKkujG18yzvU7g9MEjVpqINSTES8YDr8iHTCq%2BfXHWd4W5SSNPRDIF0AOatl5B5RkHLM8L7rfdkqbfGyOizHlws60hWlcnd2%2B0zhDxogsSinzJzfxLAoP2m6kXvLWN8ZKXJmHkeaQtKG73xBNim0IVItnFOW4CEnJNrDI3jJDG1zWHyt0Pg0AVsBbizOqyMkfX4wxeuED7G44Al5K5FUnct%2BeUqO1jDTGV%2FkMygLYBhrshqa5QFw6ozymzsN9M0dQvZB8h4KkPxdazRblIZv1IVglHb%2BY%2Bp5omH%2BLC%2FY34HehZEZSTFxMaLLNjwRJRoG%2Buj2459%2BqogOQJ7xg%2Fgs03lvIX7VArbzbmvmzxkdF9fBS3qx%2B%2FeLqdZeIGG3VOVfoaFDmmFBLujS9O0FhZViF10vTUO%2BazQwGHag55IIApRlZ3JAM%2Bq7y2ivNOGq4Em5OI51NRdfW1LuwhgUYepnbnd1rTbOjHYTM5PNdzJ8Q8uEHP0eIVtXElI%2BNERUsKroa85KPxWJ5z0wtf1SVV1Zuw7HnZkKSbfLrzrFT4JF52xrMPxiawQjxLfmIj0sVunjs9T5egBtLnyKVghl%2Fh6zKFAL19tQUDzVFULLyRRqjFBQxP9MSb6qKNpw0UB%2FC7UbJt0fjqxWUZ6BMMmKYhjbBrF7mrpOG9%2B5bcA4MuhA9eahWWy%2F%2BhTcPqVm8GTFPidAZX%2BIxwms1d4p1E6ty4%2FkZRBv5xT8j5GXUAXF3t%2FspN%2FAKSF1pcePEOuKUgUle7nYg0TaxYhn2PldrXi0Kg7ke28NX%2FzqN%2F9OIon%2BcZBXZOJ%2BJ7jmGwCEyc9y5mYs5fYxJCIEqyugJvQjUqxUcjV%2FU66zOyrlNRTivyBskI8ZzMhNZURoCOgZw5IcGuiFZYPsJ4kax7oTe31Umqk78787l%2FZ9h3mGgAhlrH7iWG4%2F2UvlUhARByhie8sFBLSg1Yg%2BU3UHGhR5pPjgoIGCrInL34x7Xg6YYyN8y3KsSoyGeS6FW%2B6Vguj69D140uuOxHpOXJ1NyrdFpgbWaPFN2YtWFXUH%2BPVQf%2B%2FOE0Cb7467DJGUqdkppCcoX3QD8wspAch2mgY1THEAYMkzX1O6q56noELRIZuY%2BNOAV8aFdOojfsRLBYQ3q2N74OsbMOHIj2hC4wrXkie%2BrsfP4332pg3GHEtx4jq%2BIjgKR3A4703RIGiw%2F6oE61xbZKhqIZcTMTFWpl8Lqgrmr2KI1zXZzwqxeB%2FakWk%2FH4OuRBURqOcLjWsIl%2FKb2wyZbl2n1Zr8R8yzUPGBJsXQlUS2ZRgKzQOjNIoiPCJ%2FjGXYZP5V%2BTxXBxzjz4PrPywPWV%2FCeWPefcA5kKJ9jXftlLOQHaOk7%2FmfF2hx1hupXEeXbjPiUOpvLg9Qa79D2xb9nJhVkNoe1t6emA4Npoz5L2JNkuyR%2BapSrIyPQet128TLmPanw51wszJhAalj427X0TXrNekK3OFYSsn6HY%2BG7rQKFE09iH4vWB093kEgXf6XzAgOKMQBwxq48f7nBMENYGQ1ttBD4Ho61JnVghFFnUswOxIVfZ%2Bvp9%2FWZZNNGAZfaRx76G87aUcGMU9EwKVl008NTHFnW%2FlnBLy1oHOBVwLddK%2FV%2B2smtBh2RCkZLYbAkT8AJd%2BoiyWYOLNYAZECHcMtoM65%2B1hWbYMK9j8%2FnBjV60ufoox38lsnmQ4KtIB6FVsKUOvMqT7PA0UKszD4LYx0N%2FMsDdmjoBnqUL4DGQmpTZANf6kR7TwFLWbH%2BESHRBn%2Bb44NJPAmq6QLElqJ9L1hxkzUryrSmiB0oYIEZZHKvg0%2BHbTv0LFpxK0d8MkBsdXFCZ2R11NXSggDzi5duyVhIq0N7gsYjXqq6BTYKXucFum4kSWWG7gYaNlc4%2BLbXqyueHjpb1YyBRXVNGdYZE3Kj7TOaH5yuMC1Ue67kuwJRBoIzZcWgNaJU8SV81%2F2XN2mZqKLgdAiiUzz%2B9%2BMPlA7AWbQ4Vl82SprElIB5L6T5zf69BLHHw9LB1PPlgOAYUgTmOAOMgS1duUIoP%2B2DVz4wpazOuoRIhI%2FqDH5UqqKsR6%2BnClV94%2BnOs%2FQutCuwhyaCKDdv0VA6m8hagzDy2KMPOl4gC7SMioGkOljwNHsRlEE5QAVYBg540vEkSO8b24rolUYnoEsnhvWN3XGj%2F8CHhZiStZ7xmx8pKIypFi00SmBk%2FNZTAJZ2dDc5SYhZ9%2FGO46SEFh53rZcqC2exGxYABG0ASOtugVjogxobqnA5SVbCjsOXd%2FZ4ymx4kYIWky0wSHiFTngvYgTBF3VLKRulm7%2FBo5mh%2FzkLD761cLl%2BZhMfSi0GMSORn9u%2B3D0xF3aKZ7OnQi6NCRZVlkh6AC1n5asQFaB6GHZDTbD%2BD%2BWtLMxJBwyK8FewFalWZE6%2F5csMEyIS1Q3pbxXbt3Tis84C6aI%2F39gNoW5F6YDDBIG90XZ8Rw8kyVXqqOdxbEm4u9lBUCQA8IzKCORH5od19zRwc8YzTZhbnVuotReoxyZj3OW6Ly6%2B3q4RfYj3W85A2RRqc8cvPycwHpVQ5ohEyFl6wrDYo3YFz5%2BecMiW69bEqk1lPIZo4x3ZtsYG1deayB5pfqEA3wdlnri2%2BaiFbaPHE3WPnO7071cDlRsXcV83z19z1k8ywpFtb37Us01QwrJN2cZ9eou5VngqA8lOqSoB%2Fgg6DTXbOpqRKcb3WCxqKhM9saGLqKeUV1i9oL4o841%2FeWczFD0RMHUi0KZNhri3Umu7dsVoda7nBCjJuMUPcjr%2F5NkJWv57K0EduirK5CMDGu%2BDYnPW5XNdBbxhAdTXVk%2FU%2BsvP4SUWJlGGfOWfguvxdn0LnOkMQ%2FZY5YbjUNAG%2F0fyxZFnCcFTbdwFoy4mlbDT%2F4jaR%2FlUkzA6CeYMRJoYBVDHassXJH4B35cBlMCU4CEo6iLHAJQgZEkZ2%2BOvVzU%2Fg4ZxUL%2FxG9MzH1eJzdkzdqWTPWOnP8wN8V6eb%2FWJupwMJxy%2Fv0rJO82I4RPncTkHQMSJ8kxwu4P2SYoPEki2uVE1qriOn3uU81ydXs4JWn2w63q5zKkYFkVVVE%2B8NNwCuX%2BHqA%2F%2BHXpWAsmyXW2ecNKlpKMbo9xXYlmv12Np1o4KrRer4bXqGwfFq8buZvMgkUSHhGCNiEKdwXYwn32lFh0hFV5Wak2f%2FQAgrHudrRm3rn3nPwV4ylh%2F7R8KlDEY6XjDoeLyxqiiX%2Fy7X3B%2B9ICab2kpE%2FjyR3ZdeBulZQCTnPzbfhmFp2JNoIS4QpgyMNUtKaQWpklmVDKsCZiyLL821JNA3OEDYryichDXuqdlUfeVwYQosER4cKGpMBQhyF5HCCtHAihDNPzEmeUsbmgiW1pGFqDk4CeE%2BdFYjlffApao9L3TjV%2B3PEqTpKwI5v3vu4eUCKA5emcddFBF9JzO22Q2UJk1NmJzZ12LVMKZd62Sj9EMHbWoZehHuXBv9J6uuUXYgI8VTRnkhpDITeeCV3jdMMh9MZVFbmGqstokfeGunPVNDxWAJyBq3JZHJvEIFoopPgYEW%2Br5JmTvPFwIeuXYhmUkNbn%2B4GqevATj2szIyvtPToTh1tIHqnAZEeiYcVT0esmQPMVi4U%2FeLbKNjtxDfOvyLpqYDQqSbJjYKbDtg%2BBaWcyLTVZ1AL9AywWzKbHZZweJmwf%2F7wmJ2h8KEuDWfPGHiMNt8OKZIv68mhWe7dEJSwtYXPhbAOvPa%2Ftea6Ujmtx8%2Bc5r64wigoDeWvqfOvlY91sI%2BioNqcXK452Ec4UElW0hjmmsHSeSLTC7OGFQUtsa8L2nJqqWOJSw43hYzmoFC6n9%2BHKFc3hVOOL5DdbaSFVPkOozHzbhSxjgXxDy0yqzFI5Rli25PhQrvE39ulW0e0QXcve1AYKug0h7GXKa8cYi4Aa9L3doqHYssqenCy6M680sYZd%2FkNRyCohnGHqj%2Brc8zoxMoM%2FMDGoFkCoUCqKEgI1cHD367kGBol8ZflcC3I198SzWD8KT3kZYgbm0Q8E8QQIGd%2BYpJc66qbtb1hYu6L4%2BBxqIPMC2o7bhIM%2B7eWux3s8ZOYsnPocik3Qgc8fYDi2U3PVSwLjlcTIr2LarWjttLcaAPcJB3mb2R9KPDsHnC2ERu7WyHar%2B22z5ireotCufcDMhbJ18lJZtOuMKH38jM6vTZZdriwqIbAzLj15FK0P88euDavcnA6Bz2DI62agC8EjgQcpQ9oCQiq7lNHKlCpD0d6%2BmEv1bsNNXgbZBCPG%2Fwx1wegky%2Fgxpkhw3pU8NYO1w9NYPV6Rf9Hg3MEjlXPt4RPJxtWBeU%2BBqmmYTmB88wIVV4o5v2ecFYDqtTej84pLPscDcbQVUa%2BS5UOHlfN7PmEimeUJWZ6%2B%2BHPB%2FbnL%2FwaTtuHdLC8VF9LXKGNebEXEPlfUIffi0Hgi2uGioV2HmmEdnguzMrpp4WwCTO3AZpOH6ViOoxr06cR1QJRznEKoKaAKkmYSfg5R5Up5wkryVa4hgk7%2B2hpcSpg8Q3ijtgThRW6SVkAQB86EKe4Xh4oq4XAz268ORbbzA6BuAl3%2FjfKbSmfmBRSK1O%2FxQujaF4e8ZDTCBH2pv7vIxXNvI5AtoQQQpx5UqnSYNESdloDEg0Fp%2FH%2BuCLPcO9KpToNEjGGB6YLv5FF17Fv4GZfWNS%2FaeOuCeLhFGu1epUo4CtINnxOoNUbuKAVw7qKxISUtOrBXjNroHPNakCMrXZaffg0FJTBIrtYxJQcbm9077Sm6PR86G9k84MxHf22wsamsntVOY%2BlMoz6JTdznUPXumX%2FxNU7sinu9YznhI%2FoSbiU5qSP7a6Mgg2pSawvMXxY17qmqdfD4M8E2RWLQuAToDl7iw5pLkyXHlyK1GdMRI6hBYc8Bk3%2F67dJ6krDW7LhesaG0ZA%3D%3D&ctl00%24PanelContent%24tbCompany=&ctl00%24PanelContent%24rbFirmSize=All&ctl00%24PanelContent%24boxCounty=All&ctl00%24PanelContent%24boxIndustry=All&ctl00%24PanelContent%24boxServices=All&ctl00%24PanelContent%24btnSearch2.x=53&ctl00%24PanelContent%24btnSearch2.y=7";




		log::info("Perform Search");
		$this->LoadPostUrl("http://www.scacpa.org/Public/Referral/findcpa.aspx",$data);
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


$r= new scacpa();
$r->parseCommandLine();
