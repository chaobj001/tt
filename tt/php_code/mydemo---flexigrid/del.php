<?php
	/*
	���������bug�� ����δ���ƵĹ��ܣ� �������ѽ���ʹ�á�

    ������CSSRAIN.CN - һ���ͳ�����
	
	����BUG������д��ע�͡����н��������ϵ QQ:21021717

    http://www.cssrain.cn
	
	*/
	
	header('Content-Type:text/html;charset=GB2312');
	$arrID = explode("&id=",$_SERVER[QUERY_STRING]);
	/*
	print_r($arrID);
	for ($i=1;$i < count($arrID);$i++){
		echo (int)$arrID[$i];
	}
	*/
	$mysql_server_name = "localhost";
	$mysql_username    = "root";
	$mysql_password    = "123456";
	$mysql_database    = "grid";
	
	for ($i=1;$i < count($arrID);$i++){
	$sql = "DELETE FROM `cssrain` WHERE `cssrain`.`id` = ".(int)$arrID[$i];		
	$conn=mysql_connect( $mysql_server_name, $mysql_username, $mysql_password);	
	mysql_select_db($mysql_database,$conn);
	mysql_query("SET NAMES 'UTF8'");
	$result = mysql_query($sql);
	}
	mysql_close($conn);
	echo '<img src="ok.png" /><br><a href="index.html" class="return">�������</a>';
?>
