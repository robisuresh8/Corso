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
        // JWT se user_id lo
        $userId = null;
        if (!empty($this->request->user_id)) {
            $userId = (int) $this->request->user_id;
        } else {
            try {
                $header  = $this->request->getHeaderLine('Authorization');
                $token   = str_replace('Bearer ', '', $header);
                $parts   = explode('.', $token);
                if (count($parts) === 3) {
                    $payload = json_decode(base64_decode(str_replace(['-','_'],['+','/'],$parts[1])), true);
                    $userId  = isset($payload['uid']) ? (int) $payload['uid'] : null;
                }
            } catch (\Throwable $e) {}
        }

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $model = new QuizAttemptModel();
        $rows  = $model->where('user_id', $userId)
            ->orderBy('attempted_at', 'DESC')
            ->findAll(100);

        return $this->response->setJSON($rows);
    }
}