<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;

/**
 * Public API for active announcements (show on dashboard etc.)
 * GET /api/announcements/active?role=student
 */
class AnnouncementsController extends BaseController
{
    public function active()
    {
        $model = new AnnouncementModel();
        if (!$model->db->tableExists('announcements')) {
            return $this->response->setJSON(['announcements' => []]);
        }
        $role = trim($this->request->getGet('role') ?? '');
        $now = date('Y-m-d H:i:s');
        $all = $model->orderBy('created_at', 'DESC')->findAll(50);
        $out = [];
        foreach ($all as $a) {
            if ($a['starts_at'] && $a['starts_at'] > $now) continue;
            if ($a['ends_at'] && $a['ends_at'] < $now) continue;
            $tr = trim($a['target_roles'] ?? '');
            if ($tr !== '' && $tr !== 'all') {
                $roles = array_map('trim', explode(',', $tr));
                if (!in_array($role, $roles, true)) continue;
            }
            $out[] = ['id' => $a['id'], 'title' => $a['title'], 'body' => $a['body'] ?? ''];
        }
        return $this->response->setJSON(['announcements' => $out]);
    }
}
