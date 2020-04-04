<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\CrowdfundingProduct;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class CrowdfundingProductsController extends CommonProductsController
{
    
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }
    
    protected function customGrid(Grid $grid)
    {
        $grid->id('ID')->sortable();
        $grid->title('product name');
        $grid->on_sale('It has been added to')->display(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $grid->price('price');
        $grid->column('crowdfunding.target_amount', 'target_amount');
        $grid->column('crowdfunding.end_at', 'end_at');
        $grid->column('crowdfunding.total_amount', 'total_amount');
        $grid->column('crowdfunding.status', ' status')->display(function ($value) {
            return CrowdfundingProduct::$statusMap[$value];
        });
    }
    
    protected function customForm(Form $form)
    {
        
        $form->text('crowdfunding.target_amount', 'target_amount')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', 'end_at')->rules('required|date');
    }
    
    
}
