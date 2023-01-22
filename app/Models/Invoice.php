<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        "invoice_number",
        "invoice_date",
        "due_date",
        "section_id",
        "product",
        "amount_collection",
        "amount_commission",
        "discount",
        "rate_vat",
        "value_vat",
        "total",
        "status",
        "value_status",
        "note"
    ];
    public function section(){
        return $this->belongsTo(Section::class);
    }
}
