<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::all();
        return view('sections.sections',compact('sections'));
    }

    public function store(Request $request)
    {
        // first way to make validation
        $rules = [
            'section_name' => 'required|unique:sections|max:255',
            'description' => 'required'
        ];
        $this->validate($request,$rules);
        Section::create([
            'section_name' => $request->section_name,
            'description' => $request->description,
            'created_by' => Auth::user()->name
        ]);
        session()->flash('Add','Add Succfully');
        return redirect(route('sections.index'));
    }

    public function update(Request $request)
    {
        $rules = [
            'section_name' => 'required|unique:sections|max:255',
            $request->id,
            'description' => 'required'
        ];
        $this->validate($request,$rules);
        $section = Section::findOrFail($request->id);
        $section->update([
            'section_name' => $request->section_name,
            'description' => $request->description,
            'created_by' => Auth::user()->name
        ]);
        session()->flash('Edit','Edit Successfully');
        return redirect(route('sections.index'));
    }

    public function destroy(Request $request)
    {
        Section::find($request->id)->delete();
        session()->flash('Delete','Deleted Successfully');
        return redirect(route('sections.index'));
    }
}
