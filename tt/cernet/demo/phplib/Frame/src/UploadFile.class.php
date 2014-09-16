<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.6
- �ļ���:UploadFile.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2004/11/11
- ��Ҫ����:�ļ��ϴ������༯��Ϊindraw�ۺ��˼����ϴ����������ɡ�
- ���л���:php4������
- �޸ļ�¼:2004/11/11��indraw��������
- �޸ļ�¼:2005/04/11��indraw�����ϴ��������о���������
---------------------------------------------------------------------
*/

/*
	$upload = new UploadFile;
	$upFileName = $upload->upload_file('./', 'md5', 10);
	$upload->img_resize('./up',$aaa[file1], 120, true);
	$upload->display_files('./');
*/
/*
	set_max_width($width)                           //����ͼƬ�����
	set_max_size($size)                             //�����ļ����ֵ
	set_valid_types($type)                          //�����ļ���������
	check_dir($dir)                                 //���Ŀ¼�����û���Զ�����,�����ؽ�����Ŀ¼��
	check_gd()                                      //���GD�汾
	delete_file($source_dir, $filename)             //ɾ���ļ�
	file_rename($source_dir, $filename, $newname)   //�ļ���������
	lantin_encode($str)                             //��������ת����ƴ��
	upload_file($target_dir = '', $encode = 'md5', $name_length = 10)
	                                                //���ļ��ϴ���������
	display_files($source_dir)                      //��ʾһ��Ŀ¼�µ��ļ��б�
*/

//===================================================================
class UploadFile
{

	var $show_errors = true;            //�Ƿ���ʾ����

	var $error_num = 0;                 //0\1\2\3\4\5
	var $file_name = array();
	var $max_file_size = 1572864;       //1.5Mb�������ϴ�ͼƬ��С
	var $max_image_width = 2048;        //���أ������ϴ�ͼƬ���
	var $_files = array();              //�ϴ����ļ�����
	var $_types = array('jpg', 'jpeg', 'png','bmp', 'doc', 'txt', 'gif','rar', 'tar', 'zip', 'tgz', 'gz', 'wml');                             //�����ϴ����ļ�����

	/*
	-----------------------------------------------------------
	��������:set_max_width($width)
	��Ҫ����:����ͼƬ�����
	����:int
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_max_width($width)
	{
		$this->max_image_width = $width;
	}
	

	/*
	-----------------------------------------------------------
	��������:set_max_size($size)
	��Ҫ����:�����ļ����ֵ
	����:int
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_max_size($size)
	{
		$this->max_file_size = $size;
	}

	/*
	-----------------------------------------------------------
	��������:set_valid_types($type)
	��Ҫ����:�����ļ���������
	����:array
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_valid_types($type_array)
	{
		$this->_types = $type_array;
	}

	/*
	-----------------------------------------------------------
	��������:check_dir($dir)
	��Ҫ����:���Ŀ¼�����û���Զ�����,�����ؽ�����Ŀ¼��
	����:string
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function check_dir($dir)
	{
		if (!is_dir($dir)) {
			mkdir($dir, 0777);
			chmod($dir, 0777);
		}
		return $dir;
	}

	/*
	-----------------------------------------------------------
	��������:check_gd()
	��Ҫ����:���GD�汾
	����:void
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function check_gd()
	{
		$gd_content = get_extension_funcs('gd');
		if (!$gd_content) { 
			$this->print_error('���Ŀռ�û�а�װGD�⣡'); 
			
			return false; 
		} else {
			ob_start();
			phpinfo(8);
			$buffer = ob_get_contents();
			ob_end_clean(); 

			if (strpos($buffer, '2.0')) {
				return 'gd2';
			} else {
				return 'gd';
			}
		}
	}	
	

	/*
	-----------------------------------------------------------
	��������:delete_file($filename, $source_dir)
	��Ҫ����:ɾ���ļ�
	����:mixed (Ŀ¼���ļ���)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function delete_file($source_dir, $filename)
	{
		$source_dir = $this->check_dir($source_dir);
		if (file_exists($source_dir.'/'.$filename)) {
			if (unlink($source_dir.'/'.$filename)) {
				return true;
			}
		}
		else{
			$this->print_error('��Ҫɾ�����ļ�������'); 
			Return false;
		}
	}


	/*
	-----------------------------------------------------------
	��������:file_rename($source_dir, $filename, $newname)
	��Ҫ����:�ļ���������
	����:mixed (�ļ�Ŀ¼��ԭʼ�ļ��������ļ���)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function file_rename($source_dir, $filename, $newname)
	{
		$source_dir = $this->check_dir($source_dir);
		if (rename($source_dir.'/'.$filename, $newname)) {
			return true;
		} 
		else {
			$this->print_error('�ļ���������ʧ��'); 
			Return false;
		}
	}

	/*
	-----------------------------------------------------------
	��������:lantin_encode($str)
	��Ҫ����:��������ת����ƴ�����Ժ�ʲôʱ����Ҫ�پ���ȥ��ơ���������Ҿ��ú�ʵ�á�
	����:string
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function lantin_encode($str)
	{
		
		return $str;
	}

	/*
	-----------------------------------------------------------
	��������:uploadFile($target_dir = '', $encode = 'md5', $name_length = 10)
	��Ҫ����:���ļ��ϴ���������
	����:mixed (·�����������룬���ֳ���)
	���:string (�ļ���)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function upload_file($target_dir = '', $encode = 'md5', $name_length = 30)
	{
		$target_dir = $this->check_dir($target_dir);
		foreach ($_FILES as $varname => $array) {
			if (!empty($array['name'])) {
				if (is_uploaded_file($array['tmp_name'])) {
					$filename = strtolower(str_replace(' ', '', $array['name']));
					$basefilename = preg_replace("/(.*)\.([^.]+)$/","\\1", $filename);
					$ext = preg_replace("/.*\.([^.]+)$/","\\1", $filename);
					$this->file_name[$varname] = $filename;
					$get_image_size = getimagesize ($array['tmp_name']);
					//echo "�ļ����ͣ�".$ext;
					if ($array['size'] > $this->max_file_size) {
						$this->print_error('�ϴ��ļ����������ϴ����޶�');
						$this->error_num = 1;
						Return false;
					
					} elseif ($get_image_size[0] > $this->max_image_width) {
						$this->print_error('ͼ���ȳ��������ϴ���Ҫ��');
						$this->error_num = 2;
						Return false;

					} elseif (!in_array($ext, $this->_types)) {
						$this->print_error('�ļ�����Ϊ��ֹ�ϴ�����');
						$this->error_num = 3;
						Return false;

					} else {
						switch ($encode) {
						case 'md5':
							$basefilename = substr(md5($basefilename), 0, $name_length);
							$filename = $basefilename.'.'.$ext;
							break;
							
						case 'latin':
							$basefilename = substr($this->lantinEncode($basefilename), 0, $name_length);
							$filename = $basefilename.'.'.$ext;
							break;
						case '':
							$basefilename = substr($basefilename, 0, $name_length);
							$filename = $basefilename.'.'.$ext;
							break;
						default: 
							$basefilename = $encode."_".$varname;
							$filename = $basefilename.'.'.$ext;
						}
						
						if (!move_uploaded_file($array['tmp_name'], $target_dir.'/'.$filename)) {
						//if (!copy($array['tmp_name'], $target_dir.'/'.$filename)) {

							$this->print_error('�ϴ��ļ�ʧ��');
							$this->error_num = 4;

							Return false;
						}
						//echo($target_dir.'/'.$filename."<br>");
						$this->_files[$varname] = $filename;
					}
				} else {
					$this->print_error('���ܽ����ϴ���ʱ�ļ�');
					$this->error_num = 5;
					Return false;
				}
			}
		}//end foreach
		return $this->_files;
	}
	
	/*
	-----------------------------------------------------------
	��������:img_resize($filename, $source_dir, $dest_width, $duplicate = false)
	��Ҫ����:��дͼƬ��С
	����:mixed (·����ͼƬ������ȣ��Ƿ񸲸�ԭ�ļ�)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function img_resize($source_dir,$filename, $dest_width="", $duplicate = false)
	{
		$source_dir = $this->check_dir($source_dir);
		$full_path = $source_dir.'/'.$filename;
		$basefilename = preg_replace("/(.*)\.([^.]+)$/","\\1", $filename);
		$ext = preg_replace("/.*\.([^.]+)$/","\\1", $filename);

		switch ($ext) {
		case 'png':
			$image = imagecreatefrompng($full_path);
			break;
		case 'gif':
			$image = ImageCreateFromGIF($full_path);
			break;
		case 'jpg':
			$image = imagecreatefromjpeg($full_path);
			break;
		
		case 'jpeg':
			$image = imagecreatefromjpeg($full_path);
			break;
		
		default:
			$this->print_error('���ϴ����ļ����� '.$ext.' ��û�еõ�����GD��汾֧��');
			Return false;
			break;
		}

		$image_width = imagesx($image);
		$image_height = imagesy($image);
		
		// resize image pro rata
		if($dest_width == ""){
			$coefficient = ($image_width > $this->max_image_width) ? (real)($this->max_image_width / $image_width) : 1;
		}
		else{
			$coefficient = ($image_width > $dest_width) ? (real)($dest_width / $image_width) : 1;
		}

		$dest_width = (int)($image_width * $coefficient);
		$dest_height = (int)($image_height * $coefficient);
		//echo $dest_width."---".$dest_height;

		if (false !== $duplicate) {
			$filename = $basefilename.'_2.'.$ext;
			copy($full_path, $source_dir.'/'.$filename);
		}
		
		if ('gd2' == $this->check_gd()) { 
			$img_id = imagecreatetruecolor($dest_width, $dest_height);
			imagecopyresampled($img_id, $image, 0, 0, 0, 0, $dest_width + 1, $dest_height + 1, $image_width, $image_height);
		} else {
			$img_id = imagecreate($dest_width, $dest_height);
			imagecopyresized($img_id, $image, 0, 0, 0, 0, $dest_width + 1, $dest_height + 1, $image_width, $image_height);
		}

		switch ($ext) {
		case 'png':
			imagepng($img_id, $source_dir.'/'.$filename);
			break;
		
		case 'jpg':
			imagejpeg($img_id, $source_dir.'/'.$filename);
			break;
		
		case 'jpeg':
			imagejpeg($img_id, $source_dir.'/'.$filename);
			break;
		}
		
		imagedestroy($img_id);
		
		return true;
	}

	/*
	-----------------------------------------------------------
	��������:display_files($source_dir)
	��Ҫ����:��ʾһ��Ŀ¼�µ��ļ��б�
	����:string
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function display_files($source_dir)
	{
		$source_dir = $this->check_dir($source_dir);
		if ($contents = opendir($source_dir)) {
			echo '<blockquote>Ŀ¼���ݣ�<br>';
			
			while (false !== ($file = readdir($contents))) {
				if (is_dir($file)) continue;

				$filesize = (real)(filesize($source_dir.'/'.$file) / 1024);
				$filesize = number_format($filesize, 3, ',', ' ');

				$ext = preg_replace("/.*\.([^.]+)$/","\\1", $file);
				echo '<p>*&nbsp;'.$file.'&nbsp;('.$filesize.') Kb</p>';
			}
			echo "</blockquote>";
		}
	}

	/*
	-----------------------------------------------------------
	��������:print_error($str = "")
	��Ҫ����:��ʾ����������Ϣ
	����:string 
	���:echo or false
	�޸���־:------
	-----------------------------------------------------------
	*/
	function print_error($str = "")
	{
		//����ȫ�ֱ���$PHPSEA_ERROR..
		global $PHPSEA_ERROR;
		$PHPSEA_ERROR['UpoadFile_Error'] = $str;
	
		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>UpoadFile Error --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
		else
		{
			return false;
		}
	}//end func

}//end class
//=============================================================================
?>