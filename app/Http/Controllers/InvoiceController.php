<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\InvoiceDetail;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
        return view('invoices.invoices',compact('invoices'));
    }

    public function create()
    {
        $sections = Section::all();
        return view('invoices.add_invoice',compact('sections'));
    }

    public function store(Request $request)
    {
        // $rules=[];
        // $this->validate($request,$rules);
        Invoice::create([
            "invoice_number"=> $request->invoice_number,
            "invoice_date"=> $request->invoice_date,
            "due_date"=> $request->due_date,
            "section_id"=> $request->section,
            "product"=> $request->product,
            "amount_collection"=> $request->amount_collection,
            "amount_commission"=> $request->amount_commission,
            "discount"=> $request->discount,
            "rate_vat"=> $request->rate_vat,
            "value_vat"=> $request->value_vat,
            "total"=> $request->total,
            "status"=> 'unpaid',
            "value_status"=> 2,
            "note"=> $request->note
        ]);
        $invoice_id = Invoice::latest()->first()->id;
        InvoiceDetail::create([
            'invoice_id' => $invoice_id,
            'invoice_number'=> $request->invoice_number,
            'product' => $request->product,
            'section' => $request->section,
            'status' => 'unpaid',
            'value_status' => 2,
            'note' =>$request->note,
            'user' => (Auth::user()->name),
        ]);
        if ($request->hasFile('pic')) {
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new InvoiceAttachment();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
        }
        session()->flash('Add','Added Successfully');
        return back();
    }

    public function show($id)
    {
        $invoice = Invoice::where('id',$id)->first();
        return view('invoices.status_update',compact('invoice'));
    }
    public function status_update($id,Request $request){
        $invoice = Invoice::find($id);
        if($request->status === "paid"){
            $invoice->update([
                'value_status' => 1,
                'status' => $request->status,
                'payment_date' =>$request->payment_date
            ]);
            InvoiceDetail::create([
                'invoice_id'=> $request->invoice_id,
                'invoice_number'=> $request->invoice_number,
                'product' => $request->product,
                'section' => $request->section,
                'status' => $request->status,
                'value_status' => 1,
                'note' =>$request->note,
                'user' => (Auth::user()->name),
            ]);
        }
        else{
            $invoice->update([
                'value_status' => 3,
                'status' => $request->status,
                'payment_date' =>$request->payment_date
            ]);
            InvoiceDetail::create([
                'invoice_id'=> $request->invoice_id,
                'invoice_number'=> $request->invoice_number,
                'product' => $request->product,
                'section' => $request->section,
                'status' => $request->status,
                'value_status' => 3,
                'note' =>$request->note,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('status_update');
        return redirect('/invoices');
    }
    public function edit($id)
    {
        $invoices = Invoice::where('id',$id)->first();
        $sections = Section::all();
        return view('invoices.edit_invoice',compact('invoices','sections'));
    }

    public function update(Request $request)
    {
        $invoice = Invoice::find($request->invoice_id);
        // dd($request->all());
        $invoice->update([
            'invoice_number'=> $request->invoice_number,
            'invoice_date'=> $request->invoice_date,
            'due_date'=> $request->due_date,
            'section_id'=> $request->section,
            'product'=> $request->product,
            'amount_collection'=> $request->amount_collection,
            'amount_commission'=> $request->amount_commission,
            'discount'=> $request->discount,
            'rate_vat'=> $request->rate_vat,
            'value_vat'=> $request->value_vat,
            'total'=> $request->total,
            'note'=> $request->note

        ]);
        session()->flash('edit','Edited Successfully');
        return back();
    }

    public function destroy(Request $request)
    {
        $invoice = Invoice::where('id',$request->invoice_id)->first();
        $attaches = InvoiceAttachment::where('invoice_id',$request->invoice_id)->first();
        if(!empty($attaches->invoice_number)){
            Storage::disk('public_uploads')->deleteDirectory($attaches->invoice_number);
            // Storage::disk('public_uploads')->delete($attaches->invoice_number.'/'.$attaches->file_name);
        }
        $invoice->forceDelete();
        session()->flash('delete_invoice');
        return back();
    }
    // using method getProducts for Ajax
    public function getProducts($id){
        $products = DB::table('products')->where("section_id",$id)->pluck('product_name','id');
        return json_encode($products);
    }

}
