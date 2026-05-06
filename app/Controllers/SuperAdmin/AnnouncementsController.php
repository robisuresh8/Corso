<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;

class AnnouncementsController extends BaseController
{
    /** GET /api/super-admin/announcements */
    public function index()
    {
        $model = new AnnouncementModel();
        if (!$model->db->tableExists('announcements')) {
            return $this->response->setJSON(['announcements' => []]);
        }
        $list = $model->orderBy('created_at', 'DESC')->findAll(50);
        return $this->response->setJSON(['announcements' => $list]);
    }

    /** POST /api/super-admin/announcements */
    public function create()
    {
        $model = new AnnouncementModel();
        if (!$model->db->tableExists('announcements')) {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'Announcements table not created. Run migration: php spark migrate']);
        }
        $data = $this->request->getJSON(true) ?? [];
        $title = trim($data['title'] ?? '');
        if (!$title) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Title required']);
        }
        $id = $model->insert([
            'title' => $title,
            'body' => trim($data['body'] ?? ''),
            'target_roles' => trim($data['target_roles'] ?? 'all'),
            'starts_at' => !empty($data['starts_at']) ? $data['starts_at'] : null,
            'ends_at' => !empty($data['ends_at']) ? $data['ends_at'] : null,
        ]);
        return $this->response->setJSON(['id' => $id, 'announcement' => $model->find($id)]);
    }

    /** PUT /api/super-admin/announcements/(:num) */
    public function update($id)
    {
        $model = new AnnouncementModel();
        $a = $model->find($id);
        if (!$a) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }
        $data = $this->request->getJSON(true) ?? [];
        $update = [];
        if (array_key_exists('title', $data)) $update['title'] = trim($data['title']);
        if (array_key_exists('body', $data)) $update['body'] = trim($data['body']);
        if (array_key_exists('target_roles', $data)) $update['target_roles'] = trim($data['target_roles']);
        if (array_key_exists('starts_at', $data)) $update['starts_at'] = $data['starts_at'] ?: null;
        if (array_key_exists('ends_at', $data)) $update['ends_at'] = $data['ends_at'] ?: null;
        if (!empty($update)) $model->update($id, $update);
        return $this->response->setJSON(['ok' => true, 'announcement' => $model->find($id)]);
    }

    /** DELETE /api/super-admin/announcements/(:num) */
    public function delete($id)
    {
        $model = new AnnouncementModel();
        if (!$model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }
        $model->delete($id);
        return $this->response->setJSON(['ok' => true]);
    }
}
