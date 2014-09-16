<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:MailSmtp.class.php
- ԭ����:�����Ѽ�
- ������:indraw
- ��д����:2004/11/17
- ��Ҫ����:imap�ʼ����պ���������imap������
- ���л���:imapģ��֧��
- �޸ļ�¼:2004/11/17��indraw��������
---------------------------------------------------------------------
*/

/*
	$imap=new myimap_ext;
	$imap->hostname="mail.dns.com.cn";
	$imap->port=143; //���Ϊpop3����Ϊ110
	$imap->username="wangyzh";
	$imap->userpwd="105080";
*/
/*
	open()                                        //��Զ������
	close()                                       //�ر�Զ������
	delete_mail($msg_no)                          //����ɾ�����
	expunge_mail()                                //ִ������ɾ������
	get_mailbox()                                 //��ȡ�����б�����ʵ�����ʣ���û�����ƣ�
	check_mailinfo($page_size,$page)              //��ȡ������Ϣ
	decode_mime_string ($string)                  //��mime�ʼ����н���
	display_toaddress ($user, $server, $from)     //��ʾ�ʼ���ַ
	get_barefrom($user, $server)                  //��ʾ�ʼ���Դ
	get_structure($msg_num)                       //��ȡ�ʼ���Ϣ
	proc_structure($msg_part, $part_no, $msg_num) //��ȡ�������ϸ��Ϣ
	get_mail_subject($msg_no)                     //��ȡ�ʼ�����
	list_attaches()                               //��ʾ�����б�
*/

//=============================================================================
class MailImap
{
	var $show_errors = true;        //��ʾ����
	var $show_debug = true;         //��ʾ����

	var $username="";               //�û���
	var $userpwd="";                //�û�����
	var $hostname="";               //smtp����
	var $port=0;                    //smtp�˿�
	var $connection=0;              //�Ƿ�����
	var $state="DISCONNECTED";      //����״̬
	var $greeting="";
	var $must_update=0;
	var $inStream=0;
	var $num_msg_parts = 0;
	var $attach;                    //����
	var $num_of_attach = 0;         //��������

	var $emailContent = "";         //�ʼ�����

	/*
	-----------------------------------------------------------
	��������:open()
	��Ҫ����:��Զ������
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function open()
	{
		if ($this->port==110)
			$this->inStream=@imap_open("{".$this->hostname."/pop3:110}inbox",$this->username,$this->userpwd);
		else
			$this->inStream=@imap_open("{".$this->hostname.":143}INBOX",$this->username,$this->userpwd);

		if ($this->inStream){
			$this->print_debug("�û���$this->username ���������ӳɹ���");
			return true;
		}
		else{
			$this->print_error("�û���$this->username ����������ʧ�ܡ�");
			return false;
		}
	}

	/*
	-----------------------------------------------------------
	��������:close()
	��Ҫ����:�ر�Զ������
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	Function close()
	{
		if(imap_close($this->inStream)){
			$this->print_debug("�Ѿ�������� $this->hostname �Ͽ����ӡ�");
			return true;
		}
		else{
			$this->print_error("������� $this->hostname �Ͽ�����ʧ�ܡ�");
			return false;
		}
	}
	/*
	-----------------------------------------------------------
	��������:DeleteMail($msg_no)
	��Ҫ����:����ɾ�����
	����:int (�ʼ���Ψһ����)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function delete_mail($msg_no)
	{
		if (@imap_delete($this->inStream,$msg_no)){
			$this->print_debug("�ɹ����ʼ� $msg_no ����ɾ�����");
			return true;
		}
		else{
			$this->print_error("�����ʼ� $msg_no ��ɾ�����ʧ��");
			return false; 
		}
	}
	/*
	-----------------------------------------------------------
	��������:ExpungeMail()
	��Ҫ����:ִ������ɾ������
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function expunge_mail()
	{
		if (@imap_expunge($this->inStream)){
			$this->print_debug("�ɹ�ɾ���ʼ�");
			return true;
		}
		else{
			$this->print_error("ɾ���ʼ�ʧ��");
			return false;
		}
	}

	/*
	-----------------------------------------------------------
	��������:get_mailbox()
	��Ҫ����:��ȡ�����б�����ʵ�����ʣ���û�����ƣ�
	����:void
	���:array
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_mailbox()
	{
		$list = imap_getmailboxes($this->inStream, "{".$this->hostname.":143}", "*");
		//var_dump($list);
		if (is_array($list)) {
			reset($list);
			while (list($key, $val) = each($list)) {
				echo "($key) ";
				echo imap_utf7_decode($val->name) . ",";
				echo "'" . $val->delimiter . "',";
				echo $val->attributes . ",<br>";
				$status = imap_status($this->inStream,$val->name,SA_ALL);
				echo "<pre>";
				var_dump($status);
				echo "</pre><br>";

			}
		}
		else {
			echo "imap_getmailboxes failed: " . imap_last_error() . "<hr>";
		}
	}

	/*
	-----------------------------------------------------------
	��������:check_mailinfo($page_size,$page)
	��Ҫ����:��ȡ������Ϣ
	����:mixed (ÿҳ��ʾ����ǰҳ)
	���:array
	�޸���־:------
	-----------------------------------------------------------
	*/
	function check_mailinfo($page_size,$page)
	{
		$mboxinfo=@imap_mailboxmsginfo($this->inStream);
		//$mboxinfo=imap_check($this->inStream);
		//����������Ϣ
		if ($mboxinfo){
			$mail_info['info'] = $mboxinfo;
		}
		else{
			$this->print_error ("�����޷���ȡ�ռ������Ϣ��");
			return false;
		}
		$sortby="SORTDATE"; //������������
		$sort_reverse=1;
		$sorted = imap_sort($this->inStream, $sortby, $sort_reverse, SE_UID);
		
		//��ʾ��ǰҳȡ�õ��ʼ���Ϣ
		for ($i=0;$i<$mboxinfo->Nmsgs;$i++){
			if (($i>=$page*$page_size) and ($i<$page*$page_size+$page_size)){
				$msg_no = @imap_msgno($this->inStream, $sorted[$i]);
				$msgHeader = @imap_header($this->inStream, $msg_no);
				
				//��ʽ�����ڣ�24 Nov 2004 12:58:58ת����11/24 12:58
				if (isset($msgHeader->date)){
					//$date = $msgHeader->date;
					//if (ord($date) > 64)$date = substr($date, 5);
					$date = date ("m/d H:s",strtotime($msgHeader->date));
				}
				//�����ʼ�����Դ
				if (isset($msgHeader->from[0])){
					$from = $msgHeader->from[0];
					if (isset($from->personal)){
						$frm = trim($this->decode_mime_string($from->personal));
						if (isset($from->mailbox) && isset($from->host)){
							$frm_add = $from->mailbox . '@' . $from->host;
						}
					}
					elseif (isset($from->mailbox) && isset($from->host))
						$frm = $from->mailbox . '@' . $from->host;
					elseif (isset($msgHeader->fromaddress))
						$frm = trim($h->fromaddress);
				}
				elseif (isset($msgHeader->fromaddress))
						$frm = trim($msgHeader->fromaddress);
					//if (strlen($frm) > 50)
					//$frm = substr($frm, 0, 50) . '...';
				//�����ʼ��Ľ�����
				if (isset($msgHeader->toaddress))
					$to = trim($msgHeader->toaddress);
				else
					$to = "δ֪";
				//�ʼ�����
				if (isset($msgHeader->subject))
					$sub = trim($this->decode_mime_string($msgHeader->subject));
				if ($sub == "")
					$sub = "������"; 
				if (strlen($sub) > 50)
					$sub = substr($sub, 0, 50) . '...';
				//�ʼ���С
				if (isset($msgHeader->Size))
					$msg_size = ($msgHeader->Size > 1024) ? sprintf("%.0f kb", $msgHeader->Size / 1024):$msgHeader->Size;

				//��ȡ�ʼ��Ƿ��Ѿ�����;
				if ($msgHeader->Unseen == "U")
					$newmail = "δ��";
				else
					$newmail = "�Ѷ�";

				//��ʽ��������Ϣ
				$mail_info['list']['newMail'][] = $newmail;
				$mail_info['list']['from'][] = $frm;
				$mail_info['list']['msgNum'][] = $msg_no;
				$mail_info['list']['topic'][] = $sub;
				$mail_info['list']['date'][] = $date;
				$mail_info['list']['msgSize'][] = $msg_size;
			}//end if
		}//end for
		return $mail_info;

	}
	/*
	-----------------------------------------------------------
	��������:decode_mime_string ($string)
	��Ҫ����:��mime�ʼ����н���
	����:string 
	���:string 
	�޸���־:------
	-----------------------------------------------------------
	*/
	function decode_mime_string ($string)
	{
		$pos = strpos($string, '=?');
		if (!is_int($pos)){
			return $string;
		}

		$preceding = substr($string, 0, $pos); // save any preceding text
		$search = substr($string, $pos+2, 75); // the mime header spec says this is the longest a single encoded word can be
		$d1 = strpos($search, '?');
		if (!is_int($d1)){
			return $string;
		}

		$charset = substr($string, $pos+2, $d1);
		$search = substr($search, $d1+1);

		$d2 = strpos($search, '?');
		if (!is_int($d2)){
			return $string;
		}

		$encoding = substr($search, 0, $d2);
		$search = substr($search, $d2+1);

		$end = strpos($search, '?=');
		if (!is_int($end)){
			return $string;
		}

		$encoded_text = substr($search, 0, $end);
		$rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text)+6));

		switch ($encoding)
		{
			case 'Q':
			case 'q':
				$encoded_text = str_replace('_', '%20', $encoded_text);
				$encoded_text = str_replace('=', '%', $encoded_text);
				$decoded = urldecode($encoded_text);
			break;

			case 'B':
			case 'b':
				$decoded = urldecode(base64_decode($encoded_text));
			break;

			default:
				$decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
			break;
		}

		return $preceding . $decoded . $this->decode_mime_string($rest);
	}

	/*
	-----------------------------------------------------------
	��������:display_toaddress ($user, $server, $from)
	��Ҫ����:��ʾ�ʼ���ַ
	����:mixed (�û�������������)
	���:string 
	�޸���־:------
	-----------------------------------------------------------
	*/
	Function display_toaddress ($user, $server, $from)
	{
		return is_int(strpos($from, $this->get_barefrom($user, $server)));
	}
	/*
	-----------------------------------------------------------
	��������:get_barefrom($user, $server)
	��Ҫ����:��ʾ�ʼ���Դ
	����:mixed (�û�������)
	���:string (�û�����)
	�޸���־:------
	-----------------------------------------------------------
	*/
	Function get_barefrom($user, $server)
	{
		$barefrom = "$user@$real_server";
		return $barefrom;
	}
	/*
	-----------------------------------------------------------
	��������:get_structure($msg_num)
	��Ҫ����:��ȡ�ʼ���Ϣ
	����:int ���ʼ�Ψһ��ʾ����
	���:object
	�޸���־:------
	-----------------------------------------------------------
	*/
	Function get_structure($msg_num)
	{
		$structure=imap_fetchstructure($this->inStream,$msg_num);
		//echo gettype($structure);
		return $structure;
	}

	/*
	-----------------------------------------------------------
	��������:proc_structure($msg_part, $part_no, $msg_num)
	��Ҫ����:��ȡ�������ϸ��Ϣ
	����:mixed (imap_fetchstructure����ֵ���ʼ����֣��ʼ�Ψһ��ʶ��)
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	Function proc_structure($msg_part, $part_no, $msg_num)
	{
		$emailContent = "�޷���ʾ";
		//
		if ($msg_part->ifdisposition){
			// ����Ƿ��и���
			if ($msg_part->disposition == "attachment"){
				$att_name = "unknown";
				for ($lcv = 0; $lcv < count($msg_part->parameters); $lcv++){
					$param = $msg_part->parameters[$lcv];

					if ($param->attribute == "name"){
						$att_name = $param->value;
						break;
					}
				}

				$att_name = $this->decode_mime_string($att_name);

				$att_path = $this->username."\\".$att_name;
				
				//�ǼǸ����б�
				$this->attach[$this->num_of_attach]=$att_name;
				//�ǼǸ�������
				$this->num_of_attach ++; 
				/*
				$att_path = $this->username."\\".$this->decode_mime_string($att_name);
				if ($this->attach=="")
				$this->attach = $att_name;
				else
				$this->attach .= ";".$att_name;
				*/
				if (!is_dir($this->username))
					mkdir($this->username,0700); 

				if (!file_exists($att_path)){
					$fp=fopen($att_path,"w");
					switch ($msg_part->encoding){
						case 3: //base64
							fputs($fp,imap_base64(imap_fetchbody($this->inStream,$msg_num,$part_no)));
						break;
						case 4: //QP
							fputs($fp,imap_qprint(imap_fetchbody($this->inStream,$msg_num,$part_no)));
						break;
						default:
							fputs($fp,imap_fetchbody($this->inStream,$msg_num,$part_no));
						break;
					}
					fclose($fp); 
				}
			//�����ͼƬ����ô��ʾ
			//if ($msg_part->type=="5"){
			//echo "<p align=center><hr align=center>\n";
			//echo "<img src=\"$att_path\" align=center></p>\n";
			//}
			}//���û�и���
			else{
				//�滮�У�û�и����Ľ���취
			}
		}
		
		else{
		//
			switch ($msg_part->type){
				case 0:
					$mime_type = "text";
				break;
				case 1:
					$mime_type = "multipart";
					//�����multipart���ͣ���ô�ݹ��ȡ������Ϣ
					$this->num_msg_parts = count($msg_part->parts);
					for ($i = 0; $i < $this->num_msg_parts; $i++){
						if ($part_no != ""){
							$part_no = $part_no.".";
						}
						for ($i = 0; $i < count($msg_part->parts); $i++){
							$this->proc_structure($msg_part->parts[$i], $part_no.($i + 1), $msg_num);
						}
					}
				break;
				case 2:
					$mime_type = "message";
				break;
				case 3:
					$mime_type = "application";
				break;
				case 4:
					$mime_type = "audio";
				break;
				case 5:
					$mime_type = "image";
				break;
				case 6:
					$mime_type = "video";
				break;
				case 7:
					$mime_type = "model";
				break;
				default:
					$mime_type = "unknown";
			}//end switch
			

			$full_mime_type = $mime_type."/".$msg_part->subtype;
			$full_mime_type = strtolower($full_mime_type);

			//���ʼ����ݱ������ʶ�𣬲���ȷѡ����뷽ʽ
			switch ($msg_part->encoding){
				case 0:
				case 1:
					if ($this->num_msg_parts == 0){
						$this->emailContent .= ereg_replace("\r\n","<br>\r\n",imap_body($this->inStream,$msg_num));
					}
					else{
						if ($part_no!=""){
							$this->emailContent .= ereg_replace("\r\n","<br>\r\n",imap_fetchbody($this->inStream,$msg_num,$part_no));
						}
					}
				break;
				case 3://BASE64
					//ʹ��imap_base64���н���
					if ($full_mime_type=="text/plain"){
						if ($this->num_msg_parts == 0){
							$content=imap_base64(imap_body($this->inStream,$msg_num));
						}
						else{
							$content = imap_base64(imap_fetchbody($this->inStream,$msg_num,$part_no));
							$att_path = $this->username . "\\text.txt";
							$fp = fopen($att_path,"w");
							fputs($fp,$content);
							fclose($fp);
							$this->attach[$this->num_of_attach]="text.txt";
							$this->num_of_attach++; 
						}
					$this->emailContent .= $content;
					}
					if ($full_mime_type=="text/html"){
						$att_path = $this->username . "\\html.htm";
						$fp = fopen($att_path,"w");
						fputs($fp,imap_base64(imap_fetchbody($this->inStream,$msg_num,$part_no)));
						fclose($fp);
						$this->attach[$this->num_of_attach]="html.htm";
						$this->num_of_attach++;
					}
				break;
				case 4: //Qp
					//ʹ��imap_qprint���н���
					if ($this->num_msg_parts == 0){
						$this->emailContent .= ereg_replace("\n","<br>",imap_qprint(imap_body($this->inStream,$msg_num)));
					}
					else{
						$this->emailContent .= ereg_replace("\n","<br>",imap_qprint(imap_fetchbody($this->inStream,$msg_num,$part_no)));
					}
					if ($full_mime_type=="text/html"){
						$att_path = $this->username . "\\qphtml.htm";
						$fp = fopen($att_path,"w");
						fputs($fp,imap_qprint(imap_fetchbody($this->inStream,$msg_num,$part_no)));
						fclose($fp);
						$this->attach[$this->num_of_attach]="qphtml.htm";
						$this->num_of_attach++;
					} 
				break;
				case 5:
					//Ĭ�Ͻ��뷽ʽ
					$this->emailContent .= ereg_replace("\n","<br>",imap_fetchbody($this->inStream,$msg_num));
				break;
			}//end switch
		}//end if
		return $this->emailContent;
	}
	/*
	-----------------------------------------------------------
	��������:get_mail_subject($msg_no)
	��Ҫ����:��ȡ�ʼ�����
	����:int
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_mail_subject($msg_no)
	{
		$msgHeader = @imap_header($this->inStream, $msg_no);
		if (isset($msgHeader->subject))
			$sub = trim($this->decode_mime_string($msgHeader->subject));
		if ($sub == "")
			$sub = "������";
		return "Fw:".$sub; 
	}
	/*
	-----------------------------------------------------------
	��������:list_attaches()
	��Ҫ����:���ظ����б�
	����:void
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function list_attaches()
	{
		for ($i=0;$i<count($this->attach);$i++){
			if ($i==0) 
				$attaches = $this->attach[$i];
			else
			$attaches .= ";".$this->attach[$i];
		}
		return $attaches;
	}
	/*
	-----------------------------------------------------------
	��������:print_attaches()
	��Ҫ����:��ʾ�����б�
	����:void 
	���:oid
	�޸���־:------
	-----------------------------------------------------------
	*/
	function print_attaches()
	{
		for ($i=0;$i<count($this->attach);$i++){
			echo "<a target=_blank href=\"".$this->username."\\".$this->attach[$i]."\">".$this->attach[$i]."</a><br/>";
		}
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
		$PHPSEA_ERROR['MailImap_Error'] = $str;
	
		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>MailImap Error --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
		else
		{
			return false;	
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
			print "<b>MailImap Debug --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
	}//end func

}//end class
//=============================================================================
?>
