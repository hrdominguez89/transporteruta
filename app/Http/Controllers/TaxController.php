<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;

class TaxController extends Controller
{
    public function taxes()
    {
        $taxes = Tax::all();
        return view('tax.index', ['taxes'=>$taxes]);
    }

    public function store(StoreTaxRequest $request)
    {
        $newTax = new Tax;
        $newTax->name = $request->name;
        $newTax->save();
        return redirect(route('taxes'));
    }

    public function update(UpdateTaxRequest $request, $id)
    {
        $tax = Tax::find($id);
        $tax->name = $request->name;
        $tax->save();
        return redirect(route('taxes'));
    }
}
