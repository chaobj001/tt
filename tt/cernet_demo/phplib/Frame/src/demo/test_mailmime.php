<?
//����mail�����ʼ�
//by indraw
//2004/11/15

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$from = "admin@dns.com.cn";
$to = "wangyzh@dns.com.cn";
$attachment = "aaa.rar";
$image = "bbb.jpg";

//-----------------------------------------------------------------------------
/*
* ���ı�
*/
if ($_GET[mimemail] == 1){

	$subject = " MIME �ʼ�: ���ı�";
	$text = "����һ�� MIME �ʼ�:\n\n- ���ı�";

	include ('MailMime.class.php');
	$mimemail = new MailMime();

	$mimemail->new_mail($from, $to, $subject, $text);
	//$mimemail->set_cc("fengwei@dns.com.cn", "С��");

	if ($mimemail->send())
   	echo "MIME�ʼ��Ѿ����ɹ�����\n\n";
	else
   	echo "����һ�������ʼ�û�б����͡�\n\n";
	echo "<br><br><a href='{$_SERVER[PHP_SELF]}'>����</a>";
}

/**
 * ���ı�
 * html
 */
elseif ($_GET[mimemail] == 2){

	$subject = "MIME �ʼ�: ���ı� + HTML";
	$text = "����һ�� MIME �ʼ�:\n\n- ���ı�\n- HTML";
	$html = "<HTML><HEAD></HEAD><BODY>������һ�� <b>MIME</b> �ʼ�:<BR><BR>- ���ı�</BR>- HTML</BODY></HTML>";

	include ('MailMime.class.php');
	$mimemail = new MailMime();

	$mimemail->new_mail($from, $to, $subject, $text, $html);

	if ($mimemail->send())
   	echo "MIME�ʼ��Ѿ����ɹ�����\n\n";
	else
   	echo "����һ�������ʼ�û�б����͡�\n\n";
	echo "<br><br><a href='{$_SERVER[PHP_SELF]}'>����</a>";
}

/**
 * ���ı�
 * ����
 */
elseif ($_GET[mimemail] == 3){

	$subject = "MIME Mail ����: ���ı� + ����";
	$text = "����һ�� MIME �ʼ�:\n\n- ���ı�\n- ����";

	include ('MailMime.class.php');
	$mimemail = new MailMime();

	$mimemail->new_mail($from, $to, $subject, $text);
	$mimemail->add_attachment($attachment, "file.tar.gz");

	if ($mimemail->send())
   	echo "MIME�ʼ��Ѿ����ɹ�����\n\n";
	else
   	echo "����һ�������ʼ�û�б����͡�\n\n";
	echo "<br><br><a href='{$_SERVER[PHP_SELF]}'>����</a>";
}


/**
 * ���ı�
 * HTML
 * ����
 */
elseif ($_GET[mimemail] == 4){

	$subject = "MIME Mail ����: ���ı� + HTML + ����";
	$text = "����һ�� MIME �ʼ�:\n\n- ���ı�\n- HTML\n- ����";
	$html = "<HTML><HEAD></HEAD><BODY>����һ��<b>MIME</b>�ʼ�:<BR><BR>- ���ı�</BR>- HTML</BR>- ����</BODY></HTML>";

	include ('MailMime.class.php');
	$mimemail = new MailMime();

	$mimemail->new_mail($from, $to, $subject, $text, $html);
	$mimemail->add_attachment($attachment, "file.tar.gz");
	
		if ($mimemail->send())
		echo "MIME�ʼ��Ѿ����ɹ�����\n\n";
		else
		echo "����һ�������ʼ�û�б����͡�\n\n";
		echo "<br><br><a href='{$_SERVER[PHP_SELF]}'>����</a>";

}


/**
 * ���ı�
 * HTML
 * Ƕ��ʽͼƬ
 */

elseif ($_GET[mimemail] == 5){

	$subject = "MIME Mail ����: ���ı� + HTML + Ƕ��ʽͼƬ";
	$text = "����һ�� MIME �ʼ�:\n\n- ���ı�\n- HTML\n- Ƕ��ʽͼƬ";
	$html = "<HTML><HEAD></HEAD><BODY>����һ��<b>MIME</b>�ʼ�:<BR><BR>- ���ı�</BR>- HTML</BR>- Ƕ��ʽͼƬ<br><br><img src='image.jpg' border='0'></BODY></HTML>";

	include ('MailMime.class.php');
	$mimemail = new MailMime();

	$mimemail->new_mail($from, $to, $subject, $text, $html);
	$mimemail->add_attachment($image, "image.jpg");

	if ($mimemail->send())
   	echo "MIME�ʼ��Ѿ����ɹ�����\n\n";
	else
   	echo "����һ�������ʼ�û�б����͡�\n\n";
	echo "<br><br><a href='{$_SERVER[PHP_SELF]}'>����</a>";
}


/**
 * ���ı�
 * HTML
 * Ƕ��ʽͼƬ
 * ����
 */
elseif ($_GET[mimemail] == 6){

	$subject = "MIME Mail ����: ���ı� + HTML + Ƕ��ʽͼƬ + ����";
	$text = "����һ�� MIME �ʼ�:\n\n- ���ı�\n- HTML\n- Ƕ��ʽͼƬ\n- ����";
	$html = "<HTML><HEAD></HEAD><BODY>����һ��<b>MIME</b>�ʼ�:<BR><BR>- ���ı�</BR>- HTML</BR>- Ƕ��ʽͼƬ</BR>- ����<br><br><img src='image.gif' border='0'></BODY></HTML>";

	include ('MailMime.class.php');
	$mimemail = new MailMime();

	$mimemail->new_mail($from, $to, $subject, $text, $html);
	$mimemail->add_attachment($image, "image.gif");
	$mimemail->add_attachment($attachment, "file.tar.gz");

	if ($mimemail->send())
   	echo "MIME�ʼ��Ѿ����ɹ�����\n\n";
	else
   	echo "����һ�������ʼ�û�б����͡�\n\n";
	echo "<br><br><a href='{$_SERVER[PHP_SELF]}'>����</a>";
}


/**
 * �˵�
 */
else {
	echo "
	<HTML><HEAD>
	<title>MIME Mail ����</title>
	</HEAD><BODY>
	<h1>MIME Mail ����</h1>
	<h3><a href='{$_SERVER[PHP_SELF]}?mimemail=1'>���ı�</a></h3>
	<h3><a href='{$_SERVER[PHP_SELF]}?mimemail=2'>���ı� + HTML</a></h3>
	<h3><a href='{$_SERVER[PHP_SELF]}?mimemail=3'>���ı� + ����</a></h3>
	<h3><a href='{$_SERVER[PHP_SELF]}?mimemail=4'>���ı� + HTML + ����</a></h3>
	<h3><a href='{$_SERVER[PHP_SELF]}?mimemail=5'>���ı� + HTML + Ƕ��ʽͼƬ</a></h3>
	<h3><a href='{$_SERVER[PHP_SELF]}?mimemail=6'>���ı� + HTML + Ƕ��ʽͼƬ + ����</a></h3>
	</BODY></HTML>
	";
}

?>
