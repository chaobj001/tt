<?
//ͼƬˮӡ�����
//by indraw
//2004/11/18

error_reporting(E_ERROR | E_WARNING | E_PARSE);
include ('ImageWatermark.class.php');
//---------------------------------------------------------
//echo("test:");
//echo(utf8_encode("��ð�"));
//ʹ�÷���:
$img = new Watermark();
$img->wm_text = "�����fadsfa";
$img->wm_text_font = "simkai.ttf";
$img->wm_image_name="./aaa.jpg"; 
$img->wm_text_size="40";
$img->create("./test.jpg"); 

//---------------------------------------------------------
?>