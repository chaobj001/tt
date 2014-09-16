<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:MailSmtp.class.php
- ԭ����:(�����ռ�)
- ������:indraw
- ��д����:2004/11/17
- ��Ҫ����:smtp�ʼ����ͺ���������socket������
- ���л���:��Ҫ��
- �޸ļ�¼:2004/11/17��indraw��������
---------------------------------------------------------------------
*/

/*
	$smtp = new MailSmtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
	$smtp->send_mail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
*/
/*
	MailSmtp($relay_host = "", $smtp_port = 25,$auth = false,$user,$pass)
	send_mail($to, $from, $subject = "", $body = "", $mailtype, $cc = "", $bcc = "", $additional_headers = "")
	                                                   //ִ���ʼ����Ͳ���
	smtp_send($helo, $from, $to, $header, $body = "")  //
	---------------------
	smtp_sockopen($address)
	smtp_sockopen_relay()
	smtp_sockopen_mx($address)
	smtp_message($header, $body)
	smtp_eom()
	smtp_ok()
	smtp_putcmd($cmd, $arg = "")
	strip_comment($address)
*/


//=============================================================================
class MailSmtp
{
	//��������
	var $show_errors = true;    //�Ƿ���ʾ����
	var $show_debug = true;     //�Ƿ���ʾ����

	var $host_name;             //��������
	var $smtp_port;             //smtp�˿�
	var $user;                  //�û���
	var $pass;                  //����
	var $relay_host;            //Զ������

	var $time_out;              //��ʱ����
	var $auth;                  //�Ƿ������֤



	//˽������
	var $sock;

	/*
	-----------------------------------------------------------
	��������:MailSmtp($relay_host = "", $smtp_port = 25,$auth = false,$user,$pass)
	��Ҫ����:���캯��
	����:mixed (������ַ���˿ڣ��Ƿ������֤���û���������)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function MailSmtp($relay_host = "", $smtp_port = 25,$auth = false,$user,$pass)
	{
		$this->smtp_port = $smtp_port;
		$this->relay_host = $relay_host;
		$this->time_out = 30;
		//
		$this->auth = $auth;
		$this->user = $user;
		$this->pass = $pass;
		//
		$this->host_name = "localhost";
		$this->log_file = "";

		$this->sock = FALSE;
	}

	/*
	-----------------------------------------------------------
	��������:sendmail()
	��Ҫ����:ִ���ʼ����Ͳ���
	����:mixed (����email������email�����⣬���ݣ����ͣ����ͣ����ͣ���Ϣͷ)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function send_mail($to, $from, $subject = "", $body = "", $mailtype, $cc = "", $bcc = "", $additional_headers = "")
	{
		$mail_from = $this->get_address($this->strip_comment($from));
		//$body = ereg_replace("(^|(\r\n))(.)", "1.3", $body);
		$header .= "MIME-Version:1.0\r\n";
		if($mailtype=="HTML")
		{
			$header .= "Content-Type:text/html\r\n";
		}
		$header .= "To: ".$to."\r\n";
		if ($cc != "")
		{
			$header .= "Cc: ".$cc."\r\n";
		}
		$value = stripos($from,"<",0);
		if(!empty($value))
			$header .= "From: $from\r\n";
		else
			$header .= "From: $from<".$from.">\r\n";
		$header .= "Subject: ".$subject."\r\n";
		$header .= $additional_headers;
		$header .= "Date: ".date("r")."\r\n";
		$header .= "X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
		list($msec, $sec) = explode(" ", microtime());
		$header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".".$mail_from.">\r\n";
		$TO = explode(",", $this->strip_comment($to));

		if ($cc != "")
		{
			$TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
		}

		if ($bcc != "")
		{
			$TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
		}

		$sent = TRUE;
		foreach ($TO as $rcpt_to)
		{
			$rcpt_to = $this->get_address($rcpt_to);
			if (!$this->smtp_sockopen($rcpt_to))
			{
				$this->print_error("���ܷ����ʼ��� ".$rcpt_to."\n");
				$sent = FALSE;
				continue;
			}
			if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body))
			{
				$this->print_debug("�ʼ��ɹ����͵� <".$rcpt_to.">\n");
			}
			else
			{
				$this->print_error("���ܷ����ʼ��� <".$rcpt_to.">\n");
				$sent = FALSE;
			}
			fclose($this->sock);
			$this->print_debug("��Զ��smtp�������Ͽ�����\n");
		}
		return $sent;
	}

//-----------------------------------------------------------------------------
	/*
	-----------------------------------------------------------
	��������:smtp_send()
	��Ҫ����:����ִ���ʼ����ͷ���
	����:mixed
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_send($helo, $from, $to, $header, $body = "")
	{
		if (!$this->smtp_putcmd("HELO", $helo))
		{
			return $this->print_error("sending HELO command");
		}
		//auth
		if($this->auth)
		{
			if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user)))
			{
				return $this->print_error("sending HELO command");
			}

			if (!$this->smtp_putcmd("", base64_encode($this->pass)))
			{
				return $this->print_error("sending HELO command");
			}
		}
		//
		if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">"))
		{
			return $this->print_error("sending MAIL FROM command");
		}

		if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">"))
		{
			return $this->print_error("sending RCPT TO command");
		}

		if (!$this->smtp_putcmd("DATA"))
		{
			return $this->print_error("sending DATA command");
		}

		if (!$this->smtp_message($header, $body))
		{
			return $this->print_error("sending message");
		}

		if (!$this->smtp_eom())
		{
			return $this->print_error("sending <CR><LF>.<CR><LF> [EOM]");
		}

		if (!$this->smtp_putcmd("QUIT"))
		{
			return $this->print_error("sending QUIT command");
		}

		return TRUE;
	}
	/*
	-----------------------------------------------------------
	��������:smtp_sockopen($address)
	��Ҫ����:��socket��ʽ��Զ��smtp����
	����:string (smtp������ַ)
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_sockopen($address)
	{
		if ($this->relay_host == "")
		{
			return $this->smtp_sockopen_mx($address);
		}
		else
		{
			return $this->smtp_sockopen_relay();
		}
	}
	/*
	-----------------------------------------------------------
	��������:smtp_sockopen_relay()
	��Ҫ����:�ȴ�smtp������Ӧ
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_sockopen_relay()
	{
		$this->print_debug("�������ӵ� ".$this->relay_host.":".$this->smtp_port."\n");
		$this->sock = @fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);

		if (!($this->sock && $this->smtp_ok()))
		{
			$this->print_error("�������ӵ��������� ".$this->relay_host."\n");
			$this->print_error("����: ".$errstr." (".$errno.")\n");
			return FALSE;
		}
		$this->print_debug("���ӵ��������� ".$this->relay_host."\n");
		return TRUE;;
	}
	/*
	-----------------------------------------------------------
	��������:smtp_sockopen_mx($address)
	��Ҫ����:---
	����:---
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_sockopen_mx($address)
	{
		$domain = ereg_replace("^.+@([^@]+)$", "1", $address);
		if (!@getmxrr($domain, $MXHOSTS))
		{
			$this->print_error("����: ���ܽ�� MX \"".$domain."\"\n");
			return FALSE;
		}
		foreach ($MXHOSTS as $host)
		{
			$this->print_debug("�������ӵ� ".$host.":".$this->smtp_port."\n");
			$this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);
			if (!($this->sock && $this->smtp_ok()))
			{
				$this->print_error("����: �������ӵ� mx host ".$host."\n");
				$this->print_error("����: ".$errstr." (".$errno.")\n");
				continue;
			}
			$this->print_debug("���ӵ� mx host ".$host."\n");
			return TRUE;
		}
		$this->print_error("����: �������ӵ��κ� mx hosts (".implode(", ", $MXHOSTS).")\n");
		return FALSE;
	}
	/*
	-----------------------------------------------------------
	��������:smtp_message($header, $body)
	��Ҫ����:ִ��smtp��Ϣд�����
	����:mixed ���ʼ�ͷ���ʼ����ݣ�
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_message($header, $body)
	{
		fputs($this->sock, $header."\r\n".$body);
		$this->print_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));

		return TRUE;
	}

	/*
	-----------------------------------------------------------
	��������:smtp_eom()
	��Ҫ����:---
	����:---
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_eom()
	{
		fputs($this->sock, "\r\n.\r\n");
		$this->print_debug(". [EOM]\n");

		return $this->smtp_ok();
	}

	/*
	-----------------------------------------------------------
	��������:smtp_ok()
	��Ҫ����:---
	����:---
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_ok()
	{
		$response = str_replace("\r\n", "", fgets($this->sock, 512));
		$this->print_debug($response."\n");

		if (!ereg("^[23]", $response))
		{
			fputs($this->sock, "QUIT\r\n");
			fgets($this->sock, 512);
			$this->print_error("����:Զ���������� \"".$response."\"\n");
			return FALSE;
		}
		return TRUE;
	}
	/*
	-----------------------------------------------------------
	��������:smtp_putcmd($cmd, $arg = "")
	��Ҫ����:---
	����:---
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function smtp_putcmd($cmd, $arg = "")
	{
		if ($arg != "")
		{
			if($cmd=="")
				$cmd = $arg;
			else
				$cmd = $cmd." ".$arg;
		}

		fputs($this->sock, $cmd."\r\n");
		$this->print_debug("> ".$cmd."\n");

		return $this->smtp_ok();
	}

	/*
	-----------------------------------------------------------
	��������:strip_comment($address)
	��Ҫ����:---
	����:---
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function strip_comment($address)
	{
		$comment = "\([^()]*\)";
		while (ereg($comment, $address))
		{
			$address = ereg_replace($comment, "", $address);
		}

		return $address;
	}
	/*
	-----------------------------------------------------------
	��������:get_address($address)
	��Ҫ����:��ȡ�ʼ���ַ����ҪΪ��ʽ��
	����:string
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_address($address)
	{
		$address = ereg_replace("([ \t\r\n])+", "", $address);
		$address = ereg_replace("^.*<(.+)>.*$", "1", $address);

		return $address;
	}
	/*
	-----------------------------------------------------------
	��������:print_error($str = "")
	��Ҫ����:��ʾ����������Ϣ
	����:string
	���:echo
	�޸���־:------
	-----------------------------------------------------------
	*/
	function print_error($str = "")
	{
		//����ȫ�ֱ���$PHPSEA_ERROR..
		global $PHPSEA_ERROR;
		$PHPSEA_ERROR['MailSmtp_Error'] = $str;

		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>MailSmtp Error --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
	}//end func

	/*
	-----------------------------------------------------------
	��������:print_debug($str = "")
	��Ҫ����:��ʾ������Ϣshow_debug
	����:string
	���:echo
	�޸���־:------
	-----------------------------------------------------------
	*/
	function print_debug($str = "")
	{

		//�ж��Ƿ���ʾ�������..
		if ( $this->show_debug )
		{
			print "<blockquote><font face=arial size=2 color=green>";
			print "<b>MailSmtp Debug --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
	}//end func

}//CLASS END
//=============================================================================
?>

