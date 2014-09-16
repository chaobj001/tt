<?
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:FileDir.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2004/11/5
- ��Ҫ����:Ŀ¼�����༯�����������е�Ŀ¼������
- ���л���: php4������
- �޸ļ�¼:2004/11/5��indraw��������
---------------------------------------------------------------------
*/

/*
	$newDir = new FileDir();
	$newDir->set_current_dir($test_dir);
*/
/*
	FileDir($current_dir)                    //
	set_current_dir($dir)                    //���õ�ǰ����Ŀ¼
	create_dir($dirname,$where='')           //����һ��Ŀ¼
	rename_dir($dirname,$where='')           //������Ŀ¼��
	delete_dir($dirname)                     //ɾ��һ���ļ�
	delete_file($filename)                   //ɾ��һ���ļ�
	empty_dir($Dir)                          //���һ��Ŀ¼��������Ŀ¼
	get_dir_info()                           //��ȡһ��Ŀ¼�µ��ļ����ļ�������
	get_dir_all()                            //��ȡһ��Ŀ¼�ļ���С,�ݹ�n������
	copy_file($filename,$to='',$as='')       //��һ���ļ�copy����һ���ļ�����
	copy_dir($Dir,$NewDirName,$delDir)       //ת��һ��Ŀ¼
	get_file_size($file, $round = false)     //��ȡһ���ļ���С,��������ļ�����ȡ��С������������ַ��ظ�ʽ��������
	three_dir()                              //��һ��Ŀ¼��һ��Ŀ¼ת�������Ŀ¼
*/

//===================================================================
class FileDir 
{

	var $show_errors = true;             //�Ƿ���ʾ������Ϣ

	var $current_dir;                    //��ǰ·��
	var $current_files = array();        //��ǰ�ļ�����
	var $current_dirs = array();         //��ǰ�ļ�������
	var $current_size = 0;               //��ǰ�ļ��ܴ�С

	function FileDir() 
	{

	}
	/*
	-----------------------------------------------------------
	��������:set_current_dir($dir)
	��Ҫ����:���õ�ǰ����Ŀ¼
	����:string (��Ի����·������βû��б�ܡ�)
	���:boolean 
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_current_dir($dir) 
	{
		if( @chdir($dir) ) {
			//$this->FileDir($dir);
			return true;
		} else
			$this->print_error("FileDir::set_current_dir: ���ܶ�λ��Ŀ¼\"$dirname\"��");
	}
	/*
	-----------------------------------------------------------
	��������:create_dir($dirname)
	��Ҫ����:����һ��Ŀ¼
	����:string (��Ի����·������βû��б��)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function create_dir($dirname) 
	{
		if (!is_dir($dirname)) {
			if(!@mkdir($dirname,777)) 
				$this->print_error("FileDir::create_dir: ���ܽ���Ŀ¼��");
		}
		else {
			$this->print_error("FileDir::create_dir: Ŀ¼\"$dirname\"�Ѿ����ڣ�");
		}
	}
	/*
	-----------------------------------------------------------
	��������:rename_dir($dirname)
	��Ҫ����:������Ŀ¼��
	����:string (��Ի����·������βû��б��)
	���:booblean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function rename_dir($new_name) 
	{
		if($new_name == "" or $new_name == "." or $new_name == ".."){
			$this->print_error("FileDir::rename_dir: �������޸ĳ�������Ŀ¼��");
		}
		if(@rename ($this->current_dir, $new_name)){
			return true;
		}
		else{
			$this->print_error("FileDir::rename_dir: Ŀ¼\"$this->current_dir\"������\"$new_name\"ʧ�ܣ�");
		}
	}
	/*
	-----------------------------------------------------------
	��������:delete_dir($dirname)
	��Ҫ����:ɾ��һ��Ŀ¼
	����:string (��Ի����·������βû��б��)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function delete_dir($dir_name) 
	{
		if (is_dir($dir_name)) {
			if(@rmdir($dir_name)) 
				return true;
			else
				$this->print_error("FileDir::delete_dir: ����ɾ��Ŀ¼ $dirname ����ȷ��Ŀ¼�Ƿ�Ϊ�գ�");
		}
		else {
			$this->print_error("FileDir::delete_dir: Ŀ¼ $dirname �����ڣ�");
		}
	}

	/*
	-----------------------------------------------------------
	��������:deleteFile($filename)
	��Ҫ����:ɾ��һ���ļ���
	����:string (��Ի����·������βû��б��)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function delete_file($filename) 
	{
		if (file_exists($filename)) {
			if(@unlink($filename)) 
				return true;
			else
				$this->print_error("FileDir::delete_file: ����ɾ���ļ� $filename ��");
		}
		else {
			$this->print_error("FileDir::delete_file: �ļ� $filename �����ڣ�");
		}
	}
	/*
	-----------------------------------------------------------
	��������:empty_dir($Dir)
	��Ҫ����:���һ��Ŀ¼��������Ŀ¼
	����:string (��Ի����·������βû��б��)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function empty_dir($dir_name) 
	{
		if ($handle = @opendir($dir_name)) {
			while (($file = readdir($handle)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if (is_dir($dir_name."/".$file)){
					$this->empty_dir($dir_name."/".$file);
					$this->delete_dir($dir_name."/".$file);
				}else {
					$this->delete_file($dir_name."/".$file);
				}
			}
		}
	   @closedir($handle);
	}
	/*
	-----------------------------------------------------------
	��������:get_dir_info() 
	��Ҫ����:��ȡһ��Ŀ¼�µ��ļ����ļ�������
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_dir_info() 
	{
		$i = 0;
		$j = 0;
		$current_dir_handle = @opendir($this->current_dir);
		while ($contents = readdir($current_dir_handle)) {
			if (is_dir($contents)) {
				if ($contents != '.' && $contents != '..') {
					$this->current_dirs[$j] = $contents;  
					$j++;
				}
			}
			elseif (is_file( $contents )) {
				
				$this->current_files[$i] = $contents;
				$i++;
			}
		}
		closedir($current_dir_handle);
	}
	/*
	-----------------------------------------------------------
	��������:get_dir_all()
	��Ҫ����:��ȡһ��Ŀ¼�ļ���С,�ݹ�n������
	����:string (��Ի����·������βû��б��)
	���:float (�����ļ���С��Ӻ��ֵ���ֽ�)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_dir_size($dir_name)
	{
		if ($handle = @opendir($dir_name)) {
			while (($file = readdir($handle)) !== false) {

				if ($file == "." || $file == "..") {
					continue;
				}

				if (is_dir($dir_name."/".$file)){
					$this->get_dir_size($dir_name."/".$file);
					$this->current_size += filesize($dir_name."/".$file);
				}else {
					$this->current_size += filesize($dir_name."/".$file);
				}
			}
		}
		@closedir($handle);
	}

	/*
	-----------------------------------------------------------
	��������:copyFile($filename,$to='',$as='')
	��Ҫ����:��һ���ļ�copy����һ���ļ����¡�
	����:mixed (��Ի����·������βû��б��)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function copy_file($filename,$new_filename) {

		if(!@copy($filename,$new_filename)) 
			$this->print_error("FileDir::copy_file: ����copy�ļ���$filename");
	}

	/*
	-----------------------------------------------------------
	��������:copy_dir()
	��Ҫ����:ת��һ��Ŀ¼
	����:mixed {��ת���ļ��У�ת��λ�ã��Ƿ�ɾ�����ļ���}
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function copy_dir($old_dir,$new_dir,$del_dir = "N") {
		$handle = @opendir($old_dir); 
			while ($file = readdir($handle)) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if(is_dir($old_dir."/".$file)) {
					@mkdir($new_dir."/".$file);
					$this->copy_dir($old_dir."/".$file,$new_dir."/".$file,$del_dir);
					if($del_dir=="Y") rmdir($old_dir."/".$file);
					//$dirNum++;
				}
				else{
					copy($old_dir."/".$file, $new_dir."/".$file);
					if($del_dir=="Y") unlink($old_dir."/".$file);
					//$fileNum++;
				}
			}
		@closedir($handle);
	}
	/*
	-----------------------------------------------------------
	��������:get_file_size($file, $round = false)
	��Ҫ����:��ȡһ���ļ���С,��������ļ�����ȡ��С������������ַ��ظ�ʽ��������
	����:mixed (�ļ��л��ֽ�);
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function get_file_size($file, $round = false)
	{
		$value = 0;
		if (@file_exists($file)) {
			$size = filesize($file);
		}
		else{
			$size = $file;
		}
			if ($size >= 1073741824) {
				$value = round($size/1073741824*100)/100;
				return  ($round) ? round($value) . 'Gb' : "{$value}Gb";
			} else if ($size >= 1048576) {
				$value = round($size/1048576*100)/100;
				return  ($round) ? round($value) . 'Mb' : "{$value}Mb";
			} else if ($size >= 1024) {
				$value = round($size/1024*100)/100;
				return  ($round) ? round($value) . 'kb' : "{$value}kb";
			} else {
				return "$size bytes";
			}
	}

	/*
	-----------------------------------------------------------
	��������:three_dir() 
	��Ҫ����:��һ��Ŀ¼��һ��Ŀ¼ת�������Ŀ¼
	����:char (�Ƿ�ɾ��ԭĿ¼,Yɾ��,N����)
	���:int (���سɹ�ת����Ŀ¼����)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function three_dir($del_dir="N") 
	{
		$this->get_dir_info(); 
		$dirNum = 0;
		for ($i=0; $i<count($this->current_dirs); $i++) 
		{
			$strOldDir = $this->current_dir."/".$this->current_dirs[$i];
			$strNewDir = $this->current_dir."/".$this->current_dirs[$i]{0};
			if ($file == "." || $file == "..") 
			{
				continue;
			}
			elseif(is_dir($strOldDir) and strlen($this->current_dirs[$i])>=3)
			{
				//
				$this->create_dir($strNewDir);
				$this->create_dir($strNewDir."/".$this->current_dirs[$i]{1});
				$this->create_dir($strNewDir."/".$this->current_dirs[$i]{1}."/".$this->current_dirs[$i]{2});
				$this->create_dir($strNewDir."/".$this->current_dirs[$i]{1}."/".$this->current_dirs[$i]{2}."/".$this->current_dirs[$i]);	$this->copy_dir($strOldDir,$strNewDir."/".$this->current_dirs[$i]{1}."/".$this->current_dirs[$i]{2}."/".$this->current_dirs[$i],$del_dir);
				if($del_dir == "Y") $this->delete_dir($strOldDir);
				//
				$dirNum++;
			}
		}//end for
		return $dirNum;
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
		$PHPSEA_ERROR['FileDir_Error'] = $str;
	
		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>FileDir Error --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
		else
		{
			return false;	
		}
	}//end func

}//end class
//===================================================================
?>
