<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Validator;

class SubCategoryController extends Controller
{
    // for listing sub-categories
    public function index(Request $request){
        $subCategories = SubCategory::select('sub_categories.*','categories.name as categoryName')->latest('sub_categories.id')->leftJoin('categories','categories.id','sub_categories.category_id');
        
        if(!empty($request->get('keyword'))){
            $subCategories = $subCategories->where('sub_categories.name','like','%'.$request->get('keyword').'%');
            $subCategories = $subCategories->orWhere('categories.name','like','%'.$request->get('keyword').'%');
        }
        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.list',compact('subCategories'));
    }

    // for showing sub-create category form
    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create',$data);
    }

    // for storing data of new create sub-category form
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if($validator->passes()){
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub Category created successfully');

            return response([
                'status' => true,
                'message' => 'Sub Category created successfully'
            ]);
        }
        else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // for showing sub category edit form
    public function edit($id, Request $request){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error','Record not found');
            return redirect()->route('sub-categories.index');
        }
        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;
        return view('admin.sub_category.edit',$data);
    }

    // for updating sub category
    public function update($id,Request $request){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error','Record not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Sub Category not found'
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            //                         db table   column  except
            'category' => 'required',
            'status' => 'required'
        ]);

        if($validator->passes()){
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub Category updated successfully');

            return response([
                'status' => true,
                'message' => 'Sub Category updated successfully'
            ]);
        }
        else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // for deleting sub category
    public function destroy($id,Request $request){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error','Record not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Sub Category not found'
            ]);
        }
        

        $subCategory->delete();

        $request->session()->flash('success','Sub Category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted successfully'
        ]);

    }
}