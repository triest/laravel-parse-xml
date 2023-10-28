<?php

namespace App\Jobs;

use App\Events\RowCreatedEvent;
use App\Models\Row;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParseFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $patch;

    public $row_count;

    public $row_step = 1000;

    /**
     * @param $patch
     * @param $row_count
     */
    public function __construct($patch)
    {
        $this->patch = $patch;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $redis = Redis::connection();
            $this->patch = str_replace('public/', '', $this->patch);

            $temp = Storage::disk('public')->path($this->patch);

            $uploadedFile = new \Symfony\Component\HttpFoundation\File\File($temp);

            $spreadsheet = IOFactory::load($uploadedFile);

            $row_count = $redis->get('last_row');
            if (!$row_count) {
                $row_count = 2;
            }

            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $row_count + $this->row_step;
            $column_limit = $sheet->getHighestDataColumn();
            $max_row = $sheet->getHighestDataRow();
            Log::debug('row limit:' . $row_limit);
            Log::debug('max row:' . $max_row);

            if ($row_limit > $max_row) {
                $row_limit = $max_row;
            }


            $row_range = range($row_count, ($row_count + $this->row_step));
            $column_range = range('F', $column_limit);
            $startcount = 2;
            $count = 1;
            foreach ($row_range as $row) {
                $data = [
                    'id' => $sheet->getCell('A' . $row)->getFormattedValue(),
                    'name' => $sheet->getCell('B' . $row)->getValue(),
                    'date' => $sheet->getCell('C' . $row)->getValue(),
                ];

                if ($count > 2474) {
                    Log::debug(print_r($data));
                    dump($data);
                    die();
                }


                $temp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($data['date']));
                $data['date'] = Carbon::parse($temp)->format('d.m.Y');
                if ($data['name'] == null || $data['date'] == null) {
                    $redis->del('last_row');
                    return;
                }

                $row = Row::query()->where('id', $data['id'])->first();

                if (!$row) {
                    $row = new Row();
                }
                $row->fill($data);
                $row->save();

                broadcast(new RowCreatedEvent($row))->toOthers();
            }

            $redis->set('last_row', $row_limit);

            if ($row_limit <= $max_row) {
                ParseFileJob::dispatch($this->patch)->delay(3);
            }else{
                if (Storage::disk('public')->exists($this->patch)) {
                    Storage::disk('public')->delete($this->patch);
                }
            }
        } catch (Exception $e) {
            Log::error($e->getLine());
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
