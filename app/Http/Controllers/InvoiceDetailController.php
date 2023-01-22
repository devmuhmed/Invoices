<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceDetailController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(InvoiceDetail $invoiceDetail)
    {
        //
    }

    public function edit($id)
    {
        $invoices = Invoice::where('id',$id)->first();
        $attachments = InvoiceAttachment::where('invoice_id',$id)->get();
        $details = InvoiceDetail::where('invoice_id',$id)->get();
        return view('invoices.details_invoice',compact('invoices','attachments','details'));
    }

    public function get_file($invoice_number,$file_name)
    {
        $pathToFile = public_path('Attachments/'.$invoice_number.'/'.$file_name);
        return response()->download($pathToFile);
    }

    public function open_file($invoice_number,$file_name){
        $fileName = public_path('Attachments/'.$invoice_number.'/'.$file_name);
        return response()->file($fileName);
    }

    public function update(Request $request, InvoiceDetail $invoiceDetail)
    {
        //
    }

    public function destroy(Request $request)
    {
        $invoice = InvoiceAttachment::find($request->id);
        $invoice->delete();
        Storage::disk('public_uploads')->delete($request->invoice_number.'/'.$request->file_name);
        session()->flash("Delete","Deleted Successfully");
        return back();
    }
}
