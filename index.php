<?php

require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "help.php";
// sid
$sid = $_POST['sid'];
// 
if($sid){

    try {
        $test_id = get_test_id($sid);
        $arr =  [
            'code'=>200,
            'status' => 'success',
            'msg' => '获取成功',
            'data'=>[
                'test_id'=> $test_id
            ]
        ];
    
    } catch (\Exception $e) {
        $arr = [
            'code'=>200,
            'status' => 'error',
            'msg' => '发生了错误',
            'data'=>[]
    
        ];
    }
    
    
    
    echo json_encode($arr);
    exit;

}else{
    echo "四六级学生证号查询接口<br>";
    echo "请求: index.php<br>";
    echo "方法: POST<br>";
    echo "内容: sid";
}








