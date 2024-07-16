<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class AnalysisService
{
    public static function perDay($subQuery){
        //日別
        $query = $subQuery->where('status', true)
            ->groupBy('id')
            ->selectRaw('SUM(subtotal) as totalPerPurchase,
            DATE_FORMAT(created_at, "%Y%m%d") as date');

        $data = DB::table($query)
            ->groupBy('date')
            ->selectRaw('date, SUM(totalPerPurchase) as total')
            ->get();

        $labels = $data->pluck('date');
        $totals = $data->pluck('total');

        return [$data, $labels, $totals];
    }

    public static function perMonth($subQuery){
        //月別
        $query = $subQuery->where('status', true)
            ->groupBy('id')
            ->selectRaw('SUM(subtotal) as totalPerPurchase,
            DATE_FORMAT(created_at, "%Y%m") as date');

        $data = DB::table($query)
            ->groupBy('date')
            ->selectRaw('date, SUM(totalPerPurchase) as total')
            ->get();

        $labels = $data->pluck('date');
        $totals = $data->pluck('total');

        return [$data, $labels, $totals];
    }
    
    public static function perYear($subQuery){
        //年別
        $query = $subQuery->where('status', true)
            ->groupBy('id')
            ->selectRaw('SUM(subtotal) as totalPerPurchase,
            DATE_FORMAT(created_at, "%Y") as date');

        $data = DB::table($query)
            ->groupBy('date')
            ->selectRaw('date, SUM(totalPerPurchase) as total')
            ->get();

        $labels = $data->pluck('date');
        $totals = $data->pluck('total');

        return [$data, $labels, $totals];
    }
}