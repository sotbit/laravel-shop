<?php
namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class SeckillProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_SECKILL;
    }
    
    protected function customGrid(Grid $grid)
    {
        $grid->id('ID')->sortable();
        $grid->title('product name');
        $grid->on_sale('It has been added to')->display(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $grid->price('价格');
        $grid->column('seckill.start_at', 'Starting time');
        $grid->column('seckill.end_at', 'End Time');
        $grid->sold_count('Sales');
    }
    
    protected function customForm(Form $form)
    {
        
        $form->datetime('seckill.start_at', 'Spike start time')->rules('required|date');
        $form->datetime('seckill.end_at', 'Spike end time')->rules('required|date');
    }
}