<?php
//����csv�ļ�������
//by indraw
//2004/11/4

//-----------------------------------------------------------------------------
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require('FileDir.class.php') ;

$test_dir = "../metoo";
$rename_dir = "../haha";
$newDir = new FileDir();

echo "<A HREF=\"test_FileDir.php?action=creat\">������Ŀ¼</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=setdir\">��λ����Ŀ¼</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=rename\">�ļ��и���</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=deletedir\">�ļ���ɾ��</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=emptydir\">�ļ������</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=get_dir_info\">�ļ�����Ϣ</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=copy_dir\">copy�ļ���</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=three_dir\">����ת��</A>&nbsp;&nbsp;<A HREF=\"test_FileDir.php?action=dir_size\">�ļ��д�С</A><hr>";

//-----------------------------------------------------------------------------

if($action == "creat"){
	echo "����Ŀ¼��$test_dir<br>";
	$newDir->create_dir($test_dir);
}
elseif($action == "setdir"){
	echo "�ı�Ŀ¼����$test_dir<br>";
	$newDir->set_current_dir($test_dir);
}
elseif($action == "rename"){
	echo "�ļ��С� $test_dir �� ������Ϊ��$rename_dir<br>";
	$newDir->set_current_dir($test_dir);
	$newDir->rename_dir($rename_dir);
}

elseif($action == "deletedir"){
	echo "�ļ��С� $rename_dir ��ɾ������<br>";
	$newDir->set_current_dir($rename_dir);
	$newDir->delete_dir($rename_dir);
}

elseif($action == "emptydir"){
	echo "�ļ��С� $rename_dir ��ɾ������<br>";
	$newDir->set_current_dir($rename_dir);
	$newDir->empty_dir($rename_dir);
}

elseif($action == "get_dir_info"){
	echo "�ļ��С� $rename_dir ����ȡ������Ϣ����<br>";
	$newDir->set_current_dir($rename_dir);
	$newDir->get_dir_info();
	echo "<pre>";
	var_dump($newDir->current_dirs);
	var_dump($newDir->current_files);
	echo "</pre>";
}

elseif($action == "copy_dir"){
	echo "�ļ��С� $rename_dir ��copy����<br>";
	$newDir->set_current_dir($rename_dir);
	$newDir->copy_dir($rename_dir,"../newdir","N");
}
elseif($action == "three_dir"){
	echo "�ļ��С� $rename_dir �������������<br>";
	$newDir->set_current_dir($rename_dir);
	$getDirNum = $newDir->three_dir("Y");
	echo "�ɹ�ת����".$getDirNum;
}

elseif($action == "dir_size"){
	echo "�ļ��С� $rename_dir ����ȡ��С<br>";
	$newDir->set_current_dir($rename_dir);
	$getDirNum = $newDir->get_dir_size($rename_dir);
	echo "�ļ���С��".$newDir->get_file_size($newDir->current_size);
}


//-----------------------------------------------------------------------------
?>