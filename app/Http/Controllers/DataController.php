<?php

namespace App\Http\Controllers;

use App\Http\Services\DataService;

class DataController extends Controller
{
    public DataService $dataService;



    //

    /**
     * @param DataService $dataService
     */
    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    public function index(){


        $data = $this->dataService->index();

        return $data;
    }
}
