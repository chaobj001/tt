<?php

//������smtp�����ʼ�
//by indraw
//2004/11/15

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require("MailSmtp.class.php");

//-----------------------------------------------------------------------------
$smtpserver = "smtp.163.com";        //SMTP�ʼ�������
$smtpserverport =25;                    //SMTP�˿ں�
$smtpusermail = "indraw@163.com";    //SMTP�����ʼ���ַ
$smtpuser = "indraw";
$smtppass = "iloveyou";                    //SMTP�ʼ�����
$smtpemailto = "wangyzh@dns.com.cn";    //Ŀ���ʼ���ַ
$mailsubject = "Test Subject";            //�ʼ�����
$mailbody = "<h1>This is a test mail</h1>";//�ʼ�����
$mailtype = "HTML";                        //�ʼ����ͷ�ʽ(HTML/TXT)

$smtp = new MailSmtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//�������һ��true�Ǳ�ʾʹ�������֤,����ʹ�������֤.
$smtp->show_debug = TRUE;                //�Ƿ���ʾ���͵ĵ�����Ϣ
$smtp->send_mail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
//-------------------------------------------------------------------------------

?>
