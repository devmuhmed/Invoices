<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $count_invoice = Invoice::count() || 1;
        $count_paid = Invoice::where('value_status', 1)->count();
        $count_unpaid = Invoice::where('value_status', 2)->count();
        $count_partialpaid = Invoice::where('value_status', 3)->count();
        $total_invoices = Invoice::sum('total');
        $total_paid = Invoice::where('value_status', 1)->sum('total');
        $total_unpaid = Invoice::where('value_status', 2)->sum('total');
        $total_partialpaid = Invoice::where('value_status', 3)->sum('total');
        $invoice_count = ($count_invoice < 1) ? 0 : $count_invoice;
        $paid_count = ($count_paid < 1) ? 0 : $count_paid;
        $unpaid_count = ($count_unpaid < 1) ? 0 : $count_unpaid;
        $partialpaid_count = ($count_partialpaid < 1) ? 0 : $count_partialpaid;
        $paid_percentage = $paid_count / $invoice_count * 100;
        $unpaid_percentage = $unpaid_count / $invoice_count * 100;
        $partialpaid_percentage = $partialpaid_count / $invoice_count * 100;
        // chart fx3costa
        if($unpaid_count == 0){
            $nspainvoices2=0;
        }
        else{
            $nspainvoices2 = $unpaid_percentage;
        }

          if($paid_count == 0){
              $nspainvoices1=0;
          }
          else{
              $nspainvoices1 = $paid_percentage;
          }

          if($partialpaid_count == 0){
              $nspainvoices3=0;
          }
          else{
              $nspainvoices3 = $partialpaid_percentage;
          }

        $chartjs_pie = app()->chartjs
        ->name('pieChartTest')
        ->type('pie')
        ->size(['width' => 340, 'height' => 200])
        ->labels(['Unpaid Invoices', 'Paid Invoices','Partial Paid Invoices'])
        ->datasets([
            [
                'backgroundColor' => ['#f85873', '#21b383','#f38745'],
                'data' => [$nspainvoices2, $nspainvoices1,$nspainvoices3]
            ]
        ])
        ->options([]);


        $chartjs_bar = app()->chartjs
            ->name('barChartTest')
            ->type('bar')
            ->size(['width' => 350, 'height' => 200])
            ->labels(['Unpaid Invoices', 'Paid Invoices','Partial Paid Invoices'])
            ->datasets([
                [
                    "label" => "Unpaid Invoices",
                    'backgroundColor' => ['#f85873'],
                    'data' => [$nspainvoices2]
                ],
                [
                    "label" => "Paid Invoices",
                    'backgroundColor' => ['#21b383'],
                    'data' => [$nspainvoices1]
                ],
                [
                    "label" => "Partial Paid Invoices",
                    'backgroundColor' => ['#f38745'],
                    'data' => [$nspainvoices3]
                ],
            ])
            ->options([]);
        return view('home', compact('total_invoices', 'invoice_count', 'total_paid', 'paid_count', 'total_unpaid', 'unpaid_count', 'total_partialpaid', 'partialpaid_count','paid_percentage','unpaid_percentage','partialpaid_percentage','chartjs_pie','chartjs_bar'));
    }
}
