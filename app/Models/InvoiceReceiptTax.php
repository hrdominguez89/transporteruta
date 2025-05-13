<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceReceipt;
use App\Models\Tax;

class InvoiceReceiptTax extends Model
{
    protected $fillable = [
        'invoice_receipt_id',
        'tax_id',
        'taxAmount',
    ];

    public function invoiceReceipt()
    {
        return $this->belongsTo(InvoiceReceipt::class, 'invoice_receipt_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
