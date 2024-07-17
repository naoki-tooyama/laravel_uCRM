<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFMService
{
    public static function rfm($subQuery, $rfmPrms){
                //RFM分析
        //1.購買ID毎にまとめる
        $subQuery = $subQuery
        ->groupBy('id')
        ->selectRaw('id, customer_id, customer_name, 
        SUM(subtotal) as totalPerPurchase, created_at');

        //2.会員ごとにまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)
        ->groupBy('customer_id')
        ->selectRaw('
        customer_id,
        customer_name, 
        MAX(created_at ) as recentDate,
        DATEDIFF(now(), MAX(created_at)) as recency,
        COUNT(customer_id ) as frequency,
        SUM(totalPerPurchase) as monetary');

        //4.会員毎にRFMランクを分類
        $subQuery = DB::table($subQuery)
        ->selectRaw('
        customer_id,
        customer_name,
        recentDate,
        recency,
        frequency,
        monetary,
        case
            when recency < ? then 5
            when recency < ? then 4
            when recency < ? then 3
            when recency < ? then 2
            else                  1
        end as r,
        case
            when ? <= frequency then 5
            when ? <= frequency then 4
            when ? <= frequency then 3
            when ? <= frequency then 2
            else                     1
        end as f,
        case
            when ? <= monetary then 5
            when ? <= monetary then 4
            when ? <= monetary then 3
            when ? <= monetary then 2
            else                    1
        end as m
        ', $rfmPrms);
        // dd($subQuery);
        Log::debug($subQuery->get());

        //5.ランクごとに数を計算する
        $totals = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=','r')
        ->groupBy('rank')
        ->selectRaw('rank as r, COUNT(r)')
        ->orderBy('r', 'desc')
        ->pluck('COUNT(r)');
        
        Log::debug($rCount);

        $fCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=','f')
        ->groupBy('rank')
        ->selectRaw('rank as f, COUNT(f)')
        ->orderBy('f', 'desc')
        ->pluck('COUNT(f)');
        
        Log::debug($fCount);

        $mCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=','m')
        ->groupBy('rank')
        ->selectRaw('rank as m, COUNT(m)')
        ->orderBy('m', 'desc')
        ->pluck('COUNT(m)');
        Log::debug($mCount);
        // dd($rCount, $fCount, $mCount);

        $eachCount =[];//Vue側に渡す空の配列
        $rank =5; //初期値５

        for($i =0; $i <5 ; $i++){
            array_push($eachCount, [
                'rank' => $rank,
                 'r' => $rCount[$i],
                 'f' => $fCount[$i],
                 'm' => $mCount[$i],
            ]);
            $rank--;//rankを１ずつ減らす
        }
        // dd($totals, $eachCount, $rCount, $fCount, $mCount);

        //6.RとFで2次元で表示
        $data = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=','r')
        ->groupBy('rank')
        ->selectRaw('
        CONCAT("r_", rank) as rRank,
        COUNT(case when f=5 then 1 end ) as f_5,
        COUNT(case when f=4 then 1 end ) as f_4,
        COUNT(case when f=3 then 1 end ) as f_3,
        COUNT(case when f=2 then 1 end ) as f_2,
        COUNT(case when f=1 then 1 end ) as f_1
        ')
        ->orderBy('rRank', 'desc')
        ->get();
        // dd($data);

        return [$data, $totals, $eachCount];
    }

}