<?
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ�����templates.func.php
- ��д�ߣ�indraw
- �����ߣ�indraw
- ��д���ڣ�2004/11/16
- ��Ҫ��������ʵ��smarty������ģ��
- ���л�����������
- �޸ļ�¼��2004/11/16��indraw��������
---------------------------------------------------------------------
*/

/*
	get_template($page)
	import_template($tplfile, $outfile)
*/

//=============================================================================

	define("phpsea", "1.0");
	$PS["TMP"]=array();
	$PS["DATA"]=array();

	/*
	-----------------------------------------------------------
	�������ƣ�get_template($page)
	��Ҫ�������ж�ģ���ļ��Ƿ���Ҫ����
	���룺�����õ�ģ���ļ�
	��������ҳ��
	�޸���־��------
	-----------------------------------------------------------
	*/
	function get_template($page)
	{
		//��ʼ��ģ���ļ�·�� //Ϊ�Ժ���ƶ�ģ�棬���û���׼��
		$PS=$GLOBALS["PS"];
		if(!empty($PS["args"]["template"])) {
			$PS["template"]=basename($PS["args"]["template"]);
		}
		if((!isset($PS['display_fixed']) || !$PS['display_fixed']) && isset($PS['user']['user_template'])) {
			$PS['template']=$PS['user']['user_template'];
		}
		if(empty($PS["template"])){
			$PS["template"]=$PS["default_template"];
		}
		$tpl="./templates/$PS[template]/$page";
		//���ģ���ļ�Ϊphp����ôֱ�Ӷ��룬�������
		if(file_exists("$tpl.php")){
			$phpfile="$tpl.php";
		} else {
			$tplfile= $tpl;
			$phpfile="$PS[cache]/tpl-$PS[template]-$page-".md5(dirname(__FILE__)).".php";
			if(!file_exists($phpfile) || filemtime($tplfile) > filemtime($phpfile)){
				import_template($tplfile, $phpfile);
			}
		}
		
		//���ر�����ҳ��
		return $phpfile;

	}//end func

	/*
	-----------------------------------------------------------
	�������ƣ�import_template($tplfile, $outfile)
	��Ҫ��������ģ���ļ����б������
	���룺ģ���ļ������������ļ���
	������������Ļ�����д���ļ�
	�޸���־��------
	-----------------------------------------------------------
	*/
	function import_template($tplfile, $outfile)
	{
		global $PS;

		//����ģ���ļ�
		$fp=fopen($tplfile, "r");
		$page=fread($fp, filesize($tplfile));
		fclose($fp);

		$page = str_replace("?>","<?php echo '?>\n' ?>",$page);
		$page = str_replace("<?xml","<?php echo '<?xml' ?>",$page);
		//����ģ���ļ�
		preg_match_all("/\{[\!\/A-Za-z].+?\}/s", $page, $matches);
		settype($oldloopvar, "string");
		settype($loopvar, "string");
		settype($olddatavar, "string");
		settype($datavar, "string");

		//ѭ������
		foreach($matches[0] as $match){

			unset($parts);
			$string=substr($match, 1, -1);

			//Ԥ�������ָʾ��ͷ
			if(strstr($string, "->")){
				$string=str_replace("->", "']['", $string);
			}
			$parts=explode(" ", $string);

			switch(strtolower($parts[0])){
				//ע��
				case "!":
					$repl="<?php // ".implode(" ", $parts)." ?>";
					break;

				//�����ļ�
				case "include":
					$repl="<?php include_once get_template('$parts[1]'); ?>";
					break;
				
				//����������������ļ�
				case "include_var": // include a file given by a variable
					$repl="<?php include_once get_template( \$PS[\"DATA\"]['$parts[1]']); ?>";
					break;						  

				//��������ʹ�õı���
				case "define":
					$repl="<?php \$PS[\"TMP\"]['$parts[1]']='";
					array_shift($parts);
					array_shift($parts);
					foreach($parts as $part){
						$repl.=str_replace("'", "\\'", $part)." ";
					}
					$repl=trim($repl)."'; ?>";
					break;

				//����ģ���ļ�ʹ�õı���				  
				case "var":
					$repl="<?php \$PS[\"DATA\"]['$parts[1]']='";
					array_shift($parts);
					array_shift($parts);
					foreach($parts as $part){
						$repl.=str_replace("'", "\\'", $part)." ";
					}
					$repl=trim($repl)."'; ?>";
					break;


				//��ʼһ��ѭ��
				case "loop":
					$loopvars[$parts[1]]=true;
					$repl="<?php if(isset(\$PS['DATA']['$parts[1]']) && is_array(\$PS['DATA']['$parts[1]'])) foreach(\$PS['DATA']['$parts[1]'] as \$PS['TMP']['$parts[1]']){ ?>";
					break;

				//����һ��ѭ��
				case "/loop":
					$repl="<?php } unset(\$PS['TMP']['$parts[1]']); ?>";
					unset($loopvars[$parts[1]]);
					break;


				//��ʼһ�������ж�
				case "if":
				case "elseif":
					//����if��elseif
					$prefix = (strtolower($parts[0])=="if") ? "if" : "} elseif";
					//Ĭ������DATA
					$index="DATA";
					//���ѭ�����������������һ��ʹ��TMP
					if(strstr($parts[1], "'") && isset($loopvars)  && count($loopvars)){
						$varname=substr($parts[1], 0, strpos($parts[1], "'"));
						if(isset($loopvars[$varname])){						  
							$index="TMP";
						}
					}						  
					if(isset($parts[2])){
						if(!is_numeric($parts[2]) && !defined($parts[2])){
							$parts[2]="\"$parts[2]\"";
						}
						$repl="<?php $prefix(isset(\$PS['$index']['$parts[1]']) && \$PS['$index']['$parts[1]']==$parts[2]){ ?>";
					} else {
						$repl="<?php $prefix(isset(\$PS['$index']['$parts[1]']) && !empty(\$PS['$index']['$parts[1]'])){ ?>";
					}
					//�������ǰ׺
					$prefix="";
					break;

				//��ʼһ��else
				case "else":
					$repl="<?php } else { ?>";
					break;

				// ����һ�������ж�
				case "/if":
					$repl="<?php } ?>";
					break;
				
				//��ֵ���
				case "assign":
					if(defined($parts[2])){
						$repl="<?php $parts[1]; ?>";
						$repl="<?php \$PS[\"DATA\"]['$parts[1]']=$parts[2]";
					} else {
						//DATA��Ĭ������
						$index="DATA";
						//���ѭ�����������������һ��ʹ��TMP
						if(strstr($parts[2], "'") && isset($loopvars)  && count($loopvars)){
							$varname=substr($parts[2], 0, strpos($parts[2], "'"));
							if(isset($loopvars[$varname])){
								$index="TMP";
							}
						}
						$repl="<?php \$PS[\"DATA\"]['$parts[1]']=\$PS['$index']['$parts[2]']; ?>";
					}
					break;

				//��DATA��TMPֱ����ʾ�������������һ����
				default:
					if(defined($parts[0])){
						$repl="<?php echo $parts[0]; ?>";
					} else {
						//DATA��Ĭ������
						$index="DATA";
						//���ѭ�����������������һ��ʹ��TMP
						if(strstr($parts[0], "'") && isset($loopvars)  && count($loopvars)){
							$varname=substr($parts[0], 0, strpos($parts[0], "'"));
							if(isset($loopvars[$varname])){
								$index="TMP";
							}
						}
						$repl="<?php echo \$PS['$index']['$parts[0]']; ?>";
					}
			}
			//ִ���滻����
			$page=str_replace($match, $repl, $page);
		}

		//��������ģ��д�����ļ�
		if( $PS["template"] == 2 )
		{
			$page = iconv("gb2312", "UTF-8",$page);
		}
		if($fp=fopen($outfile, "w")){
			fputs($fp, "<?php if(!defined(\"phpsea\")) return; ?>\n");
			fputs($fp, $page);
			fclose($fp);
		}

	}//end func

//=============================================================================
?>