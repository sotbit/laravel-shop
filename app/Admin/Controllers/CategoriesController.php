<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class CategoriesController extends Controller
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
            ->header('Commodity category list')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
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
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit commodity category')
            ->body($this->form(true)->edit($id));
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
            ->header('Create product category')
            ->body($this->form(false));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category);
    
        $grid->id('ID')->sortable();
        $grid->name('name');
        $grid->level('level');
        $grid->is_directory('Whether the directory')->display(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $grid->path('path');
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Category::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->parent_id('Parent id');
        $show->is_directory('Is directory');
        $show->level('Level');
        $show->path('Path');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($isEditing = false)
    {
        $form = new Form(new Category);
    
        $form->text('name', 'name')->rules('required');
    
        
        if ($isEditing) {
            
            
            $form->display('is_directory', 'Whether the directory')->with(function ($value) {
                return $value ? 'Yes' :'No';
            });
            
            $form->display('parent.name', 'Father');
        } else {
            
            $form->radio('is_directory', 'is_directory')
                ->options(['1' => 'Yes', '0' => 'No'])
                ->default('0')
                ->rules('required');
        
            
            $form->select('parent_id', 'parent_id')->ajax('/admin/api/categories');
        }

        return $form;
    }
    
    
    public function apiIndex(Request $request)
    {
        
        $search = $request->input('q');
        $result = Category::query()
            ->where('is_directory', boolval($request->input('is_directory', true)))
            ->where('name', 'like', '%'.$search.'%')
            ->paginate();
        
        
        $result->setCollection($result->getCollection()->map(function (Category $category) {
            return ['id' => $category->id, 'text' => $category->full_name];
        }));
        
        return $result;
    }
}
