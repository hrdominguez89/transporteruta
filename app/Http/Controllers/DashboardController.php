<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $clientsCount = Client::all()->count();
        $driversCount = Driver::all()->count();
        $invoicesCount = Invoice::all()->where('invoiced', 'NO')->count();
        $receiptsCount = Receipt::all()->where('paid', 'NO')->count();
        $clients = Client::all()->where('balance', '>', 0);
        return view('dashboard', ['clientsCount'=>$clientsCount, 'driversCount'=>$driversCount, 'invoicesCount'=>$invoicesCount, 'receiptsCount'=>$receiptsCount, 'clients'=>$clients]);
    }
}
