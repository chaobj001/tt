<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:Session.class.php
- ԭ����:Steen Rabol
- �޸���:indraw
- ��д����:2004/11/1
- ��Ҫ����:��session�洢�����ݿ��У�����������б���ϣ��������Ч�ʡ�
- ���л���:�����ݿ�����������
- �޸ļ�¼:2004/11/1��indraw��������
---------------------------------------------------------------------
*/

/*
	$db = new DBMySQL("root", "", "test", "localhost");
	$Session = new Session($db,"MySessionName",1,true,false,100);
	DROP TABLE IF EXISTS SESS_Value;
	CREATE TABLE SESS_Value (
					sessionid varchar(32) NOT NULL default '',
					sessionname varchar(254) default NULL,
					sessionexpire int(11) NOT NULL default '0',
					sessiondata text NOT NULL,
					PRIMARY KEY  (sessionid)
				) TYPE=MyISAM;
*/
/*
	Session()
	SetIni()
	Db($p_db = null)
	SessionName($p_session_name = null)
	SessionExpire($p_expire = null)
	SessionCookies($p_cookies = null)
	SessionCookiesOnly($p_cookiesonly = null)
	SessionProbability($p_session_probability = null)
	-------------------------------------------------
	open( $a, $b )
	close()
	read( $id )
	destroy( $id )
	gc( $a )
	cleanup()
*/

//=============================================================================
//�������ʹ�÷�����ο�php�ֲ��е�session����
class Session
{
	//������
	var $i_db                   = null;
	var $i_session_name         = null;
	var $i_session_expire       = 1; // Minutes
	var $i_session_cookies      = true;
	var $i_session_cookies_only = false;
	var $i_session_probability  = 0;
	var $i_error                = null;

	/*
	//��ʼ��������:
	//p_db, ��Ч�����ݿ����Ӷ���
	//p_session_name, session����
	//p_session_expire, session����Чʱ��
	//p_session_cookies, ������ܣ���ôʹ��cookie
	//p_sesion_cookies, ֻʹ��cookie
	//p_session_probability, ָ��ÿ���������ʱ�������ճ���ִ�еİٷָ��ʡ�������1-100��
	*/
	function Session(
		$p_db,
		$p_session_name         = "Session",
		$p_session_expire       = 1,
		$p_session_cookies      = true,
		$p_session_cookies_only = false,
		$p_session_probability  = 0
		)
	{
		$this->Db($p_db);
		$this->SessionName($p_session_name);
		$this->SessionExpire($p_session_expire);
		$this->SessionCookies($p_session_cookies);
		$this->SessionCookiesOnly($p_session_cookies_only);
		$this->SessionProbability($p_session_probability);
		$this->SetIni();

		$this->cleanup();
		session_set_save_handler(
			array( &$this, 'open' ),
			array( &$this, 'close' ),
			array( &$this, 'read' ),
			array( &$this, 'write' ),
			array( &$this, 'destroy' ),
			array( &$this, 'gc' )
		);
	}

	function SetIni()
	{
		ini_set('session.save_handler', 'user' );
		ini_set('session.name',             $this->SessionName() );
		ini_set('session.use_cookies',      $this->SessionCookies() );
		ini_set('session.gc_maxlifetime',   $this->SessionExpire());
		ini_set('session.gc_probability',   $this->SessionProbability());
		ini_set('session.use_only_cookies', $this->SessionCookiesOnly());
	}
//-----------------------------------------------------------------------------
//sessionȫ����������

	function Db($p_db = null)
	{
		if(!is_null($p_db))
			$this->i_db = $p_db;
			$this->i_db->debug_all = false;
			$this->i_db->save_log = false;

	}

	function SessionName($p_session_name = null)
	{
		if(!is_null($p_session_name))
		{
			$this->i_session_name = $p_session_name;
		}

		return $this->i_session_name;
	}

	function SessionExpire($p_expire = null)
	{
		if(!is_null($p_expire))
		{
			$this->i_session_expire = $p_expire;
		}

		return $this->i_session_expire;
	}

	function SessionCookies($p_cookies = null)
	{
		if(!is_null($p_cookies))
		{
			$this->i_session_cookies = $p_cookies;
		}

		return $this->i_session_cookies;
	}

	function SessionCookiesOnly($p_cookiesonly = null)
	{
		if(!is_null($p_cookiesonly))
		{
			$this->i_session_cookies_only = $p_cookiesonly;
		}

		return $this->i_session_cookies_only;
	}

	function SessionProbability($p_session_probability = null)
	{
		if(!is_null($p_session_probability))
		{
			$this->i_session_probability = $p_session_probability;
		}

		return $this->i_session_probability;
	}

//-----------------------------------------------------------------------------
//session�ľ���д�����ݿⲿ��
	function open( $a, $b )
	{
		return true;
	}

	function close()
	{
		return true;
	}

	function read( $id )
	{
		$retval = false;
		$sql    = "select * from SESS_Value where sessionname='" . $this->SessionName() . "' and sessionid='$id'";
		$dbrow  = $this->i_db->get_row($sql);
		if(!is_null($dbrow))
		{
			$retval = $dbrow->sessiondata;
		}
		return $retval;
	}

	function write( $id, $data )
	{
		$seconds    = $this->SessionExpire() * 60;
		$expires    = time() + $seconds;
		$sql        = "SELECT sessionid FROM SESS_Value WHERE sessionname='" . $this->SessionName() . "' and sessionid= '" . session_id() . "'";

		if($this->i_db->get_var($sql) !== $id)
		{
			$sql = "INSERT INTO SESS_Value VALUES( '" . $id . "', '" . $this->SessionName() . "','" . $expires . "', '" . $data . "')";
		}
		else
		{
			$sql = "UPDATE SESS_Value SET sessionexpire= '" . $expires . "', sessiondata= '" . $data . "',sessionname='" . $this->SessionName() . "' WHERE sessionid = '" . $id . "' AND sessionexpire > " . time();
		}
		$this->i_db->query($sql);
		return true;
	}

	function destroy( $id )
	{
		$sql    = "DELETE FROM SESS_Value WHERE sessionname='" . $this->SessionName() . "' and sessionid = '" . $id . "'";
		if ( isset( $_COOKIE[$this->SessionName()]))
		{
			unset( $_COOKIE[$this->SessionName()] );
		}
		return $this->i_db->query($sql);
	}
	function gc( $a )
	{
		$sql    = "DELETE FROM SESS_Value WHERE sessionname ='" . $this->SessionName() . "' and sessionexpire < '" . time() . "'";
		return $this->i_db->query($sql);
	}

	function cleanup()
	{
		$sql    = "delete from SESS_Value where sessionid='" . session_id() ."' and sessionname='" . $this->SessionName() . "' and sessionexpire < '" .  (time() + ( $this->SessionExpire() * 60)) . "'";
		return $this->i_db->query($sql);
	}

}//end class
//=============================================================================
?>