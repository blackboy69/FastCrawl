<?php
/*
Attachment Mailer class - version 1.20
PHP class handles multiple attachment e-mails using the mime mail standard

Copyright (c) 2006, Olaf Lederer
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the finalwebsites.com nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

_________________________________________________________________________
available at http://www.finalwebsites.com/snippets.php?id=41
Comments & suggestions: http://www.finalwebsites.com/forums/forum/php-classes-support-forum

*************************************************************************

Updates / bugfixes

Ver. 1.01 – The new example demonstrates how to use this class together with some php upload functionality. This example form / script needs an object of the Easy Upload class available on finalwebsites.com.

ver 1.02 - The process_mail() method returns a boolean now to give more information to a possible next step inside an application. There was a small bug inside the upload_and_mail_example.php file. The delete file object must be changed to act with file upload class.

ver 1.03 - I noticed there is sometimes a problem with the mail function and the return path. Some mail servers need a valid notation if the mail can't be deliverd. I added the "-f" option to the process_mail method.

ver. 1.20 - Since this version the class is changed into a full featured html mailer class incl. html mail + (inline) attachments, alternative text format, inline attachments mixed with external attachments and much more. Most methods are changed and the structure how an objects is defined is updated, too. You need to update formerly mail scripts, check the updated documentation.
*/

define("LIBR", "\n"); // use a "\r\n" if you have problems
define("PRIORITY", 3); // 3 = normal, 2 = high, 4 = low
define("TRANS_ENC",	"7bit");
define("ENCODING", "iso-8859-1");


class attach_mailer {
	
	var $from_name;
	var $from_mail;
	var $mail_to;
	var $mail_cc;
	var $mail_bcc;
	var $webmaster_email = "webmaster@yourdomain.com";
	
	var $mail_headers;
	var $mail_subject;
	var $text_body = "";
	var $html_body = "";
	
	var $valid_mail_adresses; // boolean is true if all mail(to) adresses are valid
	
	var $uid; // the unique value for the mail boundry
	var $alternative_uid; // the unique value for the mail boundry
	var $related_uid; // the unique value for the mail boundry
	
	var $html_images = array();
	var $att_files = array();
	
	var $msg = array();
	
	// functions inside this constructor
	// - validation of e-mail adresses
	// - setting mail variables
	// - setting boolean $valid_mail_adresses
	function attach_mailer($name = "", $from, $to, $cc = "", $bcc = "", $subject = "") {
		$this->valid_mail_adresses = true;
		if (!$this->check_mail_address($to)) {
			$this->msg[] = "Error, the \"mailto\" address is empty or not valid.";
			$this->valid_mail_adresses = false;
		} 
		if (!$this->check_mail_address($from)) {
			$this->msg[] = "Error, the \"from\" address is empty or not valid.";
			$this->valid_mail_adresses = false;
		} 
		if ($cc != "") {
			if (!$this->check_mail_address($cc)) {
				$this->msg[] = "Error, the \"Cc\" address is not valid.";
				$this->valid_mail_adresses = false;
			} 
		}
		if ($bcc != "") {
			if (!$this->check_mail_address($bcc)) {
				$this->msg[] = "Error, the \"Bcc\" address is not valid.";
				$this->valid_mail_adresses = false;
			} 
		}
		if ($this->valid_mail_adresses) {
			$this->from_name = $this->strip_line_breaks($name);
			$this->from_mail = $this->strip_line_breaks($from);
			$this->mail_to = $this->strip_line_breaks($to);
			$this->mail_cc = $this->strip_line_breaks($cc);
			$this->mail_bcc = $this->strip_line_breaks($bcc);
			$this->mail_subject = $this->strip_line_breaks($subject);
		} else {
			return;
		}		
	}
	function get_msg_str() {
		$messages = "";
		foreach($this->msg as $val) {
			$messages .= $val."<br />\n";
		}
		return $messages;			
	}
	// use this to prent formmail spamming
	function strip_line_breaks($val) {
		$val = preg_replace("/([\r\n])/", "", $val);
		return $val;
	}
	function check_mail_address($mail_address) {
		$pattern = "/^[\w-]+(\.[\w-]+)*@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4})$/i";
		if (preg_match($pattern, $mail_address)) {
			if (function_exists("checkdnsrr")) {
				$parts = explode("@", $mail_address);
				if (checkdnsrr($parts[1], "MX")){
					return true;
				} else {
					return false;
				}
			} else {
				// on windows hosts is only a limited e-mail address validation possible
				return true;
			}
		} else {
			return false;
		}
	}
	function get_file_data($filepath) {
		if (file_exists($filepath)) {
			if (!$str = file_get_contents($filepath)) {
				$this->msg[] = "Error while opening attachment \"".basename($filepath)."\"";
			} else {
				return $str;
			}
		} else {
			$this->msg[] = "Error, the file \"".basename($filepath)."\" does not exist.";
			return;
		}
	}
	// use for $dispo "attachment" or "inline" (f.e. example images inside a html mail
	function add_attach_file($file, $encoding = "base64", $dispo = "attachment", $type = "application/octet-stream") {
		$file_str = $this->get_file_data($file);
		if ($file_str == "") {
			return;
		} else {
			if ($encoding == "base64") $file_str = base64_encode($file_str);
			$this->att_files[] = array(
				"data"=>chunk_split($file_str),
				"name"=>basename($file), 
				"cont_type"=>$type, 
				"trans_enc"=>$encoding,
				"disposition"=>$dispo);
			
		}
	}
	
	function add_html_image($img_name) {
		$file_str = $this->get_file_data($img_name);
		$img_dim = getimagesize($img_name);
		if ($file_str == "") {
			return;
		} else {
			$this->html_images[] = array(
				"data"=>chunk_split(base64_encode($file_str)),
				"name"=>basename($img_name), 
				"cont_type"=>$img_dim['mime'],
				"cid"=>md5(uniqid(time()))."@".$_SERVER['SERVER_NAME']);
		}
	}

	function create_stand_headers() {
		if ($this->from_name != "") {
			$headers = "From: ".$this->from_name." <".$this->from_mail.">".LIBR;
			$headers .= "Reply-To: ".$this->from_name." <".$this->from_mail.">".LIBR;
		} else {
			$headers = "From: ".$this->from_mail.LIBR;
			$headers .= "Reply-To: ".$this->from_mail.LIBR;
		}
		if ($this->mail_cc != "") $headers .= "Cc: ".$this->mail_cc.LIBR;
		if ($this->mail_bcc != "") $headers .= "Bcc: ".$this->mail_bcc.LIBR;
		$headers .= sprintf("Message-ID: <%s@%s>%s", md5(uniqid(time())), $_SERVER['SERVER_NAME'], LIBR);
		$headers .= "X-Priority: ".PRIORITY.LIBR;
		$headers .= "X-Mailer: Attachment Mailer [version 1.2]".LIBR;
		$headers .= "MIME-Version: 1.0".LIBR;
		return $headers;
	}
	
	function create_html_image($img_array) {
		$img = "Content-Type: ".$img_array['cont_type'].";".LIBR.chr(9)." name=\"".$img_array['name']."\"".LIBR;
		$img .= "Content-Transfer-Encoding: base64".LIBR;
		$img .= "Content-ID: <image".$img_array['cid'].">".LIBR;
		$img .= "Content-Disposition: inline;".LIBR.chr(9)." filename=\"".$img_array['name']."\"".LIBR.LIBR;
		$img .= $img_array['data'];
		return $img;		
	}
	
	function create_attachment($data_array) {
		$att = "Content-Type: ".$data_array['cont_type'].";".LIBR.chr(9)." name=\"".$data_array['name']."\"".LIBR;
		$att .= "Content-Transfer-Encoding: ".$data_array['trans_enc'].LIBR;
		$att .= "Content-Disposition: ".$data_array['disposition'].";".LIBR.chr(9)." filename=\"".$data_array['name']."\"".LIBR.LIBR;
		$att .= $data_array['data'];
		return $att;		
	}
	
	function create_html_body() {
		$html = "Content-Type: text/html; charset=".ENCODING.LIBR;
		$html .= "Content-Transfer-Encoding: ".TRANS_ENC.LIBR.LIBR;
		foreach ($this->html_images as $img) {
			$this->html_body = str_replace($img['name'], "cid:image".$img['cid'], $this->html_body);
		}
		$html .= $this->html_body;
		return $html.LIBR.LIBR;
	}
	
	function build_message() {
		$this->headers = $this->create_stand_headers();
		$msg = "";
		$is_html = ($this->html_body != "") ? true : false;
		$is_attachment = (count($this->att_files) > 0) ? true : false;
		$is_images = (count($this->html_images) > 0) ? true : false;
		if ($is_attachment) {
			$this->uid = md5(uniqid(time()));
			$this->headers .= "Content-Type: multipart/mixed;".LIBR.chr(9)." boundary=\"".$this->uid."\"".LIBR.LIBR;
			$this->headers .= "This is a multi-part message in MIME format.".LIBR;
			if (!$is_html) {
				$msg .= "--".$this->uid.LIBR;
			} else {
				$this->headers .= "--".$this->uid.LIBR;
			}
		} 
		if ($is_html) {
			$this->alternative_uid = md5(uniqid(time()));
			$this->headers .= "Content-Type: multipart/alternative;".LIBR.chr(9)." boundary=\"".$this->alternative_uid."\"".LIBR.LIBR;
			if (!$is_attachment) {
				$this->headers .= "This is a multi-part message in MIME format.".LIBR;
			}
			$msg .= LIBR."--".$this->alternative_uid.LIBR;
		}
		$body_head = "Content-Type: text/plain; charset=".ENCODING."; format=flowed".LIBR;
		$body_head .= "Content-Transfer-Encoding: ".TRANS_ENC.LIBR.LIBR;
		if (!$is_attachment && !$is_html) {
			$this->headers .= $body_head;
		} else {
			$msg .= $body_head;
		}
		$msg .= trim($this->text_body).LIBR.LIBR;
		if ($is_html) {
			$msg .= "--".$this->alternative_uid.LIBR;
			if ($is_images) {
				$this->related_uid = md5(uniqid(time()));
				$msg .= "Content-Type: multipart/related;".LIBR.chr(9)." boundary=\"".$this->related_uid."\"".LIBR.LIBR.LIBR;
				$msg .= "--".$this->related_uid.LIBR;
				$msg .= $this->create_html_body();
				foreach ($this->html_images as $img) {
					$msg .= "--".$this->related_uid.LIBR;
					$msg .= $this->create_html_image($img);
				}
				$msg .= LIBR."--".$this->related_uid."--";
			} else {
				$msg .= $this->create_html_body();
			}
			$msg .= LIBR.LIBR."--".$this->alternative_uid."--".LIBR.LIBR;
		}
		if ($is_attachment) {
			foreach ($this->att_files as $att) {
				$msg .= "--".$this->uid.LIBR;
				$msg .= $this->create_attachment($att);
			}
			$msg .= "--".$this->uid."--";
		}
		return $msg;		
	}
	
	function process_mail() {
		if (!$this->valid_mail_adresses) return;
		if (mail($this->mail_to, $this->mail_subject, $this->build_message(), $this->headers, "-f".$this->webmaster_email)) {
			$this->msg[] = "Your mail is succesfully submitted.";
			return true;
		} else {
			$this->msg[] = "Error while sending you mail.";
			return false;
		}
	}
}
	
?>