<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.0
- �ļ���:Oracle.class.php
- ԭ����:Justin Vincent
- ������:indraw
- ��д����:2004/11/21
- ��Ҫ����:����Oracle���ݿ�����༯,��Ȩ����ԭ���ߡ�indrawֻ������
-          �����͹淶��.�������˼������ֵ�bug��
- ���л���:php4���ϣ�oracle7��8��9
- �޸ļ�¼:2004/11/21��indraw��������
---------------------------------------------------------------------
*/

/*
	define("Oracle_DB_USER", "sys");					// ���ݿ��û���
	define("Oracle_DB_PASSWORD", "change_on_install");	// ���ݿ�����
	define("Oracle_DB_NAME", "myfriend");				// ���ݿ���
	$db = new Oracle(Oracle_DB_USER, Oracle_DB_PASSWORD, Oracle_DB_NAME );
														// ��ʼ�����ݿ������
*/
/*
	Oracle($dbuser, $dbpassword, $dbname)
	select($db)
	escape($str)
	print_error($str = "")
	show_errors()
	hide_errors()
	flush()
	query($query)
	get_var($query=null,$x=0,$y=0)
	get_row($query=null,$output=OBJECT,$y=0)
	get_col($query=null,$x=0)
	get_results($query=null, $output = OBJECT)
	get_col_info($info_type="name",$col_offset=-1)
	vardump($mixed='')
	debug()
*/

//=============================================================================
	//Ԥ���峣��
	define("SQL_VERSION","1.0");
	define("OBJECT","OBJECT",true);
	define("ARRAY_A","ARRAY_A",true);
	define("ARRAY_N","ARRAY_N",true);

//-----------------------------------------------------------------------------
	class DBOracle
	{

		var $debug_all = false;			//�Ƿ���ʾ������Ϣ
		var $show_errors = true;		//�Ƿ���ʾ������ʾ
		
		var $num_queries = 0;			//��ʼ����ѯ����
		var $last_query;				//����ѯ��¼
		var $col_info;					//��ȡ�ֶ�����

		var $debug_called;				//�ж��Ƿ��Ѿ�debug���
		var $vardump_called;			//�ж��Ƿ��Ѿ�var���

		/*
		-----------------------------------------------------------
		��������:Oracle($dbuser, $dbpassword, $dbname)
		��Ҫ����:���ӵ����ݿ������������ѡ�����ݿ��Ա�����
		����:mixed (�û��������룬���ݿ���)
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function DBOracle($dbuser, $dbpassword, $dbname)
		{

			$this->dbh = OCILogon($dbuser, $dbpassword, $dbname);

			if ( ! $this->dbh )
			{
				$this->print_error("<ol><b>����:���ܽ������ݿ����ӣ�</b><li>�Ƿ���������ȷ���û��������룿<li>�Ƿ���������ȷ����������<li>���ݿ�������Ƿ����У�<li></ol>");
			}
			else
			{
				// ��ʼ�����������Ա��Ժ�ѡ�������ݿ�ʹ��
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbname = $dbname;
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
			$this->Oracle($this->dbuser, $this->dbpassword, $dbname);
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
			return str_replace("'","''",str_replace("''","'",stripslashes($str)));		
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
				$error = OCIError();
				$str = $error["message"] . "-" . $error["code"];
			}

			//�Ѵ���ֵ��ȫ��array..
			$SQL_ERROR[] = array 
							(
								"query" => $this->last_query,
								"error_str"  => $str
							);

			//�ж��Ƿ���ʾ�������..
			if ( $this->show_errors )
			{
				print "<blockquote><font face=arial size=2 color=ff0000>";
				print "<b>SQL/DB Error --</b> ";
				print "[<font color=000077>$str</font>]";
				print "</font></blockquote>";
			}
			else
			{
				return false;	
			}
		}

		/*
		-----------------------------------------------------------
		��������:is_equal_str($str='')
		��Ҫ����:�˺����Ը�oracle��ѯ""��ʱ�򲻷���null�ֶ����ݵ����
		����:string
		���:string
		�޸���־:------
		-----------------------------------------------------------
		*/
		function is_equal_str($str='')
		{
			return ($str==''?'IS NULL':"= '".$this->escape($str)."'");
		}

		function is_equal_int($int)
		{
			return ($int==''?'IS NULL':'= '.$int);
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
		function insert_id($seq_name)
		{
			return $this->get_var("SELECT $seq_name.nextVal id FROM Dual");
		}

		function sysdate()
		{
			return "SYSDATE";
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

			//������ѯ���
			if ( ! $stmt = OCIParse($this->dbh, $query))
			{
				$this->print_error();
			}

			//ִ�в�ѯ���
			elseif ( ! $this->result = OCIExecute($stmt))
			{
				$this->print_error();
			}

			$this->num_queries++;

			//ִ��insert, delete, update, replace����
			if ( preg_match('/^(insert|delete|update|create)\s+/i', $query) )
			{
				//��ȡ������Ӱ��ļ�¼����
				$return_value = $this->rows_affected = OCIRowCount($stmt);
			}
			//ִ��select����
			else
			{
				//��ȡ�ֶ���Ϣ
				if ( $num_cols = @OCINumCols($stmt) )
				{
	    			for ( $i = 1; $i <= $num_cols; $i++ )
	    			{
	    				$this->col_info[($i-1)]->name = OCIColumnName($stmt,$i);
	    				$this->col_info[($i-1)]->type = OCIColumnType($stmt,$i);
	    				$this->col_info[($i-1)]->size = OCIColumnSize($stmt,$i);
				    }
				}

				//��ȡ��ѯ���
				if ($this->num_rows = @OCIFetchStatement($stmt,$results))
				{
					//�������ת��ɶ�����Ϊoracle�ķ��ؽ���Ƚ���֣���
					foreach ( $results as $col_title => $col_contents )
					{
						$row_num=0;
						//ѭ�����е���
						foreach (  $col_contents as $col_content )
						{
							$this->last_result[$row_num]->{$col_title} = $col_content;
							$row_num++;
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
			//����x,y�����ӻ��������л�ȡ����
			if ( $this->last_result[$y] )
			{
				$values = array_values(get_object_vars($this->last_result[$y]));
			}
			//�����ȡֵ����ô���أ����򷵻ؿա�
			return (isset($values[$x]) && $values[$x]!=='')?$values[$x]:null;
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
			//��ȡ��ֵ
			for ( $i=0; $i < count($this->last_result); $i++ )
			{
				$new_array[$i] = $this->get_var(null,$x,$i);
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
			echo "</font></pre></font></blockquote></td></tr></table>".$this->php_sql();
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

			if ( $this->col_info )
			{

				// --------------------------------------------------
				// ��ʾ��һ��
				echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
				echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";


				for ( $i=0; $i < count($this->col_info); $i++ )
				{
					echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->col_info[$i]->type} {$this->col_info[$i]->max_length}</font><br><span style='font-family: arial; font-size: 10pt; font-weight: bold;'>{$this->col_info[$i]->name}</span></td>";
				}

				echo "</tr>";

				// --------------------------------------------------
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

		//����php�����ݿ�汾
		function php_sql()
		{
			$get_version = explode("-",oci_server_version($this->dbh));
			return "ENV:&nbsp;php ".phpversion()."[Oracle ".$get_version[0]."]";	
		}

	}//end class
//=============================================================================
?>
