<?php

return [
    
    
    'name' => 'Shop Test',
    

    'logo' => '<b>Shop</b> Test',
    

    'logo-mini' => '<b>ST</b>',
    

    'route' => [
        
        'prefix' => 'admin',
        
        'namespace' => 'App\\Admin\\Controllers',
        
        'middleware' => ['web', 'admin'],
    ],
    

    'directory' => app_path('Admin'),
    

    'title' => 'Shop Test',
    

    'secure' => false,
    

    'auth' => [
        'guards' => [
            'admin' => [
                'driver'   => 'session',
                'provider' => 'admin',
            ],
        ],
        
        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => Encore\Admin\Auth\Database\Administrator::class,
            ],
        ],
    ],
    

    'upload' => [
        
        'disk' => 'public',
        
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
        ],
    ],
    

    'database' => [
        
        
        'connection' => '',
        
        
        'users_table' => 'admin_users',
        'users_model' => Encore\Admin\Auth\Database\Administrator::class,
        
        
        'roles_table' => 'admin_roles',
        'roles_model' => Encore\Admin\Auth\Database\Role::class,
        
        
        'permissions_table' => 'admin_permissions',
        'permissions_model' => Encore\Admin\Auth\Database\Permission::class,
        
        
        'menu_table' => 'admin_menu',
        'menu_model' => Encore\Admin\Auth\Database\Menu::class,
        
        
        'operation_log_table'    => 'admin_operation_log',
        'user_permissions_table' => 'admin_user_permissions',
        'role_users_table'       => 'admin_role_users',
        'role_permissions_table' => 'admin_role_permissions',
        'role_menu_table'        => 'admin_role_menu',
    ],
    

    'operation_log' => [
        
        'enable' => true,

        'except' => [
            'admin/auth/logs*',
        ],
    ],
    

    'skin' => 'skin-blue-light',
    
    /*
    |---------------------------------------------------------|
    |LAYOUT OPTIONS | fixed                                   |
    |               | layout-boxed                            |
    |               | layout-top-nav                          |
    |               | sidebar-collapse                        |
    |               | sidebar-mini                            |
    |---------------------------------------------------------|
     */
    'layout' => ['sidebar-mini', 'sidebar-collapse'],
    
    'version' => '1.5.x-dev',

    'extensions' => [
    
    ],
];