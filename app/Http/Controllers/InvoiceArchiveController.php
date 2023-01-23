<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceArchiveController extends Controller
{

    public function index()
    {
        $invoices = Invoice::onlyTrashed()->get();
        return view('invoices.archive_invoice',compact('invoices'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        Invoice::withTrashed()->where('id',$request->invoice_id)->restore();
        session()->flash('restore_invoice');
        return redirect('/invoices');

    }
    public function destroy(Request $request)
    {
        $invoice = Invoice::withTrashed()->where('id',$request->invoice_id)->first();
        $invoice->forceDelete();
        session()->flash('delete_invoice');
        return redirect('/archive');
    }
}
