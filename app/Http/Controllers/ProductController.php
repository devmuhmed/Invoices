<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Section;
use Illuminate\Http\Request;

use function GuzzleHttp\Promise\all;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $sections = Section::all();
        return view('products.products',compact('products','sections'));
    }

    public function store(Request $request)
    {
        $rules = [
            'product_name' => 'required|unique:products|max:255',
            'section_id' => 'required'
        ];
        Product::create($request->all());
        session()->flash('Add','Added successfully');
        return redirect(route('products.index'));
    }


    public function update(Request $request)
    {
        $id = Section::where('section_name',$request->section_name)->first()->id;
        // dd($id);
        $rules = [
            'product_name' => 'required|unique:products|max:255',
        ];
        // dd($request);
        $this->validate($request,$rules);
        $product = Product::find($request->pro_id);
        $product->update([
            'product_name' => $request->product_name ,
            'section_name' => $request->section_name,
            'description' => $request->description,
            'section_id' => $id,
        ]);
        session()->flash('Edit','Edited successfully');
        return redirect(route('products.index'));
    }

    public function destroy(Request $request)
    {
        Product::find($request->id)->delete();
        session()->flash('Delete','Deleted Successfully');
        return redirect(route('products.index'));
    }
}
