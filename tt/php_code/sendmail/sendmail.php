<?php

require_once ('email.class.php');
//##########################################
$smtpserver = "smtp.163.com";//SMTP������
$smtpserverport =25;//SMTP�������˿�
$smtpusermail = "3345191837@163.com";//SMTP���������û�����
$smtpemailto = "wangchao@ninetowns.com";//���͸�˭
$smtpuser = "3345191837";//SMTP���������û��ʺ�
$smtppass = "10562782800";//SMTP���������û�����
$mailsubject = "wangchao�����ʼ�ϵͳ";//�ʼ�����
$mailbody = "<h1> ����һ�����Գ��� 1111111111111111111 </h1>";//�ʼ�����
$mailtype = "HTML";//�ʼ���ʽ��HTML/TXT��,TXTΪ�ı��ʼ�
##########################################
$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//�������һ��true�Ǳ�ʾʹ�������֤,����ʹ�������֤.
$smtp->debug = true;//�Ƿ���ʾ���͵ĵ�����Ϣ
$smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);

?>