<?php
//�����ļ���չ��
define('EXT','chaozi');
/**
*Filie:File_DIR_Opration �ļ�Ŀ¼ɾ������
 * ���ܣ�һ����Ŀ�����е�.svnĿ¼
 * ���Ը�����Ҫ��չ
 *@param $path �ļ�Ŀ¼
 * return true
*/
function delete_file($path) {
    if(! is_writable($path))
    {
        if(! chmod($path,0777)) {
            echo '�ļ�����ʧ�ܣ�~';exit();
        }
    }
    $dh = opendir($path);
    while(($file = readdir($dh)) !== false) {
        if(($file !=".") && ($file !="..")) {
            if(is_dir($path.'/'.$file)) {
                if($file == '.svn') {
                    full_rmdir($path.'/'.$file);
                    continue;
                }
                delete_file($path.'/'.$file);
            }
        }
    }
    return false;
}
/**
 * ���Ŀ¼���е�ɾ��
 * @param $dir Ŀ¼·��
 *
 */
function full_rmdir( $dir ) {
    if( !is_writable( $dir ))
    {
        if( !chmod( $dir ,0777))
        {
            return false;
        }
    }
    $dh = opendir( $dir );
    while( FALSE !== ( $entry = readdir( $dh ) ))
    {
        if( $entry =='.' || $entry == '..')
        {
            continue;
        }
        $entry = $dir . '/' . $entry;
        if( is_dir($entry) )
        {
            if( !full_rmdir( $entry ))
            {
                return false;
            }
            continue;
        }
        if( !@unlink( $entry ))
        {
            //�������ɾ���޸��ļ�Ȩ�ޣ���ɾ������������޸��ļ�Ȩ�ޱ���~
            if(! is_writable($entry))
            {
                if(! chmod($entry,0777))
                {
                    echo 'not chmod!~';
                }
            }
            if(! unlink($entry))
            {
                echo "##Error:file-".$entry." is not person";
            }
            echo "<br />DELETE -file:" . $entry;
            continue;
        }
    }
   //�����ж��Ƿ�ɾ��Ŀ¼
	closedir($dh);
    if(rmdir( $dir ))
    {
        echo "<br /><font color=red>" . $dir."</font>";
        $entry = $dir;
    }
    return true;
}
/**
*��ȡ�ļ�����չ��
*/
function get_file_ext($filename)
{
    $ext = pathinfo($filename);
    if(empty($ext['extension']))
    {
        die('Unknow file extension!');
    }
    return $ext['extension'];
}
$dir = "D:/www/OpenSource/frog";
$filename = $dir.'/index.chaozi';
delete_file($dir);
echo get_file_ext($filename);

//full_rmdir( $dir );
?>