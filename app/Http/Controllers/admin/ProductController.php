<?php

namespace App\Http\Controllers\admin;

use Image;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\TempImage;
use App\Models\SubCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductRating;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // for listing products
    public function index(Request $request){
        $products = Product::latest('id')->with('product_images');
        if($request->get('keyword') != ""){
            $products = $products->where('title','like','%'.$request->keyword.'%');
        }
        $products = $products->paginate();
        $data['products'] = $products;
        return view('admin.products.list',$data);
    }
    // for showing create product form
    public function create(){
        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.products.create', $data);
    }
// for storing data of new create product form
    public function store(Request $request){
        $rules = [
            'title' => 'required',
            'price' => 'required|numeric',
            'slug' => 'required|unique:products',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'is_featured' => 'required|in:Yes,No',
            'category' => 'required|numeric'
        ];

        if(!empty($request->track_qty) && $request->track_qty == 'Yes'){
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(),$rules);
        if($validator->passes()){
            $product = new Product;
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->description = $request->description;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->shipping_returns = $request->shipping_returns;
            $product->short_description = $request->short_description;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();

            // Save Gallery Pics
            if(!empty($request->image_array)){
                foreach ($request->image_array as $key => $temp_image_id) {

                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.',$tempImageInfo->name);
                    // 1685990169.jpg
                    $ext = last($extArray);     // like jpg,gif,png etc

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    // product_id => 4 ; product_image_id => 1
                    // 4-1-123424.jpg
                    $productImage->image = $imageName;
                    $productImage->save();

                    // Generate Product Thumbnails

                    // Large Image
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
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
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destPath = public_path().'/uploads/product/small/'.$imageName;
                    // $image = \Intervention\Image\Facades\Image::make($sourcePath);
                    // $image = Image::make($sourcePath); // used by youtuber
                    // $image->fit(300,300);   // small image thumbnail will be fixed size
                    File::copy($sourcePath,$destPath);


                    //$image->save($destPath);
                }
            }

            $request->session()->flash('success','Product added successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Product added successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // for showing product edit form
    public function edit($id, Request $request){
        $product = Product::find($id);

        if(empty($product)){
            // $request->session()->flash('error','Product not found');
            return redirect()->route('products.index')->with('error','Product not found');
        }                                             // use this instead of session method

        // Fetch Product Images
        $productImages = ProductImage::where('product_id',$product->id)->get();

        $subCategories = SubCategory::where('category_id',$product->category_id)->get();

        $relatedProducts = [];
        // fetch related products
        if($product->related_products != ''){
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id',$productArray)->with('product_images')->get();
        }

        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['product'] = $product;
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;
        return view('admin.products.edit',$data);
    }
    // for updating product
    public function update($id,Request $request){
        $product = Product::find($id);


        $rules = [
            'title' => 'required',
            'price' => 'required|numeric',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
            'sku' => 'required|unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'is_featured' => 'required|in:Yes,No',
            'category' => 'required|numeric'
        ];

        if(!empty($request->track_qty) && $request->track_qty == 'Yes'){
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(),$rules);
        if($validator->passes()){
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->description = $request->description;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->shipping_returns = $request->shipping_returns;
            $product->short_description = $request->short_description;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();

            $request->session()->flash('success','Product updated successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    // for deleting product
    public function destroy($id,Request $request){
        $product = Product::find($id);
        if(empty($product)){
            $request->session()->flash('error','Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Product not found'
            ]);
        }
        $productImages = ProductImage::where('product_id',$id)->get();

        if(!empty($productImages)){
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/product/large/'.$productImage->image));
                File::delete(public_path('uploads/product/small/'.$productImage->image));
            }

            ProductImage::where('product_id',$id)->delete();

        }


        $product->delete();

        $request->session()->flash('success','Product deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);

    }

    public function getProducts(Request $request){

        $tempProduct = [];
        if($request->term != ""){
            $products = Product::where('title','like','%'.$request->term.'%')->get();

            if($products != null){
                foreach ($products as $product) {
                    $tempProduct[] = array('id'   => $product->id,
                                           'text' => $product->title
                                    );
                }
            }
        }

        // print_r($tempProduct);

        return response()->json([
            'tags' => $tempProduct,
            'status' => true
        ]);

    }

    public function productRatings(Request $request){
        $ratings = ProductRating::select('product_ratings.*','products.title as productTitle')->orderBy('created_at','DESC');
        $ratings = $ratings->leftJoin('products','products.id','product_ratings.product_id');
        if($request->get('keyword') != ''){
            $ratings = $ratings->orWhere('products.title','like','%'.$request->keyword.'%');
            $ratings = $ratings->orWhere('product_ratings.username','like','%'.$request->keyword.'%');
        }
        $ratings = $ratings->paginate(10);
        return view('admin.products.ratings',compact('ratings'));
    }

    public function changeRatingStatus(Request $request){
        $productRating = ProductRating::find($request->id);
        $productRating->status = $request->status;
        $productRating->save();

        session()->flash('success', 'Status change successfully.');

        return response()->json([
            'status' => true,
        ]);
    }
}