<?php
namespace App\Services;

use App\Models\CategoryModel;

class CategoryService
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    // Get all categories
    public function getAllCategories()
    {
        return $this->categoryModel->findAll();
    }

    // Create a new category
    public function createCategory(array $data)
    {
        return $this->categoryModel->insert($data);
    }

    // Update an existing category
    public function updateCategory(int $id, array $data)
    {
        return $this->categoryModel->update($id, $data);
    }

    // Delete a category
    public function deleteCategory(int $id)
    {
        return $this->categoryModel->delete($id);
    }

    // Find a category by ID
    public function getCategoryById(int $id)
    {
        return $this->categoryModel->find($id);
    }
}
