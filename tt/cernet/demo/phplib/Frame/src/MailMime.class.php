<?
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:MailMime.class.php
- ԭ����:Alejandro Garcia Gonzalez
- ������:indraw
- ��д����:2004/11/5
- ��Ҫ����:mime�ʼ����ͺ���������ϵͳmail������
- ���л���:ֻ��Ҫ֧��mail�����������Է��ż���
- �޸ļ�¼:2004/11/5��indraw��������
---------------------------------------------------------------------
*/

/*
	$mimemail = new MailMime();
	$mimemail->new_mail($from, $to, $subject, $text);
	$mimemail->add_attachment($attachment, "file.tar.gz");
	$mimemail->send()
*/

/*
	MailMime()
	set_from($mail_from, $name = "")          //�����ʼ���������ͷ���������
	set_to($mail_to, $name = "")              //�����ʼ����͵�����ͽ���������
	set_cc($mail_to, $name = "")              //���ó��������������
	set_bcc($mail_bcc, $name = "")            //���ð��������������
	add_to($mail_to, $name = "")              //�����ʼ����������������
	add_cc($mail_cc, $name = "")              //���ӳ��������������
	add_bcc($mail_bcc, $name = "")            //���Ӱ��������������

	set_subject($subject)                     //�����ʼ�����
	set_text($text)                           //�����ʼ��ı�����
	set_html($html)                           //�����ʼ�html����
	new_mail($from = "", $to = "", $subject = "", $text = "", $html = "")
	                                          //�������ʼ�
	add_attachment($file, $name, $type = "")  //��ӷ��͸���
	send()                                    //�ʼ����Ͳ���
	---------------------------------------------------------------------
	build_header($content_type)
	parse_elements()
	search_images()
	validate_mail($mail)
	get_mimetype($name)
	open_file($file)
	print_error($msg, $element = "")
*/


//=============================================================================
class MailMime 
{

	//������
	var $show_errors = true;                         //

	var $separator = ",";                             //
	var $charset = "gb2312";                         //
	var $mail_subject = "No subject";                //
	var $mail_from = "Indraw <wangyzh@dns.com.cn>";  //
	var $mail_to;                                    //
	var $mail_cc;                                    //
	var $mail_bcc;                                   //
	var $mail_text;                                  //
	var $mail_html;                                  //
	var $mail_type;                                  //
	var $mail_header;                                //
	var $mail_body;                                  //
	var $attachments = array();                      //
	var $attachments_index;                          //
	var $attachments_img;                            //
	var $boundary_mix;                               //
	var $boundary_rel;                               //
	var $boundary_alt;                               //

	var $error_msg = array(
			1	=>	'No existe un correo destino',
			2	=>	'',
			3	=>	''
	);

	var $mime_types = array(
			'gif'  => 'image/gif',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpe'  => 'image/jpeg',
			'bmp'  => 'image/bmp',
			'png'  => 'image/png',
			'tif'  => 'image/tiff',
			'tiff' => 'image/tiff',
			'swf'  => 'application/x-shockwave-flash',
			'doc'  => 'application/msword',
			'xls'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'pdf'  => 'application/pdf',
			'ps'   => 'application/postscript',
			'eps'  => 'application/postscript',
			'rtf'  => 'application/rtf',
			'bz2'  => 'application/x-bzip2',
			'gz'   => 'application/x-gzip',
			'tgz'  => 'application/x-gzip',
			'tar'  => 'application/x-tar',
			'zip'  => 'application/zip',
			'html' => 'text/html',
			'htm'  => 'text/html',
			'txt'  => 'text/plain',
			'css'  => 'text/css'
	);

	//���캯��
	function MailMime()
	{
		$this->boundary_mix = "=-dns_mix_" . md5(uniqid(rand()));
		$this->boundary_rel = "=-dns_rel_" . md5(uniqid(rand()));
		$this->boundary_alt = "=-dns_alt_" . md5(uniqid(rand()));
		$this->attachments_index = 0;
		if(!defined('BR')){
			define('BR', "\n", TRUE);
		}
	}

	/*
	-----------------------------------------------------------
	��������:set_from($mail_from, $name = "")
	��Ҫ����:�����ʼ���������ͷ���������
	����:mixed (������email������������)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_from($mail_from, $name = "")
	{
		if ($this->validate_mail($mail_from)){
			if (!empty($name)){
				$this->mail_from = "$name <$mail_from>";
			}
			else {
				$this->mail_from = $mail_from;
			}
		}
		else {
			$this->mail_from = "Indraw <wangyzh@dns.com.cn>";
		}
	}

	/*
	-----------------------------------------------------------
	��������:set_to($mail_to, $name = "")
	��Ҫ����:�����ʼ����͵�����ͽ���������
	����:mixed (������email������)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_to($mail_to, $name = "")
	{
		if ($this->validate_mail($mail_to)){
			if (!empty($name)){
				$mail_to = "$name <$mail_to>";
				return true;
			}
			$this->mail_to = $mail_to;
			return true;
		}
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:set_cc($mail_to, $name = "")
	��Ҫ����:���ó��������������
	����:mixed
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_cc($mail_cc, $name = "")
	{
		if ($this->validate_mail($mail_cc)){
			if (!empty($name)){
				$mail_cc = "$name <$mail_cc>";
				return true;
			}
			$this->mail_cc = $mail_cc;
			return true;
		}
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:set_bcc($mail_bcc, $name = "")
	��Ҫ����:���ð��������������
	����:mixed
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_bcc($mail_bcc, $name = "")
	{
		if ($this->validate_mail($mail_bcc)){
			if (!empty($name)){
				$mail_bcc = "$name <$mail_bcc>";
				return true;
			}
			$this->mail_bcc = $mail_bcc;
			return true;
		}
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:add_to($mail_to, $name = "")
	��Ҫ����:�����ʼ����������������
	����:mixed
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function add_to($mail_to, $name = "")
	{
		if ($this->validate_mail($mail_to)){
			if (!empty($name)){
				$mail_to = "$name <$mail_to>";
			}
			if (empty($this->mail_to)){
				$this->mail_to = $mail_to;
				return true;
			}
			else {
				$this->mail_to .= ", " . $mail_to;
				return true;
			}
		}
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:add_cc($mail_cc, $name = "")
	��Ҫ����:���ӳ��������������
	����:mixed
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function add_cc($mail_cc, $name = "")
	{
		if ($this->validate_mail($mail_cc)){
			if (!empty($name)){
				$mail_cc = "$name <$mail_cc>";
			}
			if (empty($this->mail_cc)){
				$this->mail_cc = $mail_cc;
				return true;
			}
			else {
				$this->mail_cc .= ", " . $mail_cc;
				return true;
			}
		}
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:add_bcc($mail_bcc, $name = "")
	��Ҫ����:���Ӱ��������������
	����:mixed
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function add_bcc($mail_bcc, $name = "")
	{
		if ($this->validate_mail($mail_bcc)){
			if (!empty($name)){
				$mail_bcc = "$name <$mail_bcc>";
			}
			if (empty($this->mail_bcc)){
				$this->mail_bcc = $mail_bcc;
				return true;
			}
			else {
				$this->mail_bcc .= ", " . $mail_bcc;
				return true;
			}
		}
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:set_subject($subject)
	��Ҫ����:�����ʼ�����
	����:string
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_subject($subject)
	{
		if (!empty($subject)){
			$this->mail_subject = $subject;
		}
	}

	/*
	-----------------------------------------------------------
	��������:set_text($text)
	��Ҫ����:�����ʼ��ı�����
	����:string
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_text($text)
	{
		if (!empty($text)){
			$this->mail_text = $text;
		}
	}

	/*
	-----------------------------------------------------------
	��������:set_html($html)
	��Ҫ����:�����ʼ�html����
	����:string
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_html($html)
	{
		if (!empty($html)){
			$this->mail_html = $html;
		}
	}

	/*
	-----------------------------------------------------------
	��������:new_mail($from = "", $to = "", $subject = "", $text = "", $html = "")
	��Ҫ����:�������ʼ�
	����:mixed (����email������email�����⣬���ݣ��Ƿ�Ϊhtml)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function new_mail($from = "", $to = "", $subject = "", $text = "", $html = "")
	{
		$this->mail_subject = "";
		$this->mail_from = "";
		$this->mail_to = "";
		$this->mail_cc = "";
		$this->mail_bcc = "";
		$this->mail_text = "";
		$this->mail_html = "";
		$this->mail_header = "";
		$this->attachments_index = 0;

		//����������
		$this->set_from($from);
		$this->set_to($to);
		$this->set_subject($subject);
		$this->set_text($text);
		$this->set_html($html);
	}

	/*
	-----------------------------------------------------------
	��������:add_attachment($file, $name, $type = "")
	��Ҫ����:��ӷ��͸���
	����:mixed �ļ�����Ƕ�뵽�ʼ��ڲ�������֣�����
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function add_attachment($file, $name, $type = "")
	{
		if ($content = $this->open_file($file)){
			if (empty($type)){$type = $this->get_mimetype($name);}
			$this->attachments[$this->attachments_index][content] = chunk_split(base64_encode($content));
			$this->attachments[$this->attachments_index][name] = $name;
			$this->attachments[$this->attachments_index][type] = $type;
			$this->attachments[$this->attachments_index][embedded] = false;
			$this->attachments_index++;
		}
	}

	/*
	-----------------------------------------------------------
	��������:send()
	��Ҫ����:�ʼ����Ͳ���
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function send()
	{
		switch ($this->parse_elements()){
			case 1:
				$this->build_header("Content-Type: text/plain");
				$this->mail_body = $this->mail_text;
				break;
			case 3:
				$this->build_header("Content-Type: multipart/alternative; boundary=\"$this->boundary_alt\"");
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/plain" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_text . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/html; charset=\"$this->charset\"" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_html . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . "--" . BR;
				break;
			case 5:
				$this->build_header("Content-Type: multipart/mixed; boundary=\"$this->boundary_mix\"");
				$this->mail_body .= "--" . $this->boundary_mix . BR;
				$this->mail_body .= "Content-Type: text/plain" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_text . BR . BR;
				foreach($this->attachments as $key => $value){
					$this->mail_body .= "--" . $this->boundary_mix . BR;
					$this->mail_body .= "Content-Type: " . $this->attachments[$key][type] . "; name=\"" . $this->attachments[$key][name] . "\"" . BR;
					$this->mail_body .= "Content-Disposition: attachment; filename=\"" . $this->attachments[$key][name] . "\"" . BR;
					$this->mail_body .= "Content-Transfer-Encoding: base64" . BR . BR;
					$this->mail_body .= $this->attachments[$key][content] . BR . BR;
				}
				$this->mail_body .= "--" . $this->boundary_mix . "--" . BR;
				break;
			case 7:
				$this->build_header("Content-Type: multipart/mixed; boundary=\"$this->boundary_mix\"");
				$this->mail_body .= "--" . $this->boundary_mix . BR;
				$this->mail_body .= "Content-Type: multipart/alternative; boundary=\"$this->boundary_alt\"" . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/plain" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_text . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/html; charset=\"$this->charset\"" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_html . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . "--" . BR . BR;
				foreach($this->attachments as $key => $value){
					$this->mail_body .= "--" . $this->boundary_mix . BR;
					$this->mail_body .= "Content-Type: " . $this->attachments[$key][type] . "; name=\"" . $this->attachments[$key][name] . "\"" . BR;
					$this->mail_body .= "Content-Disposition: attachment; filename=\"" . $this->attachments[$key][name] . "\"" . BR;
					$this->mail_body .= "Content-Transfer-Encoding: base64" . BR . BR;
					$this->mail_body .= $this->attachments[$key][content] . BR . BR;
				}
				$this->mail_body .= "--" . $this->boundary_mix . "--" . BR;
				break;
			case 11:
				$this->build_header("Content-Type: multipart/related; type=\"multipart/alternative\"; boundary=\"$this->boundary_rel\"");
				$this->mail_body .= "--" . $this->boundary_rel . BR;
				$this->mail_body .= "Content-Type: multipart/alternative; boundary=\"$this->boundary_alt\"" . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/plain" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_text . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/html; charset=\"$this->charset\"" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_html . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . "--" . BR . BR;
				foreach($this->attachments as $key => $value){
					if ($this->attachments[$key][embedded]){
						$this->mail_body .= "--" . $this->boundary_rel . BR;
						$this->mail_body .= "Content-ID: <" . $this->attachments[$key][embedded] . ">" . BR;
						$this->mail_body .= "Content-Type: " . $this->attachments[$key][type] . "; name=\"" . $this->attachments[$key][name] . "\"" . BR;
						$this->mail_body .= "Content-Disposition: attachment; filename=\"" . $this->attachments[$key][name] . "\"" . BR;
						$this->mail_body .= "Content-Transfer-Encoding: base64" . BR . BR;
						$this->mail_body .= $this->attachments[$key][content] . BR . BR;
					}
				}
				$this->mail_body .= "--" . $this->boundary_rel . "--" . BR;
				break;
			case 15:
				$this->build_header("Content-Type: multipart/mixed; boundary=\"$this->boundary_mix\"");
				$this->mail_body .= "--" . $this->boundary_mix . BR;
				$this->mail_body .= "Content-Type: multipart/related; type=\"multipart/alternative\"; boundary=\"$this->boundary_rel\"" . BR . BR;
				$this->mail_body .= "--" . $this->boundary_rel . BR;
				$this->mail_body .= "Content-Type: multipart/alternative; boundary=\"$this->boundary_alt\"" . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/plain" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_text . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . BR;
				$this->mail_body .= "Content-Type: text/html; charset=\"$this->charset\"" . BR;
				$this->mail_body .= "Content-Transfer-Encoding: 7bit" . BR . BR;
				$this->mail_body .= $this->mail_html . BR . BR;
				$this->mail_body .= "--" . $this->boundary_alt . "--" . BR . BR;
				foreach($this->attachments as $key => $valor){
					if ($this->attachments[$key][embedded]){
						$this->mail_body .= "--" . $this->boundary_rel . BR;
						$this->mail_body .= "Content-ID: <" . $this->attachments[$key][embedded] . ">" . BR;
						$this->mail_body .= "Content-Type: " . $this->attachments[$key][type] . "; name=\"" . $this->attachments[$key][name] . "\"" . BR;
						$this->mail_body .= "Content-Disposition: attachment; filename=\"" . $this->attachments[$key][name] . "\"" . BR;
						$this->mail_body .= "Content-Transfer-Encoding: base64" . BR . BR;
						$this->mail_body .= $this->attachments[$key][content] . BR . BR;
					}
				}
				$this->mail_body .= "--" . $this->boundary_rel . "--" . BR . BR;
				foreach($this->attachments as $key => $value){
					if (!$this->attachments[$key][embedded]){
						$this->mail_body .= "--" . $this->boundary_mix . BR;
						$this->mail_body .= "Content-Type: " . $this->attachments[$key][type] . "; name=\"" . $this->attachments[$key][name] . "\"" . BR;
						$this->mail_body .= "Content-Disposition: attachment; filename=\"" . $this->attachments[$key][name] . "\"" . BR;
						$this->mail_body .= "Content-Transfer-Encoding: base64" . BR . BR;
						$this->mail_body .= $this->attachments[$key][content] . BR . BR;
					}
				}
				$this->mail_body .= "--" . $this->boundary_mix . "--" . BR;
				break;
			default:
				return false;
		}
		if (mail($this->mail_to, $this->mail_subject, $this->mail_body,$this->mail_header)){
			return true;
		}
		return false;
	}

//-----------------------------------------------------------------------------
	/*
	-----------------------------------------------------------
	��������:build_header($content_type)
	��Ҫ����:�����ʼ�ͷ
	����:string (�����ʼ�����)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function build_header($content_type)
	{
		$this->mail_header = "MIME-Version: 1.0" . BR;
		if (!empty($this->mail_from)){
			$this->mail_header .= "From: " . $this->mail_from . BR;
			$this->mail_header .= "Reply-To: " . $this->mail_from . BR;
		}
		if (!empty($this->mail_cc)){
			$this->mail_header .= "Cc: " . $this->mail_cc . BR;
		}
		if (!empty($this->mail_bcc)){
			$this->mail_header .= "Bcc: " . $this->mail_bcc . BR;
		}
		$this->mail_header .= "MailMime.class.php: MIME Mail - PHP/". phpversion() . BR;
		$this->mail_header .= $content_type;
	}

	/*
	-----------------------------------------------------------
	��������:parse_elements()
	��Ҫ����:�����ʼ���ÿһ����
	����:void
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function parse_elements()
	{
		if (empty($this->mail_to)){
			$this->print_error("MailMime::parse_elements: ȱ�ٷ����ʼ���ַ");
			return false;
		}
		$this->mail_type = 0;
		$this->search_images();
		if (!empty($this->mail_text)){
			$this->mail_type = $this->mail_type + 1;
		}
		if (!empty($this->mail_html)){
			$this->mail_type = $this->mail_type + 2;
			if (empty($this->mail_text)){
				$this->mail_text = strip_tags(eregi_replace("<br>", BR, $this->mail_html));
				$this->mail_type = $this->mail_type + 1;
			}
		}
		if ($this->attachments_index != 0){
			if (count($this->attachments_img) != 0){
				$this->mail_type = $this->mail_type + 8;
			}
			if ((count($this->attachments) - count($this->attachments_img)) >= 1){
				$this->mail_type = $this->mail_type + 4;
			}
		}
		return $this->mail_type;
	}

	/*
	-----------------------------------------------------------
	��������:search_images()
	��Ҫ����:����ͼƬ
	����:void
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function search_images()
	{
		if ($this->attachments_index != 0){
			foreach($this->attachments as $key => $value){

				//TNX to Pawel Tomicki
				if (eregi('image', $this->attachments[$key][type]) && (eregi('<img.*src=[\"|\'](' . $this->attachments[$key][name] . ')[\"|\'].*>', $this->mail_html) || eregi('.*background=[\"|\'](' . $this->attachments[$key][name] . ')[\"|\'].*', $this->mail_html))){
					$img_id = md5($this->attachments[$key][name]) . ".dns@mimemail";

					// TNX to Pawel Tomicki
					$this->mail_html = str_replace($this->attachments[$key][name], 'cid:' . $img_id, $this->mail_html);
					//$this->mail_html = eregi_replace('(<img [^<]*src=[\"|\'])(' . $this->attachments[$key][name] . ')([\"|\'][^>]*>[^<]*)', '\\1cid:' . $img_id . '\\3', $this->mail_html);
					$this->attachments[$key][embedded] = $img_id;
					$this->attachments_img[] = $this->attachments[$key][name];
				}
			}
		}
	}

	/*
	-----------------------------------------------------------
	��������:validate_mail($mail)
	��Ҫ����:�ʼ���ַ�Ϸ��Լ��
	����:string (email)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function validate_mail($mail)
	{
		//echo("DD:".$mail);
		if (preg_match("/^[\da-z][\.\w-]+@[\da-z][\.\w-]+\.[a-z]{2,4}$/i", $mail)){
			return true;
		}
		$this->print_error("MailMime::validate_mail �ʼ���ַ���Ϸ�");
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:get_mimetype($name)
	��Ҫ����:��ȡmime����
	����:string
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_mimetype($name)
	{
		$ext_array = explode(".", $name);
		if (($last = count($ext_array) - 1) != 0){
			$ext = $ext_array[$last];
			foreach($this->mime_types as $key => $value){
				if ($ext == $key){
					return $value;
				}
			}
		}
		return "application/octet-stream";
	}

	/*
	-----------------------------------------------------------
	��������:open_file($file)
	��Ҫ����:���ļ�
	����:string ()
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function open_file($file)
	{
		if($fp = @fopen($file, 'r')){
			$content = fread($fp, filesize($file));
			fclose($fp);
			return $content;
		}
		$this->print_error("MailMime::open_file: ���������ڻ��޷��򿪣�");
		return false;
   }

	/*
	-----------------------------------------------------------
	��������:print_error($str = "")
	��Ҫ����:��ʾ����������Ϣ
	����:string 
	���:echo or false
	�޸���־:------
	-----------------------------------------------------------
	*/
	function print_error($str = "")
	{
		//����ȫ�ֱ���$PHPSEA_ERROR..
		global $PHPSEA_ERROR;
		$PHPSEA_ERROR['MailMime_Error'] = $str;
	
		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>MailMime Error --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
		else
		{
			return false;	
		}
	}//end func

}//end class
//=============================================================================
?>
