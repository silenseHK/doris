<?php


namespace app\common\service;


class Excel
{

    protected $error = '';

    protected $code = 0;

    public function getError(){
        return $this->error;
    }

    public function importExcel($file){
        include(__DIR__.'/../library/phpExcel/PHPExcel.php');
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '16MB');
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);//文件缓存
        //当前空间不用\，非当前空间要加\
        $PHPExcel = new \PHPExcel();//创建一个excel对象
        $PHPReader = new \PHPExcel_Reader_Excel2007(); //建立reader对象，excel—2007以后格式
        if (!$PHPReader->canRead($file)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();//建立reader对象，excel—2007以前格式
            if (!$PHPReader->canRead($file)) {
                $this->error = '不是excel格式文件';
                return false;
            }
        }
        $PHPExcel = $PHPReader->load($file); //加载excel对象
        $sheet = $PHPExcel->getSheet(0); //获取指定的sheet表
        $rows = $sheet->getHighestRow();//行数
        $cols = $sheet->getHighestColumn();//列数

        $data = array();
        for ($i = 1; $i <= $rows; $i++){ //行数是以第1行开始
            $count = 0;
            for ($j = 'A'; $j <= $cols; $j++) { //列数是以A列开始
                $value = $sheet->getCell($j . $i)->getValue();
                if ($value) {
                    $data[$i - 1][$count] = (string)$sheet->getCell($j . $i)->getValue();
                    $count += 1;
                }
            }
        }
        return $data;
    }

}