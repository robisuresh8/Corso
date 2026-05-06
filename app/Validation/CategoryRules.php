<?php
namespace App\Validation;

class CategoryRules
{
    public $createCategory = [
        'name' => 'required|min_length[3]|max_length[100]',
        'slug' => 'required|min_length[3]|max_length[120]|is_unique[categories.slug,id,{id}]',
        'status' => 'required|in_list[active,inactive]'
    ];

    public $updateCategory = [
        'name' => 'required|min_length[3]|max_length[100]',
        'slug' => 'required|min_length[3]|max_length[120]|is_unique[categories.slug,id,{id}]',
        'status' => 'required|in_list[active,inactive]'
    ];
}
