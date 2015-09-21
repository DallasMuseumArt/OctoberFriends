<?php namespace DMA\Friends\Controllers;

//use App\Http\Controllers\Controller;
use Backend\Classes\Controller;


class Ajax extends Controller
{
    static protected $reportBaseClass = '\DMA\Friends\ReportWidgets\\';

    static public function report($class)
    {
        $class = self::getClass($class);
        $data = $class::generateData();
        return response()->json($data);
    }

    static protected function getClass($class)
    {
        return str_replace("@", '\\', $class);
    }
}