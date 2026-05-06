<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CategoryService;

class CategoryController extends BaseController
{
    protected $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    // GET all categories
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $categories
        ]);
    }

    // GET create form data
    public function create()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'name'   => '',
                'slug'   => '',
                'status' => 'active'
            ]
        ]);
    }

    // POST store new category
    public function store()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['name'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Name is required'
            ]);
        }

        if (empty($data['status']) || !in_array($data['status'], ['active', 'inactive'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Status must be active or inactive'
            ]);
        }

        $categoryData = [
            'name'   => $data['name'],
            'slug'   => url_title($data['name'], '-', true),
            'status' => $data['status'],
        ];

        $this->categoryService->createCategory($categoryData);

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Category created successfully'
        ]);
    }

    // GET single category for editing
    public function edit($id)
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Category not found'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $category
        ]);
    }

    // PUT update category
    public function update($id)
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Category not found'
            ]);
        }

        $data = $this->request->getJSON(true);

        $categoryData = [
            'name'   => $data['name'] ?? $category['name'],
            'slug'   => url_title($data['name'] ?? $category['name'], '-', true),
            'status' => $data['status'] ?? $category['status'],
        ];

        $this->categoryService->updateCategory($id, $categoryData);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Category updated successfully'
        ]);
    }

    // DELETE category
    public function delete($id)
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Category not found'
            ]);
        }

        $this->categoryService->deleteCategory($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }
}