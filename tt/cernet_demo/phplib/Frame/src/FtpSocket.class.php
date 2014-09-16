<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:FtpSocket.class.php
- ԭ����:TOMO
- ������:indraw
- ��д����:2004/11/1
- ��Ҫ����:ftp������,��������socket����,���Բ�����Ҫphp֧��ftpģ��.
- ���л���:php3������
- �޸ļ�¼:2005/02/06,indraw,����������������������
---------------------------------------------------------------------
*/

/*
	$ftp = new FtpSocket("192.168.0.168","john","111111");
	$ftp->ftp_put("test.rar",1);
	$ftp->ftp_nlist("./");
*/

/*
	FtpSocket($server,$user,$pass,$port=21,$timeout=90)    //���ӵ�ftp������
	ftp_connect($server, $port = 21)               //ִ�����Ӳ���
	ftp_login($user, $pass)                        //ִ�е�½����
	ftp_quit()                                     //�Ͽ�����

	ftp_pwd()                                      //��ȡ��ǰ����Ŀ¼
	ftp_chdir($pathname)                           //�л�����Ŀ¼
	ftp_cdup()                                     //�л�����Ŀ¼
	ftp_mkdir($pathname)                           //����Ŀ¼
	ftp_rmdir($pathname)                           //ɾ��Ŀ¼

	ftp_nlist($arg = "", $pathname = "")           //��ȡĿ¼���ļ��б�
	ftp_file_exists($pathname)                     //�ж��ļ��Ƿ����
	ftp_delete($pathname)                          //ɾ���ļ�
	ftp_rename($from, $to)                         //�������ļ�
	ftp_get($localfile, $remotefile, $mode = 1)    //�����ļ�
	ftp_put($remotefile, $localfile, $mode = 1)    //�ϴ��ļ�

	ftp_size($pathname)                            //��ȡ�ļ���С
	ftp_mdtm($pathname)                            //��ȡ�ļ����޸�ʱ��
	ftp_systype()                                  //����FTP ��������ϵͳ����
	ftp_rawlist($pathname = "")                    //����Ŀ¼���ļ�����ϸ�б�

	ftp_site($command)                             //ִ��CMD����
*/

//=============================================================================
class FtpSocket
{
	//
	var $show_errors = true;          //�Ƿ�error
	var $show_debug  = true;          //�Ƿ�debug
	var $show_cmd    = false;         //�Ƿ���ʾftp����
	//
	VAR $server         = "";         //������ַ
	VAR $port           = 21;         //�˿ں�
	VAR $timeout        = 90;         //��ʱ����
	VAR $user           = "";         //�û���
	VAR $pwd            = "";         //����
	//
	var $ftp_sock;                   //���ӱ�ʶ
	var $ftp_resp       ="";         //socket���ر�ʶ
	var $umask          = 0022;      //�ı䵱ǰ��umask

	/*
	-----------------------------------------------------------
	��������:FtpSocket($server,$user,$pass,$port=21,$timeout=90) 
	��Ҫ����:���캯��,������������,��½
	����:mixed (������,�û�,����,�˿�,��ʱ)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function FtpSocket($server,$user,$pass,$port=21,$timeout=90) 
	{
		$this->server       = $server;
		$this->user         = $user;
		$this->pass         = $pass;
		$this->port         = $port;
		$this->timeout      = $timeout;
		$this->ftp_connect();
		$this->ftp_login();
	}

	/*
	-----------------------------------------------------------
	��������:ftp_connect()
	��Ҫ����:�������ӵ�ftp������
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_connect()
	{
		$this->ftp_sock = @fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
		if (!$this->ftp_sock || !$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_connect: �������ӵ�Զ������: ".$this->server.":".$this->port."");
			$this->print_error("FtpSocket::ftp_connect: fsockopen() ".$errstr." (".$errno.")");
			return FALSE;
		}
		$this->print_debug("FtpSocket::ftp_connect: ���ӵ�Զ������: ".$this->server.":".$this->port);
		return TRUE;
	}
	/*
	-----------------------------------------------------------
	��������:ftp_login()
	��Ҫ����:���Ե�½ftp������
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_login()
	{
		$this->ftp_putcmd("USER", $this->user);
		if (!$this->ftp_ok()) 
		{
			$this->print_error("FtpSocket::ftp_login: �û�������");
			return FALSE;
		}
		$this->ftp_putcmd("PASS", $this->pass);
		if (!$this->ftp_ok()) 
		{
			$this->print_error("FtpSocket::ftp_login: �������");
			return FALSE;
		}
		$this->print_debug("FtpSocket::ftp_login: �ɹ�ͨ����֤,����½.");
		return TRUE;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_quit()
	��Ҫ����:�ر�һ����� FTP ����
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_quit()
	{
		$this->ftp_putcmd("QUIT");
		if (!$this->ftp_ok() || !fclose($this->ftp_sock)) 
		{
			$this->print_error("FtpSocket::ftp_quit: �ر�ftp����������ʧ��");
			return FALSE;
		}
		$this->print_debug("FtpSocket::ftp_quit: === �ɹ��ر� FTP: ".$this->server." ���� ===");
		return TRUE;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_pwd()
	��Ҫ����:���ص�ǰĿ¼��
	����:void
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/

	function ftp_pwd()
	{
		$this->ftp_putcmd("PWD");
		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_pwd: ��ȡftp������ǰ����Ŀ¼���ƴ���");
			return FALSE;
		}
		$res = ereg_replace("^[0-9]{3} \"(.+)\" .+\r\n", "\\1", $this->ftp_resp);

		$this->print_debug("FtpSocket::ftp_pwd: ��ȡ��ǰ����Ŀ¼: ".$res." �ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_chdir($pathname)
	��Ҫ����:�� FTP ���������л���ǰĿ¼
	����:string
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_chdir($pathname)
	{
		$this->ftp_putcmd("CWD", $pathname);
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_chdir: �л�����Ŀ¼: ".$pathname."ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_chdir: �л�����Ŀ¼: ".$pathname." �ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_cdup()
	��Ҫ����:�л�����ǰĿ¼�ĸ�Ŀ¼
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_cdup()
	{
		$this->ftp_putcmd("CDUP");
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_cdup: �л�����ǰ��Ŀ¼ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_cdup: �л���ǰ��Ŀ¼�ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_mkdir($pathname)
	��Ҫ����:����Ŀ¼
	����:string
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_mkdir($pathname)
	{
		$this->ftp_putcmd("MKD", $pathname);
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_mkdir: ����Ŀ¼: ".$pathname."ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_mkdir: ����Ŀ¼: ".$pathname."�ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_rmdir($pathname)
	��Ҫ����:ɾ��Ŀ¼
	����:string
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_rmdir($pathname)
	{
		$this->ftp_putcmd("RMD", $pathname);
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_rmdir: ɾ��Ŀ¼".$pathname."ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_rmdir: ɾ��Ŀ¼".$pathname."�ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_nlist($arg = "", $pathname = "")
	��Ҫ����:���ظ���Ŀ¼���ļ��б�
	����:mixed
	���:array
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_nlist($arg = "", $pathname = "")
	{
		if (!($string = $this->ftp_pasv()))
		{
			return FALSE;
		}

		if ($arg == "")
			$nlst = "NLST";
		else
			$nlst = "NLST ".$arg;

		$this->ftp_putcmd($nlst, $pathname);

		$sock_data = $this->ftp_open_data($string);

		if (!$sock_data || !$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_nlist: ����ָ��Ŀ¼: ".$pathname."�ļ��б�ʧ��");
			return FALSE;
		}
		while (!feof($sock_data))
		{
			$list[] = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
		}
		$this->ftp_close_data($sock_data);

		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_nlist: ����ָ��Ŀ¼: ".$pathname."�ļ��б�ʧ��");
			return FALSE;
		}
		else
		{
			$this->print_debug("FtpSocket::ftp_nlist: �ɹ�����ָ��Ŀ¼: ".$pathname."�ļ��б�");
			if ( $this->show_debug )
			{
				echo("<blockquote><pre>");
				var_dump($list);
				echo("</blockquote></pre>");
			}
		}
		return $list;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_file_exists($pathname)
	��Ҫ����:�ж��ļ��Ƿ����
	����:string
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_file_exists($pathname)
	{
		if (!($remote_list = $this->ftp_nlist("-a")))
		{
			$this->print_error("FtpSocket::ftp_file_exists: ���ܻ�ȡ�ļ��б�");
			return -1;
		}
		reset($remote_list);
		while (list(,$value) = each($remote_list))
		{
			if ($value == $pathname)
			{
				$this->print_debug("FtpSocket::ftp_file_exists: Զ���ļ�: ".$pathname." ����");
				return true;
			}
		}
		$this->print_debug("FtpSocket::ftp_file_exists: Զ���ļ�: ".$pathname." ������");
		return false;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_delete($pathname)
	��Ҫ����:ɾ���ļ�
	����:string
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_delete($pathname)
	{
		$this->ftp_putcmd("DELE", $pathname);
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_delete: ɾ���ļ�: ".$pathname."ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_delete: ɾ���ļ�: ".$pathname." �ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_rename($from, $to)
	��Ҫ����:�޸��ļ���
	����:mixed
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_rename($from, $to)
	{
		$this->ftp_putcmd("RNFR", $from);
		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_rename: �޸��ļ�: ".$from." Ϊ: ".$to."ʧ��");
			return FALSE;
		}
		$this->ftp_putcmd("RNTO", $to);
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_rename: �޸��ļ�: ".$from." Ϊ: ".$to."ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_rename: �޸��ļ�: ".$from." Ϊ: ".$to."�ɹ�");
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_get($localfile, $remotefile, $mode = 1)
	��Ҫ����:�� FTP ������������һ���ļ�
	����:mixed
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_get($localfile, $remotefile, $mode = 1)
	{
		umask($this->umask);
		if (@file_exists($localfile))
		{
			$this->print_error("FtpSocket::ftp_get: ���棡�����ļ�:".$localfile."���ᱻ����");
		}
		$fp = @fopen($localfile, "w");
		if (!$fp)
		{
			$this->print_error("FtpSocket::ftp_get: ���ܽ��������ļ�: ".$localfile."");
			return FALSE;
		}

		if (!$this->ftp_type($mode))
		{
			$this->print_error("FtpSocket::ftp_get: ���ô���ģʽʧ��");
			return FALSE;
		}

		if (!($string = $this->ftp_pasv()))
		{
			$this->print_error("FtpSocket::ftp_get: ���ط���������������ģʽʧ��");
			return FALSE;
		}

		$this->ftp_putcmd("RETR", $remotefile);

		$sock_data = $this->ftp_open_data($string);
		if (!$sock_data || !$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_get: �������ӵ�Զ��ftp������");
			return FALSE;
		}

		while (!feof($sock_data))
		{
			fputs($fp, fread($sock_data, 4096));
		}
		fclose($fp);

		$this->ftp_close_data($sock_data);

		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_get: �ļ�: ".$remotefile." ����ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_get: �ɹ�����ftp�������ļ�: ".$remotefile." ����Ϊ: ".$localfile);
		return $res;
	}
	/*
	-----------------------------------------------------------
	��������:ftp_put($remotefile, $localfile, $mode = 1)
	��Ҫ����:�ϴ�һ���Ѿ��򿪵��ļ��� FTP ������
	����:mixed
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_put($remotefile, $localfile, $mode = 1)
	{
		
		if (!@file_exists($localfile))
		{
			$this->print_error("FtpSocket::ftp_put: �ļ���Ŀ¼: ".$localfile."������");
			return FALSE;
		}

		$fp = @fopen($localfile, "r");
		if (!$fp)
		{
			$this->print_error("FtpSocket::ftp_put: ���ܶ�ȡ�����ļ�: ".$localfile);
			return FALSE;
		}

		if (!$this->ftp_type($mode))
		{
			$this->print_error("FtpSocket::ftp_put: ���ô���ģʽʧ��");
			return FALSE;
		}

		if (!($string = $this->ftp_pasv()))
		{
			$this->print_error("FtpSocket::ftp_put: ���ط���������������ģʽʧ��");
			return FALSE;
		}
		$this->ftp_putcmd("STOR", $remotefile);

		$sock_data = $this->ftp_open_data($string);
		if (!$sock_data || !$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_put: �ϴ��ļ�: ".$localfile."ʧ��,�ļ����ܴ��ڻ�û���ϴ�Ȩ��");
			return FALSE;
		}
		while (!feof($fp))
		{
			fputs($sock_data, fread($fp, 4096));
		}
		fclose($fp);

		$this->ftp_close_data($sock_data);

		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_put: �ϴ��ļ�: ".$localfile."ʧ��,�ļ����ܴ��ڻ�û���ϴ�Ȩ��");
		}
		$this->print_debug("FtpSocket::ftp_put: �������ļ�: ".$localfile." �ϴ�Ϊ: ".$remotefile);
		return $res;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_size($pathname)
	��Ҫ����:��÷�����ָ��Ŀ¼��С
	����:string
	���:int
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_size($pathname)
	{
		$this->ftp_putcmd("SIZE", $pathname);
		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_size: ��ȡ�ļ�: ".$pathname." ��Сʧ��");
			return false;
		}
		$res = ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->ftp_resp);
		$this->print_debug("FtpSocket::ftp_size: ��ȡ�ļ�: ".$pathname." ��СΪ: ".$res."�ɹ�");
		return $res;
	}
	/*
	-----------------------------------------------------------
	��������:ftp_mdtm($pathname)
	��Ҫ����:����ָ���ļ�������޸�ʱ��
	����:string
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_mdtm($pathname)
	{
		$this->ftp_putcmd("MDTM", $pathname);
		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_mdtm: �����ļ�: ".$pathname."����޸�ʱ��ʧ��");
			return -1;
		}
		$mdtm = ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->ftp_resp);
		$date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
		$timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
		
		$this->print_debug("FtpSocket::ftp_mdtm: �����ļ�: ".$pathname."����޸�ʱ��: ".$timestamp);
		return $timestamp;
	}
	/*
	-----------------------------------------------------------
	��������:ftp_systype()
	��Ҫ����:��÷���������ϵͳ����
	����:void
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_systype()
	{
		$this->ftp_putcmd("SYST");
		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_systype: ��ȡ����������ϵͳ����ʧ��");
			return FALSE;
		}
		$DATA = explode(" ", $this->ftp_resp);
		
		$this->print_debug("FtpSocket::ftp_systype: ��ȡ����������ϵͳ����: ".$DATA[1]);
		return $DATA[1];
	}

	/*
	-----------------------------------------------------------
	��������:ftp_rawlist($pathname = "")
	��Ҫ����:����ָ��Ŀ¼���ļ�����ϸ�б�
	����:string
	���:array
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_rawlist($pathname = "")
	{
		if (!($string = $this->ftp_pasv()))
		{
			$this->print_error("FtpSocket::ftp_rawlist: ���ط���������������ģʽʧ��");
			return FALSE;
		}
		$this->ftp_putcmd("LIST", $pathname);

		$sock_data = $this->ftp_open_data($string);

		if (!$sock_data || !$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_rawlist: ��ȡĿ¼: ".$pathname." �ļ���ϸ�б�ʧ��\n");
			return FALSE;
		}

		while (!feof($sock_data))
		{
			$list[] = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
		}
		$this->print_error(implode("\n", $list));
		$this->ftp_close_data($sock_data);

		if (!$this->ftp_ok())
		{
			$this->print_error("FtpSocket::ftp_rawlist: ��ȡĿ¼: ".$pathname." �ļ���ϸ�б�ʧ��\n");
			return FALSE;
		}
		$this->print_debug("FtpSocket::ftp_rawlist: �ɹ�����Ŀ¼: ".$pathname." ��ϸ�ļ��б�ɹ�");
		if ( $this->show_debug )
		{
			echo("<blockquote><pre>");
			var_dump($list);
			echo("</blockquote></pre>");
		}
		return $list;
	}

	/*
	-----------------------------------------------------------
	��������:ftp_site($command)
	��Ҫ����:����������� SITE ����
	����:string
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_site($command)
	{
		$this->ftp_putcmd("SITE", $command);
		$res = $this->ftp_ok();
		if (!$res)
		{
			$this->print_error("FtpSocket::ftp_site: SITE �����ʧ��");
		}
		$this->print_debug("FtpSocket::ftp_site: SITE ����: ".$command);
		return $res;
	}

//=============================================================================
	//�����ļ�����ģʽ
	function ftp_type($mode)
	{
		if ($mode)
			$type = "I"; //Binary mode
		else
			$type = "A"; //ASCII mode

		$this->ftp_putcmd("TYPE", $type);
		$res = $this->ftp_ok();
		return $res;
	}
	//���ö˿ں�
	function ftp_port($ip_port)
	{
		$this->ftp_putcmd("PORT", $ip_port);
		$res = $this->ftp_ok();
		return $res;
	}

	//���ص�ǰ FTP ����ģʽ�Ƿ��
	function ftp_pasv()
	{
		$this->ftp_putcmd("PASV");
		if (!$this->ftp_ok())
		{
			return FALSE;
		}
		$ip_port = ereg_replace("^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*\r\n$", "\\1", $this->ftp_resp);
		return $ip_port;
	}
	//ִ��cmd����
	function ftp_putcmd($cmd, $arg = "")
	{
		if ($arg != "")
		{
			$cmd = $cmd." ".$arg;
		}

		fputs($this->ftp_sock, $cmd."\r\n");
		if( $this->show_cmd )
		{
			$this->print_debug("<font color='green'>".$cmd."</font>");
		}
		return TRUE;
	}
	//�Է��ؽ�����з���
	function ftp_ok()
	{
		$this->ftp_resp = "";
		do {
			$res = fgets($this->ftp_sock, 512);
			$this->ftp_resp .= $res;
		} while (substr($res, 3, 1) != " ");
		
		if( $this->show_cmd )
		{
			$this->print_debug("<font color='green'>".str_replace("\r\n", "\n", $this->ftp_resp)."</font>");
		}
		if (!ereg("^[123]", $this->ftp_resp))
		{
			return FALSE;
		}

		return TRUE;
	}
	//���ݷ��ͽ���
	function ftp_close_data($sock)
	{
		$this->print_debug("FtpSocket::ftp_close_data: ��������Ͽ����ݴ���");
		return fclose($sock);
	}
	//���ݷ��Ϳ�ʼ
	function ftp_open_data($ip_port)
	{
		if (!ereg("[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+", $ip_port))
		{
			$this->print_error("FtpSocket::ftp_open_data: ��������Ӷ˿�(".$ip_port.")");
			return FALSE;
		}

		$DATA = explode(",", $ip_port);
		$ipaddr = $DATA[0].".".$DATA[1].".".$DATA[2].".".$DATA[3];
		$port   = $DATA[4]*256 + $DATA[5];
		$this->print_debug("FtpSocket::ftp_open_data: �������".$ipaddr." ��ʼ���ݴ���...");
		$data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);
		if (!$data_connection)
		{
			$this->print_error("FtpSocket::ftp_open_data: ���ܴ�:  ".$ipaddr.":".$port." ����������");
			$this->print_error("FtpSocket::ftp_open_data: ".$errstr." (".$errno.")");
			return FALSE;
		}
		return $data_connection;
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
		//$PHPSEA_ERROR['FileSocket_Error'] = $str;
	
		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>\n";
			print "<b>FtpSocket Debug --</b>\n";
			print "[<font color=000077>$str</font>]\n";
			print "</font></blockquote>\n";
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
			print "<blockquote><font face=arial size=2 color=green>\n";
			print "<b>FtpSocket Debug --</b>\n";
			print "[<font color=000077>$str</font>]\n";
			print "</font></blockquote>\n";
		}
	}

}//end class
//=============================================================================
?>