<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:FtpPure.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2004/11/1
- ��Ҫ����:ftp������,��������ftp����,��Ҫphp֧��ftpģ��.
- ���л���:php4,��Ҫftpģ��֧��
- �޸ļ�¼:2005/01/28,indraw,�����˼������õĺ���;
---------------------------------------------------------------------
*/

/*
	$ftp = new FtpPure("192.168.0.168","john","111111");
	$ftp->ftp_put("test.rar",1);
	$ftp->ftp_nlist("./");
*/
/*
	FtpPure($server,$user,$pass,$port=21,$timeout=90)    //���ӵ�ftp������
	ftp_connect()                   //ִ������
	ftp_quit()                      //�Ͽ�����

	ftp_pwd()                       //��ȡ��ǰ����Ŀ¼
	ftp_chdir($dirname)             //�л�����Ŀ¼
	ftp_cdup()                      //�л�����Ŀ¼
	ftp_mkdir($dirname)             //����Ŀ¼
	ftp_rmdir($dirname)             //ɾ��Ŀ¼

	ftp_nlist($dir)                 //��ȡĿ¼���ļ��б�
	ftp_file_exists($pathname)      //�ж��ļ��Ƿ����
	ftp_delete($filename)           //ɾ���ļ�
	ftp_rename($orig,$dest)         //�������ļ�
	ftp_get($filename,$mode)        //�����ļ�
	ftp_put($filename, $mode=0)     //�ϴ��ļ�

	ftp_site($strCMD)               //ִ��CMD����
*/

//=============================================================================
class FtpPure 
{

	VAR $show_errors    = true;       //�Ƿ�error
	VAR $show_debug     = true;       //�Ƿ�debug
	//
	VAR $server         = "";         //������ַ
	VAR $port           = 21;         //�˿ں�
	VAR $timeout        = 90;         //��ʱ����
	VAR $user           = "";         //�û���
	VAR $pass            = "";         //����
	VAR $type           = 0;          //����
	VAR $mode           = true;       //����ģʽ�Ƿ��
	//
	VAR $ftpstream      = 0;          //���ӱ�ʶ
	VAR $connected      = false;      //�Ƿ����ӳɹ���ʶ

	/*
	-----------------------------------------------------------
	��������:function FtpPure($server,$user,$pass,$port=21,$timeout=90)
	��Ҫ����:���캯��,ͬʱ���ӵ�ftp����
	����:mixd (������,�û���,����,�˿�,��ʱ);
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function FtpPure($server,$user,$pass,$port=21,$timeout=90) 
	{
		$this->server       = $server;
		$this->user         = $user;
		$this->pass         = $pass;
		$this->port         = $port;
		$this->timeout      = $timeout;
		$this->connected    = $this->ftp_connect();
	}//end func

	/*
	-----------------------------------------------------------
	��������:ftp_connect()
	��Ҫ����:�������ӵ�ftp����������½
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_connect() 
	{
		$res = false;
		$this->ftpstream = @ftp_connect($this->server,$this->port,$this->timeout);
		if ($this->ftpstream)
		{
			$this->print_debug("FtpPure::ftp_connect: ���������ɹ�,���Ե�½���û�: " . $this->user );
			if (@ftp_login($this->ftpstream,$this->user,$this->pass))
			{
				$this->print_debug("FtpPure::ftp_connect: ��½�ɹ�,���ӵ�����: " . $this->server . " ���û�: " . $this->user );
				ftp_pasv($this->ftpstream,$this->mode) ;
				$res = true;
			}
			else
			{
				$this->print_error("FtpPure::ftp_connect: ��½ʧ��" );
				$res = false;
			}
		}
		else
		{
			$this->print_error("FtpPure::ftp_connect: ���� " . $this->server . " ���ܱ��ҵ�");
			$res = false;
		}
		return $res;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_close()
	��Ҫ����:���ԶϿ�ftp����
	����:void
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_quit() 
	{
		if ($this->ftpstream) 
			ftp_close($this->ftpstream);
		$this->print_debug("FtpPure::ftp_quit: === �ɹ��ر� FTP: ".$this->server." ���� ===" . "\r\n" );
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_pwd ()
	��Ҫ����:���ص�ǰĿ¼��
	����:void
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_pwd() 
	{
		if ($res = @ftp_pwd($this->ftpstream))
			$this->print_debug("FtpPure::ftp_rmdir: �ɹ����ص�ǰĿ¼�� " . $res );
		else
			$this->print_error("FtpPure::ftp_rmdir: ���ܵ�ǰĿ¼�� " . $res );
		return $res;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_chdir($dirname)
	��Ҫ����:�� FTP ���������л���ǰĿ¼
	����:string (�л�����Ŀ¼����)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_chdir($dirname)
	{
		if ($res = @ftp_chdir($this->ftpstream,$dirname))
			$this->print_debug("FtpPure::ftp_chdir: �л�Ŀ¼�ɹ�,��ǰĿ¼Ϊ: " . $dirname );
		else
			$this->print_error("FtpPure::ftp_chdir: �л�Ŀ¼ʧ��,�����л���: " . $dirname );
		return $res ;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_chup($dirname)
	��Ҫ����:�л�����ǰftpĿ¼�ĸ�Ŀ¼
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_cdup() 
	{
		if ($res = @ftp_cdup($this->ftpstream))
			$this->print_debug("FtpPure::ftp_chup: �ɹ��л�����Ŀ¼" );
		else
			$this->print_error("FtpPure::ftp_chup: �л�����Ŀ¼ʧ��" );
		return $res ;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_mkdir($dirname)
	��Ҫ����:��ftp�������Ͻ���Ŀ¼
	����:string (Ŀ¼��)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_mkdir($dirname) 
	{
		if( $this->ftp_file_exists($dirname) )
			$this->print_debug("FtpPure::ftp_mkdir: Զ��Ŀ¼�Ѿ�����,����Ҫ����: " . $dirname );
		elseif ($res = @ftp_mkdir($this->ftpstream,$dirname))
			$this->print_debug("FtpPure::ftp_mkdir: �ɹ�����Ŀ¼: " . $dirname );
		else
			$this->print_error("FtpPure::ftp_mkdir: û�гɹ�����Ŀ¼: " . $dirname );
		return $res;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_rmdir($dirname)
	��Ҫ����:��ftp��������ɾ��Ŀ¼
	����:string (Ŀ¼��)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_rmdir($dirname) 
	{
		if ($res = @ftp_rmdir($this->ftpstream,$dirname))
			$this->print_debug("FtpPure::ftp_rmdir: �ɹ�ɾ��Ŀ¼: " . $dirname );
		else
			$this->print_error("FtpPure::ftp_rmdir: ����ɾ��Ŀ¼: " . $dirname );
		return $res;
	}//end func

	
	/*
	-----------------------------------------------------------
	��������: ftp_nlist($dirname)
	��Ҫ����:��ftp��������һ��Ŀ¼�µ��ļ�
	����:string (Ŀ¼��)
	���:array (�ļ�������)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_nlist($dir)
	{
		$l = @ftp_nlist($this->ftpstream,$dir);
		if ($l==false)
		{
			$this->print_error("FtpPure::ftp_nlist: ����ȡ���ļ��б�: ".$dir );
		}
		else 
		{
			$this->print_debug("FtpPure::ftp_nlist: �ļ��б�: ".$dir );
			if ( $this->show_debug )
			{
				echo("<blockquote><pre>");
				var_dump($l);
				echo("</blockquote></pre>");
			}
		}
		Return $l;
	}//end func

	/*
	-----------------------------------------------------------
	��������:ftp_file_exists($pathname)
	��Ҫ����:�ж��ļ��Ƿ����
	����:string (�ļ���)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_file_exists($pathname)
	{
		if (!($remote_list = $this->ftp_nlist($this->ftp_pwd() )))
		{
			$this->print_error("FtpPure::ftp_file_exists: ���ܻ�ȡԶ��ftp�������ļ��б�\n");
			return -1;
		}
		if( !$remote_list )
		{
			$this->print_debug("FtpPure::ftp_file_exists: Զ���ļ�: ".$pathname." ������\n");
			return false;
		}
		reset($remote_list);
		while (list(,$value) = each($remote_list))
		{
			if ($value == $pathname)
			{
				$this->print_debug("FtpPure::ftp_file_exists: Զ���ļ�: ".$pathname." ����\n");
				return true;
			}
		}
		$this->print_debug("FtpPure::ftp_file_exists: Զ���ļ�: ".$pathname." ������\n");
		return false;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_delete($dirname)
	��Ҫ����:��ftp������ɾ��һ���ļ�
	����:string (�ļ���)
	���:boolean 
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_delete($filename) 
	{
		$res = @ftp_delete($this->ftpstream,$filename);
		if ($res) 
			$this->print_debug("FtpPure::ftp_delete: �ļ�: " . $filename . " ���ɹ�ɾ��" );
		else 
			$this->print_error("FtpPure::ftp_delete: ɾ���ļ�: " . $filename . " ʧ��" );
		return $res;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_rename($dirname)
	��Ҫ����:��ftp�������޸��ļ���
	����:mixed (ԭʼ�ļ�,������)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_rename($orig,$dest)
	{
		$res = @ftp_rename($this->ftpstream,$orig,$dest);
		if ($res)
			$this->print_debug("FtpPure::ftp_rename: �������ļ�: " . $orig ." Ϊ: " .$dest." �ɹ�" );
		else
			$this->print_error("FtpPure::ftp_rename: �������ļ�: " . $orig ." Ϊ: " .$dest." ʧ��" );
		return $res;
	}//end func

	
	/*
	-----------------------------------------------------------
	��������: ftp_put($dirname)
	��Ҫ����:��ftp���������ϴ��ļ�
	����:mixed (�ļ���,�ϴ�ģʽ)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_put($filename,$remotefile, $mode=0) 
	{
		$res = false;
		switch ($mode)
		{
			case 0:
				$m = FTP_BINARY;
			break;
			case 1:
				$m = FTP_ASCII;
			break;
		}
		$res = @ftp_put($this->ftpstream,$filename,$remotefile,$m);
		if ($res)
			$this->print_debug("FtpPure::ftp_put: �ļ�:" . $filename . " ���ɹ��ϴ�" );
		else 
			$this->print_error("FtpPure::ftp_put: �ļ�:" . $filename . " �ϴ�ʧ��,�ļ����ܴ��ڻ�û���ϴ�Ȩ��" );
		return $res;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_get($dirname,$mode)
	��Ҫ����:��ftp����������
	����:mixed (�ļ���,�ϴ�ģʽ)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_get($filename,$local_filename,$mode)
	{
		$res = false;
		switch ($mode)
		{
			case 0:
				$m = FTP_BINARY;
			break;
			case 1:
				$m = FTP_ASCII;
			break;
		}
		$res = @ftp_get($this->ftpstream,$local_filename,$filename,$m);
		if ($res)
			$this->print_debug("FtpPure::ftp_get: �ɹ������ļ�: " . $filename );
		else
			$this->print_error("FtpPure::ftp_get: �����ļ�: " . $filename . " ʧ��" );
		return $res;
	}//end func

	/*
	-----------------------------------------------------------
	��������: ftp_site($strCMD)
	��Ҫ����:��ftp������ִ��cmd����
	����:$strCMD (�����ַ���)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ftp_site($strCMD) 
	{
		if (@ftp_site($this->ftpstream, $strCMD))
			$this->print_debug("FtpPure::ftp_site: �ɹ�ִ������:  " . $strCMD ."");
		else
			$this->print_error("FtpPure::ftp_site: ִ������: " . $strCMD . " ʧ��" );
	}//end func

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
		$PHPSEA_ERROR['FilePure_Error'] = $str;
		//�ж��Ƿ���ʾerror���..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>\n";
			print "<b>FtpPure Error --</b>\n";
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
		//�ж��Ƿ���ʾdebug���...
		if ( $this->show_debug )
		{
			print "<blockquote><font face=arial size=2 color=green>\n";
			print "<b>FtpPure Debug --</b>\n";
			print "[<font color=000077>$str</font>]\n";
			print "</font></blockquote>\n";
		}
	}//end func

}//end class
//=============================================================================
?>
