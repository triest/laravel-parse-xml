<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileValidation;
use App\Jobs\ParseFileJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ParseController extends Controller


{
    //

    public function upload(UploadFileValidation $request)
    {
        $file_name = $request->file('file')->store('public');

        $redis = Redis::connection();
        if($redis->exists('last_row')) {
            $redis->del('last_row');
        }

        ParseFileJob::dispatch($file_name)->delay(1);

        return response()->json(['result' => true]);
    }
}
