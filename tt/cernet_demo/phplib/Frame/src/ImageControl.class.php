<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:ImageControl.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2004/11/11
- ��Ҫ����:����ͼƬ��С�����ࡣ
- ���л���:��ҪGD��֧�֣��汾���Ϊ2.0���ϣ����磺2.0.28
- �޸ļ�¼:2004/11/11��indraw��������
---------------------------------------------------------------------
*/

/*
	$imm = new ImageControl('test.jpg', '.');
	$imm->make_thumb("180");
*/
/*
	ImageControl ($name = "",$directory = ".",$mode = "1")
	set_back($bg_red="0",$bg_green="0",$bg_blue="0")               //���ñ���
	set_frame($fr_red="0",$fr_green="0",$fr_blue="0")              //���ñ߿�
	get_image_name ()                                              //��ȡͼƬ����
	get_image_height ()                                            //��ȡͼƬ�߶�
	get_image_path ()                                              //��ȡͼƬ·��
	get_image_width ()                                             //��ȡͼƬ���
	get_image_size ()                                              //��ȡͼƬ��С
	get_thumb_name ()                                              //��ȡ����ͼ��
	get_image_type ()                                              //��ȡ�ļ�����
	exist_thumb ($prefix = 'thb_')                                 //�ж��Ƿ��������ͼ
	exist_image ()                                                 //�ж��ļ��Ƿ����
	make_thumb ($dimension = 100, $quality = 70, $prefix = "thb_") //��������ͼ
	html_thumb_image ()                                            //��ie����ʾ����ͼ
	read_image_from_file($filename, $type)                         //���ļ��ж���ͼƬ
	write_image_to_file($im, $filename, $type, $quality)           //��ͼƬд�뵽�ļ�
*/

//=============================================================================
class ImageControl 
{

	var $name;                    //ͼƬ��
	var $directory;               //ͼƬ·��
	var $path;                    //ͼƬ·��+ͼƬ��
	var $width, $height, $type, $dimension;   //ͼƬ���� �� ���� �ı��ַ���
	var $prefix;                  //��СͼƬ���ǰ׺
	var $exist;                   //�ж��ļ��Ƿ����

	var $bg_red;                  //ǰ��ɫ->RGB
	var $bg_green;                //ǰ��ɫ->RGB
	var $bg_blue;                 //ǰ��ɫ->RGB

	var $fr_red;                  //�߿�ɫ->RGB
	var $fr_green;                //�߿�ɫ->RGB
	var $fr_blue;                 //�߿�ɫ->RGB

	var $max_size;                //�Ƿ�ǿ�ƴ�С
	var $max_fram;                //�Ƿ������߿�

	var $mode;                    //���ͼƬ������ʾ���� 1д���ļ�0��ʾ����

	/*
	-----------------------------------------------------------
	��������:ImageControl ($name = "",$directory = ".",$mode = "1") 
	��Ҫ����:���캯��
	����:mixed (�ļ������ļ�·����ģʽ��1����ͼƬ��0ֱ������������)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function ImageControl ($name = "",$directory = ".",$mode = "1") 
	{
		if (!is_dir ($directory))
			mkdir ($directory, 0775);
			$this->path = $directory."/".$name;
		if (file_exists ($this->path)) {
			$this->name	 	= $name;
			$this->directory	= $directory;
			$this->exist		= TRUE;
			$size				= GetImageSize($this->path);
			$this->width		= $size [0];
			$this->height		= $size [1];
			$this->type	 	= $size [2];
			$this->dimension 	= $size [3];

			$this->mode 	= $mode;

		} else {
			$this->name 		= $name;
			$this->directory 	= $directory;
			$this->exist		= FALSE;
			$this->width	 	= 0;
			$this->heigth 	= 0;
			$this->type		= "";
		}
	}

//-----------------------------------------------------------------------------
	/*
	-----------------------------------------------------------
	��������:set_back($bg_red="0",$bg_green="0",$bg_blue="0") 
	��Ҫ����:���ñ�����ɫ
	����:mixed (�������õ�����ԭɫ)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_back($bg_red="0",$bg_green="0",$bg_blue="0") 
	{
		$this->bg_red	 	= $bg_red;
		$this->bg_green		= $bg_green;
		$this->bg_blue		= $bg_blue;
		$this->max_size		= 1;
	}

	/*
	-----------------------------------------------------------
	��������:set_frame($fr_red="0",$fr_green="0",$fr_blue="0") 
	��Ҫ����:���ñ߿���ɫ��ɫ
	����:mixed (�������õ�����ԭɫ)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_frame($fr_red="0",$fr_green="0",$fr_blue="0") 
	{
		$this->fr_red	 	= $fr_red;
		$this->fr_green		= $fr_green;
		$this->fr_blue		= $fr_blue;
		$this->max_fram		= 1;
	}

	/*
	-----------------------------------------------------------
	��������:exist_thumb ($prefix = 'thb_') 
	��Ҫ����:�ж����Ժ��ͼƬ�Ƿ����
	����:string(����ͼǰ׺)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function exist_thumb ($prefix = 'thb_') 
	{
		if (file_exists ($this->directory."/$prefix".$this->name)) {
			$this->prefix = $prefix; 
			return TRUE;
		} else 
			return FALSE;
	}

	/*
	-----------------------------------------------------------
	��������:get_thumb_name () 
	��Ҫ����:��ȡ����ͼ������
	����:void
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_thumb_name () 
	{
		$result = $this->prefix . $this->get_image_name();
		return $result;
	}
//-----------------------------------------------------------------------------
	/*
	-----------------------------------------------------------
	��������:---
	��Ҫ����:����ԭʼ�ļ������ߡ���·������С���ı��ַ�����ͼƬ���ͣ�ԭʼ�ļ��Ƿ����
	����:---
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_image_name () 
	{
		return $this->name;
	}

	function get_image_height () 
	{
		return $this->height;
	}

	function get_image_width () 
	{
		return $this->width;
	}

	function get_image_path () 
	{
		return $this->path;
	}
	
	function get_image_size () 
	{
		$size = filesize ($this->path);
		return $size;
	}
	function get_image_dimension () 
	{
		return $this->dimension;
	}	

	function get_image_type ()
	{
		switch ($this->type) {
			case 1 :
				return "GIF";
			case 2 :
				return "Jpeg";
			case 3 :
			 	return "PNG";
			case 4 :
				return "SWF";
			case 5 :
				return "PSD";
			case 6 :
				return "BMP";
			case 7 :
				return "TIFF_II";
			case 8 :
				return "TIFF_MM";
			case 9 :
				return "JPC";
			case 10 :
				return "JP2";
			case 11 :
				return "JPX";	
		}
	}
	function exist_image () 
	{
		return $this->exist;
	}

//-----------------------------------------------------------------------------
	/*
	-----------------------------------------------------------
	��������:make_thumb ($dimension = 100, $quality = 70, $prefix = "thb_")
	��Ҫ����:��������ͼ����
	����:mixed (ͼƬ��������ͼƬ������ͼ��ǰ׺)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function make_thumb ($dimension = 100, $quality = 70, $prefix = "thb_") 
	{
		
		$this->prefix = $prefix;
		$newHeight = $dimension;
		$newWidth	= $dimension;
		
 		if ($im = $this->read_image_from_file($this->path, $this->type)) {
			//�̶���������������ñ������
			if ($newHeight && ($this->width < $this->height)) {
				$newWidth = ($newHeight / $this->height) * $this->width;
			} else {
				$newHeight = ($newWidth / $this->width) * $this->height;
			}
			if( $this->max_size )
			{
				if ($this->width < $this->height)
				{
					$newxsize = $newHeight * ($this->width/$this->height);
					$adjustX = ($dimension - $newxsize)/2;
					$adjustY = 0;
				}
				else
				{
					$newysize = $newWidth / ($this->width/$this->height);
					$adjustX = 0;
					$adjustY = ($dimension - $newysize)/2;
				}
				if (function_exists("ImageCreateTrueColor")) {
					$im2 = ImageCreateTrueColor($dimension,$dimension);
				} else {
					$im2 = ImageCreate($dimension,$dimension);
				}
				$bgfill = imagecolorallocate( $im2, $this->bg_red, $this->bg_green, $this->bg_blue );	
				$frame = imagecolorallocate ( $im2, $this->fr_red, $this->fr_green, $this->fr_blue);
				imagefilledrectangle ( $im2, 0, 0, $dimension, $dimension, $bgfill);

				if (function_exists("ImageCopyResampled")) {
					ImageCopyResampled($im2,$im,$adjustX+2,$adjustY,0,0,$newWidth-4,$newHeight,$this->width,$this->height);
				} else {
					ImageCopyResized($im2,$im,$adjustX,$adjustY,0,0,$newWidth,$newHeight,$this->width,$this->height);
				}
				//�Ƿ���ʾ�߿�
				if($this->max_fram)
				imagerectangle ( $im2, 1, 1, ($dimension-2), ($dimension-2), $frame);
			}//��ԭ��������
			else{
				if (function_exists("ImageCreateTrueColor")) {
					$im2 = ImageCreateTrueColor($newWidth,$newHeight);
				} else {
					$im2 = ImageCreate($newWidth,$newHeight);
				}
				if (function_exists("ImageCopyResampled")) {
					ImageCopyResampled($im2,$im,0,0,0,0,$newWidth,$newHeight,$this->width,$this->height);
				} else {
					ImageCopyResized($im2,$im,0,0,0,0,$newWidth,$newHeight,$this->width,$this->height);
				}
			}//end maxsize

			if ($this->write_image_to_file($im2, $this->directory."/".$this->prefix.$this->name, $this->type, $quality)) {
				return true;
			}
		}//end checkexist
	 }

	/*
	-----------------------------------------------------------
	��������:html_thumb_image ()
	��Ҫ����:��ʾ����ͼ
	����:void
	���:void (ֱ�������������ʾ����ͼ)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function html_thumb_image () 
	{
		$html = "<a href=\"".$this->path."\"> <img src=\"".$this->directory."/".$this->get_thumb_name()."\" border=\"0\">";
		return $html;
	}	
	
	
	/*
	-----------------------------------------------------------
	��������:read_image_from_file($filename, $type)
	��Ҫ����:��ͼƬ�����ַ���
	����:mixed (ͼƬ���ƣ�����)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function read_image_from_file($filename, $type) 
	{
		
		$imagetypes = imagetypes();

 		switch ($type) {
		case 1 :
		 	if ($imagetypes & IMG_GIF)
				return ImageCreateFromGIF($filename);
			else
				return FALSE;
		 	break;
		case 2 :
		 	if ($imagetypes & IMG_JPEG)
				return ImageCreateFromJPEG($filename);
			else
				return FALSE;
		 	break;
		case 3 :
		 	if ($imagetypes & IMG_PNG)
				return ImageCreateFromPNG($filename);
			else
				return FALSE;
		 	break;
		default:
		 	return FALSE;
 		}
 	}

	/*
	-----------------------------------------------------------
	��������:write_image_to_file($im, $filename, $type, $quality)
	��Ҫ����:��ͼƬд���ļ�
	����:mixed (ͼƬ�����ͼ�����ƣ����ͣ�����)
	���:boolean or echo
	�޸���־:------
	-----------------------------------------------------------
	*/
	function write_image_to_file($im, $filename, $type, $quality) 
	{
		if($this->mode){
			switch ($type) {
			case 1 :
				return ImageGif($im, $filename);
				break;	 
			case 2 :
				return ImageJpeg($im, $filename, $quality);
				break;	 
			case 3 :
				return ImagePNG($im, $filename);
				break;
			default:
				return false;
			}
			return false;
		}
		else{
			header("Content-type: image/jpeg");
			imagejpeg($im);
		}
	}
	

}//end class
//=============================================================================