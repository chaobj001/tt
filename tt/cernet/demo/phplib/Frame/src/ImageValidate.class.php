<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:ImageValidate.class.php
- ԭ����:١����
- ������:indraw
- ��д����:2004/11/11
- ��Ҫ����:ͼƬ��֤����
- ���л���:��ҪGD��֧�֣��汾���Ϊ2.0���ϣ����磺2.0.28
- �޸ļ�¼:2004/11/11��indraw��������
---------------------------------------------------------------------
*/

/*
if($_GET["show"] == "true")
{
	$im = new ImageValidate;
	$im->show();
	echo($this->Code);
}
	ImageValidate()    //
	create_image()     //����������֤ͼƬ
	transfer_code()    //����session
	show()             //��ʾ��֤��ͼƬ
*/
//=============================================================================
class ImageValidate
{

	var $x;                 //������ͼƬ�е�x����
	var $y;                 //������ͼƬ�е�y����
	var $numChars;          //���ɼ�λ����ͼƬ
	var $Code;              //��֤������
	var $Width;             //ͼƬ���
	var $Height;            //ͼƬ�߶�
	var $BG;                //������ԭɫ
	var $colTxt;            //����ԭɫ
	var $colBorder;         //ͼƬ�߿���ԭɫ
	var $numCirculos;       //��������������
	//var $vCode;             //session��������

	//���캯������ʼֵ
	function ImageValidate($x = "",$y = "6",$numChars = "4",$Code = "",$Width = "80",$Height = "25",$BG = "255 255 255",$colTxt = "0 0 0 0",$colBorder = "100 100 100",$numCirculos = "800"/*,$vCode = "vCode"*/)
	{
		$this->x = $x;
		$this->y = $y;
		$this->numChars = $numChars;
		$this->Code = $Code;
		$this->Width = $Width;
		$this->Height = $Height;
		$this->BG = $BG;
		$this->colTxt = $colTxt;
		$this->Border = $colBorder;
		$this->numCirculos = $numCirculos;
		//$this->vCode = $vCode;
	}

	/*
	-----------------------------------------------------------
	��������:create_image()
	��Ҫ����:����������֤ͼƬ
	����:void
	���:source (�ȴ�д��ͼƬ��Դ)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function create_image()
	{
		//�½�һ�����ڵ�ɫ���ͼ��
		$im = imagecreate ($this->Width, $this->Height) or die ("Cannot Initialize new GD image stream");

		//��ȡ��ԭɫ
		$colorBG = explode(" ", $this->BG);
		$colorBorder = explode(" ", $this->Border);
		$colorTxt = explode(" ", $this->colTxt);

		//������ͼƬд��ͼƬ
		$imBG = imagecolorallocate ($im, $colorBG[0], $colorBG[1], $colorBG[2]);

		//���߿���ɫд��ͼƬ
		$Border = ImageColorAllocate($im, $colorBorder[0], $colorBorder[1], $colorBorder[2]);
		$imBorder = ImageRectangle($im, 0, 0, $this->Width-1,$this->Height-1, $Border);

		//��ͼƬ��ɫд��ͼƬ
		$imTxt = imagecolorallocate ($im, $colorTxt[0], $colorTxt[1], $colorTxt[2]);

		//�����800
		for($i = 0; $i < $this->numCirculos; $i++)
		{
			$imPoints = imagesetpixel($im, mt_rand(0,80), mt_rand(0,80), $Border);
		}

		//�������д��ͼƬ
		//$this->Code = "";
		for($i = 0; $i < $this->numChars; $i++)
		{
			$this->x = 21 * $i + 5;

			mt_srand((double) microtime() * 1000000*getmypid());
			$this->Code.= (mt_rand(0, 9));
			$putCode = substr($this->Code, $i, "1");
			$Code = imagestring($im, 5, $this->x, $this->y, $putCode,$imTxt);
		}

		return $im;
	}

	/*
	-----------------------------------------------------------
	��������:transfer_code()
	��Ҫ����:����session
	����:void
	���:void (����session����)
	�޸���־:------
	-----------------------------------------------------------
	*/
	/*
	function transfer_code()
	{
		$this->create_image();
		session_start();
		session_register($this->vCode);
		$_SESSION[$this->vCode] = $this->Code;
	}
	*/

	/*
	-----------------------------------------------------------
	��������:show()
	��Ҫ����:��ʾ��֤��ͼƬ
	����:void
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function show()
	{
		header("Content-type:image/png");
		$sImages = $this->create_image();
		Imagepng($sImages);
		Imagedestroy($sImages);
	}


}//end class
//=============================================================================
?>
