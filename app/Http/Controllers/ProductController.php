<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::with('product_variant_price')->orderBy('id', 'DESC')->paginate(5);
        $variants = Variant::with('product_variant')->get();
        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        return $request->all();

        // store product details

        $product = Product::create([
            'title' => $request->product_name,
            'sku' => $request->product_sku,
            'description' => $request->product_description,
        ]);
        //store product variant
        foreach ($request->product_variant as $key => $value) {

            foreach ($value['value'] as $option => $variant) {
                $product_variant = ProductVariant::create([
                    'variant' =>  $variant,
                    'variant_id' => $value['option'],
                    'product_id' => $product->id,

                ]);
            }
        }
        //store product variant price
        foreach ($request->product_preview as $key => $value) {
            $variant = explode('/', $value['variant']);

            $product_variant_one = ProductVariant::where('variant', $variant[0])->first();
            $product_variant_two = ProductVariant::where('variant', $variant[1])->first();
            $product_variant_three = ProductVariant::where('variant', $variant[2])->first();

            $product_variant_price = ProductVariantPrice::create([
                'product_variant_one' =>     $product_variant_one->id,
                'product_variant_two' => $product_variant_two->id,
                'product_variant_three' => $product_variant_three->id,
                'price' => $value['price'],
                'stock' => $value['stock'],
                'product_id' => $product->id,

            ]);
        }
        return back();
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit($productId)
    {

        $variants = Variant::with('product_variant')->get();
        $product = Product::with('product_variant_price')->find($productId);
        return view('products.edit', compact('variants', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
    public function filterData(Request $request)
    {

        // return $request->all();

        $date = $request->date;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $variant = $request->variant;
        $title = $request->title;

        $products = Product::with('product_variant', 'product_variant_price')->when(request()->filled('date'), function ($query) use ($date) {
            return $query->whereDate('created_at', $date);
        })->when(request()->filled('title'), function ($query) use ($title) {
            return $query->where('title', "LIKE", "%" . $title . "%");
        })->when(request()->filled('price_from'), function ($query) use ($price_from) {
            return $query->where(function ($query1) use ($price_from) {
                $query1->whereHas('product_variant_price', function ($query2) use ($price_from) {
                    return $query2->where('price', '>=', $price_from);
                });
            });
        })->when(request()->filled('price_to'), function ($query) use ($price_to) {
            return $query->where(function ($query1) use ($price_to) {
                $query1->whereHas('product_variant_price', function ($query2) use ($price_to) {
                    return $query2->where('price', '<=', $price_to);
                });
            });
        })->when(request()->filled('variant'), function ($query) use ($variant) {
            return  $query->where(function ($query1) use ($variant) {
                $query1->whereHas('product_variant_price.variant_one', function ($query2) use ($variant) {
                    return $query2->where('id', $variant);
                })->orWhereHas('product_variant_price.variant_two', function ($query2) use ($variant) {
                    return $query2->where('id', $variant);
                })->orWhereHas('product_variant_price.variant_three', function ($query2) use ($variant) {
                    return $query2->where('id', $variant);
                });
            });
        })->paginate(5);

        $variants = Variant::with('product_variant')->get();
        return view('products.index', compact('products', 'variants'));
    }
}
