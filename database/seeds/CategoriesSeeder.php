<?php

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name'     => 'Mobile phone accessories',
                'children' => [
                    ['name' => 'phone case'],
                    ['name' => 'Film'],
                    ['name' => 'storage card'],
                    ['name' => 'Data cable'],
                    ['name' => 'charger'],
                    [
                        'name'     => 'headset',
                        'children' => [
                            ['name' => 'Wired headset'],
                            ['name' => 'Bluetooth earphone'],
                        ],
                    ],
                ],
            ],
        ];
        
        foreach ($categories as $data) {
            $this->createCategory($data);
        }
    }
    
    protected function createCategory($data, $parent = null)
    {
        
        $category = new Category(['name' => $data['name']]);
        
        $category->is_directory = isset($data['children']);
        
        if (!is_null($parent)) {
            $category->parent()->associate($parent);
        }
        
        $category->save();
        
        if (isset($data['children']) && is_array($data['children'])) {
            
            foreach ($data['children'] as $child) {
                
                $this->createCategory($child, $category);
            }
        }
    }
}
