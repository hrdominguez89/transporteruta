<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TravelCertificateController;
use App\Http\Controllers\TravelItemController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DriverSettlementController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\UserController;

Auth::routes(['register' => false]);

Route::group(['middleware' => 'auth'], function () {

    //Dashboard
    Route::get('', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Client
    Route::get('clientes', [ClientController::class, 'clients'])->name('clients');
    Route::post('guardar/cliente', [ClientController::class, 'store'])->name('storeClient');
    Route::get('ver/cliente/{id}', [ClientController::class, 'show'])->name('showClient');
    Route::get('generar/pdf/deudores', [ClientController::class, 'generateDebtorsPdf'])->name('generateDebtorsPdf');
    Route::put('actualizar/cliente/{id}', [ClientController::class, 'update'])->name('updateClient');

    // Driver
    Route::get('choferes', [DriverController::class, 'drivers'])->name('drivers');
    Route::post('guardar/chofer', [DriverController::class, 'store'])->name('storeDriver');
    Route::get('ver/chofer/{id}', [DriverController::class, 'show'])->name('showDriver');
    Route::put('actualizar/chofer/{id}', [DriverController::class, 'update'])->name('updateDriver');
    //Filtro de liquidaciones de choferes por rango de fechas
    Route::get('/driver-settlements/print', [DriverSettlementController::class, 'print'])->name('driverSettlements.print');

    // TravelCertificate
    Route::get('constancias-de-viaje', [TravelCertificateController::class, 'travelCertificates'])->name('travelCertificates');
    Route::post('guardar/constancia-de-viaje', [TravelCertificateController::class, 'store'])->name('storeTravelCertificate');
    Route::get('ver/constancia-de-viaje/{id}', [TravelCertificateController::class, 'show'])->name('showTravelCertificate');
    Route::put('actualizar/constancia-de-viaje/{id}', [TravelCertificateController::class, 'update'])->name('updateTravelCertificate');
    Route::get('imprimir/constancia-de-viaje/{id}', [TravelCertificateController::class, 'generateTravelCertificatePdf'])->name('travelCertificatePdf');
    Route::put('agregar/a/la/factura/{id}', [TravelCertificateController::class, 'addToInvoice'])->name('addToInvoice');
    Route::put('quitar/de/la/factura/{id}', [TravelCertificateController::class, 'removeFromInvoice'])->name('removeFromInvoice');
    Route::put('agregar/a/la/liquidacion/{id}', [TravelCertificateController::class, 'addToDriverSettlement'])->name('addToDriverSettlement');
    Route::put('quitar/de/la/liquidacion/{id}', [TravelCertificateController::class, 'removeFromDriverSettlement'])->name('removeFromDriverSettlement');

    // TravelItem
    Route::post('guardar/item-de-viaje/{travelCertificateId}', [TravelItemController::class, 'store'])->name('storeTravelItem');
    Route::delete('eliminar/item-de-viaje/{id}/{travelCertificateId}', [TravelItemController::class, 'delete'])->name('deleteTravelItem');

    // Invoice
    Route::get('facturas', [InvoiceController::class, 'invoices'])->name('invoices');
    Route::post('generar/factura', [InvoiceController::class, 'generate'])->name('generateInvoice');
    Route::get('ver/factura/{id}', [InvoiceController::class, 'show'])->name('showInvoice');
    Route::get('facturar/{id}', [InvoiceController::class, 'invoiced'])->name('invoicedInvoice');
    Route::get('imprimir/factura/{id}', [InvoiceController::class, 'generateInvoicePdf'])->name('invoicePdf');
    Route::post('agregar/al/recibo/{id}', [InvoiceController::class, 'addToReceipt'])->name('addToReceipt');
    Route::post('agregar/tax/al/recibo/{id}', [InvoiceController::class, 'addTaxToReceiptInvoice'])->name('addTaxToReceiptInvoice');
    Route::delete('remover/tax/al/recibo/{taxId}', [InvoiceController::class, 'removeTaxFromInvoiceReceipt'])->name('removeTaxFromInvoiceReceipt');
    
    Route::delete('quitar/al/recbibo/{id}', [InvoiceController::class, 'removeFromReceipt'])->name('removeFromReceipt');
    Route::get('anular/factura/{id}', [InvoiceController::class, 'cancel'])->name('cancelInvoice');
    
    // DriverSettlement
    Route::get('liquidaciones-de-choferes', [DriverSettlementController::class, 'driverSettlements'])->name('driverSettlements');
    Route::post('generar/liquidacion', [DriverSettlementController::class, 'generate'])->name('generateDriverSettlement');
    Route::get('ver/liquidacion/{id}', [DriverSettlementController::class, 'show'])->name('showDriverSettlement');
    Route::get('imprimir/liquidacion/{id}', [DriverSettlementController::class, 'generateDriverSettlementPdf'])->name('driverSettlementPdf');
    Route::get('liquidar/{id}', [DriverSettlementController::class, 'liquidated'])->name('liquidatedDriverSettlement');
    Route::get('anular/liquidacion/{id}', [DriverSettlementController::class, 'cancel'])->name('cancelDriverSettlement');
    Route::get('eliminar/liquidacion/{id}', [DriverSettlementController::class, 'delete'])->name('deleteDriverSettlement');

    // Receipt
    Route::get('recibos', [ReceiptController::class, 'receipts'])->name('receipts');
    Route::post('generar/recibo', [ReceiptController::class, 'generate'])->name('generateReceipt');
    Route::get('ver/recibo/{id}', [ReceiptController::class, 'show'])->name('showReceipt');
    Route::get('imprimir/recibo/{id}', [ReceiptController::class, 'generateReceiptPdf'])->name('receiptPdf');
    Route::get('pagar/{id}', [ReceiptController::class, 'paid'])->name('paidReceipt');
    Route::get('anular/recibo/{id}', [ReceiptController::class, 'cancel'])->name('cancelReceipt');

    // PaymentMethod
    Route::get('medios-de-pago', [PaymentMethodController::class, 'paymentMethods'])->name('paymentMethods');
    Route::post('guardar/medio-de-pago', [PaymentMethodController::class, 'store'])->name('storePaymentMethod');
    Route::put('actualizar/medio-de-pago/{id}', [PaymentMethodController::class, 'update'])->name('updatePaymentMethod');

    // Credit
    Route::get('notas-de-credito', [CreditController::class, 'credits'])->name('credits');
    Route::post('generar/nota-de-credito', [CreditController::class, 'generate'])->name('generateCredit');
    Route::get('ver/nota-de-/credito/{id}', [CreditController::class, 'show'])->name('showCredit');
    Route::post('agregar/factura/a/la/nota-de-credito/{id}', [CreditController::class, 'addInvoice'])->name('addInvoiceToCredit');
    Route::get('quitar/factura/de/la/nota-de-credito/{id}', [CreditController::class, 'removeInvoice'])->name('removeInvoiceFromCredit');

    // Tax
    Route::get('impuestos', [TaxController::class, 'taxes'])->name('taxes');
    Route::post('guardar/impuesto', [TaxController::class, 'store'])->name('storeTax');
    Route::put('actualizar/impuesto/{id}', [TaxController::class, 'update'])->name('updateTax');

    // Vehicle
    Route::get('vehiculos', [VehicleController::class, 'vehicles'])->name('vehicles');
    Route::post('guardar/vehiculo', [VehicleController::class, 'store'])->name('storeVehicle');
    Route::put('actualizar/vehiculo/{id}', [VehicleController::class, 'update'])->name('updateVehicle');

    // User
    Route::get('usuarios', [UserController::class, 'users'])->name('users');
    Route::post('guardar/usuario', [UserController::class, 'store'])->name('storeUser');
    Route::put('actualizar/usuario/{id}', [UserController::class, 'update'])->name('updateUser');
    Route::delete('eliminar/usuario/{id}', [UserController::class, 'delete'])->name('deleteUser');
});