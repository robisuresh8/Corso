<?php
namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'status'];
    protected $useTimestamps = true; // automatically handles created_at and updated_at
}
