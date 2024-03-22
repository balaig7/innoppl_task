<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::select('id','name','sku','amount')
            ->when(auth()->user()->role == 'customer', function ($query) {
                $query->where('created_by', auth()->id());
            })->get();

        return view('index',compact('products'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        return view('product-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $prodId = Products::selectRaw('ifnull(MAX(id), 0) + 1 as prod_id')->pluck('prod_id')->first();

        $request->validate([
            'product_name' => 'required|string',
            'price' => 'required|decimal:2',
            'images' => 'required',
            'images.*' => 'mimes:jpeg,png'            
        ]);

        $loggedUserData = auth()->user();
        
        $userId = $loggedUserData->id;
        $images = $productsToAttach = [];
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image) {
                $name = md5(time().$prodId).'.'.$image->getClientOriginalExtension(); 
                $image->move(public_path().'/uploads/user_'.$userId, $name);
                $images[] = $name;
                $productsToAttach[] = public_path("uploads/user_{$userId}/$name");//preparing attachment for email
            }
        }
        $data = [
            'name' => $request->product_name,
            'created_by' => $userId,
            'amount' => $request->price,
            'description' => $request->description,
            'images' => implode(",",$images),
            'sku' => "SKU-".strtoupper(Str::random(3))."-".rand(2,99).$prodId,
        ];
        if(Products::create($data)) {

            $data['user_name'] = $loggedUserData->name;
            $data['user_email'] = $loggedUserData->email;
            $isAdmin = User::select('email')->where('role','admin');
            //check if admin user created then send email to admin
            if($isAdmin->exists()) {
                $toAddress = $isAdmin->pluck('email')->first();
                Mail::send('mail.product-share', $data, function($message) use ($productsToAttach,$toAddress) {
                    $message->to($toAddress)->subject("New Products Creation");
                    foreach ($productsToAttach as $productImage){
                        $message->attach($productImage);
                    }
                });
            }
            return response()->json(['status'=>'success','message' => 'Product created','redirect_url' => '/product']);

        } else {

            return response()->json(
                ['status'=>'success','message' => 'Internal server error','redirect_url' => '/product'],
                500
            );

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Products::find($id);
        //if product exist then convert to array 
        if($product->images!='') {
            $product->images = explode(',',$product->images);
        }
        return view('product-edit',compact('product'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'product_name' => 'required|string',
            'price' => 'required|decimal:2',
            'images.*' => 'mimes:jpeg,png'            

        ]);

        $product = Products::find($id);
        $oldImages = [];
        if($product->images!="") {
            $oldImages = explode(',',$product->images);
        }
        $newlyUploadedImages = [];
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image) {
                $name = md5(time()).'.'.$image->getClientOriginalExtension();  
                $image->move(public_path().'/uploads/user_'.$product->created_by, $name);  
                $newlyUploadedImages[] = $name;  
                
            }
        }
        $updatedImages = array_merge($oldImages,$newlyUploadedImages);//merge old and newly upload image
        $product->name = $request->product_name;
        $product->amount = $request->price;
        $product->description = $request->description;
        $product->images = implode(",",$updatedImages);
        if($product->save()) {
            return response()->json(['status'=>'success','message' => 'Product updated']);

        } else {
            return response()->json(
                ['status'=>'error','message' => 'Internal server error'],
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $productToDelete = Products::find($id);
        $productImages = $productToDelete->images;
        if($productToDelete->delete()) {
            if($productImages!= '') {
                foreach(explode(',',$productImages) as $img) {
                    File::delete(public_path().'/uploads/user_'.auth()->id()."/".$img);
                }
            }
            return response()->json(['status'=>'success','message' => 'Product Deleted','redirect_url' => '/product']);

        } else {
            return response()->json(
                ['status'=>'error','message' => 'Internal server error','redirect_url' => '/product'],
                500
            );

        }
    }

    public function export() {
        $products = Products::select('name','sku','amount','description','created_at','images','created_by')
                    ->when(auth()->user()->role == 'customer', function ($query) {
                        $query->where('created_by', auth()->id());
                    })->get();
        $csvFileName = 'products.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];
        $handle = fopen('php://output', 'w');
        //prepare columns to export
        fputcsv($handle, ['Product name','Sku', 'Price','images','Created On']); 
        
        foreach ($products as $product) {
            //prepare data to export
            $productImages = $product->images;
            $productImagesToExport = [];
            $productImagesData = '';
            if($productImages!='') {
                $explodedProduct = explode(',',$productImages);
                foreach ($explodedProduct as $evalue) {
                    $productImagesToExport[] = asset('/uploads/user_'.$product->created_by."/".$evalue);
                }
                $productImagesData = implode(',',$productImagesToExport);
            }
            fputcsv($handle, 
                [
                    $product->name,
                    $product->sku,
                    "₹{$product->amount}",
                    $productImagesData,
                    $product->created_at
                ]
            ); 
        }
    
        fclose($handle);
    
        return response()->make('', 200, $headers);
    }

    public function import(Request $request) {

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,xlsx,xls',
        ]);
        $loggedUserData = auth()->user();
        $userId = $loggedUserData->id;
        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), "r");
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;
            if($row == 1){
                continue;
            }
            //if the image url is added get the content from other server and post it in our server
            if($data['3']!='') {
                $explodedImage = explode(",",$data['3']);
                $importedFiles = [];
                foreach ($explodedImage as $img) {
                    if (filter_var($img, FILTER_VALIDATE_URL)) {
                        $allowedExtension = ['jpeg','png'];
                        $imageExtension = pathinfo($img, PATHINFO_EXTENSION); // get ext from path
                        if(in_array($imageExtension,$allowedExtension)) {
                            
                            $imageFileName = md5(time()).'.'.$imageExtension;  
                            $importedFiles[] =$imageFileName;
                            $imageFile = file_get_contents($img);
                            $fileToSave = public_path().'/uploads/user_'.$userId."/";

                            if(!File::isDirectory($fileToSave)){
                                File::makeDirectory($fileToSave, 0777, true, true);
                            } 
                            file_put_contents($fileToSave.$imageFileName,$imageFile);

                        }
                    }
                }
            }
            //validate data from csv 
            $validatedData = Validator::make([
                'product name' => $data[0],
                'sku' => $data[1],
                'price' => $data[2],
                ],
                [
                'product name' => 'required',
                'sku' => 'required',
                'price' => 'required|decimal:2',
                ]
            );

            if($validatedData->fails()){
                return response()->json(array(
                    'success' => false,
                    'errors' => $validatedData->getMessageBag()
            
                ), 400);
                
            } else {

                Products::create([
                    'name' => $data[0],
                    'sku' => $data[1],
                    'amount' => $data[2],
                    'images' => implode(",",$importedFiles),
                    'created_by' => $userId,
                ]);

            }
        }
        fclose($handle);
        return response()->json(['status'=>'success','message' => 'CSV file imported successfully.','redirect_url' => '/product']);
    }

    public function deleteProductThumnail(Request $request) {
        $product = Products::find($request->product);
        $fileToDelete = $request->file_to_delete;
        $currentlyUploadedThumbnails = $product->images;
        if($currentlyUploadedThumbnails!= '') {
            $productImages = explode(',',$currentlyUploadedThumbnails);
        }
        
        $product->images = implode(",",array_diff( $productImages, [$fileToDelete] ));
        if($product->save()) {
            File::delete(public_path().'/uploads/user_'.auth()->id()."/".$fileToDelete);
            return response()->json(['status'=>'success','message' => 'Product Thumbnail Deleted']);

        } else {
            return response()->json(
                ['status'=>'error','message' => 'Internal server error.'],
                500
            );

        }
        

    }
}
