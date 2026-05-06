<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\QuizAttemptModel;

/**
 * JSON list of quiz attempts for the authenticated user (JWT).
 */
class QuizAttemptController extends BaseController
{
    public function index()
    {
        $auth = session()->get('auth_user');
        $userId = null;
        if (is_object($auth) && isset($auth->uid)) {
            $userId = (int) $auth->uid;
        }
        if (!$userId) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized']);
        }

        $model = new QuizAttemptModel();
        $rows = $model->where('user_id', $userId)
            ->orderBy('attempted_at', 'DESC')
            ->findAll(100);

        return $this->response->setJSON($rows);
    }
}
