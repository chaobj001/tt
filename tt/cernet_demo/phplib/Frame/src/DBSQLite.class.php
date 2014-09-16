<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:SQLite.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2004/11/1
- ��Ҫ����:����sqlite���ݿ�����༯������Ϊindraw����Justin Vincent 
-          ��mysql��ʽ���±�д�����޸����ư汾��
- ���л���:php4.3��5��SQLite�汾Ҫ > 2.811��
- �޸ļ�¼:2004/11/1��indraw��������
---------------------------------------------------------------------
*/

/*
	$db = new DBSQLite( "./", "DB" );
*/
/*
	DBSQLite($dbpath, $dbname)                        //��ʼ�����ݿ�����
	select($db)                                       //ѡ��һ�����ݿ�
	escape($str)                                      //��ȫ�Թ���
	flush()                                           //��ջ���
	query($query)                                     //ִ��sql���
	get_var($query=null,$x=0,$y=0)                    //��ȡһ���ֶ�
	get_row($query=null,$output=OBJECT,$y=0)          //��ȡһ�м�¼
	get_col($query=null,$x=0)                         //��ȡһ��
	get_results($query=null, $output = OBJECT)        //��ȡ���м�¼
	get_col_info($info_type="name",$col_offset=-1)    //��ȡ�ֶ���Ϣ

	vardump($mixed='')                                //����-���������Ϣ
	debug()                                           //����-����sql��ѯ����
	print_error($str = "")                            //����-�������
	show_errors()                                     //����-��ʾ����
	hide_errors()                                     //����-���ش���
*/

//=============================================================================
	//Ԥ���峣��
	define("SQL_VERSION","1.0");              //�����汾
	define("OBJECT","OBJECT",true);           //Ԥ����������
	define("ARRAY_A","ARRAY_A",true);         //Ԥ�����������
	define("ARRAY_N","ARRAY_N",true);         //Ԥ��������

//-----------------------------------------------------------------------------
	class DBSQLite
	{
		var $debug_all = true;            //�Ƿ���ʾ������Ϣ
		var $show_errors = true;          //�Ƿ���ʾ������ʾ

		var $num_queries = 0;             //��ʼ����ѯ����
		var $last_query;                  //����ѯ��¼
		var $col_info;                    //��ȡ�ֶ�����

		var $debug_called;                //��ʾ
		var $vardump_called;              //����

		var $dbh;                        //���ݿ����Ӿ��
		var $func_call;                  //�����õķ���
		var $result;                     //query���صĽ����
		var $last_result;                //select�����(�������)

		var $num_rows;                   //select���صļ�¼����
		var $rows_affected;              //updateӰ��ļ�¼��
		var $insert_id;                  //������ļ�¼id
		/*
		-----------------------------------------------------------
		��������:DBSQLite($dbpath, $dbname)
		��Ҫ����:���ӵ�sqlite���ݿ��ļ�������ѡ�����ݿ��Ա�����
		����:mixed(���ݿ�·�������ݿ���)
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function DBSQLite($dbpath, $dbname)
		{
			$this->dbh = @sqlite_open($dbpath.$dbname);
			if ( !$this->dbh )
			{
				$this->print_error("Error","<ol><b>����:���ܽ������ݿ�����</b><li>�Ƿ�ȷ����������ȷ��·����<li>�Ƿ�ȷ����������ȷ�����ݿ��ļ�����<li>���ݿ�����Ƿ񱻰�װ��</ol>");
			}
		}//end func

		/*
		-----------------------------------------------------------
		��������:select($dbpath, $dbname)
		��Ҫ����:ѡ��һ�����ݿ��Ա�����
		����:mixed(���ݿ�·�������ݿ���)
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function select($dbpath, $dbname)
		{
			$this->SQLite($dbpath, $dbname);
		}//end func

		/*
		-----------------------------------------------------------
		��������:escape($str)
		��Ҫ����:ת��һ���ַ�����ȫ���� sqlite_query 
		����:string
		���:string
		�޸���־:------
		-----------------------------------------------------------
		*/
		function escape($str)
		{
			if (!get_magic_quotes_gpc()) {
				$lastname = addslashes($str);
			} else {
				$lastname = $str;
			}
			return sqlite_escape_string($lastname);
		}//end func

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
			//�������ѯ������ֶ����ԣ�����ѯ��¼
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

			//��ջ���..
			$this->flush();

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->query(\"$query\")";

			//��������ѯ��䣬���ڵ���..
			$this->last_query = $query;

			//if(!sqlite_complete($query)){
			//	$this->print_error("Invalid Query String Format",$query);
			//	return false;
			//} ��ѯǰ���д����飬�����С���
			
			//ͨ��mysql_query����ִ�в�ѯ����..
			$this->result = sqlite_query($this->dbh,$query);
			$this->num_queries++;
			
			//ִ��insert, delete, update, replace����
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) )
			{
					$this->rows_affected = sqlite_changes($this->dbh);
					$return_val = $this->rows_affected;

					//��ȡ�������¼id(��idΪAUTO_INCREMENT)
					if ( preg_match("/^(insert|replace)\s+/i",$query) )
					{
						$this->insert_id = sqlite_last_insert_rowid($this->dbh);
						//$return_val = $this->insert_id;
					}
			}
			//ִ��select����
			else
			{
				//��ȡ�ֶ���Ϣ
				$i=0;
				while($name = @sqlite_field_name($this->result,$i))
				{
					$this->col_info[$i++]->name = $name;
				}
				//��ȡ��ѯ���
				$num_rows=0;
				while ( $row = sqlite_fetch_object($this->result) )
				{
					//ȡ�ð�������Ľ������
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}
				//��ȡ��ѯ�������
				$this->num_rows = $num_rows;
				
				//����ѡ�еĽ������
				$return_val = $this->num_rows;
			}

			//�Ƿ���ʾ���еĲ�ѯ��Ϣ
			$this->debug_all ? $this->debug() : null ;

			return $return_val;

		}//end func

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

			//����x,y�����ӻ��������л�ȡ����
			if ( $this->last_result[$y] )
			{
				$values = array_values(get_object_vars($this->last_result[$y]));
			}

			//�����ȡֵ����ô���أ����򷵻ؿա�
			return (isset($values[$x]) && $values[$x]!=='')?$values[$x]:null;

		}//end func

		/*
		-----------------------------------------------------------
		��������:get_row($query=null,$output=OBJECT,$y=0)
		��Ҫ����:�����ݿ��ȡһ�м�¼
		����:string
		���:---
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_row($query=null,$output=OBJECT,$y=0)
		{

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->get_row(\"$query\",$output,$y)";

			//����в�ѯ�����ô��ѯ���������û���...
			if ( $query )
			{
				$this->query($query);
			}

			//��ƫ�����а�����Ҫ����������..
			if ( $output == OBJECT )
			{
				return $this->last_result[$y]?$this->last_result[$y]:null;
			}
			//��ƫ�����а�����Ҫ�����������..
			elseif ( $output == ARRAY_A )
			{
				return $this->last_result[$y]?get_object_vars($this->last_result[$y]):null;
			}
			//��ƫ�����а�����Ҫ�������..
			elseif ( $output == ARRAY_N )
			{
				return $this->last_result[$y]?array_values(get_object_vars($this->last_result[$y])):null;
			}
			//�������Ƿ�����ʾ����..
			else
			{
				$this->print_error(" \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N");
			}

		}//end func

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

			//��ȡ��ֵ
			for ( $i=0; $i < count($this->last_result); $i++ )
			{
				$new_array[$i] = $this->get_var(null,$x,$i);
			}

			return $new_array;

		}//end func

		/*
		-----------------------------------------------------------
		��������:get_results($query=null, $output = OBJECT)
		��Ҫ����:�����ݿⷵ�ض��в�ѯ���
		����:string
		���:array
		�޸���־:------
		-----------------------------------------------------------
		*/
		function get_results($query=null, $output = OBJECT)
		{

			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->get_results(\"$query\", $output)";

			//����в�ѯ�����ô��ѯ���������û���...
			if ( $query )
			{
				$this->query($query);
			}
			//���ض�������. ÿһ�м�¼Ϊһ������.
			if ( $output == OBJECT )
			{
				return $this->last_result;
			}
			//�������󷵻�������������
			elseif ( $output == ARRAY_A || $output == ARRAY_N )
			{
				if ( $this->last_result )
				{
					$i=0;
					foreach( $this->last_result as $row )
					{

						$new_array[$i] = get_object_vars($row);

						if ( $output == ARRAY_N )
						{
							$new_array[$i] = array_values($new_array[$i]);
						}

						$i++;
					}

					return $new_array;
				}
				else
				{
					return null;
				}
			}
		}//end func

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

		}//end func

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
				echo "<font color=800080><b>SQL</b> (v".SQL_VERSION.") <b>Variable Dump..</b></font>&nbsp;".$this->php_sql()."\n\n";
			}

			$var_type = gettype ($mixed);
			print_r(($mixed?$mixed:"<font color=red>No Value / False</font>"));
			echo "\n\n<b>��������:</b> " . ucfirst($var_type) . "\n";
			echo "<b>����ѯ���</b> [$this->num_queries]<b>:</b> ".($this->last_query?$this->last_query:"NULL")."\n";
			echo "<b>�����ú���:</b> " . ($this->func_call?$this->func_call:"None")."\n";
			echo "<b>��󷵻�����:</b> ".count($this->last_result)."\n";
			echo "</font></pre></font></blockquote></td></tr></table>";
			echo "\n<hr size=1 noshade color=dddddd>";

			$this->vardump_called = true;

		}//end func

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

			echo "<blockquote>";

			// ֻ��ʾһ��ͷ��Ϣ.
			if ( ! $this->debug_called )
			{
				echo "<font color=800080 face=arial size=2><b>SQL</b> (v".SQL_VERSION.") <b>Debug..</b></font>&nbsp;".$this->php_sql()."<p>\n";
			}
			echo "<font face=arial size=2 color=000099><b>��ѯ���:</b> [$this->num_queries] <b>--</b> ";
			echo "[<font color=000000><b>$this->last_query</b></font>]</font><p>";

				echo "<font face=arial size=2 color=000099><b>��ѯ���...</b></font>";
				echo "<blockquote>";

			if ( $this->col_info )
			{

				// =====================================================
				// ��ʾ��һ��
				echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
				echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";

				for ( $i=0; $i < count($this->col_info); $i++ )
				{
					echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->col_info[$i]->type} {$this->col_info[$i]->max_length}</font><br><span style='font-family: arial; font-size: 10pt; font-weight: bold;'>{$this->col_info[$i]->name}</span></td>";
				}

				echo "</tr>";

				// ======================================================
				// ��ʾ��ѯ���

				if ( $this->last_result )
				{

					$i=0;
					foreach ( $this->get_results(null,ARRAY_N) as $one_row )
					{
						$i++;
						echo "<tr bgcolor=ffffff><td bgcolor=eeeeee nowrap align=middle><font size=2 color=555599 face=arial>$i</font></td>";

						foreach ( $one_row as $item )
						{
							echo "<td nowrap><font face=arial size=2>$item</font></td>";
						}

						echo "</tr>";
					}

				} //������Ϊ��
				else
				{
					echo "<tr bgcolor=ffffff><td colspan=".(count($this->col_info)+1)."><font face=arial size=2>No Results...</font></td></tr>";
				}

			echo "</table>";

			} // ����ֶ�����Ϊ��
			else
			{
				echo "<font face=arial size=2>No Results</font>";
			}

			echo "</blockquote></blockquote><hr noshade color=dddddd size=1>";

			$this->debug_called = true;
		}//end func

		/*
		-----------------------------------------------------------
		��������:print_error($str = "")
		��Ҫ����:��ʾ���ݿ��������
		����:string
		���:echo or false
		�޸���־:------
		-----------------------------------------------------------
		*/
		function print_error($title = "SQL/DB Error", $str = "")
		{
			//����ȫ�ֱ���$SQL_ERROR..
			global $PHPSEA_ERROR;
			//���û�нػ������ô����sqlite�Զ������..
			if ( !$str )
			{
				$str = sqlite_error_string($this->dbh);
			}
			//�Ѵ���ֵ��ȫ��array..
			/*
			$PHPSEA_ERROR['DBSQLite'] = array
							(
								"query" => $this->last_query,
								"error_str"  => $str
							);
			*/
			//�ж��Ƿ���ʾ�������..
			$PHPSEA_ERROR['DBSQLite'] = $str;
			if ( $this->show_errors )
			{
				print "<blockquote><font face=arial size=2 color=ff0000>";
				print "<b>$title --</b> ";
				print "[<font color=000077>$str</font>]";
				print "</font></blockquote>";
			}
			else
			{
				return false;
			}
		}//end func

		/*
		-----------------------------------------------------------
		��������:show_errors()/hide_errors()
		��Ҫ����:������ʾ����
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
	
		//����php�����ݿ�汾
		function php_sql()
		{
			$get_version = sqlite_libversion ();
			return "<font color=800080 face=arial size=2><b>ENV:</b>&nbsp;(php ".phpversion()." - SQLite ".$get_version.")</font>";	
		}


	}//end class
//=============================================================================
?>
