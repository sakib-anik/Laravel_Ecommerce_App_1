<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Image;

class ProductImageController extends Controller
{
    public function update(Request $request){
        
        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $sourcePath = $image->getPathName();

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL';
        $productImage->save();

        $imageName = $request->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
        // product_id => 4 ; product_image_id => 1
        // 4-1-123424.jpg
        $productImage->image = $imageName;
        $productImage->save();

        // Large Image
        $destPath = public_path().'/uploads/product/large/'.$imageName;
        // $image = \Intervention\Image\Facades\Image::make($sourcePath);
        // $image = Image::make($sourcePath); // used by youtuber
        // $image->resize(1400, null,  function($constraint){
           // maximum width, for maintaining aspect ratio
            // $constraint->aspectRatio(); // for maintaining aspect ratio
        // });
        // $image->save($destPath);
        File::copy($sourcePath,$destPath);
        // Small Image
        //$sourcePath = public_path().'/temp/'.$tempImageInfo->name;
        $destPath = public_path().'/uploads/product/small/'.$imageName;
        // $image = \Intervention\Image\Facades\Image::make($sourcePath);
        // $image = Image::make($sourcePath); // used by youtuber
        // $image->fit(300,300);   // small image thumbnail will be fixed size
        File::copy($sourcePath,$destPath);

        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset('uploads/product/small/'.$productImage->image),
            'message' => 'Image saved successfully'
        ]);
    }
    public function destroy(Request $request){
        $productImage = ProductImage::find($request->id);

        if(empty($productImage)){
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ]);
        }

        // Delete Images From Folder
        File::delete(public_path('uploads/product/large/'.$productImage->image));
        File::delete(public_path('uploads/product/small/'.$productImage->image));

        $productImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}