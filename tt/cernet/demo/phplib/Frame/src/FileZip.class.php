<?php
/*
---------------------------------------------------------------------
- 项目: DNS phpsea
- 版本: 1.5
- 文件名:FileZip.class.php
- 原作者:Devin Doucette
- 整理者:indraw
- 编写日期:2004/11/4
- 简要描述:TAR/GZIP/BZIP2/ZIP 文件操作类集
- 运行环境:php4或以上
- 修改记录:2004/11/4，indraw，程序创立
---------------------------------------------------------------------
*/

/*
	$test = new gzip_file("test.tgz");
	$test->set_options(array('basedir'=>"../",'overwrite'=>1,'level'=>1));
	$test->add_files("db");
	$test->exclude_files("db/*.swf");
	$test->store_files("db/*.txt");
	$test->create_archive();

	$test = new gzip_file("test.tgz");
	$test->set_options(array('overwrite'=>1));
	$test->extract_files();
*/

/*
	set_options($options)    //设置压缩参数
	create_archive()         //执行压缩操作
	add_data($data)          //向压缩包中写数据
	make_list()              //获取所有将要压缩的文件
	add_files($list)         //添加文件名称
	exclude_files($list)     //设置压缩时过滤的文件类型
	store_files($list)       //设置哪些文件不压缩，只是打包存储
	list_files($list)        //获取文件全路径
	parse_dir($dirname)      //格式化目录
	sort_files($a,$b)        //文件类型
	download_file()          //下载压缩包

	archive($name)           //核心类
	tar_file($name)          //tar类
	gzip_file($name)         //gzip类
	bzip_file($name)         //bzip类
	zip_file($name)          //zip类
*/

//===================================================================
class archive
{
	var $show_errors = false;			//是否显示出错信息

	function archive($name)
	{
		$this->options = array(
			'basedir'=>".",				//压缩包路径
			'name'=>$name,				//压缩包名
			'prepend'=>"",				//预处理路径
			'inmemory'=>0,				//是否将压缩包放内存中
			'overwrite'=>0,				//如果压缩包存在是否覆盖
			'recurse'=>1,				//是否采用递归目录压缩
			'storepaths'=>1,			//是否压缩目录结构
			'level'=>3,					//压缩率
			'method'=>1,				//???(1文件小，0文件大)
			'sfx'=>"",					//???
			'type'=>"",					//类型
			'comment'=>""				//注释
		);
		$this->files = array();
		$this->exclude = array();
		$this->storeonly = array();
		$this->error = array();
	}
	/*
	-----------------------------------------------------------
	函数名称:set_options($options)
	简要描述:设置压缩参数
	输入:array (具体设置查看上面$this->options)
	输出:void
	修改日志:------
	-----------------------------------------------------------
	*/
	function set_options($options)
	{
		foreach($options as $key => $value)
		{
			$this->options[$key] = $value;
		}
		if(!empty($this->options['basedir']))
		{
			$this->options['basedir'] = str_replace("\\","/",$this->options['basedir']);
			$this->options['basedir'] = preg_replace("/\/+/","/",$this->options['basedir']);
			$this->options['basedir'] = preg_replace("/\/$/","",$this->options['basedir']);
		}
		if(!empty($this->options['name']))
		{
			$this->options['name'] = str_replace("\\","/",$this->options['name']);
			$this->options['name'] = preg_replace("/\/+/","/",$this->options['name']);
		}
		if(!empty($this->options['prepend']))
		{
			$this->options['prepend'] = str_replace("\\","/",$this->options['prepend']);
			$this->options['prepend'] = preg_replace("/^(\.*\/+)+/","",$this->options['prepend']);
			$this->options['prepend'] = preg_replace("/\/+/","/",$this->options['prepend']);
			$this->options['prepend'] = preg_replace("/\/$/","",$this->options['prepend']) . "/";
		}
	}
	/*
	-----------------------------------------------------------
	函数名称:create_archive()
	简要描述:执行压缩操作
	输入:void
	输出:boolean
	修改日志:------
	-----------------------------------------------------------
	*/
	function create_archive()
	{
		$this->make_list();

		if($this->options['inmemory'] == 0)
		{
			$pwd = getcwd();
			chdir($this->options['basedir']);
			if($this->options['overwrite'] == 0 && file_exists($this->options['name'] . ($this->options['type'] == "gzip" || $this->options['type'] == "bzip"? ".tmp" : "")))
			{
				$this->print_error("FileZip::create_archive: 压缩包 {$this->options['name']} 已经存在.");
				chdir($pwd);
				return 0;
			}
			else if($this->archive = @fopen($this->options['name'] . ($this->options['type'] == "gzip" || $this->options['type'] == "bzip"? ".tmp" : ""),"wb+"))
			{
				chdir($pwd);
			}
			else
			{
				$this->print_error("FileZip::create_archive: 不能打开压缩包 {$this->options['name']} 进行写操作.");
				chdir($pwd);
				return 0;
			}
		}
		else
		{
			$this->archive = "";
		}

		switch($this->options['type'])
		{
		case "zip":
			if(!$this->create_zip())
			{
				$this->print_error("FileZip::create_archive: 不能生成压缩包 zip 文件.");
				return 0;
			}
			break;
		case "bzip":
			if(!$this->create_tar())
			{
				$this->print_error("FileZip::create_archive: 不能生成压缩包 tar 文件.");
				return 0;
			}
			if(!$this->create_bzip())
			{
				$this->print_error("FileZip::create_archive: 不能生成压缩包 bzip2 文件.");
				return 0;
			}
			break;
		case "gzip":
			if(!$this->create_tar())
			{
				$this->print_error("FileZip::create_archive: 不能生成压缩包 tar 文件.");
				return 0;
			}
			if(!$this->create_gzip())
			{
				$this->print_error("FileZip::create_archive: 不能生成压缩包 gzip 文件.");
				return 0;
			}
			break;
		case "tar":
			if(!$this->create_tar())
			{
				$this->print_error("FileZip::create_archive: 不能生成压缩包 tar 文件.");
				return 0;
			}
		}

		if($this->options['inmemory'] == 0)
		{
			fclose($this->archive);
			if($this->options['type'] == "gzip" || $this->options['type'] == "bzip")
			{
				unlink($this->options['basedir'] . "/" . $this->options['name'] . ".tmp");
			}
		}
	}
	/*
	-----------------------------------------------------------
	函数名称:add_data($data)
	简要描述:向压缩包中写数据
	输入:string
	输出:void
	修改日志:------
	-----------------------------------------------------------
	*/
	function add_data($data)
	{
		if($this->options['inmemory'] == 0)
		{
			fwrite($this->archive,$data);
		}
		else
		{
			$this->archive .= $data;
		}
	}
	/*
	-----------------------------------------------------------
	函数名称:make_list()
	简要描述:获取所有将要压缩的文件
	输入:void
	输出:array
	修改日志:------
	-----------------------------------------------------------
	*/
	function make_list()
	{
		if(!empty($this->exclude))
		{
			foreach($this->files as $key => $value)
			{
				foreach($this->exclude as $current)
				{
					if($value['name'] == $current['name'])
					{
						unset($this->files[$key]);
					}
				}
			}
		}
		if(!empty($this->storeonly))
		{
			foreach($this->files as $key => $value)
			{
				foreach($this->storeonly as $current)
				{
					if($value['name'] == $current['name'])
					{
						$this->files[$key]['method'] = 0;
					}
				}
			}
		}
		unset($this->exclude,$this->storeonly);
	}
	/*
	-----------------------------------------------------------
	函数名称:add_files($list) 
	简要描述:添加文件名称
	输入:array
	输出:void (进行类属性附值)
	修改日志:------
	-----------------------------------------------------------
	*/
	function add_files($list)
	{
		$temp = $this->list_files($list);
		foreach($temp as $current)
		{
			$this->files[] = $current;
		}
	}
	/*
	-----------------------------------------------------------
	函数名称:exclude_files($list)
	简要描述:设置压缩时过滤的文件类型
	输入:array
	输出:void
	修改日志:------
	-----------------------------------------------------------
	*/
	function exclude_files($list)
	{
		$temp = $this->list_files($list);
		foreach($temp as $current)
		{
			$this->exclude[] = $current;
		}
	}
	/*
	-----------------------------------------------------------
	函数名称:store_files($list)
	简要描述:设置哪些文件不压缩，只是打包存储
	输入:array
	输出:void
	修改日志:------
	-----------------------------------------------------------
	*/
	function store_files($list)
	{
		$temp = $this->list_files($list);
		foreach($temp as $current)
		{
			$this->storeonly[] = $current;
		}
	}
	/*
	-----------------------------------------------------------
	函数名称:list_files($list)
	简要描述:获取文件全路径
	输入:array
	输出:array
	修改日志:------
	-----------------------------------------------------------
	*/
	function list_files($list)
	{
		if(!is_array($list))
		{
			$temp = $list;
			$list = array($temp);
			unset($temp);
		}

		$files = array();

		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach($list as $current)
		{
			$current = str_replace("\\","/",$current);
			$current = preg_replace("/\/+/","/",$current);
			$current = preg_replace("/\/$/","",$current);
			if(strstr($current,"*"))
			{
				$regex = preg_replace("/([\\\^\$\.\[\]\|\(\)\?\+\{\}\/])/","\\\\\\1",$current);
				$regex = str_replace("*",".*",$regex);
				$dir = strstr($current,"/")? substr($current,0,strrpos($current,"/")) : ".";
				$temp = $this->parse_dir($dir);
				foreach($temp as $current2)
				{
					if(preg_match("/^{$regex}$/i",$current2['name']))
					{
						$files[] = $current2;
					}
				}
				unset($regex,$dir,$temp,$current);
			}
			else if(@is_dir($current))
			{
				$temp = $this->parse_dir($current);
				foreach($temp as $file)
				{
					$files[] = $file;
				}
				unset($temp,$file);
			}
			else if(@file_exists($current))
			{
				$files[] = array('name'=>$current,'name2'=>$this->options['prepend'] .
					preg_replace("/(\.+\/+)+/","",($this->options['storepaths'] == 0 && strstr($current,"/"))? 
					substr($current,strrpos($current,"/") + 1) : $current),'type'=>0,
					'ext'=>substr($current,strrpos($current,".")),'stat'=>stat($current));
			}
		}

		chdir($pwd);

		unset($current,$pwd);

		usort($files,array("archive","sort_files"));

		return $files;
	}
	/*
	-----------------------------------------------------------
	函数名称:parse_dir($dirname)
	简要描述:格式化目录
	输入:string
	输出:string
	修改日志:------
	-----------------------------------------------------------
	*/
	function parse_dir($dirname)
	{
		if($this->options['storepaths'] == 1 && !preg_match("/^(\.+\/*)+$/",$dirname))
		{
			$files = array(array('name'=>$dirname,'name2'=>$this->options['prepend'] . 
				preg_replace("/(\.+\/+)+/","",($this->options['storepaths'] == 0 && strstr($dirname,"/"))? 
				substr($dirname,strrpos($dirname,"/") + 1) : $dirname),'type'=>5,'stat'=>stat($dirname)));
		}
		else
		{
			$files = array();
		}
		$dir = @opendir($dirname);

		while($file = @readdir($dir))
		{
			if($file == "." || $file == "..")
			{
				continue;
			}
			else if(@is_dir($dirname."/".$file))
			{
				if(empty($this->options['recurse']))
				{
					continue;
				}
				$temp = $this->parse_dir($dirname."/".$file);
				foreach($temp as $file2)
				{
					$files[] = $file2;
				}
			}
			else if(@file_exists($dirname."/".$file))
			{
				$files[] = array('name'=>$dirname."/".$file,'name2'=>$this->options['prepend'] . 
					preg_replace("/(\.+\/+)+/","",($this->options['storepaths'] == 0 && strstr($dirname."/".$file,"/"))? 
					substr($dirname."/".$file,strrpos($dirname."/".$file,"/") + 1) : $dirname."/".$file),'type'=>0,
					'ext'=>substr($file,strrpos($file,".")),'stat'=>stat($dirname."/".$file));
			}
		}

		@closedir($dir);

		return $files;
	}
	/*
	-----------------------------------------------------------
	函数名称:sort_files($a,$b)
	简要描述:文件类型
	输入:---
	输出:---
	修改日志:------
	-----------------------------------------------------------
	*/
	function sort_files($a,$b)
	{
		if($a['type'] != $b['type'])
		{
			return $a['type'] > $b['type']? -1 : 1;
		}
		else if($a['type'] == 5)
		{
			return strcmp(strtolower($a['name']),strtolower($b['name']));
		}
		else
		{
			if($a['ext'] != $b['ext'])
			{
				return strcmp($a['ext'],$b['ext']);
			}
			else if($a['stat'][7] != $b['stat'][7])
			{
				return $a['stat'][7] > $b['stat'][7]? -1 : 1;
			}
			else
			{
				return strcmp(strtolower($a['name']),strtolower($b['name']));
			}
		}
		return 0;
	}
	/*
	-----------------------------------------------------------
	函数名称:download_file()
	简要描述:下载压缩包
	输入:void
	输出:定位浏览器输出
	修改日志:------
	-----------------------------------------------------------
	*/
	function download_file()
	{
		if($this->options['inmemory'] == 0)
		{
			$this->print_error("FileZip::download_file: 只有当压缩包在内存中的时候，才能耐用 download_file() 函数下载。 但是压缩包放在内存中，执行压缩的速度非常快。");
			return;
		}
		switch($this->options['type'])
		{
		case "zip":
			header("Content-type:application/zip");
			break;
		case "bzip":
			header("Content-type:application/x-compressed");
			break;
		case "gzip":
			header("Content-type:application/x-compressed");
			break;
		case "tar":
			header("Content-type:application/x-tar");
		}
		$header = "Content-disposition: attachment; filename=\"";
		$header .= strstr($this->options['name'],"/")? substr($this->options['name'],strrpos($this->options['name'],"/") + 1) : $this->options['name'];
		$header .= "\"";
		header($header);
		header("Content-length: " . strlen($this->archive));
		header("Content-transfer-encoding: binary");
		header("Pragma: no-cache");
		header("Expires: 0");
		print($this->archive);
	}

	/*
	-----------------------------------------------------------
	函数名称:print_error($str = "")
	简要描述:显示操作错误信息
	输入:string 
	输出:echo or false
	修改日志:------
	-----------------------------------------------------------
	*/
	function print_error($str = "")
	{
		//设置全局变量$PHPSEA_ERROR..
		global $PHPSEA_ERROR;
		$PHPSEA_ERROR['FileZip_Error'] = $str;
	
		//判断是否显示错误输出..
		if ( $this->show_errors )
		{
			print "<blockquote><font face=arial size=2 color=ff0000>";
			print "<b>FileZip Error --</b> ";
			print "[<font color=000077>$str</font>]";
			print "</font></blockquote>";
		}
		else
		{
			return false;	
		}
	}//end func

}

//=============================================================================
//建立tar压缩包
class tar_file extends archive
{
	function tar_file($name)
	{
		$this->archive($name);
		$this->options['type'] = "tar";
	}

	function create_tar()
	{
		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach($this->files as $current)
		{
			if($current['name'] == $this->options['name'])
			{
				continue;
			}
			if(strlen($current['name2']) > 99)
			{
				$path = substr($current['name2'],0,strpos($current['name2'],"/",strlen($current['name2']) - 100) + 1);
				$current['name2'] = substr($current['name2'],strlen($path));
				if(strlen($path) > 154 || strlen($current['name2']) > 99)
				{
					$this->print_error("FileZip::create_tar: 不能填加 {$path}{$current['name2']} 到压缩包中，因为文件名太长。");
					continue;
				}
			}
			$block = pack("a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12",$current['name2'],decoct($current['stat'][2]),
				sprintf("%6s ",decoct($current['stat'][4])),sprintf("%6s ",decoct($current['stat'][5])),
				sprintf("%11s ",decoct($current['stat'][7])),sprintf("%11s ",decoct($current['stat'][9])),
				"        ",$current['type'],"","ustar","00","Unknown","Unknown","","",!empty($path)? $path : "","");

			$checksum = 0;
			for($i = 0; $i < 512; $i++)
			{
				$checksum += ord(substr($block,$i,1));
			}
			$checksum = pack("a8",sprintf("%6s ",decoct($checksum)));
			$block = substr_replace($block,$checksum,148,8);

			if($current['stat'][7] == 0)
			{
				$this->add_data($block);
			}
			else if($fp = @fopen($current['name'],"rb"))
			{
				$this->add_data($block);
				while($temp = fread($fp,1048576))
				{
					$this->add_data($temp);
				}
				if($current['stat'][7] % 512 > 0)
				{
					$temp = "";
					for($i = 0; $i < 512 - $current['stat'][7] % 512; $i++)
					{
						$temp .= "\0";
					}
					$this->add_data($temp);
				}
				fclose($fp);
			}
			else
			{
				$this->print_error("FileZip::create_tar: 不能打开 {$current['name']} 以备读取. 没有被添加.");
			}
		}

		$this->add_data(pack("a512",""));

		chdir($pwd);

		return 1;
	}

	function extract_files()
	{
		$pwd = getcwd();
		chdir($this->options['basedir']);

		if($fp = $this->open_archive())
		{
			if($this->options['inmemory'] == 1)
			{
				$this->files = array();
			}

			while($block = fread($fp,512))
			{
				$temp = unpack("a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100temp/a6magic/a2temp/a32temp/a32temp/a8temp/a8temp/a155prefix/a12temp",$block);
				$file = array(
					'name'=>$temp['prefix'] . $temp['name'],
					'stat'=>array(
						2=>$temp['mode'],
						4=>octdec($temp['uid']),
						5=>octdec($temp['gid']),
						7=>octdec($temp['size']),
						9=>octdec($temp['mtime']),
					),
					'checksum'=>octdec($temp['checksum']),
					'type'=>$temp['type'],
					'magic'=>$temp['magic'],
				);
				if($file['checksum'] == 0x00000000)
				{
					break;
				}
				else if($file['magic'] != "ustar")
				{
					$this->print_error("FileZip::extract_files: 本类库不支持这种tar压缩格式。");
					break;
				}
				$block = substr_replace($block,"        ",148,8);
				$checksum = 0;
				for($i = 0; $i < 512; $i++)
				{
					$checksum += ord(substr($block,$i,1));
				}
				if($file['checksum'] != $checksum)
				{
					$this->print_error("FileZip::extract_files: 不能解压缩 {$this->options['name']}, 文件有问题.");
				}

				if($this->options['inmemory'] == 1)
				{
					$file['data'] = fread($fp,$file['stat'][7]);
					fread($fp,(512 - $file['stat'][7] % 512) == 512? 0 : (512 - $file['stat'][7] % 512));
					unset($file['checksum'],$file['magic']);
					$this->files[] = $file;
				}
				else
				{
					if($file['type'] == 5)
					{
						if(!is_dir($file['name']))
						{
							mkdir($file['name'],$file['stat'][2]);
							chown($file['name'],$file['stat'][4]);
							chgrp($file['name'],$file['stat'][5]);
						}
					}
					else if($this->options['overwrite'] == 0 && file_exists($file['name']))
					{
						$this->error[] = "{$file['name']} already exists.";
					}
					else if($new = @fopen($file['name'],"wb"))
					{
						fwrite($new,fread($fp,$file['stat'][7]));
						fread($fp,(512 - $file['stat'][7] % 512) == 512? 0 : (512 - $file['stat'][7] % 512));
						fclose($new);
						chmod($file['name'],$file['stat'][2]);
						chown($file['name'],$file['stat'][4]);
						chgrp($file['name'],$file['stat'][5]);
					}
					else
					{
						$this->print_error("FileZip::extract_files: 不能打开 {$file['name']} 以备写操作.");
					}
				}
				unset($file);
			}
		}
		else
		{
			$this->print_error("FileZip::extract_files: 不能打开 {$this->options['name']}");
		}

		chdir($pwd);
	}

	function open_archive()
	{
		return @fopen($this->options['name'],"rb");
	}

} //end class

//=============================================================================
//建立gzip压缩包
class gzip_file extends tar_file
{
	function gzip_file($name)
	{
		$this->tar_file($name);
		$this->options['type'] = "gzip";
	}

	function create_gzip()
	{
		if($this->options['inmemory'] == 0)
		{
			$pwd = getcwd();
			chdir($this->options['basedir']);
			if($fp = gzopen($this->options['name'],"wb{$this->options['level']}"))
			{
				fseek($this->archive,0);
				while($temp = fread($this->archive,1048576))
				{
					gzwrite($fp,$temp);
				}
				gzclose($fp);
				chdir($pwd);
			}
			else
			{
				$this->print_error("FileZip::create_gzip: 不能打开 {$this->options['name']} 以备写操作.");
				chdir($pwd);
				return 0;
			}
		}
		else
		{
			$this->archive = gzencode($this->archive,$this->options['level']);
		}

		return 1;
	}

	function open_archive()
	{
		return @gzopen($this->options['name'],"rb");
	}
}//end class

//=============================================================================
//建立bzip压缩包
class bzip_file extends tar_file
{
	function bzip_file($name)
	{
		$this->tar_file($name);
		$this->options['type'] = "bzip";
	}

	function create_bzip()
	{
		if($this->options['inmemory'] == 0)
		{
			$pwd = getcwd();
			chdir($this->options['basedir']);
			if($fp = bzopen($this->options['name'],"wb"))
			{
				fseek($this->archive,0);
				while($temp = fread($this->archive,1048576))
				{
					bzwrite($fp,$temp);
				}
				bzclose($fp);
				chdir($pwd);
			}
			else
			{
				$this->print_error("FileZip::create_bzip: 不能打开 {$this->options['name']} 以备写操作.");
				chdir($pwd);
				return 0;
			}
		}
		else
		{
			$this->archive = bzcompress($this->archive,$this->options['level']);
		}

		return 1;
	}

	function open_archive()
	{
		return @bzopen($this->options['name'],"rb");
	}

}//end class

//=============================================================================
//建立zip压缩包
class zip_file extends archive
{
	function zip_file($name)
	{
		$this->archive($name);
		$this->options['type'] = "zip";
	}

	function create_zip()
	{
		$files = 0;
		$offset = 0;
		$central = "";

		if(!empty($this->options['sfx']))
		{
			if($fp = @fopen($this->options['sfx'],"rb"))
			{
				$temp = fread($fp,filesize($this->options['sfx']));
				fclose($fp);
				$this->add_data($temp);
				$offset += strlen($temp);
				unset($temp);
			}
			else
			{
				$this->print_error("FileZip::create_zip: 不能打开 sfx module 从 {$this->options['sfx']}.");
			}
		}

		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach($this->files as $current)
		{
			if($current['name'] == $this->options['name'])
			{
				continue;
			}

			$translate =  array('�'=>pack("C",128),'�'=>pack("C",129),'�'=>pack("C",130),'�'=>pack("C",131),'�'=>pack("C",132),
								'�'=>pack("C",133),'�'=>pack("C",134),'�'=>pack("C",135),'�'=>pack("C",136),'�'=>pack("C",137),
								'�'=>pack("C",138),'�'=>pack("C",139),'�'=>pack("C",140),'�'=>pack("C",141),'�'=>pack("C",142),
								'�'=>pack("C",143),'�'=>pack("C",144),'�'=>pack("C",145),'�'=>pack("C",146),'�'=>pack("C",147),
								'�'=>pack("C",148),'�'=>pack("C",149),'�'=>pack("C",150),'�'=>pack("C",151),'_'=>pack("C",152),
								'�'=>pack("C",153),'�'=>pack("C",154),'�'=>pack("C",156),'�'=>pack("C",157),'_'=>pack("C",158),
								'�'=>pack("C",159),'�'=>pack("C",160),'�'=>pack("C",161),'�'=>pack("C",162),'�'=>pack("C",163),
								'�'=>pack("C",164),'�'=>pack("C",165));
			$current['name2'] = strtr($current['name2'],$translate);

			$timedate = explode(" ",date("Y n j G i s",$current['stat'][9]));
			$timedate = ($timedate[0] - 1980 << 25) | ($timedate[1] << 21) | ($timedate[2] << 16) | 
				($timedate[3] << 11) | ($timedate[4] << 5) | ($timedate[5]);

			$block = pack("VvvvV",0x04034b50,0x000A,0x0000,(isset($current['method']) || $this->options['method'] == 0)? 0x0000 : 0x0008,$timedate);

			if($current['stat'][7] == 0 && $current['type'] == 5)
			{
				$block .= pack("VVVvv",0x00000000,0x00000000,0x00000000,strlen($current['name2']) + 1,0x0000);
				$block .= $current['name2'] . "/";
				$this->add_data($block);
				$central .= pack("VvvvvVVVVvvvvvVV",0x02014b50,0x0014,$this->options['method'] == 0? 0x0000 : 0x000A,0x0000,
					(isset($current['method']) || $this->options['method'] == 0)? 0x0000 : 0x0008,$timedate,
					0x00000000,0x00000000,0x00000000,strlen($current['name2']) + 1,0x0000,0x0000,0x0000,0x0000,$current['type'] == 5? 0x00000010 : 0x00000000,$offset);
				$central .= $current['name2'] . "/";
				$files++;
				$offset += (31 + strlen($current['name2']));
			}
			else if($current['stat'][7] == 0)
			{
				$block .= pack("VVVvv",0x00000000,0x00000000,0x00000000,strlen($current['name2']),0x0000);
				$block .= $current['name2'];
				$this->add_data($block);
				$central .= pack("VvvvvVVVVvvvvvVV",0x02014b50,0x0014,$this->options['method'] == 0? 0x0000 : 0x000A,0x0000,
					(isset($current['method']) || $this->options['method'] == 0)? 0x0000 : 0x0008,$timedate,
					0x00000000,0x00000000,0x00000000,strlen($current['name2']),0x0000,0x0000,0x0000,0x0000,$current['type'] == 5? 0x00000010 : 0x00000000,$offset);
				$central .= $current['name2'];
				$files++;
				$offset += (30 + strlen($current['name2']));
			}
			else if($fp = @fopen($current['name'],"rb"))
			{
				$temp = fread($fp,$current['stat'][7]);
				fclose($fp);
				$crc32 = crc32($temp);
				if(!isset($current['method']) && $this->options['method'] == 1)
				{
					$temp = gzcompress($temp,$this->options['level']);
					$size = strlen($temp) - 6;
					$temp = substr($temp,2,$size);
				}
				else
				{
					$size = strlen($temp);
				}
				$block .= pack("VVVvv",$crc32,$size,$current['stat'][7],strlen($current['name2']),0x0000);
				$block .= $current['name2'];
				$this->add_data($block);
				$this->add_data($temp);
				unset($temp);
				$central .= pack("VvvvvVVVVvvvvvVV",0x02014b50,0x0014,$this->options['method'] == 0? 0x0000 : 0x000A,0x0000,
					(isset($current['method']) || $this->options['method'] == 0)? 0x0000 : 0x0008,$timedate,
					$crc32,$size,$current['stat'][7],strlen($current['name2']),0x0000,0x0000,0x0000,0x0000,0x00000000,$offset);
				$central .= $current['name2'];
				$files++;
				$offset += (30 + strlen($current['name2']) + $size);
			}
			else
			{
				$this->print_error("FileZip::create_zip: 不能打开文件 {$current['name']} 以备读取. 没有被添加.");
			}
		}

		$this->add_data($central);

		$this->add_data(pack("VvvvvVVv",0x06054b50,0x0000,0x0000,$files,$files,strlen($central),$offset,
			!empty($this->options['comment'])? strlen($this->options['comment']) : 0x0000));

		if(!empty($this->options['comment']))
		{
			$this->add_data($this->options['comment']);
		}

		chdir($pwd);

		return 1;
	}

} //end class
//=============================================================================
?>