<?php
/**
 * Created by PhpStorm.
 * User: gavinwang
 * Date: 2017/9/22
 * Time: 下午4:53
 */

namespace App\libraries;


class ExcelToPHPTools
{
    public function ExcelDateToPHPDate($date)
    {
        $result = 0;
        if (stripos($date, 't') !== false) {
            $date= str_replace(',', '', $date);
            $date= str_replace('.00', '', $date);
            $result = \PHPExcel_Shared_Date::ExcelToPHP(intval(substr($date, 1)));
        } else {
                $date= str_replace('"', '', $date);
                $date= str_replace('年', '/', $date);
                $date= str_replace('月', '/', $date);
                $date= str_replace('日', '', $date);
                $date= str_replace('-', '/', $date);
                $dates = date_create_from_format("Y/m/d", $date);
                $date = date_format($dates, 'Y/m/d');
                $result = strtotime($date);
        }
        return $result;
    }

}