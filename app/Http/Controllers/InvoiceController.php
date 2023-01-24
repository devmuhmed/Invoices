<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\InvoiceDetail;
use App\Models\Section;
use App\Models\User;
use App\Notifications\AddInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
        $user = User::first();
        Notification::send($user,new AddInvoice($invoice_id));
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
        $id_page =$request->id_page;
        if($id_page != 2)
        {
            if(!empty($attaches->invoice_number)){
                Storage::disk('public_uploads')->deleteDirectory($attaches->invoice_number);
            }
            $invoice->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');
        }
        else{
            $invoice->delete();
            session()->flash('archive_invoice');
            return redirect('/archive');
        }
    }
    // using method getProducts for Ajax
    public function getProducts($id){
        $products = DB::table('products')->where("section_id",$id)->pluck('product_name','id');
        return json_encode($products);
    }
    public function invoice_paid()
    {
        $invoices = Invoice::where('value_status',1)->get();
        return view('invoices.invoices_paid',compact('invoices'));

    }
    public function invoice_unpaid()
    {
        $invoices = Invoice::where('value_status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }
    public function invoice_partial()
    {
        $invoices = Invoice::where('value_status',3)->get();
        return view('invoices.invoices_Partial',compact('invoices'));
    }
    public function print_invoice($id)
    {
        $invoice = Invoice::where('id',$id)->first();
        return view('invoices.print_invoice',compact('invoice'));
    }

    public function export()
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }
    public function report()
    {
        return view('reports.invoices_report');
    }
    public function invoices_search(Request $request)
    {
        $rdio = $request->rdio;

        if($rdio == 1)
        {
            // search whitout date
            if($request->type && $request->start_at=='' && $request->end_at ==''){
                $invoices = Invoice::select('*')->where('status',$request->type)->get();
                $type = $request->type;
                return view('reports.invoices_report',compact('type'))->withDetails($invoices);
            }
            // search by date
            else{
                $start_at = date($request->start_at);
                $end_at = date($request->end_at);
                $type = $request->type;
                $invoices = Invoice::whereBetween('invoice_date',[$start_at,$end_at])->where('status',$request->type)->get();
                return view('reports.invoices_report',compact('type','start_at','end_at'))->withDetails($invoices);
            }
        } else{
            $invoices = Invoice::select('*')->where('invoice_number',$request->invoice_number)->get();
            return view('reports.invoices_report')->withDetails($invoices);
        }
    }

}
