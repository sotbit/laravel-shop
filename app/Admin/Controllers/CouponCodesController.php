<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CouponCodesController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Coupon list')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit coupon')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('New coupon')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode);
    
        $grid->model()->orderBy('created_at', 'desc');
        $grid->id('ID')->sortable();
        $grid->name('name');
        $grid->code('code');
        $grid->description('description');
        $grid->column('usage', 'usage')->display(function ($value) {
            return "{$this->used} / {$this->total}";
        });
        $grid->enabled('enabled')->display(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $grid->created_at('created_at');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CouponCode::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->code('Code');
        $show->type('Type');
        $show->value('Value');
        $show->total('Total');
        $show->used('Used');
        $show->min_amount('Min amount');
        $show->not_before('Not before');
        $show->not_after('Not after');
        $show->enabled('Enabled');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CouponCode);
    
        $form->display('id', 'ID');
        $form->text('name', 'name')->rules('required');
        $form->text('code', 'code')->rules(function($form) {
            
            if ($id = $form->model()->id) {
                return 'nullable|unique:coupon_codes,code,'.$id.',id';
            } else {
                return 'nullable|unique:coupon_codes';
            }
        });
        $form->radio('type', 'type')->options(CouponCode::$typeMap)->rules('required');
        $form->text('value', 'value')->rules(function ($form) {
            if ($form->type === CouponCode::TYPE_PERCENT) {
                
                return 'required|numeric|between:1,99';
            } else {
                
                return 'required|numeric|min:0.01';
            }
        });
        $form->text('total', 'total')->rules('required|numeric|min:0');
        $form->text('min_amount', 'min_amount')->rules('required|numeric|min:0');
        $form->datetime('not_before', 'not_before');
        $form->datetime('not_after', 'not_after');
        $form->radio('enabled', 'enabled')->options(['1' => 'Yes', '0' => 'No']);
    
        $form->saving(function (Form $form) {
            if (!$form->code) {
                $form->code = CouponCode::findAvailableCode();
            }
        });

        return $form;
    }
}
