<?php

//sqlite���ݿ����
//by indraw
//2004/11/3

//---------------------------------------------------------
	//��������ʼ��
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	include_once("DBSQLite.class.php");
	$db = new DBSQLite( "./", "DB" );	//��ʼ�����ݿ������

//---------------------------------------------------------

	//����һ�����ݿ�]
	//$db->query("CREATE TABLE test_table ( ColumnA INTEGER PRIMARY KEY, ColumnB text, test1 varchar(10), test2 int(12))");
	//$db->debug();

	//�����ݿ��в�������
	for($i=0;$i<3;++$i)
	{
		//$db->query("INSERT INTO test_table (ColumnB,test1,test2) VALUES ('".md5(microtime())."','168','".time()."')");
		//$db->debug();

	}
	//����һ�����ڵ�����
	$my_count = $db->get_var("SELECT count(*) FROM test_table");
	$db->debug();

	//���һ���������е�����
	$my_tables = $db->get_results("SELECT * FROM test_table");
	$db->debug();

	//����һ�м�¼
	$db->query("UPDATE test_table SET test1='���̨��' WHERE ColumnA ='2'");
	$db->debug();

	//���һ���������е�����
	$my_tables = $db->get_results("SELECT * FROM test_table");
	$db->debug();

	//��ʾ�ֶ���Ϣ
	$my_col = $db->get_col_info("name");
	$db->vardump($my_col);

	//ɾ�����м�¼
	//$db->query("DELETE FROM test_table");
	//$db->debug();


//---------------------------------------------------------
?>