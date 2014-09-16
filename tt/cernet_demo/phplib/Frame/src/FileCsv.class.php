<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:FileCsv.class.php
- ԭ����:Ron Barnett
- ������:indraw
- ��д����:2004/11/4
- ��Ҫ����:�����ı�csv�ļ������༯��
- ���л���:php4�����ϰ汾
- �޸ļ�¼:2004/11/4��indraw��������
---------------------------------------------------------------------
*/

/*
	$db = new FileCsv("./","data.csv");
*/
/*
	FileCsv($df = '', $dd = '', $as = false, $ck = false)
	read_csv()                                    //��csv�ļ�����2ά�����ʽ��������
	append_csv()                                  //��������׷�ӵ�csv�ļ�
	delete($rownumber, $n = 1)                    //��csv�ļ���ɾ������
	update($rownumber, $aValues)                  //��csv�ļ��и�������
	append()                                      //���ؽ�Ҫ��ӵ�������������
	find($aValues)                                //��csv�ļ��в�������
	write_csv($append = false, $force = false)    //������д��csv�ļ������Դ��´�������ӡ�
	writeable()                                   //�ж�csv�ļ��Ƿ��д��
	row_count()                                   //��ȡcsv�ļ�����
	rows_to_write()                               //��ȡҪд��csv�ļ�������
	package($aData)                               //�������ļ�ת�����ַ�����ʽ�Ա�һ��д��-���ܸ�ʽ
	xpackage($aData)                              //�������ļ�ת�����ַ�����ʽ�Ա�һ��д��-��ͨ��ʽ
	encrypt(&$txt, $key)                          //�����ݱ���
	decrypt(&$txt, $key)                          //�����ݽ���
	krypt($txt, $crypt_key)                       //Ϊ���ݱ��������׼��
*/

//===================================================================
class FileCsv 
{

	var $show_errors = true;       //�Ƿ���ʾ������Ϣ

	var $data_file = "data.csv";   //csv�ļ���
	var $dir = "data";             //csv�ļ�·��
	var $db ;                      //ȡ����csv�ļ���ά����
	var $new_rows ;                //��׷����������
	var $last_update = "";         //�ļ�����޸�ʱ��
	var $written = 0;              //д�����������
	var $assoc = false ;           //�Ƿ�ʹ�ù�������
	var $crypt_key = false;        //�Ƿ��������

	/*
	-----------------------------------------------------------
	��������:FileCsv($df = '', $dd = '', $as = false, $ck = false) 
	��Ҫ����:��ʼ��csv�ļ�������
	����:mixed (�ļ������ļ�·�����Ƿ�Ϊ�������飬�Ƿ����)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function FileCsv($df = '', $dd = '', $as = false, $ck = false) 
	{
		$this->new_rows = array();
		$this->db = array();

		if ($df != '') {
			$this->data_file = $df;
		} 
		if ($dd != '') {
			$this->dir = $dd;
		} 
		if ($as !== false) {
			$this->assoc = $as;
		} 
		if ($ck !== false) {
			$this->crypt_key = $ck;
		} 
	} 

	/*
	-----------------------------------------------------------
	��������:read_csv() 
	��Ҫ����:��csv�ļ�����2ά�����ʽ��������
	����:void
	���:boolean (�ö�ά�������������$this->db)
	�޸���־:------
	-----------------------------------------------------------
	*/
	function read_csv() 
	{
		$cf = "$this->dir/$this->data_file";
		$row = 0;
		$this->last_update = @filemtime($cf) or $this->print_error("FileCsv::read_csv: ���ܻ�ȡ�ļ� $cf ���޸�ʱ��\n");
		$fp = @fopen($cf, 'rb') ;
		if (is_resource($fp)) {
			while ($data = fgetcsv ($fp, 1024, ",")) {
				if ($this->crypt_key) {
					for ($n = 0;$n < count($data);$n++) {
						$this->decrypt($data[$n], $this->crypt_key);
					} 
				} 
				if (($this->assoc === true) && ($row == 0)) {
					$num = count ($data);
					$key = $data;
					$row = 1;
				} else {
					$num = count ($data);
					$tmp = array() ;
					for ($n = 0 ;$n < $num ; $n++) {
						if ($this->assoc === true) {
							$k = strtolower($key[$n]) ;
						} else {
							$k = $n;
						} 
						$d = $data[$n] ;
						$tmp[$k] = $d ;
					} 
					$this->db[] = $tmp ;
				} 
			} 
			$this->db = array_change_key_case($this->db, CASE_LOWER);
			return @fclose($fp);
		} else {
			$this->print_error("FileCsv::read_csv: ���ܴ��ļ� $cf\n");
			return false;
		} 
	} 
	/*
	-----------------------------------------------------------
	��������:append_csv()
	��Ҫ����:��������(��ά����)׷�ӵ�csv�ļ�($this->new_rows)
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function append_csv() 
	{
		if ($this->rows_to_write() == 0) {
			$this->print_error("FileCsv:: append_csv: ����û�������Ҫ׷�ӵ�����\n");
		} 
		$tmp = '';
		$this->written = 0;
		for ($n = 0;$n < count($this->new_rows);$n++) {
			$tmp .= $this->package($this->new_rows[$n]);
			$this->written++ ;
		} 
		$cf = "$this->dir/$this->data_file";
		$fp = @fopen($cf, 'ab') ;
		if (is_resource($fp)) {
			if (!@fwrite($fp, $tmp)) {
				@fclose($fp);
				$this->print_error("FileCsv:: append_csv: ����д�����ݵ� $cf\n");
				return false;
			} 
			$this->new_rows = array();
			return @fclose($fp);
		} else {
			$this->print_error("FileCsv::append_csv: ���ܴ��ļ�:$cf");
			return false;
		} 
	} 
	/*
	-----------------------------------------------------------
	��������:delete($rownumber, $n = 1) 
	��Ҫ����:��csv�ļ���ɾ������
	����:mixed (�ڼ�������)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function delete($rownumber, $n = 1) 
	{
		$key_index = array_keys(array_keys($this->db), $rownumber);
		array_splice($this->db, $key_index[0], 1);
		if (!$this->db) {
			$this->print_error("FileCsv::delete: ɾ������ $n �д�  $rownumber ʧ��") . $this->row_count();
			return false;
		} else {
			return true;
		} 
	} 
	/*
	-----------------------------------------------------------
	��������:update($rownumber, $aValues) 
	��Ҫ����:��csv�ļ��и�������
	����:mixed (������ֵ)
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function update($rownumber, $aValues) 
	{
		$aValues = array_change_key_case($aValues, CASE_LOWER);
		while (list($key, $val) = each($aValues)) {
			if (@array_key_exists($key, $this->db[$rownumber])) {
				// echo "found key $key new value=> $val<br />";
				$this->db[$rownumber][$key] = $val;
			} else {
				$this->print_error("FileCsv::update: ����Ҫ���µ� [$rownumber] ��û���ҵ���Ҫ���µ��ֶ� [$key]");
				return false;
			} 
		} // while
		return true;
	} 
	/*
	-----------------------------------------------------------
	��������:append()
	��Ҫ����:���ؽ�Ҫ��ӵ�������������
	����:void
	���:int
	�޸���־:------
	-----------------------------------------------------------
	*/
	function append() 
	{
		$n = $this->rows_to_write();
		foreach($this->new_rows as $new) {
			$this->db[] = $new;
		} 
		$this->new_rows = array();
		return $n;
	} 
	/*
	-----------------------------------------------------------
	��������:find($aValues) 
	��Ҫ����:��csv�ļ��в�������
	����:array(key/value)
	���:array
	�޸���־:------
	-----------------------------------------------------------
	*/
	function find($aValues) 
	{
		$afound = false;
		$n = 0;
		$target = count($aValues); 
		// echo "Target = $target <pre>";
		// print_r($aValues);
		// echo "</pre>";
		foreach($this->db as $record) {
			// echo $n."<br />";
			$found = 0;
			reset($aValues);
			while (list($key, $val) = each($aValues)) {
				if ((array_key_exists($key, $record)) && (strcasecmp(trim(onespace($record[$key])), trim(onespace($val))) == 0)) {
					// echo "$key => [$val]	 record[$n] ?= [".$record[$key]."]<br />";
					$found += 1;
				} else {
					$found = 0;
					continue 1;
				} 
			} // while
			if ($found == $target) {
				// echo 'Matched<br />';
				$afound[] = $n;
			} 
			$n++;
		} 
		return $afound;
	} 
	/*
	-----------------------------------------------------------
	��������:write_csv($append = false, $force = false)
	��Ҫ����:������д��csv�ļ������Դ��´�������ӡ�
	����:$append//�Ƿ�׷�� $force//ǿ�Ʋ���
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function write_csv($append = false, $force = false)
	{ 
		//�ж��ļ��Ƿ�ǿ�Ʋ���
		$cf = "$this->dir/$this->data_file";
		if (! $force) {
			if ($this->last_update != filemtime($cf)) {
				$this->print_error("FileCsv::write_csv: $cf ����ʧ�ܣ���Ϊ�ļ����޸Ĺ���");
				return false;
			} 
		} 
		//�ж��Ǵ��´���������ӣ����ж����������黹����ͨ����
		$mode = ($append ? 'ab+' : 'wb+');
		if ($this->assoc) {
			$loop = 0;
		} else {
			$loop = 1;
		} 
		//���ļ�ִ��д�����
		$fp = @fopen($cf, $mode);
		$tmp = '';
		if (is_resource($fp)) {
			foreach($this->db as $row) {
				if (($loop == 0) && (!$append)) {
					$loop = 1;
					$tmp .= $this->package(array_keys($row));
					$tmp .= $this->package($row);
					$this->written++ ;
				} else {
					$test = implode(' ', $row);
					if (trim($test) > '') { //���ǿ���
						$tmp .= $this->package($row);
						$this->written++ ;
					} 
				} 
			} 
			if ($this->written == 0) { //���д����Ϊ0
				$tmp .= " ";
			} 
			if (!@fwrite($fp, $tmp)) {
				@fclose($fp);
				$this->print_error("FileCsv::write_csv: д�� $cf (" . $this->written . " ������ʧ�� )");
				return false;
			} 
		} else {
			$this->print_error("FileCsv::write_csv: ���ܴ�csv�ļ� $cf �Ա�����");
			return false;
		} 
		return @fclose($fp);
	} 
	/*
	-----------------------------------------------------------
	��������:writeable()
	��Ҫ����:�ж�csv�ļ��Ƿ��д��
	����:void
	���:boolean
	�޸���־:------
	-----------------------------------------------------------
	*/
	function writeable()
	{
		$cf = "$this->dir/$this->data_file";
		if (!is_writeable($cf)) {
			return false;
		} else {
			return true;
		} 
	} 
	/*
	-----------------------------------------------------------
	��������:row_count()
	��Ҫ����:��ȡcsv�ļ�����
	����:void
	���:int
	�޸���־:------
	-----------------------------------------------------------
	*/
	function row_count()
	{ 
		return count($this->db);
	} 
	/*
	-----------------------------------------------------------
	��������:rows_to_write()
	��Ҫ����:��ȡҪд��csv�ļ�������
	����:void
	���:int
	�޸���־:------
	-----------------------------------------------------------
	*/
	function rows_to_write()
	{ 
		return count ($this->new_rows);
	} 
	/*
	-----------------------------------------------------------
	��������:package($aData)
	��Ҫ����:�������ļ�ת�����ַ�����ʽ�Ա�һ��д��
	����:array
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function package($aData)
	{ 
		if ($this->crypt_key) {
			while (list($key, $val) = each($aData)) {
				$this->encrypt($aData[$key], $this->crypt_key);
			} // while
		} 
		return '"' . implode ('","', $aData) . '"' . "\n";
	} 
	function xpackage($aData)
	{ 
		return '"' . implode ('","', $aData) . '"' . "\n";
	} 
	/*
	-----------------------------------------------------------
	��������:encrypt(&$txt, $key) 
	��Ҫ����:�����ݱ���
	����:mixd(��Ҫ�����ַ������������key)
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function encrypt(&$txt, $key) 
	{
		srand((double)microtime() * 1000000);
		$encrypt_key = md5(rand(0, 32000));
		$ctr = 0;
		$tmp = "";
		$tx = $txt;
		for ($i = 0;$i < strlen($tx);$i++) {
			if ($ctr == strlen($encrypt_key))
				$ctr = 0;
			$tmp .= substr($encrypt_key, $ctr, 1) . (substr($tx, $i, 1) ^ substr($encrypt_key, $ctr, 1));
			$ctr++;
		} 
		$txt = base64_encode($this->krypt($tmp, $key));
	} 
	/*
	-----------------------------------------------------------
	��������:decrypt(&$txt, $key) 
	��Ҫ����:�����ݽ���
	����:mixed(��Ҫ�����ַ������������key)
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function decrypt(&$txt, $key) 
	{
		$tx = $this->krypt(base64_decode($txt), $key);
		$tmp = "";
		for ($i = 0;$i < strlen($tx);$i++) {
			$md5 = substr($tx, $i, 1);
			$i++;
			$tmp .= (substr($tx, $i, 1) ^ $md5);
		} 
		$txt = $tmp;
	} 
	/*
	-----------------------------------------------------------
	��������:krypt($txt, $crypt_key) 
	��Ҫ����:Ϊ���ݱ��������׼��
	����:mixed
	���:string
	�޸���־:------
	-----------------------------------------------------------
	*/
	function krypt($txt, $crypt_key) 
	{
		$md5 = md5($crypt_key);
		$ctr = 0;
		$tmp = "";
		for ($i = 0;$i < strlen($txt);$i++) {
			if ($ctr == strlen($md5)) $ctr = 0;
			$tmp .= substr($txt, $i, 1) ^ substr($md5, $ctr, 1);
			$ctr++;
		} 
		return $tmp;
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
		$PHPSEA_ERROR['FileCsv_Error'] = $str;
	
		//�ж��Ƿ���ʾ�������..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>FileCsv Error --</b> ";
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
	
	//ȥ������ո�
	function onespace($a) 
	{
		$b = str_replace('  ', ' ', $a);
		return $b;
	} 
	//����չ��
	class NewCsv extends FileCsv 
	{
		var $unpacked = '';
		function unpack_csv()
		{
			if (($this->read_csv() === true) && (count($this->db) >= 1)) {
				// echo $this->crypt_key?$this->crypt_key:'';
				foreach ($this->db as $row) {
					$this->unpacked .= $this->xpackage ($row);
				} 
			} 
			return $this->unpacked;
		} 
	} 

//===================================================================
?>