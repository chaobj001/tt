<?
//����imap�����ʼ�
//by indraw
//2004/11/24

	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	require("MailImap.class.php");

//-----------------------------------------------------------------------------

	$imap=new MailImap;
	$imap->hostname="mail.dns.com.cn";
	$imap->port=143;
	$imap->username="wangyzh";
	$imap->userpwd="105080";


	/*
	$imap=new MailImap;
	$imap->hostname="pop.163.com";
	$imap->port=110;
	$imap->username="indraw";
	$imap->userpwd="iloveyou";
	*/
	

//-----------------------------------------------------------------------------
	$imap->open();

	$imap->get_mailbox();
	if ($page=="") $page=0;
	$getMailInfo = $imap->check_mailinfo(10,$page);

	if ($getMailInfo[info]->Nmsgs>0){
		//echo "�����䣺".$mboxinfo->Mailbox."<br>";
		echo $imap->username."@".$imap->hostname."���ռ����ﹲ���ʼ�����".$getMailInfo[info]->Nmsgs."<br>\n";
		echo "δ���ʼ�����".$getMailInfo[info]->Unread."��";
		echo "���ʼ�����".$getMailInfo[info]->Recent." ";
		echo "�ܹ�ռ�ÿռ䣺";
		echo $getMailInfo[info]->Size > 1024 ? sprintf("%.0f kb", $getMailInfo[info]->Size / 1024):$getMailInfo[info]->Size;
		echo "�ֽ�<br>\n";
		$last_page = ceil($getMailInfo[info]->Nmsgs/10);
		$cur_page = $page +1;
		echo "��".$cur_page."ҳ����".$last_page."ҳ��\n";
	}
	else{
		echo "����������û���ʼ���<br><hr>\n";
	}

	echo "<table border=1 width=100% cellpadding=2 cellspacing=0 bordercolorlight=#000080 bordercolordark=#ffffff style=\"font:9pt Tahoma,����\">\n";
	echo "<tr bgcolor=#ffffd8><td width=24>״̬</td><td>������</td><td>����</td><td>ʱ��</td><td>��С</td></tr>\n";

	$getMailList = $getMailInfo['list'];
	for( $i=0; $i<count($getMailInfo['list']); $i++ )
	{
		echo "<tr>\n";
		echo "<td align=center>".$getMailList['newMail'][$i]."</td>\n";
		echo '<td>'.$getMailList['from'][$i].'</td><td><a href="test_mailimap1.php?msg='.$getMailList['msgNum'][$i].'">'.$getMailList['topic'][$i].'</a></td><td width=125>'.$getMailList['date'][$i].'</td><td width=50>'.$getMailList['msgSize'][$i].'</td>';
		echo "</tr>\n";
	}


	echo "</table>\n";
	echo "<table border=0 width=100% cellspacing=4 cellpadding=4><tr>\n";
	if ($page == 0)
		echo "<td>��һҳ</td>\n";
	else
		echo "<td><a href=\"test_mailimap.php?page=0\">��һҳ</a></td>\n";
	if (($prev_page = $page-1) < 0)
		echo "<td>ǰһҳ</td>\n";
	else
		echo "<td><a href=\"test_mailimap.php?page=$prev_page\">ǰһҳ</a></td>\n";

	if (($next_page = $page + 1) >= $last_page)
		echo "<td>��һҳ</td>\n";
	else
		echo "<td><a href=\"test_mailimap.php?page=$next_page\">��һҳ</a></td>\n";
	$last_page --;
	if ( $last_page < $next_page)
		echo "<td>��ĩҳ</td>\n";
	else
		echo "<td><a href=\"test_mailimap.php?page=$last_page\">��ĩҳ</a></td>\n";
		echo "</tr></table>\n";
	@$imap->close();

//-----------------------------------------------------------------------------
?>