<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 2.0
- �ļ���:DBPDO.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2006/01/18
- ��Ҫ����:��pdo�����ݿ�������з�װ,������������php���Բ������������ݿ�
- ���л���:php5���ϣ�oracle7,8,9,10;SQLite2,3;MySQL4,5;��
- �޸ļ�¼:2006/01/18��indraw��������
---------------------------------------------------------------------
*/

/*
	define("DB_USER", "sys");                // ���ݿ��û���
	define("DB_PASSWORD", "");               // ���ݿ�����
	define("DB_NAME", "pdb");                // ���ݿ���
	$db = new Oracle(DB_USER, DB_PASSWORD, DB_NAME );
											// ��ʼ�����ݿ������
*/
/*
	DBPDO($dbuser, $dbpassword, $dbname)
	select($db)
	escape($str)
	flush()
	query($query)
	get_var($query=null,$x=0,$y=0)
	get_row($query=null,$output=OBJECT,$y=0)
	get_col($query=null,$x=0)
	get_results($query=null, $output = OBJECT)
	get_col_info($info_type="name",$col_offset=-1)
	vardump($mixed='')
	debug()
	show_errors()
	hide_errors()
	print_error($str = "")
*/
//=============================================================================
	//Ԥ���峣��
	define("SQL_VERSION","2.0");        //�����汾

//-----------------------------------------------------------------------------
	class DBPDO
	{

		var $debug_all = 0;          //�Ƿ���ʾ������Ϣ
		var $show_errors = 1;        //�Ƿ���ʾ������ʾ

		var $modify_log = false;          //�Ƿ��¼�������ݿ����

		var $num_queries = 0;           //��ʼ����ѯ����
		var $last_query;                //����ѯ��¼
		var $col_info;                  //��ȡ�ֶ�����

		var $debug_called;              //�ж��Ƿ��Ѿ�debug���
		var $vardump_called;            //�ж��Ƿ��Ѿ�var���

		var $dbh;                        //���ݿ����Ӿ��
		var $func_call;                  //�����õķ���
		var $result;                     //query���صĽ����
		var $last_result;                //select�����(�������)

		var $num_rows;                   //select���صļ�¼����
		var $rows_affected;              //updateӰ��ļ�¼��
		var $insert_id;                  //������ļ�¼id

		/*
		-----------------------------------------------------------
		��������:DBPDO($dbuser, $dbpassword, $dbname)
		��Ҫ����:���ӵ����ݿ������������ѡ�����ݿ��Ա�����
		����:mixed (�û��������룬���ݿ���)
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function DBPDO($dbuser, $dbpassword, $dbname)
		{
			try {
				$this->dbh = new PDO($dbname, $dbuser, $dbpassword);
				// ��ʼ�����������Ա��Ժ�ѡ�������ݿ�ʹ��
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbname = $dbname;
			}
			catch (PDOException $e) {
				$this->print_error($e->getMessage());
				//var_dump($this->dbh->errorInfo());
			}
		}

		/*
		-----------------------------------------------------------
		��������:select($db)
		��Ҫ����:�����Ҫ������ѡ��һ���µ����ݿ�
		����:string �����ݿ�����
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function select($db)
		{
			$this->DBPDO($this->dbuser, $this->dbpassword, $db);
		}

		/*
		-----------------------------------------------------------
		��������:escape($str)
		��Ҫ����:ת��һ���ַ�����ȫ����query 
		����:string
		���:string
		�޸���־:------
		-----------------------------------------------------------
		*/
		function escape($str)
		{
			return $this->dbh->quote($str);
		}

		/*
		-----------------------------------------------------------
		��������:get_insert_id($query) 
		��Ҫ����:��ȡ����������֮id
		����:string 
		���:int 
		�޸���־:------
		-----------------------------------------------------------
		*/
		function insert_id($seq_name="")
		{
			if($seq_name) {
				//�����debug���Ӷ��м�¼�л�ȡֵ
				if($this->last_result)
				{
					//var_dump($this->last_result);
					return $this->last_result[0];
				}
				return $this->get_var("SELECT $seq_name.nextVal id FROM Dual")-1;
			}
			return @$this->dbh->lastInsertId();
		}

		/*
		-----------------------------------------------------------
		��������:flush()
		��Ҫ����:��ջ���
		����:void
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function flush()
		{
			$this->last_result = null;
			$this->col_info = null;
			$this->last_query = null;
		}

		/*
		-----------------------------------------------------------
		��������:query($query)
		��Ҫ����:������ѯ����
		����:string (�����ѯ���)
		���:int (����)
		�޸���־:------
		-----------------------------------------------------------
		*/
		function query($query)
		{

			//ȥ����ѯ����ǰ��ո�
			$query = trim($query); 
			$return_value = 0;

			//��ջ���..
			$this->flush();

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->query(\"$query\")";

			//��������ѯ��䣬���ڵ���..
			$this->last_query = $query;

			//ִ��insert, delete, update, replace����
			if ( preg_match('/^(insert|delete|update|create)\s+/i', $query) )
			{
				//ִ�в�ѯ���
				/*
				if(!$this->rows_affected = $this->dbh->exec($query)) {
					$this->print_error();
				}
				*/
				
				try{
					$this->rows_affected = $this->dbh->exec($query);
				}
				catch (PDOException $e) 
				{
					$this->print_error();
				}

				//��¼��Ϣ
				$this->num_queries++;
				$return_value = $this->rows_affected;

				//��ȡ�������¼id(��idΪAUTO_INCREMENT)
				if ( preg_match("/^(insert|replace)\s+/i",$query) )
				{
					$this->insert_id = $this->insert_id();
					//$return_val = $this->insert_id;
				}
				//�Ƿ��¼��־
				$this->modify_log ? $this->sql_log("success") : null ;
			}
			//ִ��select����
			else
			{
				//ִ�в�ѯ���
				//ִ�в�ѯ���
				try{
					$this->result = $this->dbh->query($query);
				}
				catch (PDOException $e) 
				{
					$this->print_error();
				}
				if (!$this->result)
				{
					$this->print_error();
					return false;
				}
				//var_dump($this->result->rowCount());
				$this->num_rows = $this->result->rowCount();
				$this->num_queries++;

				//��ȡ�ֶ���Ϣ
				if ( $num_cols = $this->result->columnCount() )
				{
					for ( $i = 1; $i <= $num_cols; $i++ )
					{
						$aColInfo = @$this->result->getColumnMeta($i-1);
						//var_dump($aColInfo);
						if($aColInfo) {
							$this->col_info[($i-1)]['name'] = $aColInfo['name'];
							$this->col_info[($i-1)]['type'] = $aColInfo['native_type'];
							$this->col_info[($i-1)]['size'] = $aColInfo['len'];
						}
					}
				}
				//��ȡ��ѯ�������
				$return_value = $this->num_rows;
			}

			//�Ƿ���ʾ���еĲ�ѯ��Ϣ
			$this->debug_all ? $this->debug() : null ;

			return $return_value;

		}

		/*
		-----------------------------------------------------------
		��������:get_var($query=null,$x=0,$y=0)
		��Ҫ����:�����ݿ��ȡ��������
		����:mixed (��ѯ��䣬����������)
		���:string (�ֶ�����)
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_var($query=null,$x=0,$y=0)
		{

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->get_var(\"$query\",$x,$y)";

			//����в�ѯ�����ô��ѯ���������û���...
			if ( $query )
			{
				$this->query($query);
			}
			//�����debug���Ӷ��м�¼�л�ȡֵ
			if($this->last_result)
			{
				//echo "1";
				//var_dump($this->last_result);
				$tmp_array = array_values($this->last_result[$y]);
				return $tmp_array[$x];
			}
			//����x,y�����ӻ��������л�ȡ����
			if ( $this->result )
			{
				@$tmp_array = $this->result->fetchAll(3);
				//var_dump($tmp_array);
				return $tmp_array[$y][$x];
			}
		}

		/*
		-----------------------------------------------------------
		��������:get_row($query=null,$output=OBJECT,$y=0)
		��Ҫ����:�����ݿ��ȡһ�м�¼
		����:mixed (��ѯ��䣬�������ͣ�����)
		���:array (����Ҫ�󷵻أ����󣬹������飬��������)
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_row($query=null,$output="ARRAY_A",$y=0)
		{

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->get_row(\"$query\",$output,$y)";

			//����в�ѯ�����ô��ѯ���������û���...
			if ( $query )
			{
				$this->query($query);
			}
			//�����debug���Ӷ��м�¼�л�ȡֵ
			if($this->last_result)
			{
				//var_dump($this->last_result);
				return $this->last_result[$y];
			}
			//��ƫ�����а�����Ҫ����������..
			if ( $output == "OBJECT" )
			{
				$return_results = $this->result->fetchAll(1);
			}
			//��ƫ�����а�����Ҫ�����������..
			elseif ( $output == "ARRAY_A" )
			{
				$return_results = $this->result->fetchAll(2);
			}
			//��ƫ�����а�����Ҫ�������..
			elseif ( $output == "ARRAY_N" )
			{
				$return_results = $this->result->fetchAll(3);
			}
			//�������Ƿ�����ʾ����..
			else
			{
				$this->print_error(" \$db->get_row(string query, output type, int offset) -- ������ͱ�����: OBJECT, ARRAY_A, ARRAY_N");
			}
			$this->num_rows = count($return_results);
			return $return_results[$y];

		}

		/*
		-----------------------------------------------------------
		��������:get_col($query=null,$x=0)
		��Ҫ����:�����ݿ��ȡһ�м�¼������xΪ�ڼ��У�
		����:mixed (��ѯ��䣬�ڼ���)
		���:array
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_col($query=null,$x=0)
		{

			//����в�ѯ�����ô��ѯ���������û���...
			if ( $query )
			{
				$this->query($query);
			}
			//�����debug���Ӷ��м�¼�л�ȡֵ
			if($this->last_result)
			{
				//var_dump($this->last_result);
				foreach( $this->last_result as $old_array )
				{
					$tmp_array = array_values($old_array);
					$new_array[] = $tmp_array[$x];
				}
				return $new_array;
			}
			//��ȡ��ֵ
			$new_array = @$this->result->fetchAll(3);
			for ( $i=0; $i < count($new_array); $i++ )
			{
				$new_array[$i] = $new_array[$i][$x];
			}
			return $new_array;
		}

		/*
		-----------------------------------------------------------
		��������:get_results($query=null, $output = OBJECT)
		��Ҫ����:�����ݿⷵ�ض��в�ѯ���
		����:mixed (��ѯ��䣬��������)
		���:array (����Ҫ�󷵻ض��󣬹������飬��������)
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_results($query=null, $output = "ARRAY_A")
		{

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->get_results(\"$query\", $output)";
			//����в�ѯ�����ô��ѯ���������û���...
			if ( $query )
			{
				$this->query($query);
				//echo "1";
			}
			if($this->last_result)
			{
				return $this->last_result;
			}
			//���ض�������. ÿһ�м�¼Ϊһ������.
			if ( $output == "OBJECT" )
			{
				$this->last_result = $this->result->fetchAll(1);
			}
			//��ƫ�����а�����Ҫ�����������..
			elseif ( $output == "ARRAY_A" )
			{
				$this->last_result = @$this->result->fetchAll(2);
			}
			//��ƫ�����а�����Ҫ�������..
			elseif ( $output == "ARRAY_N" )
			{
				$this->last_result = $this->result->fetchAll(3);
			}
			//�������Ƿ�����ʾ����..
			else
			{
				$this->print_error(" \$db->get_results(string query, output type, int offset) -- ������ͱ�����: OBJECT, ARRAY_A, ARRAY_N");
			}
			$this->num_rows = count($this->last_result);

			return $this->last_result;
		}

		/*
		-----------------------------------------------------------
		��������:get_col_info($info_type="name",$col_offset=-1)
		��Ҫ����:��ȡ����ѯ������ֶ���Ϣ
		����:mixed (�ֶ���Ϣ���ԣ��ڼ����ֶ�)
		���:array
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_col_info($info_type="name",$col_offset=-1)
		{

			if ( $this->col_info )
			{
				if ( $col_offset == -1 )
				{
					$i=0;
					foreach($this->col_info as $col )
					{
						$new_array[$i] = $col->{$info_type};
						$i++;
					}
					return $new_array;
				}
				else
				{
					return $this->col_info[$col_offset]->{$info_type};
				}

			}

		}

		/*
		-----------------------------------------------------------
		��������:vardump($mixed='')
		��Ҫ����:����������������������ĸ�ʽ��ʾ����Ļ��
		����:mixed
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function vardump($mixed='')
		{

			echo "<p><table><tr><td bgcolor=ffffff><blockquote><font color=000090>";
			echo "<pre><font face=arial>";

			if ( ! $this->vardump_called )
			{
				echo "<font color=800080><b>SQL</b> (v".SQL_VERSION.") <b>Variable Dump..</b></font>\n\n";
			}

			$var_type = gettype ($mixed);
			print_r(($mixed?$mixed:"<font color=red>No Value / False</font>"));
			echo "\n\n<b>��������:</b> " . ucfirst($var_type) . "\n";
			echo "<b>����ѯ���</b> [$this->num_queries]<b>:</b> ".($this->last_query?$this->last_query:"NULL")."\n";
			echo "<b>�����ú���:</b> " . ($this->func_call?$this->func_call:"None")."\n";
			echo "<b>��󷵻�����:</b> ".count($this->last_result)."\n";
			echo "</font></pre>".$this->php_sql()."</font></blockquote></td></tr></table>";
			echo "\n<hr size=1 noshade color=dddddd>";

			$this->vardump_called = true;
		}

		/*
		-----------------------------------------------------------
		��������:debug()
		��Ҫ����:��ʾ���һ�����ݿ��ѯ��䣻��ʾһ����ص����ݱ�
		����:void
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function debug()
		{
			if($this->debug_all == 2) 
			{
				$this->debug_log("success");
				return true;
			}
			echo "<blockquote>";

			// ֻ��ʾһ��ͷ��Ϣ.
			if ( ! $this->debug_called )
			{
				echo "<font color=800080 face=arial size=2><b>SQL</b> (v".SQL_VERSION.") <b>Debug..&nbsp;".$this->php_sql()."</b></font><p>\n";
			}
			echo "<font face=arial size=2 color=000099><b>��ѯ���</b> [$this->num_queries] <b>--</b> ";
			echo "[<font color=000000><b>$this->last_query</b></font>]</font><p>";

				echo "<font face=arial size=2 color=000099><b>��ѯ���...</b></font>";
				echo "<blockquote>";

			//������ܻ�ȡ���Ե�����
			if( !$this->col_info and eregi("select",$this->last_query)) {
				$this->get_results(null,"ARRAY_A");
				if(is_array($this->last_result[0])) 
				{
					foreach (array_keys($this->last_result[0]) as $key=>$con_name)
					{
						$this->col_info[$key]['name'] = $con_name;
						$this->col_info[$key]['type'] = "δ֪";
						$this->col_info[$key]['size'] = "0";
					}
				}
			}
			//echo "test:";
			//var_dump($this->last_result[0]);
			if ( $this->col_info )
			{

				// --------------------------------------------------
				// ��ʾ��һ��
				echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
				echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";
				for ( $i=0; $i < count($this->col_info); $i++ )
				{
					echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->col_info[$i]['type']} {$this->col_info[$i]['size']}</font><br><span style='font-family: arial; font-size: 10pt; font-weight: bold;'>{$this->col_info[$i]['name']}</span></td>";
				}

				echo "</tr>";

				// --------------------------------------------------
				// ��ʾ��ѯ���

				if ( $this->result )
				{
					$i=0;
					foreach ( $this->get_results(null,"ARRAY_A") as $one_row )
					{
						$i++;
						echo "<tr bgcolor=ffffff><td bgcolor=eeeeee nowrap align=middle><font size=2 color=555599 face=arial>$i</font></td>";

						foreach ( $one_row as $item )
						{
							echo "<td nowrap><font face=arial size=2>$item</font></td>";
						}

						echo "</tr>";
					}
				//-------------------------------------------------------------
				}// ������Ϊ��
				else
				{
					echo "<tr bgcolor=ffffff><td colspan=".(count($this->col_info)+1)."><font face=arial size=2>No Results</font></td></tr>";
				}

				echo "</table>";

			}//����ֶ�����Ϊ��
			else
			{
				echo "<font face=arial size=2>No Results</font>";
			}

			echo "</blockquote></blockquote><hr noshade color=dddddd size=1>";

			$this->debug_called = true;
		}

		/*
		-----------------------------------------------------------
		��������:sql_log()
		��Ҫ����:��¼sql���
		����:void
		���:void 
		�޸���־:------
		-----------------------------------------------------------
		*/
		function sql_log($sResult)
		{
			global $LibSet;
			//$LibSet['LogDir'] = "./";
			$sToday = "sqlog".date("Y-m-d",time());
			//���ļ�
			$sFileName = $LibSet['LogDir']."PDB/".$sToday.".xml";
			if( !file_exists($sFileName))
			{
				$handle = @fopen ($sFileName,"w");
			}
			else
			{
				$handle = @fopen ($sFileName,"a");
			}
			//д������
			$sTime = date("H:i:s",time());
			$aContent[] = "<sqlitem>";
			$aContent[] = "\t<time>".$sTime."</time>";
			$aContent[] = "\t<ip>".$_SERVER["REMOTE_ADDR"]."</ip>";
			$msg =  @htmlspecialchars($this->last_query);
			$aContent[] = "\t<sql>".$msg."</sql>";
			$aContent[] = "\t<url>".getenv('REQUEST_URI')."</url>";
			$aContent[] = "\t<result>".htmlspecialchars($sResult)."</result>";
			$aContent[] = "</sqlitem>";
			$sContent = join("\n",$aContent);
			@fwrite($handle, $sContent."\n\n");
			//�ر��ļ�
			@fclose($handle);
		}

		/*
		-----------------------------------------------------------
		��������:debug_log()
		��Ҫ����:��¼Debug���
		����:void
		���:void 
		�޸���־:------
		-----------------------------------------------------------
		*/
		function debug_log($sResult)
		{
			global $LibSet;
			//$LibSet['LogDir'] = "./";
			$sToday = "debuglog".date("Y-m-d",time());
			//���ļ�
			$sFileName = $LibSet['LogDir']."PDB/".$sToday.".xml";
			if( !file_exists($sFileName))
			{
				$handle = @fopen ($sFileName,"w");
			}
			else
			{
				$handle = @fopen ($sFileName,"a");
			}
			//д������
			$sTime = date("H:i:s",time());
			$aContent[] = "<item>";
			$aContent[] = "\t<time>".$sTime."</time>";
			$aContent[] = "\t<ip>".$_SERVER["REMOTE_ADDR"]."</ip>";
			$msg =  @htmlspecialchars($this->last_query);
			$aContent[] = "\t<sql>".$msg."</sql>";
			$aContent[] = "\t<url>".getenv('REQUEST_URI')."</url>";
			$aContent[] = "\t<result>".htmlspecialchars($sResult)."</result>";
			$aContent[] = "</item>";
			$sContent = join("\n",$aContent);
			@fwrite($handle, $sContent."\n\n");
			//�ر��ļ�
			@fclose($handle);
		}

		/*
		-----------------------------------------------------------
		��������:show_errors()/hide_errors()
		��Ҫ����:���ô�����ʾ
		����:void
		���:void 
		�޸���־:------
		-----------------------------------------------------------
		*/
		function show_errors()
		{
			$this->show_errors = true;
		}
		
		function hide_errors()
		{
			$this->show_errors = false;
		}

		/*
		-----------------------------------------------------------
		��������:print_error($str = "")
		��Ҫ����:��ʾ���ݿ��������
		����:string
		���:echo or fause
		�޸���־:------
		-----------------------------------------------------------
		*/
		function print_error($str = "")
		{
			
			//����ȫ�ֱ���$SQL_ERROR..
			global $SQL_ERROR;
						
			//���û�нػ������ô����Oracle�Զ������..
			if ( !$str )
			{
				$error = $this->dbh->errorInfo();
				$str = $error[0] . "(".$error[1].")-" . $error[2];
			}

			//�Ѵ���ֵ��ȫ��array..
			$SQL_ERROR[] = array 
							(
								"query" => $this->last_query,
								"error_str"  => $str
							);
			if($this->show_errors == 2) 
			{
				$this->debug_log($str);
				return true;
			}
			//�ж��Ƿ���ʾ�������..
			if ( $this->show_errors )
			{
				print "<blockquote><font face=arial size=2 color=ff0000>";
				print "<b>SQL/DB Error --</b> ";
				print "[<font color=green>$this->last_query</font>][<font color=000077>$str</font>]";
				print "</font></blockquote>";
			}
			else
			{
				return false;	
			}
		}

		//����php�����ݿ�汾
		function php_sql()
		{
			$db_array = explode(":",$this->dbname);
			switch($db_array[0]) {
			case "mysql":
				$db_type = "MySQL";
			break;
			case "oci":
				$db_type = "Oracle";
			break;
			case "sqlite":
				$db_type = "SQLite";
			break;
			default:
				$db_type = "SQL";
			break;
			}
			return "ENV:&nbsp;php ".phpversion()."[$db_type ".@$this->dbh->getAttribute(4)."]";	
		}

	}//end class
//=============================================================================
?>
