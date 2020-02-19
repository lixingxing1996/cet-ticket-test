<?php

define('HOST', 'http://cet-bm.neea.edu.cn', true);

use Zxing\QrReader;

function public_path($path)
{
    return dirname(__FILE__).'public/'.$path;
}
/*
   php 从zip压缩文件中提取文件
*/
function unzip($zipfile,$toDir,$name)
{


//    $name = '英语四级笔试准考证('.$name.').zip';
// 需要支持ZipArchive 拓展
    $zip = new ZipArchive();
    if($zip->open($zipfile) === TRUE){
        if(!file_exists($toDir)) {
            mkdir($toDir);
        }
        // 获取文件数量
        $docnum = $zip->numFiles;
        for($i = 0; $i < $docnum; $i++) {
            $statInfo = $zip->statIndex($i);
            if($statInfo['crc'] == 0) {
                //新建目录
                mkdir($toDir.'/'.substr($statInfo['name'], 0,-1));
            } else {
                //拷贝文件

                copy('zip://'.$zipfile.'#'.$statInfo['name'], $toDir.'/'.$name);
            }
        }

        return scandir($toDir);


    }else{
        return false;
    }




}

/**
 * 将pdf文件转化为多张png图片（需要支持Imagick）
 * @param string $pdf  pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
 * @param string $path 新生成图片所在路径 (/www/pngs/)
 *
 * @return array|bool
 */
function pdf2png($pdf, $path)
{

    if (!extension_loaded('imagick')) {
        return false;
    }
    if (!file_exists($pdf)) {
        return false;
    }
    if (!file_exists('image')) {
        mkdir ( 'image', 0777, true );
    }

    $im = new Imagick();
    $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
    $im->setCompressionQuality(100);
    $im->readImage($pdf);
    foreach ($im as $k => $v) {
        $v->setImageFormat('png');
        $fileName = $path . md5($k . time()) . '.png';
        if ($v->writeImage($fileName) == true) {
            $return[] = $fileName;
        }
    }
    return $return[0];
}
//删除指定文件夹以及文件夹下的所有文件
function deldir($dir) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
       if($file!="." && $file!="..") {
          $fullpath=$dir."/".$file;
          if(!is_dir($fullpath)) {
             unlink($fullpath);
          } else {
             deldir($fullpath);
          }
       }
    }
  
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
       return true;
    } else {
       return false;
    }
 }

// 获取转考证
function get_test_id($sid)
{

    $fileurl = HOST.'/Home/DownTestTicket?SID='.$sid;
    // 
    $zip_file_path = public_path('cet_book/zip/').time().'.zip';
    // 开启exec函数
    $command = "wget -c -O ".$zip_file_path." ".$fileurl;
    $output = exec($command);
    if($output!=''){
        echo "异常！";
    }
    $pdf_name=time().'.pdf';
    $pdf_dir = 'cet_book/pdf';
    $re =  unzip($zip_file_path, $pdf_dir,$pdf_name);
    //pdf转png
    $img_url = pdf2png(public_path('cet_book/pdf/'.$pdf_name),public_path('cet_book/image/'));
    // 开启全部内存
    ini_set("memory_limit","-1");
    // 二维码识别
    $qrcode = new QrReader($img_url);
    $test_id = $qrcode->text();
    // 删除文件
    
    return $test_id;

}