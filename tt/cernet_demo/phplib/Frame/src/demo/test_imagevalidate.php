<?php

/*
 *�����session��
 */

session_start();

?>
<HTML>
<HEAD>
<TITLE> test </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</HEAD>

<BODY>
<form action ="test_imagevalidate.php" method = "post">

<input type = text name = "number">
<img src = "ImageValidate.class.php?show=true"><br>

<input type = "submit" name = "submit">

</form>

<?php
if(isset($_POST["submit"])):
	if($_POST["number"] != $_SESSION["vCode"] || empty($_POST["number"]))
	{
		echo "<font color=red>У���벻��ȷ!</font>";
	}
	else
	{
		echo"��֤��ͨ����";
	}
endif;

?>
</BODY>
</HTML>