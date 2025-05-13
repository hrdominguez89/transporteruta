<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
use App\Models\Receipt;

class InvoiceReceipt extends Model
{
    protected $table = 'invoice_receipt'; // Nombre personalizado

    public $timestamps = true;

    protected $fillable = [
        'invoice_id',
        'receipt_id',
        'total',
        'paymentMethodId',
        'taxId',
        'taxAmount'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function taxes()
    {
        return $this->hasMany(InvoiceReceiptTax::class, 'invoice_receipt_id');
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'paymentMethodId');
    }
}
