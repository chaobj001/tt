<?php
/*
* �ļ����ƣ�index.php
* ժ    Ҫ���ֱ���д�����ɹ���
* ��    �ߣ�����δ��[����]
* ��    �ͣ�http://blog.s135.com
* ��    ʾ��http://www.s135.com/font/
* ��    ����1.0
* ʱ    �䣺2007-05-05
*/
$text = $_POST["text"];
if ($text != "")
{
    $text = explode("\r\n", $text);
    $text_temp = "";
    $t = 0;
    foreach ($text as $key => $value)
    {
        $text_split = str_split($value, 50);
        foreach ($text_split as $key_split => $value_split)
        {
            $text_temp[$t] = $value_split;
            $t++;
        }
    }
    $text = $text_temp;

    $text_count = count($text);

    $fontname = "FZJLJT.FON";
    $im = imagecreate(600, $text_count * 29);
    $white = ImageColorAllocate($im, 255, 255, 255);
    $black = ImageColorAllocate($im, 0, 0, 0);
    $red = ImageColorAllocate($im, 255, 0, 0);

    for ($n = 0; $n < $text_count; $n++)
    {
        $value = $text[$n];
        $value_length = strlen($value);
        $value_count = 0;
        for ($i = 0; $i < $value_length; $i++)
        {
            if (ord($value{$i}) > 127)
            {
                $value_count++;
            }
        }
        if ($value_count % 2 != 0)
        {
            //$text[$n] = substr($value, 0, $value_length - 1);
            //$text[$n + 1] = substr($value, -1, 1) . $text[$n + 1];
            $text[$n] = $value . substr($text[$n + 1], 0, 1);
            $text[$n + 1] = substr($text[$n + 1], 1, strlen($text[$n + 1]) - 1);
        }
    }
    $text = implode("\r\n", $text);
    for ($n = 0; $n <= 1; $n++)
    {
        ImageTTFText($im, 18, 0, 2, 30, $black, $fontname, iconv("GBK", "UTF-8", $text));
    }
    $dir = "images/";
    if (is_dir($dir))
    {
        if ($dh = opendir($dir))
        {
            while (($file = readdir($dh)) !== false)
            {
                if (filetype($dir . $file) == "file")
                {
                    unlink($dir . $file);//ɾ���ļ�
                }
            }
            closedir($dh);
        }
    }
    $file_name = $dir . md5($text) . ".png";
    ImagePng($im, $file_name);
    ImageDestroy($im);
}
else
{
    $file_name = "welcome.png";
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>�ֱ���д�����ɹ��� V1.0</title>
</head>
<body bgcolor="#000000">
<center>
<form id="form1" name="form1" method="post" action="">
    <label>
	<font color="#FFFFFF">�ֱ���д�����ɹ��� V1.0 by ����δ��[����]</font><BR />
    </label>
    <label>
    <textarea name="text" cols="82" rows="15" id="text"></textarea>
    <BR />
    </label>
    <label>
    <input name="�ύ" type="submit" value="���ɸֱ���д��" /><BR />
    </label>	
</form>
<font color="#FFFFFF" size="2">������ͼ�ϵ������Ҽ���ѡ��ͼƬ���Ϊ�������ɵĸֱ���д��PNGͼƬ���浽���ء���վ�������档</font><br>
<img src="<?= $file_name ?>" border="0">
</center>
</body>
</html>
