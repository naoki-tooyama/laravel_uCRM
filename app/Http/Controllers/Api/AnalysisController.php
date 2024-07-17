<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;
use App\Services\DecileService;
use App\Services\RFMService;

class AnalysisController extends Controller
{
    public function index(Request $request){
        
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if($request->type === 'perDay')
        {
            //日別
            list($data, $labels, $totals) =  AnalysisService::perDay($subQuery);
        }
        if($request->type === 'perMonth')
        {
            //日別
            list($data, $labels, $totals) =  AnalysisService::perMonth($subQuery);
        }
        if($request->type === 'perYear')
        {
            //日別
            list($data, $labels, $totals) =  AnalysisService::perYear($subQuery);
        }
        if($request->type === 'decile')
        {
            //Decile分析
            list($data, $labels, $totals) =  DecileService::decile($subQuery);
        }
        if($request->type === 'rfm')
        {
            //RFM分析
            list($data, $totals, $eachCount) =  RFMService::rfm($subQuery, $request->rfmPrms);
            return response()->json([
                'data' => $data,
                'type' => $request->type,
                'eachCount' => $eachCount,
                'totals' => $totals,
            ],  Response::HTTP_OK);
        }
        
        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals,
        ],  Response::HTTP_OK);
    }
}
