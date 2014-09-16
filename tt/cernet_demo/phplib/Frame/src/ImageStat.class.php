<?php
/*
---------------------------------------------------------------------
- ��Ŀ: DNS phpsea
- �汾: 1.5
- �ļ���:ImageStat.class.php
- ԭ����:indraw
- ������:indraw
- ��д����:2004/11/11
- ��Ҫ����:����ͳ��ͼƬ��ʾ����༯��Ϊ�ۺϼ��������±�д
- ���л���:��ҪGD��֧�֣��汾���Ϊ2.0���ϣ����磺2.0.28
- �޸ļ�¼:2004/11/11��indraw��������
---------------------------------------------------------------------
*/

//-----------------------------------------------------------------------------
//����Ϊ����ʹ�÷���
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	//����
	$newStat = new ImageStat();
	$newStat->set_data($a);
	if($action == "line"){
		$newStat->set_size("500","150");
		$newStat->stat_line();
	}
	//����
	if($action == "pie"){
		$newStat->set_size("300","150");
		$newStat->stat_pie();
	}
	//��״
	if($action == "bar"){
		$newStat->set_size("400","150");
		$newStat->set_border(20,20,20,20);
		$newStat->set_items("1M,2M,3M,4M,5M,6M,7M,8M,9M,10M,11M,12M");
		$newStat->stat_bar();
	}
/*
	set_data($data)                        //��ͳ�����ݸ���������$this->statistic�Ա�ʹ��
	set_size($width,$height)               //����ͼ���ߣ�Ҳ����ͳ��ͼ���ǿ�Ƴ���
	set_border($left,$right,$top,$down)    //����ͳ��ͼ��ͼ��߿�ľ���
	set_items($items)                      //��ͳ����Ŀд�������Ա�ʹ��
	set_color($color)                      //����ǰ����ɫ
	set_bkcolor($color)                    //���ñ�����ɫ
	stat_pie()                             //����ͼ
	stat_line()                            //����ͼ
	stat_bar()                             //��״ͼ
*/
//=============================================================================
class ImageStat
{
	var $img_width  = 300;		//����ͼ����
	var $img_height = 150;		//����ͼ��߶�

	var $statistic  = array();	//���屻ͳ����������
	var $items      = array();	//����ѡ��

	var $left       = 20;		//ͼ�����
	var $right      = 20;		//ͼ���Ҿ�
	var $top        = 20;		//ͼ���Ͼ�
	var $down       = 10;		//ͼ���¾�

	var $color = array(
				array(0x97, 0xbd, 0x00),
				array(0x00, 0x99, 0x00),
				array(0xcc, 0x33, 0x00),
				array(0xff, 0xcc, 0x00),
				array(0x33, 0x66, 0xcc),
				array(0x33, 0xcc, 0x33),
				array(0xff, 0x99, 0x33),
				array(0xcc, 0xcc, 0x99),
				array(0x99, 0xcc, 0x66),
				array(0x66, 0xff, 0x99),

				array(0x99, 0xcc, 0x66),
				array(0x66, 0xff, 0x99)
				);//ͼ��ǰ��ɫ
	var $bkcolor = array(
				array(0x4f, 0x66, 0x00),
				array(0x00, 0x33, 0x00),
				array(0x48, 0x10, 0x00),
				array(0x7d, 0x64, 0x00),
				array(0x17, 0x30, 0x64),
				array(0x1a, 0x6a, 0x1a),
				array(0x97, 0x4b, 0x00),
				array(0x78, 0x79, 0x3c),
				array(0x55, 0x7e, 0x27),
				array(0x00, 0x93, 0x37),

				array(0x55, 0x7e, 0x27),
				array(0x00, 0x93, 0x37)
				);//ͼ�󱳾�ɫ
	/*
	-----------------------------------------------------------
	��������:set_data($data)
	��Ҫ����:��ͳ�����ݸ���������$this->statistic�Ա�ʹ��
	����:string (���ܸ�ʽ��3,5,8,34��)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_data($data)
	{
		$this->statistic = explode(",", $data);
		if(count($this->statistic) == 0) die("ͳ������̫��");
	}

	/*
	-----------------------------------------------------------
	��������:set_size($width,$height)
	��Ҫ����:����ͼ���ߣ�Ҳ����ͳ��ͼ���ǿ�Ƴ���
	����:mixed (ͼ�����)
	���:---
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_size($width,$height)
	{
		$this->img_width = $width;
		$this->img_height = $height;
	}

	/*
	-----------------------------------------------------------
	��������:set_border($left,$right,$top,$down)
	��Ҫ����:����ͳ��ͼ��ͼ��߿�ľ���
	����:mixed (���ң��ϣ���);
	���:mixed
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_border($left,$right,$top,$down)
	{
		$this->left       = $left;
		$this->right      = $right;
		$this->top        = $top;
		$this->down       = $down;
	}

	/*
	-----------------------------------------------------------
	��������:set_items($items)
	��Ҫ����:��ͳ����Ŀд�������Ա�ʹ��
	����:mixed (���ܸ�ʽ��3,5,8,34��)
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_items($items)
	{
		$this->items = explode(",", $items);
		if(count($items) == 0) die("ͳ����Ŀ̫��");
	}

	/*
	-----------------------------------------------------------
	��������:set_color($color)
	��Ҫ����:����ǰ����ɫ
	����:array (ÿ����Ŀ����ɫ��Ϊ��ԭɫ)��
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_color($color)
	{
		$this->color = $color;
		if(count($this->color[0]) == 0) die("ͳ����ɫ̫��");
	}

	/*
	-----------------------------------------------------------
	��������:set_bkcolor($color)
	��Ҫ����:���ñ�����ɫ
	����:array (ÿ����Ŀ����ɫ��Ϊ��ԭɫ)��
	���:void
	�޸���־:------
	-----------------------------------------------------------
	*/
	function set_bkcolor($color)
	{
		$this->bkcolor = $color;
		if(count($this->color[0]) == 0) die("ͳ����ɫ̫��");
	}

	/*
	-----------------------------------------------------------
	��������:stat_pie()
	��Ҫ����:������ʾͳ������
	����:void
	���:ֱ������������
	�޸���־:------
	-----------------------------------------------------------
	*/
	function stat_pie()
	{
		$angle = array();
		$total = 0;
		//ͳ�Ƽ���
		for ($i=0; $i< count($this->statistic); $i++){
			$total += $this->statistic[$i];
		}
		//����ÿ��ͳ����ռ�ýǶ�
		for ($i=0; $i<count($this->statistic); $i++) {
			array_push ($angle, round(360*$this->statistic[$i]/$total));
		}
		//��ʼ������ͼƬ
		$image = imagecreate($this->img_width, $this->img_height);
		$white = imagecolorallocate($image, 0xEE, 0xEE, 0xEE);
		$border=imagecolorallocate($image,"0","0","0");

		$radius = $this->img_width/2;
		//����Բ����
		for ($h=$this->img_height/2+5; $h>$this->img_height/2-5; $h--) {
			$start = 0;
			$end = 0;
			for ($i=0; $i<count($this->statistic); $i++)  {
				$start  = $start+0;
				$end  = $start+$angle[$i];
				$color_bit = $this->bkcolor[$i];
				$color = imagecolorallocate($image, $color_bit[0],$color_bit[1], $color_bit[2]);
				imagefilledarc($image, $radius, $h, $this->img_width, $this->img_height-20, $start, $end, $color, IMG_ARC_PIE);
				$start += $angle[$i];
				$end += $angle[$i];
			}
		}
		//����Բ��������
		for ($i=0; $i<count($this->statistic); $i++)  {
				$start  = $start + 0;
				$end  = $start + $angle[$i];
				$color_bit = $this->color[$i];
				$color = imagecolorallocate($image, $color_bit[0], $color_bit[1], $color_bit[2]);
				imagefilledarc($image, $radius, $h, $this->img_width, $this->img_height-20, $start, $end, $color, IMG_ARC_PIE);
				
				//�������д�ٷֱ�---������
				//$mid_point = round((($angle[$i])/2) + $start);
				//echo $mid_point."--".$start."++".$angle[$i]."<br>";
				//$x= $this->img_width + cos($mid_point * (pi()/180.0)) * (($this->img_width)*3/4);
				//$y= $this->img_height + sin($mid_point * (pi()/180.0)) * (($this->img_height)*3/4);
				//$percent = number_format($this->statistic[$i]/$total*100, 1);
				//imagestring($image,3,$x,$i*10,$percent."%",$border);

				$start += $angle[$i];
				$end += $angle[$i];
			}
		//���ͼ��
		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	}

	/*
	-----------------------------------------------------------
	��������:stat_line()
	��Ҫ����:����ͼ��ʾ���
	����:void
	���:ֱ������������
	�޸���־:------
	-----------------------------------------------------------
	*/
	function stat_line()
	{
		$left=$this->left;
		$right=$this->right;
		$top=$this->top;
		$down=$this->down;
		$data=$this->statistic;

		$max_value=1;
		$p_x = array();
		$p_y = array();

		for($i=0;$i<count($data);$i++){
			if(!is_numeric($data[$i])) die("error id:1");
			if($data[$i]>$max_value) $max_value=$data[$i];
		}

		$space = ($this->img_width-$left-$right)/count($data);

		$image = imagecreate($this->img_width,$this->img_height);

		$white    = imagecolorallocate($image, 0xEE, 0xEE, 0xEE);
		$back_color = imagecolorallocate($image, 0x00, 0x00, 0x00);
		$line_color = imagecolorallocate($image, 0x00, 0x00, 0xFF);

		imageline ( $image, $left, $this->img_height-$down, $this->img_width-$right/2, $this->img_height-$down, $back_color);
		imageline ( $image, $left, $top/2,  $left, $this->img_height-$down, $back_color);

		for($i=0;$i<count($data);$i++){
			array_push ($p_x, $left+$i*$space);
			array_push ($p_y, $top+round(($this->img_height-$top-$down)*(1-$data[$i]/$max_value)));
		}

		imageline ( $image, $left, $top,  $left+6, $top, $back_color);
		imagestring ( $image, 1, $left/4, $top,$max_value, $back_color);
		imageline ( $image, $left, $top+($this->img_height-$top-$down)*1/4,  $left+6, $top+($this->img_height-$top-$down)*1/4, $back_color);
		imagestring ( $image, 1, $left/4, $top+($this->img_height-$top-$down)*1/4,$max_value*3/4, $back_color);
		imageline ( $image, $left, $top+($this->img_height-$top-$down)*2/4,  $left+6, $top+($this->img_height-$top-$down)*2/4, $back_color);
		imagestring ( $image, 1, $left/4, $top+($this->img_height-$top-$down)*2/4,$max_value*2/4, $back_color);
		imageline ( $image, $left, $top+($this->img_height-$top-$down)*3/4,  $left+6, $top+($this->img_height-$top-$down)*3/4, $back_color);
		imagestring ( $image, 1, $left/4, $top+($this->img_height-$top-$down)*3/4,$max_value*1/4, $back_color);

		for($i=0;$i<count($data);$i++){
			imageline ( $image, $left+$i*$space, $this->img_height-$down,  $left+$i*$space, $this->img_height-$down-6, $back_color);
			imagestring ( $image, 1, $left+$i*$space-$space/4, $top+($this->img_height-$top-$down)+2,$this->items[$i], $back_color);
		}

		for($i=0;$i<count($data);$i++){
			if($i+1<>count($data)){
				imageline ( $image, $p_x[$i], $p_y[$i],  $p_x[$i+1], $p_y[$i+1], $line_color);
				$point_color = imagecolorallocate($image, $this->color[$i][0], $this->color[$i][1], $this->color[$i][2]);
				imagefilledrectangle($image, $p_x[$i]-1, $p_y[$i]-1,  $p_x[$i]+1, $p_y[$i]+1, $point_color);
			}
		}

		imagefilledrectangle($image, $p_x[count($data)-1]-1, $p_y[count($data)-1]-1,  $p_x[count($data)-1]+1, $p_y[count($data)-1]+1, $line_color);

		for($i=0;$i<count($data);$i++){
			imagestring ( $image, 3, $p_x[$i]+4, $p_y[$i]-12,$data[$i], $back_color);
		}

		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	}

	/*
	-----------------------------------------------------------
	��������:stat_bar()
	��Ҫ����:����ͼ��ʾ���
	����:void
	���:ֱ������������
	�޸���־:------
	-----------------------------------------------------------
	*/
	function stat_bar()
	{

		$left=$this->left;
		$right=$this->right;
		$top=$this->top;
		$down=$this->down;

		$data=$this->statistic;

		$space = ($this->img_width-$left-$right)/(count($data)*3);
		$bar_width = $space*2;

		$max_value=1;

		for($i=0;$i<count($data);$i++){
			if(!is_numeric($data[$i])) die("error id:1");
			if($data[$i]>$max_value) $max_value=$data[$i];
		}
		$bar_height = array();
		$image = imagecreate($this->img_width,$this->img_height);
		$white    = imagecolorallocate($image, 0xEE, 0xEE, 0xEE);

		$img_color = imagecolorallocate($image, 0x00, 0x00, 0x00);
		imageline ( $image, $left, $this->img_height-$down, $this->img_width-$right/2, $this->img_height-$down, $img_color);
		imageline ( $image, $left, $top/2,  $left, $this->img_height-$down, $img_color);

		imageline ( $image, $left, $top,  $left+6, $top, $img_color);
		imagestring ( $image, 3, $left/4, $top,round($max_value), $img_color);
		imageline ( $image, $left, $top+($this->img_height-$top-$down)*1/4,  $left+6, round($top+($this->img_height-$top-$down)*1/4), $img_color);
		imagestring ( $image, 3, $left/4, $top+($this->img_height-$top-$down)*1/4,round($max_value*3/4), $img_color);
		imageline ( $image, $left, $top+($this->img_height-$top-$down)*2/4,  $left+6, $top+($this->img_height-$top-$down)*2/4, $img_color);
		imagestring ( $image, 3, $left/4, $top+($this->img_height-$top-$down)*2/4,round($max_value*2/4), $img_color);
		imageline ( $image, $left, $top+($this->img_height-$top-$down)*3/4,  $left+6, $top+($this->img_height-$top-$down)*3/4, $img_color);
		imagestring ( $image, 3, $left/4, $top+($this->img_height-$top-$down)*3/4,round($max_value*1/4), $img_color);

		for($i=0;$i<count($data);$i++){
			array_push ($bar_height, round(($this->img_height-$top-$down)*$data[$i]/$max_value));
		}

		for($i=0;$i<count($data);$i++){
			$bar_color = imagecolorallocate($image, $this->color[$i][0], $this->color[$i][1], $this->color[$i][2]);
			imagefilledrectangle( $image,$left+$space+$i*($bar_width+$space),$top+($this->img_height-$top-$down)-$bar_height[$i],$left+$space+$i*($bar_width+$space)+$bar_width,($this->img_height-$down)-1 ,$bar_color);

			imagestring ( $image, 1, $left+$space+$i*($bar_width+$space), $top+($this->img_height-$top-$down)+2,$this->items[$i], $img_color);
		}

		for($i=0;$i<count($data);$i++){
			imagestring ( $image, 1, $left+$space+$i*($bar_width+$space)+2,$top+($this->img_height-$top-$down)-$bar_height[$i]-10,$data[$i], $img_color);
		}
		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);

	}

}//end class
//=============================================================================
?>