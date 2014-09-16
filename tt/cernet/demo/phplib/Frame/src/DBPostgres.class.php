<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.0
- �ļ���:Postgres.class.php
- ԭ����:Justin Vincent
- ������:indraw
- ��д����:2004/11/17
- ��Ҫ����:����PostgreSQL���ݿ�����༯,��Ȩ����ԭ���ߡ�indrawֻ������
-          �����͹淶��.�������˼������ֵ�bug��
- ���л���:phpΪ > 4.2,PostgreSQL�汾Ҫ >= 6.5,��� >= 7.0��
- �޸ļ�¼:2004/11/17��indraw��������
---------------------------------------------------------------------
*/

/*
	define("Postgres_DB_USER", "postgres");			// ���ݿ��û���
	define("Postgres_DB_PASSWORD", "111111");		// ���ݿ�����
	define("Postgres_DB_NAME", "test");				// ���ݿ���
	define("Postgres_DB_HOST", "localhost");		// ��������ַ
	$db = new Postgres(Postgres_DB_USER, Postgres_DB_PASSWORD, Postgres_DB_NAME, Postgres_DB_HOST);
													// ��ʼ�����ݿ������
*/
/*
	Postgres($dbuser, $dbpassword, $dbname, $dbhost)
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
	class Postgres 
	{

		var $show_errors = true;		//�Ƿ���ʾ������Ϣ
		var $debug_all = false;			//�Ƿ���ʾ������ʾ

		var $num_queries = 0;			//��ʼ����ѯ����
		var $last_query;				//����ѯ��¼
		var $col_info;					//��ȡ�ֶ�����
		
		var $debug_called;				//�ж��Ƿ��Ѿ�debug���
		var $vardump_called;			//�ж��Ƿ��Ѿ�var���
		
		/*
		-----------------------------------------------------------
		��������:Postgres($dbuser, $dbpassword, $dbname, $dbhost)
		��Ҫ����:���ӵ����ݿ������������ѡ�����ݿ��Ա�����
		����:mixed (�û��������룬���ݿ�������������)
		���:void
		�޸���־:------
		-----------------------------------------------------------
		*/
		function Postgres($dbuser, $dbpassword, $dbname, $dbhost)
		{
			$connect_str = "";
			if ( !empty($dbhost) ) $connect_str .= " host=$dbhost";
			if ( !empty($dbname) ) $connect_str .= " dbname=$dbname";
			if ( !empty($dbuser) ) $connect_str .= " user=$dbuser";
			if ( !empty($dbpassword) ) $connect_str .= " password=$dbpassword";
	
			$this->dbh = @pg_connect($connect_str);
	
			if ( ! $this->dbh )
			{
				$this->print_error("<ol><b>����:���ܽ������ݿ����ӣ�</b><li>�Ƿ���������ȷ���û��������룿</li><li>�Ƿ���������ȷ����������</li><li>���ݿ�������Ƿ����У�</li></ol>");
			}
			else
			{
				// ��ʼ�����������Ա��Ժ�ѡ�������ݿ�ʹ��
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbname = $dbname;
				$this->dbhost = $dbhost;
			}
		}//end func

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
			$this->Postgres($this->dbuser, $this->dbpassword, $db, $this->dbhost);
		}

		/*
		-----------------------------------------------------------
		��������:escape($str)
		��Ҫ����:ת��һ���ַ�����ȫ���� pg_query 
		����:string
		���:string
		�޸���־:------
		-----------------------------------------------------------
		*/
		function escape($str)
		{
			return pg_escape_string(stripslashes($str));				
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
	
			//���û�нػ������ô����PostgreSQL�Զ������..
			if ( !$str ) $str = pg_last_error();
	
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
		}//end func

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
			//�������ѯ������ֶ����ԣ�����ѯ��¼
			$this->last_result = null;
			$this->col_info = null;
			$this->last_query = null;
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
		function get_insert_id($query) 
		{
				//������һ������� oid
				$this->last_oid = pg_last_oid($this->result);
				
				//��sql����л�ȡ����
				eregi ("insert *into *([^ ]+).*", $query, $regs);
				$table_name = $regs[1];
				
				//ִ�в�ѯ����
				$query_for_id = "SELECT * FROM $table_name WHERE oid='$this->last_oid'";
				$result_for_id = pg_query($this->dbh, $query_for_id);
				
				//����������id
				if(pg_num_rows($result_for_id)) {
					$id = pg_fetch_array($result_for_id,0,PGSQL_NUM);
					return $id[0];
				}
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
			
			//��ʼ������ֵΪ0
			$return_val = 0;
			
			//��ջ���..
			$this->flush();
	
			//��¼�˺�����α����ã����ڵ���...
			$this->func_call = "\$db->query(\"$query\")";
	
			//��������ѯ��䣬���ڵ���..
			$this->last_query = $query;
	
			//ͨ��pg_query����ִ�в�ѯ����..
			if (!$this->result = @pg_query($this->dbh,$query)) {
				$this->print_error();
				return false;
			}
			
			//��¼��ѯ���������ڵ���...
			$this->num_queries++;
	

			//ִ��insert, delete, update, replace����
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) )
			{
				//��ȡ������Ӱ��ļ�¼����
				$this->rows_affected = pg_affected_rows($this->result);
				$return_val = $this->rows_affected;
				
				//��ȡ�������¼id
				if ( preg_match("/^(insert)\s+/i",$query ))
				{
					$this->insert_id = $this->get_insert_id($query);
					//$return_val = $this->insert_id;
				}
			}

			//ִ��select����
			else{
				//��ȡ�ֶ���Ϣ
				$i=0;
				while ($i < @pg_num_fields($this->result))
				{
					$this->col_info[$i]->name = pg_field_name($this->result,$i);
					$this->col_info[$i]->type = pg_field_type($this->result,$i);
					$this->col_info[$i]->size = pg_field_size($this->result,$i);
					$i++;
				}

				//��ȡ��ѯ���
				$i=0;
				while ( $row = @pg_fetch_object($this->result, $i) )
				{//php5�в�֧�ֵ���������PGSQL_ASSOC�������Ѿ���ɾ�������ֲ��ϻ�û��˵����
				
					//ȡ�ð�������Ľ������
					$this->last_result[$i] = $row;
					$i++;
				}
				@pg_free_result($this->result);

				//��ȡ��ѯ�������
				$this->num_rows = $i;
				
				$return_val = $this->num_rows;
			}

			//�Ƿ���ʾ���еĲ�ѯ��Ϣ
			$this->debug_all ? $this->debug() : null ;

			return $return_val;
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
				$this->print_error(" \$db->get_row(string query, output type, int offset) -- ������ͱ�����: OBJECT, ARRAY_A, ARRAY_N");
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
			}//end elseif

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
				echo "<font color=800080><b>SQL</b> (v".SQL_VERSION.") <b>Variable Dump..</b></font>&nbsp;".$this->php_sql()."\n\n";
			}

			$var_type = gettype ($mixed);
			print_r(($mixed?$mixed:"<font color=red>No Value / False</font>"));
			echo "\n\n<b>��������::</b> " . ucfirst($var_type) . "\n";
			echo "<b>����ѯ���</b> [$this->num_queries]<b>:</b> ".($this->last_query?$this->last_query:"NULL")."\n";
			echo "<b>�����ú���:</b> " . ($this->func_call?$this->func_call:"None")."\n";
			echo "<b>��󷵻�����:</b> ".count($this->last_result)."\n";
			echo "</font></pre></font></blockquote></td></tr></table>";
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
				echo "<font color=800080 face=arial size=2><b>SQL</b> (v".SQL_VERSION.") <b>Debug..</b></font>&nbsp;".$this->php_sql()."<p>\n";
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
					//---------------------------------------------------
				} // ������Ϊ��
				else
				{
					echo "<tr bgcolor=ffffff><td colspan=".(count($this->col_info)+1)."><font face=arial size=2>No Results</font></td></tr>";
				}
				echo "</table>";
			} // ����ֶ�����Ϊ��
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
			$get_version = "";
			return "<font color=800080 face=arial size=2><b>ENV</b>&nbsp;(php ".phpversion()." - PostgSQL".$get_version.")";	
		}

	}//end class
//=============================================================================
?>