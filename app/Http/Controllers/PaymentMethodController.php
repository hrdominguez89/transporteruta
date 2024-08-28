<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;

class PaymentMethodController extends Controller
{
    public function paymentMethods()
    {
        $paymentMethods = PaymentMethod::all();
        return view('paymentMethod.index', ['paymentMethods'=>$paymentMethods]);
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $newPaymentMethod = new PaymentMethod;
        $newPaymentMethod->name = $request->name;
        $newPaymentMethod->save();
        return redirect(route('paymentMethods'));
    }


    public function update(UpdatePaymentMethodRequest $request, $id)
    {
        $paymentMethod = PaymentMethod::find($id);
        $paymentMethod->name = $request->name;
        $paymentMethod->save();
        return redirect(route('paymentMethods'));
    }
}
