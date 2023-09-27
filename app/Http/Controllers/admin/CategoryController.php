<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Validator;
use App\Models\Category;

use drh2so4\Thumbnail\Traits\thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;
use Intervention\Image\ImageManagerStatic as Image;
class CategoryController extends Controller
{
    // for listing categories
    public function index(Request $request){
        $categories = Category::latest();
        if(!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
        }
        $categories = $categories->paginate(10);
        return view('admin.category.list',compact('categories'));
    }
    // for showing create category form
    public function create(){
        return view('admin.category.create');
    } 
    // for storing data of new create category form
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);
        if($validator->passes()){
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            // save image here
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;

                // For copying Image

                // temp image source Path
                $sPath = public_path().'/temp/'.$tempImage->name;
                // temp image destination Path
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // Generate Image Thumbnail
                // $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                // $img = Image::make($sPath);
                // $img->resize(450,600);
                //         // width,height
                // $img->fit(450, 600, function ($constraint) {
                //     //  width, height
                //     $constraint->upsize();
                // });
                // $img->save($dPath);

                // alternative Thumbnail Generator
                // $image = \Intervention\Image\Facades\Image::create($this->validateData());
                // $image->makeThumbnail('image'); //This handles uploading image and storing it's thumbnail
                // return redirect('/imageUpload');
 
                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->flash('success','Category added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    // for showing category edit form
    public function edit($categoryId, Request $request){

        $category = Category::find($categoryId);
        if(empty($category)){
            return redirect()->route('categories.index');
        }

        return view('admin.category.edit',compact('category'));
    }
    // for updating category
    public function update($categoryId,Request $request){
        $category = Category::find($categoryId);
        if(empty($category)){
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
            //                         db table   column  except
        ]);
        if($validator->passes()){
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();
            
            $oldImage = $category->image;
            // save image here
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'-'.time().'.'.$ext;

                // For copying Image

                // temp image source Path
                $sPath = public_path().'/temp/'.$tempImage->name;
                // temp image destination Path
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // Generate Image Thumbnail
                // $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                // $img = Image::make($sPath);
                // $img->resize(450,600);
                //         // width,height
                // $img->fit(450, 600, function ($constraint) {
                //     //  width, height
                //     $constraint->upsize();
                // });
                // $img->save($dPath);

                // alternative Thumbnail Generator
                // $image = \Intervention\Image\Facades\Image::create($this->validateData());
                // $image->makeThumbnail('image'); //This handles uploading image and storing it's thumbnail
                // return redirect('/imageUpload');
 
                $category->image = $newImageName;
                $category->save();

                // Delete old Inages Here
                File::delete(public_path().'/uploads/category/'.$oldImage);

            }

            $request->session()->flash('success','Category updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    // for deleting category
    public function destroy($categoryId,Request $request){
        $category = Category::find($categoryId);
        if(empty($category)){
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
            //return redirect()->route('categories.index');
        }
        File::delete(public_path().'/uploads/category/'.$category->image);

        $category->delete();

        $request->session()->flash('success','Category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);

    }


    public function getImages(){
        return view('admin.category.create',['images' => TempImage::get()]);
    }

}