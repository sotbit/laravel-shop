<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class ProductsController extends CommonProductsController
{
    
    public function getProductType()
    {
        return Product::TYPE_NORMAL;
    }
    
    protected function customGrid(Grid $grid)
    {
        $grid->model()->with(['category']);
        $grid->id('ID')->sortable();
        $grid->title('product name');
        $grid->column('category.name', 'Category');
        $grid->on_sale('It has been added to')->display(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $grid->price('price');
        $grid->rating('rating');
        $grid->sold_count('Sales');
        $grid->review_count('Number of comments');
    }
    
    protected function customForm(Form $form)
    {
        
    }

}
