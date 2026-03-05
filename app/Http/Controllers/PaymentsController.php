<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payments;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payments::all();
        $clients = Client::all();
        return view('payments.index',[
            'payments' => $payments , 
            'clients' => $clients]);
    }
    public function show(Request $request)
    {

    }
    public function generate(Request $request)
    {
        
    }
    public function edit(Request $request)
    {

    }
    public function delete(Request $request)
    {

    }
    public function pdf(Request $request)
    {
        
    }
}