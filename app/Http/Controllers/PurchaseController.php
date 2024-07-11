<?php

namespace App\Http\Controllers;

use App\Models\purchase;
use App\Http\Requests\StorepurchaseRequest;
use App\Http\Requests\UpdatepurchaseRequest;
use Inertia\Inertia;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $customers = Customer::select('id', 'name', 'kana')->get();
        $items = Item::select('id', 'name', 'price')
        ->where('is_selling', true)
        ->get();

        return Inertia::render('Purchases/Create', [
            // 'customers' => $customers,
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorepurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepurchaseRequest $request)
    {
        // dd($request);
        DB::beginTransaction();

        try{

            $purchase = Purchase::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status
            ]);
            
            foreach($request->items as $item){
                $purchase->items()->attach($purchase->id,[
                    'item_id' => $item['id'],
                    'quantity'=> $item['quantity']
                ]);
            }
            DB::commit();
            return to_route('dashboard');
        }catch(\Exception $e){
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepurchaseRequest  $request
     * @param  \App\Models\purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepurchaseRequest $request, purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(purchase $purchase)
    {
        //
    }
}
