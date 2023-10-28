<?php

namespace App\Mappers;

class RowMapper
{
    public static function map($rows){

        $resultArray = [];

        foreach ($rows as $row){
            $temp = [
                'id' => $row['id'],
                'name' => $row->name,
                'date' => $row->date
            ];
            $resultArray[$row['date']][] = $temp;
        }

        return $resultArray;
    }
}
