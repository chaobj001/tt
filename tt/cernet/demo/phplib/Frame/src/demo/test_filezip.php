<?

//ѹ�����������
//by indraw
//2004/11/4

require('FileZip.class.php') ;
//---------------------------------------------------------

$test = new zip_file("test.zip");
$test->set_options(array('basedir'=>"./",'overwrite'=>1,'level'=>1,'type'=>zip));
$test->add_files("FileCsv.class.php");						//Ҫѹ�����ļ�Ŀ¼
//$test->exclude_files("d/*.swf");			//�������ļ�
//$test->store_files("d/*.txt");				//ֻ���棬��ѹ��
$test->create_archive();

echo "�ɹ�ѹ����test.zip";

//---------------------------------------------------------
/*
$test = new gzip_file("test.gzip");
$test->set_options(array('overwrite'=>1));
$test->extract_files();

echo "�ɹ�չ����test.gzip";

*/
//---------------------------------------------------------
?>