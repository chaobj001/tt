<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>ClassFileCsv.phpʹ����ʾ</title>
<style type='text/CSS'>
body { 
font-family: "Arial"; font-size: 12px; 
}
pre { 
background-color:EDEDED; color:black ; 
font-size: 12px;padding:10px 10px 10px 10px;
}
</style>
</head>
<body>

<?php
//����csv�ļ�������
//by indraw
//2004/11/4

//-----------------------------------------------------------------------------
//������
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$vars = strtolower($_SERVER['REQUEST_METHOD']) == 'get' ? $_GET : $_POST;
require('FileCsv.class.php') ;
$phase = $vars['phase'] ;
$db = new FileCsv();
//$db->dir = "./";
// $db->crypt_key = 'EncryptionKey';		//���������ݽ��м��ܽ���
//echo "ClassFileCsv.php";

if (!isset($phase) || ($phase == '0')) {
	phase0($db);
} elseif ($phase == '1') {
	phase1($db);
} elseif ($phase == '2') {
	phase2_3($db, $var);
} elseif ($phase == '3') {
	phase2_3($db, $var, true);
} elseif ($phase == '4') {
	phase4($db, $vars);
} elseif ($phase == '5') {
	phase5($db, $_SERVER);
} 
//if ($PHPSEA_ERROR)  echo "���ִ��󣬺Ǻǡ�$PHPSEA_ERROR[FileCsv_Error]";
//-----------------------------------------------------------------------------
//��ȡһ�����飬��Ϊд���ļ���׼����
function phase0($db)
{
	echo "<H2>ʵ�� 0 - ���һ����ά����</H2>";

	$db->new_rows = getdata();
	$db->append();
	echo "<pre>";
	print_r($db->db) ;
	echo "</pre>";
	echo "���һ����ά���飬Ϊд��csv�ļ���׼����<br />\n";
	echo "<a href='$PHP_SELF?phase=1' > ��һ��</a>\n";

	endpage();
} 
//-----------------------------------------------------------------------------
//��һ����ά����д���ļ�����������ʾ��
function phase1($db)
{
	global $PHPSEA_ERROR;
	echo "<H2> ʵ�� 1 - ����һ��������д��csv�ļ�</H2>";
	$db->new_rows = getdata();
	$db->append();
	if ($db->write_csv(false, true)) { // Write database to file forcing overwrite/ file creation
		echo "<pre>";
		$cf = "$db->dir/$db->data_file";
		readfile($cf) ;
		echo "</pre>";
		echo "��һ����ά����д���ļ�����������ʾ��<br />\n";
		echo "<a href='$PHP_SELF?phase=0' > ��һ��</a>\n&nbsp;&nbsp;&nbsp;";
		echo "<a href='$PHP_SELF?phase=2' > ��һ��</a>\n";
	} else {
		echo "<Hr>".$PHPSEA_ERROR['FileCsv_Error'];
	} 
	endpage();
} 
//-----------------------------------------------------------------------------
//
function phase2_3($db, $var, $i = false)
{
	$db->assoc = $i; 
	// $db->crypt_key = ($var['cryp'] == 'on' ? 'Encryption' : false) ; // key is 'Encryption'
	echo "<H2> ʵ�� " . ($i?"3":"2") . " - ��csv�ļ��ж������� - " . ($i?"����":"����") . " ����</H2>";
	if ($db->read_csv()) {
		$row = 0;
		echo "<table>";
		foreach($db->db as $d) {
			echo "<tr>\n";
			echo "<td>\n";
			echo sprintf("%8s", "Row  " . $row++ . "  ");
			echo "</td>\n";
			while (list($key, $val) = each($d)) {
				echo "<td>";
				echo sprintf("%8s", $key) . "=>" . sprintf("%12s", $val);
				echo "</td>\n";
			} // while
			echo "</tr>\n";
		} 
		// echo "<tr><td> Encryption</td>";//
		// echo "<td> <input type='checkbox' name='cryp'></td>";
		// echo "</tr>";
		echo "</table>\n";
		echo "<pre>";
		echo "\n";
		$cf = "$db->dir/$db->data_file";
		readfile($cf) ;
		echo "</pre>";
		if ($i) {
			echo "��csv�ļ��ж������ݣ� \n";
			echo "���Թ��������ʽ��ʾ��<br />\n";
			echo "ע�⣺�����ļ��е���������һ�У���Ϊ��һ�б������洢������<br />";
			echo "<a href='$PHP_SELF?phase=2' > ��һ��</a>\n&nbsp;&nbsp;&nbsp;";
			echo "<a href='$PHP_SELF?phase=4' > ��һ��</a>\n";
		} else {
			echo "��csv�ļ��ж������ݣ� \n";
			echo "�������������ʽ��ʾ�� <br />\n";
			echo "<a href='$PHP_SELF?phase=1' > ��һ��</a>\n&nbsp;&nbsp;&nbsp;";
			echo "<a href='$PHP_SELF?phase=3' > ��һ��</a>\n";
		} 
	} else {
		echo $PHPSEA_ERROR['FileCsv_Error'];
	} 
	endpage();
} 
//-----------------------------------------------------------------------------
//�ļ����ң����£�ɾ������
function phase4($db, $vars)
{
	$db->assoc = true;
	echo "<H2> ʵ�� 4 - ���ң����£�ɾ��</H2>";
	if ($db->read_csv()) { // read DB file
		// 
		$findKey = $vars['fk'];
		$findVal = $vars['fv'];
		$newVal = $vars['nv'];
		$a = array($findKey => $findVal) ; //ע�⣺��������������
		
		// ����and��ѯ�������ʹ��or�����Լ�д����,
		$b = $db->find($a); 
		//���Ҳ���
		if ($vars['fnd']) { // basic find routine
			echo "���Ҽ���<span class='b'>$findKey</span> ֵ�� <span class='b'>$findVal</span><br /> ";
			if ($b) {
				reset($b);
				while (list($key, $val) = each ($b)) {
					// echo "�ҵ�һ�� <span class='b'>$val</span><br />\n";
					echo "<style type='text/css' > #ID" . $val . "_" . $findKey . " {background-color:aqua;border:blue solid 1px;} </style>\n";
				} // while
			} else {
				echo "û���ҵ���";
			} 
		} 
		if ($vars['upd']) { 
			if ($b) {
				while (list($key, $val) = each($b)) {
					if ($db->update($val, array($findKey => $newVal))) {
						// echo "Replaced <span class='b'>$findVal</span> with <span class='b'>$newVal</span> in row <span class='b'>" . $b['0'] . "</span><br />";
						echo "<style type='text/css' > #ID" . $val . "_" . $findKey . " {background-color:yellow;border:blue solid 1px;} </style>\n";
					} else {
						$PHPSEA_ERROR['FileCsv_Error'];
					} 
				} 
			} 
		} 
		if ($vars['del']) { // find all matching values and process them all - in reverse order !
			if ($b) {
				rsort($b);
				reset($b);
				while (list($key, $val) = each($b)) {
					if ($db->delete($val)) {
						echo "ɾ�� <span class='b'>" . $b['0'] . "</span> ���ݼ� <span class='b'>$findKey</span> ֵ <span class='b'>$findVal</span><br />";
					} else {
						echo $PHPSEA_ERROR['FileCsv_Error'];
					} 
				} // while
			} 
		} 

		//�����º���ļ�д��
		if ($vars['cbx'] == 'on') {
			if ($db->write_csv()) {
				echo "�ļ�д��";
			} else {
				echo $PHPSEA_ERROR['FileCsv_Error'];
			} 
		} 
		$row = 0;
		echo "<form>";
		echo "<input type = 'hidden' name ='phase' value = '4' >";
		echo "<table>";
		foreach($db->db as $d) {
			echo "<tr>\n";
			echo "<td>\n";
			echo sprintf("%8s", "Row  " . $row . "  ");
			echo "</td>\n";
			// $cl = 0;
			while (list($key, $val) = each($d)) {
				echo "<td id='ID" . $row . "_" . $key . "'>"; 
				// $cl++;
				echo sprintf("%8s", $key) . "=>" . sprintf("%12s", $val);
				echo "</td>\n";
			} // while
			$row++;
			echo "</tr>\n";
		} 
		echo "<tr><td colspan=6><hr /></td></tr>";
		echo "<tr>";
		echo "<td colspan =2 class ='l'>";
		echo "<input type = 'submit' name='fnd' value = ' ���� ' >";
		echo "</td>\n";
		echo "<td colspan =1 class='r'>";
		echo "������:";
		echo "</td>\n";
		echo "<td colspan =3 class='l'><input type = 'text' name = 'fk' style='width:120px'>";
		echo "</td>\n";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan =2 class ='l'>";
		echo "<input type = 'submit' name ='del' value = ' ɾ�� ' >";
		echo "</td>\n";
		echo "<td colspan =1 class='r'>";
		echo "ƥ��ֵ:";
		echo "<td colspan =3 class='l'><input type = 'text' name = 'fv' style='width:120px'>";
		echo "</td>\n";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan =2 class ='l'>";
		echo "<input type = 'submit' name = 'upd' value = ' ���� ' >";
		echo "</td>\n";
		echo "<td colspan =1 class='r'>";
		echo "��ֵ:";
		echo "<td colspan =3 class='l'><input type = 'text' name = 'nv' style='width:120px'>";
		echo "</td>\n";
		echo "</tr>";
		echo "<tr><td colspan =2 > д���ļ�</td>";
		echo "<td> <input type='checkbox' name='cbx'></td>";
		echo "</tr>";
		echo "<tr><td colspan=6><hr /></td></tr>";

		echo "</table><form>\n";
		echo "<a href='$PHP_SELF?phase=1' > ���¼�������</a>\n";
		echo "<a href='$PHP_SELF?phase=3' > ��һ��</a>\n";
		echo "<a href='$PHP_SELF?phase=5' > ��һ��</a>\n";
	} else {
		echo $PHPSEA_ERROR['FileCsv_Error'];
	} 
	endpage();
} 

//-----------------------------------------------------------------------------
function phase5($log, $server)
{
	echo "<H2> ʵ�� 5 - д����־</H2>";

	$log->data_file = 'Log.csv';
	$log->new_rows[] = array(date('Y:m:d-H:i:s'), $server['HTTP_USER_AGENT'], $server['HTTP_REFERER']);
	$log->append_csv();
	if ($log->read_csv()) {
		echo "<table>";
		echo "<tr><td> ���� <hr /></td><td> ����� <hr /></td><td> ����url <hr /></td></tr>";
		foreach($log->db as $d) {
			echo "<tr>\n";
			while (list($key, $val) = each($d)) {
				echo "<td>";
				echo $val;
				echo "</td>\n";
			} // while
			echo "</tr>\n";
		} 
		echo "</table>\n";
	} else {
		echo $PHPSEA_ERROR['FileCsv_Error'];
	} 
	echo "<a href='$PHP_SELF?phase=5' > ����ִ����־����</a>\n";
	echo "<a href='$PHP_SELF?phase=4' > ��һ��</a>\n";
	echo "<a href='$PHP_SELF?phase=0' > ��һ��</a>\n";
	endpage();
} 


//-----------------------------------------------------------------------------
function getdata()
{
	$data1[] = array('car_maker', 'fruit', 'river', 'town', 'county');
	$data1[] = array('Rover', 'orange', 'lea', 'hertford', 'herts');
	$data1[] = array('Vauxhall', 'apple', 'mimram', 'ware', 'essex');
	$data1[] = array('Volkswagen', 'bananna', 'ash', 'London', 'hants');
	$data1[] = array('BMW', 'grape', 'rib', 'Welwyn', 'devon');
	$data1[] = array('ford', 'lemon', 'thames', 'stevenage', 'cornwall');
	return $data1;
} 

function endpage()
{
	echo "</body>";
	echo "</html>";
} 

?>