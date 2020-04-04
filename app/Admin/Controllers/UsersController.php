<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class UsersController extends Controller
{
    
    public function index()
    {
        return Admin::content(function (Content $content) {
            
            $content->header('user list');
            $content->body($this->grid());
        });
    }
    
    protected function grid()
    {
        
        return Admin::grid(User::class, function (Grid $grid) {
            
            
            $grid->id('ID')->sortable();
            
            
            $grid->name('username');
            
            $grid->email('email');
            
            $grid->email_verified('Verified email')->display(function ($value) {
                return $value ? 'Yes' : 'No';
            });
            
            $grid->created_at('Registration time');
            
            
            $grid->disableCreateButton();
            
            $grid->actions(function ($actions) {
                
                $actions->disableView();
                
                
                $actions->disableDelete();
                
                
                $actions->disableEdit();
            });
            
            $grid->tools(function ($tools) {
                
                
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        });
    }
    
}
