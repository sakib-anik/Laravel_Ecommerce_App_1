<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempImage;
use Image;

class TempImagesController extends Controller
{
    public function create(Request $request){
        $image = $request->image;
        if(!empty($image)){
            $ext = $image->getClientOriginalExtension();
            $newName = time().'.'.$ext;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path().'/temp',$newName);

            // generate thumbnail
            // $sourcePath = public_path().'/temp/'.$newName;
            // $destPath = public_path().'/temp/thumb/'.$newName;
            // $image = \Intervention\Image\Facades\Image::make($sourcePath);
            // $image = Image::make($sourcePath); // youtuber used this
            //$image->fit(300,275);
                //    width, height
            //$image->save($destPath);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                // 'ImagePath' => asset('/temp/thumb/'.$newName), // youtuber provided
                'ImagePath' => asset('/temp/'.$newName),
                'message' => 'Image uploaded successfully'
            ]);
        }
    }
}