<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:ImageWatermark.class.php
- ԭ����:JackMing
- ԭ����:indraw
- ��д����:2004/11/11
- ��Ҫ����:����ͼƬˮӡ�������༯��
- ���л���:��ҪGD��֧�֣��汾���Ϊ2.0���ϣ����磺2.0.28
- �޸ļ�¼:2004/11/11��indraw��������
---------------------------------------------------------------------
*/

/*
	$img = new Watermark();
	$img->wm_text = "hello are you vindraw ?";
	$img->wm_text_font = "CHILLERN.TTF";
	$img->wm_image_name="./aaa.gif"; 
	$img->create("./test.jpg"); 
*/
/*
	create($filename="")              //����ˮӡͼƬ
	create_image($type,$img_name)     //�����ļ��������ʹ���ͼƬ,����gif,jpg,png,���� " ./mouse.jpg"
	get_pos($sourcefile_width,$sourcefile_height,$pos,$wm_image="")
	                    //����Դͼ��ĳ�����λ�ô��룬ˮӡͼƬid�����ɰ�ˮӡ���õ�Դͼ���е�λ��
	gb2utf8($gb)                      //ָ��������ת��ΪUTF-8��ʽ��������Ӣ�Ļ��
	u2utf8($c)                        //ָ��������ת��ΪUTF-8��ʽ��������Ӣ�Ļ��
	get_type($img_name)               //���ͼƬ�ĸ�ʽ������jpg,png,gif
*/

//=============================================================================
Class Watermark
{
	var $src_image_name = "";				//����ͼƬ���ļ���(�������·����)
	var $jpeg_quality = 90;					//jpegͼƬ����
	var $save_image_file = '';				//����ļ���

	var $wm_image_name = "";				//ˮӡͼƬ���ļ���(�������·����)
	var $wm_image_pos = 3;					//ˮӡͼƬ���õ�λ��
	var $wm_image_transition = 50;			//ˮӡͼƬ��ԭͼƬ���ں϶� (1=100)

	var $wm_text = "";						//ˮӡ����(֧����Ӣ���Լ�����\r\n�Ŀ�������)
	var $wm_text_size = 20;					//ˮӡ���ִ�С
	var $wm_text_angle = 4;					//ˮӡ���ֽǶ�,���ֵ������Ҫ����
	var $wm_text_pos = 0;					//ˮӡ���ַ���λ��
	var $wm_text_font = "";					//ˮӡ���ֵ�����
	var $wm_text_color = "#FFCC33";			//ˮӡ�������ɫֵ
	// 0 = middle 1 = top left 2 = top right 3 = bottom right 4 = bottom left 5 = top middle 
	// 6 = middle right        7 = bottom middle              8 = middle left

	/*
	-----------------------------------------------------------
	��������: create($filename="")
	��Ҫ����:����ˮӡͼƬ
	����:string
	���:ֱ������������
	�޸���־:------
	-----------------------------------------------------------
	*/
	function create($filename="")
	{
		if ($filename) 
			$this->src_image_name = strtolower(trim($filename));
		$src_image_type = $this->get_type($this->src_image_name);
		$src_image = $this->create_image($src_image_type,$this->src_image_name);
		if (!$src_image) 
			return;
		$src_image_w=ImageSX($src_image);
		$src_image_h=ImageSY($src_image);

		if ($this->wm_image_name){
			$this->wm_image_name = strtolower(trim($this->wm_image_name));
			$wm_image_type = $this->get_type($this->wm_image_name);
			$wm_image = $this->create_image($wm_image_type,$this->wm_image_name);
			$wm_image_w=ImageSX($wm_image);
			$wm_image_h=ImageSY($wm_image);
			$temp_wm_image = $this->get_pos($src_image_w,$src_image_h,$this->wm_image_pos,$wm_image);
			$wm_image_x = $temp_wm_image["dest_x"];
			$wm_image_y = $temp_wm_image["dest_y"];
			imageCopyMerge($src_image, $wm_image,$wm_image_x,$wm_image_y,0,0,$wm_image_w,$wm_image_h,$this->wm_image_transition);
		}

		if ($this->wm_text){
			$this->wm_text = $this->gb2utf8($this->wm_text);
			$temp_wm_text = $this->get_pos($src_image_w,$src_image_h,$this->wm_text_pos);
			$wm_text_x = $temp_wm_text["dest_x"];
			$wm_text_y = $temp_wm_text["dest_y"];
			if(preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->wm_text_color, $color))
			{
				$red = hexdec($color[1]);
				$green = hexdec($color[2]);
				$blue = hexdec($color[3]);
				$wm_text_color = imagecolorallocate($src_image, $red,$green,$blue);
			}else{
				$wm_text_color = imagecolorallocate($src_image, 255,255,255);
			}

			ImageTTFText($src_image, $this->wm_text_size, $this->wm_angle, $wm_text_x, $wm_text_y, $wm_text_color,$this->wm_text_font,  $this->wm_text);
		}

		if ($this->save_file)
		{
			switch ($this->output_type){
				case 'gif':
					$src_img=ImagePNG($src_image, $this->save_file); 
				break;
				case 'jpeg':
					$src_img=ImageJPEG($src_image, $this->save_file, $this->jpeg_quality); 
				break;
				case 'png':
					$src_img=ImagePNG($src_image, $this->save_file); 
				break;
				default:
					$src_img=ImageJPEG($src_image, $this->save_file, $this->jpeg_quality);
				break;
			}
		}
		else
		{
			if ($src_image_type = "jpg") 
				$src_image_type="jpeg";
			header("Content-type: image/{$src_image_type}");
			switch ($src_image_type){
				case 'gif':
					$src_img=ImagePNG($src_image); 
				break;
				case 'jpg':
					$src_img=ImageJPEG($src_image, "", $this->jpeg_quality);
				break;
				case 'png':
					$src_img=ImagePNG($src_image);
				break;
				default:
					$src_img=ImageJPEG($src_image, "", $this->jpeg_quality);
				break;
			}
		}
		imagedestroy($src_image);
	}

	/*
	-----------------------------------------------------------
	��������: create_image($type,$img_name)
	��Ҫ����:�����ļ��������ʹ���ͼƬ,����gif,jpg,png,���� " ./mouse.jpg"
	����:mixed (ͼƬ���ͣ�ͼƬ����)
	���:source (ͼƬ���)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function create_image($type,$img_name)
	{
		if (!$type){
			$type = $this->get_type($img_name);
		}

		switch ($type){
			case 'gif':
				if (function_exists('imagecreatefromgif'))
					$tmp_img=@ImageCreateFromGIF($img_name);
			break;
			case 'jpg':
				$tmp_img=ImageCreateFromJPEG($img_name);
			break;
			case 'png':
				$tmp_img=ImageCreateFromPNG($img_name);
			break;
			default:
			$tmp_img=ImageCreateFromString($img_name);
			break;
		}
		return $tmp_img;
	}
	/*
	-----------------------------------------------------------
	��������: get_pos($sourcefile_width,$sourcefile_height,$pos,$wm_image="")
	��Ҫ����:����Դͼ��ĳ�����λ�ô��룬ˮӡͼƬid�����ɰ�ˮӡ���õ�Դͼ���е�λ��
	����:mixed ()
	���:array (xy��������)
	�޸���־:$wm_image:ˮӡͼƬID
	-----------------------------------------------------------
	*/
	function get_pos($sourcefile_width,$sourcefile_height,$pos,$wm_image="")
	{
		if($wm_image){
			$insertfile_width = ImageSx($wm_image);
			$insertfile_height = ImageSy($wm_image);
		}else {
			$lineCount = explode("\r\n",$this->wm_text);
			$fontSize = imagettfbbox($this->wm_text_size,$this->wm_text_angle,$this->wm_text_font,$this->wm_text);
			$insertfile_width = $fontSize[2] - $fontSize[0];
			$insertfile_height = count($lineCount)*($fontSize[1] - $fontSize[3]);
		}

		 switch ($pos){
			case 0:
				$dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
				$dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
			break;

			case 1:
				$dest_x = 0;
				if ($this->wm_text){
					$dest_y = $insertfile_height;
				}
				else{
					$dest_y = 0;
				}
			break;

			case 2:
				$dest_x = $sourcefile_width - $insertfile_width;
				if ($this->wm_text){
					$dest_y = $insertfile_height;
				}
				else{
					$dest_y = 0;
				}
			break;

			case 3:
				$dest_x = $sourcefile_width - $insertfile_width;
				$dest_y = $sourcefile_height - $insertfile_height;
			break;

			case 4:
				$dest_x = 0;
				$dest_y = $sourcefile_height - $insertfile_height;
			break;

			case 5:
				$dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
				if ($this->wm_text){
					$dest_y = $insertfile_height;
				}
				else{
					$dest_y = 0;
				}
			break;

			case 6:
				$dest_x = $sourcefile_width - $insertfile_width;
				$dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
			break;

			case 7:
				$dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
				$dest_y = $sourcefile_height - $insertfile_height;
			break;

			case 8:
				$dest_x = 0;
				$dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
			break;

			default:
				$dest_x = $sourcefile_width - $insertfile_width;
				$dest_y = $sourcefile_height - $insertfile_height;
			break;
		}
		return array("dest_x"=>$dest_x,"dest_y"=>$dest_y);
	}

	/*
	-----------------------------------------------------------
	��������: gb2utf8($gb)
	��Ҫ����:ָ��������ת��ΪUTF-8��ʽ��������Ӣ�Ļ��
	����:string
	���:string
	�޸���־:
	-----------------------------------------------------------
	*/
	function gb2utf8($gb)
	{
		if(!trim($gb))
			return $gb;

        $filename="gb2312.txt";
        $tmp=file($filename);
        $codetable=array();
        while(list($key,$value)=each($tmp))
        $codetable[hexdec(substr($value,0,6))]=substr($value,7,6);

		$utf8="";
		while($gb)
		{
			if (ord(substr($gb,0,1))>127)
			{
				$tthis=substr($gb,0,2);
				$gb=substr($gb,2,strlen($gb)-2);
				$utf8.=$this->u2utf8(hexdec($codetable[hexdec(bin2hex($tthis))-0x8080]));
			}
			else
			{
				$tthis=substr($gb,0,1);
				$gb=substr($gb,1,strlen($gb)-1);
				$utf8.=$this->u2utf8($tthis);
			}
		}
	return $utf8;
	}

	/*
	-----------------------------------------------------------
	��������: u2utf8($c)
	��Ҫ����:ָ��������ת��ΪUTF-8��ʽ��������Ӣ�Ļ��
	����:string
	���:string
	�޸���־:---
	-----------------------------------------------------------
	*/
	function u2utf8($c)
	{
		$str="";
		if ($c < 0x80)
		{
			$str.=$c;
		}
		elseif ($c < 0x800)
		{
			$str.=chr(0xC0 | $c>>6);
			$str.=chr(0x80 | $c & 0x3F);
		}
		elseif ($c < 0x10000)
		{
			$str.=chr(0xE0 | $c>>12);
			$str.=chr(0x80 | $c>>6 & 0x3F);
			$str.=chr(0x80 | $c & 0x3F);
		}
		else if ($c < 0x200000)
		{
			$str.=chr(0xF0 | $c>>18);
			$str.=chr(0x80 | $c>>12 & 0x3F);
			$str.=chr(0x80 | $c>>6 & 0x3F);
			$str.=chr(0x80 | $c & 0x3F);
		}
	return $str;
	}

	/*
	-----------------------------------------------------------
	��������: get_type($img_name)
	��Ҫ����:���ͼƬ�ĸ�ʽ������jpg,png,gif
	����:string (ͼƬ����)
	���:string 
	�޸���־:
	-----------------------------------------------------------
	*/
	function get_type($img_name)
	{
		$name_array = explode(".",$img_name);
		if (preg_match("/\.(jpg|jpeg|gif|png)$/", $img_name, $matches)){
			$type = strtolower($matches[1]);
		}
		else{
			$type = "string";
		}
		return $type;
	}


}//end class
//=============================================================================
?>