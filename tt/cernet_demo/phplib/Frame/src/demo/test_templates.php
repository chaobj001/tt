<?
//��smartyʵ�ֲ���
//by indraw
//2004/11/16�糿

include_once( "./templates.inc.php" );

//-----------------------------------------------------------------------------
//��ʼ��
$PS["template"] = "default";
$PS["cache"]    = "./templates_c";

//��ʾ��������
$PS["DATA"]["hello"] = "���";

//ѭ������
for($i=0; $i<5; $i++){
	$getNum["getNewNum"] = $i+100;
	$PS["DATA"]["TEST2"][] = $getNum;
}
//echo "<pre>";
//var_dump($PS["DATA"]["TEST2"]);
//echo "</pre>";
//�����ж�
$PS["DATA"]["testIf"] = "ok";

//���԰���
include get_template("index");




//-----------------------------------------------------------------------------
?>