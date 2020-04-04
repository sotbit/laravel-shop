<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use App\SearchBuilders\ProductSearchBuilder;
use App\Services\ProductService;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->input('page', 1);
        $perPage = 16;
        
        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage, $page);
    
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            
            $builder->category($category);
        }
    
        if ($search = $request->input('search', '')) {
            $keywords = array_filter(explode(' ', $search));
            
            $builder->keywords($keywords);
        }
    
        if ($search || isset($category)) {
            
            $builder->aggregateProperties();
        }
    
        $propertyFilters = [];
        if ($filterString = $request->input('filters')) {
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                list($name, $value) = explode(':', $filter);
                $propertyFilters[$name] = $value;
                
                $builder->propertyFilter($name, $value);
            }
        }
    
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
    
        
        $result = app('es')->search($builder->getParams());
        
        
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        $products = Product::query()->byIds($productIds)->get();
        
        $pager = new LengthAwarePaginator($products, $result['hits']['total'], $perPage, $page, [
            'path' => route('products.index', false), 
        ]);
    
        $properties = [];
        
        if (isset($result['aggregations'])) {
            
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                ->map(function ($bucket) {
                    
                    return [
                        'key'    => $bucket['key'],
                        'values' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                    ];
                })->filter(function ($property) use ($propertyFilters) {
                    
                    return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]) ;
                });
        }
        
        return view('products.index', [
            'products' => $pager,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
            'category' => $category ?? null,
            'properties' => $properties,
            'propertyFilters' => $propertyFilters,
        ]);
    }
    
    
    public function index_old(Request $request)
    {
        
        $builder = Product::query()->where('on_sale', true);
        
        
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }
    
        
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            
            if ($category->is_directory) {
                
                $builder->whereHas('category', function ($query) use ($category) {
                    
                    $query->where('path', 'like', $category->path.$category->id.'-%');
                });
            } else {
                
                $builder->where('category_id', $category->id);
            }
        }
    
        
        
        if ($order = $request->input('order', '')) {
            
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
    
        $products = $builder->orderByDesc('created_at')->paginate(16);
        return view('products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
            'category' => $category ?? null
        ]);
    }
    
    
    public function show(Product $product, Request $request, ProductService $service)
    {
        
        if (!$product->on_sale) {
            throw new InvalidRequestException('Product not available');
        }
        
        $favored = false;
        
        if($user = $request->user()) {
            
            
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }
    
        
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) 
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') 
            ->orderBy('reviewed_at', 'desc') 
            ->limit(10) 
            ->get();
    
        
        $builder = (new ProductSearchBuilder())->onSale()->paginate(4, 1);
        
        foreach ($product->properties as $property) {
            
            $builder->propertyFilter($property->name, $property->value, 'should');
        }
        
        $builder->minShouldMatch(ceil(count($product->properties) / 2));
        $params = $builder->getParams();
        
        $params['body']['query']['bool']['must_not'] = [['term' => ['_id' => $product->id]]];
        
        $result = app('es')->search($params);
        $similarProductIds = $service->getSimilarProductIds($product, 4);
        $similarProducts   = Product::query()->byIds($similarProductIds)->get();
        
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews,
            'similar' => $similarProducts,
        ]);
    }
    
    
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }
    
    
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }
    
    
    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);
        return view('products.favorites', ['products' => $products]);
    }
    
}

