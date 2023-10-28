<?php

namespace App\Http\Services;

use App\Mappers\RowMapper;
use App\Models\Row;
use Illuminate\Support\Facades\Redis;

class DataService
{
    public function index(){



        $rows = Row::query()->orderBy('date')->paginate(100);

        return RowMapper::map($rows);
    }
}
