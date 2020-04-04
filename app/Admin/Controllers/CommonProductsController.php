<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Jobs\SyncOneProductToES;

abstract class CommonProductsController extends Controller
{
    
    use HasResourceActions;
    
    
    abstract public function getProductType();
    
    
    abstract protected function customGrid(Grid $grid);
    
    
    abstract protected function customForm(Form $form);
    
    public function index(Content $content)
    {
        return $content
            ->header(Product::$typeMap[$this->getProductType()].'List')
            ->body($this->grid());
    }
    
    public function edit($id, Content $content)
    {
        return $content
            ->header('edit'.Product::$typeMap[$this->getProductType()])
            ->body($this->form()->edit($id));
    }
    
    public function create(Content $content)
    {
        return $content
            ->header('create'.Product::$typeMap[$this->getProductType()])
            ->body($this->form());
    }
    
    protected function grid()
    {
        $grid = new Grid(new Product());
        
        
        $grid->model()->where('type', $this->getProductType())->orderBy('id', 'desc');
        
        $this->customGrid($grid);
        
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        
        return $grid;
    }
    
    protected function form()
    {
        $form = new Form(new Product());
        
        $form->hidden('type')->value($this->getProductType());
        $form->text('title', 'title')->rules('required');
        $form->text('long_title', 'long_title')->rules('required');
        $form->select('category_id', 'category_id')->options(function ($id) {
            $category = Category::find($id);
            if ($category) {
                return [$category->id => $category->full_name];
            }
        })->ajax('/admin/api/categories?is_directory=0');
        $form->image('image', 'image')->rules('required|image');
        $form->editor('description', 'description')->rules('required');
        $form->radio('on_sale', 'on_sale')->options(['1' => 'Yes', '0' => 'No'])->default('0');
        
        
        $this->customForm($form);
        
        $form->hasMany('skus', 'Product SKU', function (Form\NestedForm $form) {
            $form->text('title', 'SKU name')->rules('required');
            $form->text('description', 'SKU description')->rules('required');
            $form->text('price', 'price')->rules('required|numeric|min:0.01');
            $form->text('stock', 'stock')->rules('required|integer|min:0');
        });
        $form->hasMany('properties', 'properties', function (Form\NestedForm $form) {
            $form->text('name', 'name')->rules('required');
            $form->text('value', 'value')->rules('required');
        });
        $form->saving(function (Form $form) {
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
        });
    
        $form->saved(function (Form $form) {
            $product = $form->model();
            $this->dispatch(new SyncOneProductToES($product)); 
        });
        
        return $form;
    }
}