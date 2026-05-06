<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CertificateModel;
use App\Models\CourseModel;
use App\Models\CategoryModel;
use App\Models\PaymentModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use App\Models\QuizAttemptModel;
use App\Libraries\PermissionService;
use CodeIgniter\RESTful\ResourceController;

class Admin extends ResourceController
{
    protected $format = 'json';

    /**
     * Session (set by AdminAuth filter) or Bearer JWT — some clients send no session cookie.
     */
    private function authUser()
    {
        $auth = session()->get('auth_user');
        if ($auth) {
            return $auth;
        }
        $header = $this->request->getHeaderLine('Authorization');
        if ($header !== '' && str_starts_with($header, 'Bearer ')) {
            try {
                $jwt = new \App\Libraries\JWTService();

                return $jwt->verifyToken(trim(substr($header, 7)));
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    private function authRole(): ?string
    {
        $auth = $this->authUser();
        if (!$auth) {
            return null;
        }
        $role = is_object($auth) ? ($auth->role ?? '') : ($auth['role'] ?? '');

        return is_string($role) ? strtolower(trim($role)) : '';
    }

    private function can(string $permission): bool
    {
        $permService = new PermissionService();
        $role = $this->authRole();
        if ($role === 'super_admin') return true;
        return $role && $permService->can($role, $permission);
    }

    /**
     * GET /api/admin/my-permissions
     * Returns current user (from JWT) and their permissions so the admin panel can show only allowed items.
     */
    public function myPermissions()
    {
        $auth = $this->authUser();
        if (!$auth) {
            return $this->respond(['error' => 'Not authenticated'], 401);
        }
        $uid = (int) (is_object($auth) ? ($auth->uid ?? 0) : ($auth['uid'] ?? 0));
        $role = is_object($auth) ? ($auth->role ?? '') : ($auth['role'] ?? '');
        $userModel = new UserModel();
        $dbUser = $uid ? $userModel->select('id, name, email, role')->find($uid) : null;
        $roleFromDb = $dbUser['role'] ?? $role;
        $roleNormalized = is_string($roleFromDb) ? strtolower(trim($roleFromDb)) : '';
        $user = [
            'id'    => $uid,
            'name'  => $dbUser['name'] ?? '',
            'email' => $dbUser['email'] ?? '',
            'role'  => $roleNormalized,
        ];
        $permService = new PermissionService();
        $permissions = $permService->getPermissionsForRole($user['role']);
        return $this->respond([
            'user'        => $user,
            'permissions' => $permissions,
        ]);
    }

    private function cors()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    }

    /**
     * GET /api/v1/admin/stats
     * Returns dashboard stats for admin panel.
     * Public endpoint so admin panel can call it without JWT when admin logs in client-side.
     */
    public function stats()
    {
        $this->cors();

        $userModel = new UserModel();
        $certModel = new CertificateModel();

        $totalUsers = (int) $userModel->countAll();
        $totalCertificates = (int) $certModel->countAll();

        $db = $userModel->db;
        if ($db->fieldExists('status', 'users')) {
            $inactiveUsers = (int) $db->table('users')->where('status', 'inactive')->countAllResults();
            $activeUsers = (int) $db->table('users')->where('status', 'active')->countAllResults();
            $unmarked = $totalUsers - $activeUsers - $inactiveUsers;
            if ($unmarked > 0) {
                $activeUsers += $unmarked;
            }
        } else {
            $activeUsers = $totalUsers;
            $inactiveUsers = 0;
        }

        if ($db->fieldExists('role', 'users')) {
            $totalStudents = (int) $db->table('users')->where('role', 'student')->countAllResults();
        } else {
            $totalStudents = $totalUsers;
        }

        $courseModel = new CourseModel();
        $quizModel = new QuizModel();
        $attemptModel = new QuizAttemptModel();
        $paymentModel = new PaymentModel();
        $totalCourses = $courseModel->countAll();
        $totalQuizzes = $quizModel->countAll();
        $totalAttempts = (int) $attemptModel->countAll();
        $attemptsToday = 0;
        $attemptsLast7Days = 0;
        if ($db->tableExists('quiz_attempts')) {
            $attemptDateCol = $db->fieldExists('created_at', 'quiz_attempts') ? 'created_at' : 'attempted_at';
            $dayStart = date('Y-m-d 00:00:00');
            $dayEnd = date('Y-m-d 23:59:59');
            $attemptsToday = (int) $db->table('quiz_attempts')
                ->where($attemptDateCol . ' >=', $dayStart)
                ->where($attemptDateCol . ' <=', $dayEnd)
                ->countAllResults();
            $since7 = date('Y-m-d H:i:s', strtotime('-7 days'));
            $attemptsLast7Days = (int) $db->table('quiz_attempts')->where($attemptDateCol . ' >=', $since7)->countAllResults();
        }
        $revenue = 0;
        $revenueToday = 0;
        if ($db->tableExists('payments') && $db->fieldExists('amount', 'payments')) {
            $row = $paymentModel->selectSum('amount')->first();
            $revenue = $row ? (float) ($row['amount'] ?? 0) : 0;
            $dateCol = $db->fieldExists('paid_at', 'payments') ? 'paid_at' : 'created_at';
            $dayStart = date('Y-m-d 00:00:00');
            $dayEnd = date('Y-m-d 23:59:59');
            $todayRow = $db->table('payments')
                ->selectSum('amount')
                ->where($dateCol . ' >=', $dayStart)
                ->where($dateCol . ' <=', $dayEnd)
                ->get()
                ->getRowArray();
            $revenueToday = (float) ($todayRow['amount'] ?? 0);
        }
        $passRate = $averageScore = 0;
        if ($totalAttempts > 0) {
            $passed = $attemptModel->db->fieldExists('passed', 'quiz_attempts')
                ? $attemptModel->where('passed', 1)->countAllResults()
                : (int) round($totalAttempts * 0.7);
            $passRate = round($passed / $totalAttempts * 100, 1);
            if ($attemptModel->db->fieldExists('score', 'quiz_attempts')) {
                $avg = $attemptModel->selectAvg('score')->first();
                $averageScore = round((float) ($avg['score'] ?? 0), 1);
            }
        }

        return $this->respond([
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'total_students' => $totalStudents,
            'total_courses' => (int) $totalCourses,
            'total_quizzes' => (int) $totalQuizzes,
            'total_attempts' => $totalAttempts,
            'attempts_today' => $attemptsToday,
            'attempts_last_7_days' => $attemptsLast7Days,
            'pass_rate' => $passRate,
            'average_score' => $averageScore,
            'revenue' => $revenue,
            'revenue_today' => $revenueToday,
            'users' => $totalUsers,
            'certificates' => $totalCertificates,
        ]);
    }

    /**
     * Detect certificate table shape: legacy rows use VARCHAR id + name/course text;
     * newer migrations use certificate_number + course_id.
     */
    private function certificateSchemaKind(): string
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('certificates')) {
            return 'none';
        }
        if ($db->fieldExists('certificate_number', 'certificates')) {
            return 'modern';
        }
        if ($db->fieldExists('course', 'certificates')) {
            return 'legacy';
        }

        return 'unknown';
    }

    /** GET /api/admin/users - list users (users_view) */
    public function users()
    {
        if (!$this->can('users_view')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $userModel = new UserModel();
        $db = $userModel->db;
        $fields = ['id', 'name', 'email', 'role', 'created_at'];
        if ($db->fieldExists('status', 'users')) {
            $fields[] = 'status';
        }
        if ($db->fieldExists('phone', 'users')) {
            $fields[] = 'phone';
        }
        if ($db->fieldExists('last_login_at', 'users')) {
            $fields[] = 'last_login_at';
        }
        $users = $userModel->select(implode(', ', $fields))
            ->orderBy('id', 'DESC')
            ->findAll(500);

        return $this->respond(['users' => $users]);
    }

    /** GET /api/admin/certificates - list with optional search (certificates_view) */
    public function certificates()
    {
        if (!$this->can('certificates_view')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $search = $this->request->getGet('q');
        $certModel = new CertificateModel();
        $db = $certModel->db;
        $kind = $this->certificateSchemaKind();

        if ($kind === 'legacy') {
            $builder = $certModel->builder()
                ->select('certificates.*, users.name as user_name, users.email as user_email')
                ->join('users', 'users.id = certificates.user_id')
                ->orderBy('certificates.issued_at', 'DESC');
            if ($search !== null && $search !== '') {
                $builder->groupStart()
                    ->like('certificates.id', $search)
                    ->orLike('certificates.name', $search)
                    ->orLike('certificates.course', $search)
                    ->orLike('users.name', $search)
                    ->orLike('users.email', $search)
                    ->groupEnd();
            }
            $rows = $builder->get(100)->getResultArray();
            $supportsStatus = $db->fieldExists('status', 'certificates');
            foreach ($rows as &$row) {
                $row['certificate_number'] = $row['id'] ?? '';
                $row['course_title'] = $row['course'] ?? '';
                if (!isset($row['status'])) {
                    $row['status'] = 'active';
                }
                $row['revoke_supported'] = $supportsStatus;
            }
            unset($row);

            return $this->respond(['certificates' => $rows]);
        }

        if ($kind === 'modern') {
            $builder = $certModel->builder()
                ->select('certificates.*, users.name as user_name, users.email as user_email, courses.title as course_title')
                ->join('users', 'users.id = certificates.user_id')
                ->join('courses', 'courses.id = certificates.course_id', 'left')
                ->orderBy('certificates.issued_at', 'DESC');
            if ($search !== null && $search !== '') {
                $builder->groupStart()
                    ->like('certificates.certificate_number', $search)
                    ->orLike('users.name', $search)
                    ->orLike('users.email', $search)
                    ->groupEnd();
            }
            $certs = $builder->get(100)->getResultArray();
            foreach ($certs as &$row) {
                $row['revoke_supported'] = true;
            }
            unset($row);

            return $this->respond(['certificates' => $certs]);
        }

        return $this->respond(['certificates' => [], 'error' => 'Certificates table not found or unsupported schema'], 200);
    }

    /** POST /api/admin/certificates/(:segment)/revoke (certificates_manage) */
    public function certificateRevoke($id)
    {
        if (!$this->can('certificates_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $certModel = new CertificateModel();
        $db = $certModel->db;
        if (!$db->fieldExists('status', 'certificates')) {
            return $this->respond(['error' => 'Revoke/reissue requires a certificates table with status column'], 400);
        }
        $cert = $certModel->find($id);
        if (!$cert) {
            return $this->respond(['error' => 'Not found'], 404);
        }
        $certModel->update($id, ['status' => 'revoked', 'revoked_at' => date('Y-m-d H:i:s')]);

        return $this->respond(['ok' => true]);
    }

    /** POST /api/admin/certificates/(:segment)/reissue (certificates_manage) */
    public function certificateReissue($id)
    {
        if (!$this->can('certificates_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $certModel = new CertificateModel();
        $db = $certModel->db;
        if (!$db->fieldExists('status', 'certificates')) {
            return $this->respond(['error' => 'Revoke/reissue requires a certificates table with status column'], 400);
        }
        $cert = $certModel->find($id);
        if (!$cert) {
            return $this->respond(['error' => 'Not found'], 404);
        }
        $certModel->update($id, ['status' => 'active', 'revoked_at' => null]);

        return $this->respond(['ok' => true]);
    }

    /** GET /api/admin/categories (courses_manage) — for course form dropdown */
    public function categories()
    {
        if (!$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->orderBy('name', 'ASC')->findAll(200);

        return $this->respond(['categories' => $categories]);
    }

    /** GET /api/admin/courses (courses_manage) */
    public function courses()
    {
        if (!$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $courseModel = new CourseModel();
        $courses = $courseModel->select('courses.*, categories.name as category_name')
            ->join('categories', 'categories.id = courses.category_id', 'left')
            ->orderBy('courses.updated_at', 'DESC')
            ->findAll(200);
        return $this->respond(['courses' => $courses]);
    }

    /** POST /api/admin/courses (courses_manage) */
    public function courseCreate()
    {
        if (!$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $data = $this->request->getJSON(true) ?? [];
        $categoryId = (int) ($data['category_id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        if (!$title) return $this->respond(['error' => 'Title required'], 400);
        $auth = $this->authUser();
        $createdBy = $auth && (is_object($auth) ? isset($auth->uid) : isset($auth['uid']))
            ? (int) (is_object($auth) ? $auth->uid : $auth['uid'])
            : null;
        $categoryModel = new CategoryModel();
        if ($categoryId && !$categoryModel->find($categoryId)) {
            return $this->respond(['error' => 'Invalid category'], 400);
        }
        $courseModel = new CourseModel();
        $slug = url_title($title, '-', true);
        $id = $courseModel->insert([
            'category_id' => $categoryId ?: 1,
            'created_by' => $createdBy,
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'status' => $data['status'] ?? 'draft',
            'level' => $data['level'] ?? 'beginner',
        ]);
        return $this->respond(['id' => $id, 'course' => $courseModel->find($id)]);
    }

    /** PUT /api/admin/courses/(:num) (courses_manage) */
    public function courseUpdate($id)
    {
        if (!$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $courseModel = new CourseModel();
        $course = $courseModel->find($id);
        if (!$course) return $this->respond(['error' => 'Not found'], 404);
        $data = $this->request->getJSON(true) ?? [];
        $allowed = ['title', 'slug', 'description', 'category_id', 'status', 'level', 'price'];
        $update = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) $update[$k] = $data[$k];
        }
        if (!empty($update)) $courseModel->update($id, $update);
        return $this->respond(['ok' => true, 'course' => $courseModel->find($id)]);
    }

    /** DELETE /api/admin/courses/(:num) (courses_manage) */
    public function courseDelete($id)
    {
        if (!$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $id = (int) $id;
        $courseModel = new CourseModel();
        if (!$courseModel->find($id)) {
            return $this->respond(['error' => 'Not found'], 404);
        }
        $db = $courseModel->db;

        if ($db->tableExists('payments')) {
            $payCount = $db->table('payments')->where('course_id', $id)->countAllResults();
            if ($payCount > 0) {
                return $this->respond(['error' => 'This course has payment records. Remove or reassign them before deleting.'], 409);
            }
        }
        if ($db->tableExists('certificates') && $db->fieldExists('course_id', 'certificates')) {
            $certCount = $db->table('certificates')->where('course_id', $id)->countAllResults();
            if ($certCount > 0) {
                return $this->respond(['error' => 'This course has issued certificates. Cannot delete.'], 409);
            }
        }

        $db->transStart();
        try {
            $quizRows = $db->table('quizzes')->select('id')->where('course_id', $id)->get()->getResultArray();
            $quizIds = array_column($quizRows, 'id');
            if ($quizIds !== []) {
                $attemptRows = $db->table('quiz_attempts')->select('id')->whereIn('quiz_id', $quizIds)->get()->getResultArray();
                $attemptIds = array_column($attemptRows, 'id');
                if ($attemptIds !== [] && $db->tableExists('quiz_attempt_answers')) {
                    $db->table('quiz_attempt_answers')->whereIn('attempt_id', $attemptIds)->delete();
                }
                if ($db->tableExists('quiz_attempts')) {
                    $db->table('quiz_attempts')->whereIn('quiz_id', $quizIds)->delete();
                }
                $db->table('quiz_questions')->whereIn('quiz_id', $quizIds)->delete();
                $db->table('quizzes')->where('course_id', $id)->delete();
            }
            if ($db->tableExists('enrollments')) {
                $db->table('enrollments')->where('course_id', $id)->delete();
            }
            if ($courseModel->delete($id) === false) {
                $db->transRollback();

                return $this->respond(['error' => 'Could not delete course'], 500);
            }
        } catch (\Throwable $e) {
            $db->transRollback();

            return $this->respond(['error' => 'Could not delete course'], 500);
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->respond(['error' => 'Could not delete course'], 500);
        }

        return $this->respond(['ok' => true]);
    }

    /** GET /api/admin/payments (payments_view) */
    public function payments()
    {
        if (!$this->can('payments_view')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $paymentModel = new PaymentModel();
        $payments = $paymentModel->select('payments.*, users.name as user_name, users.email as user_email, courses.title as course_title')
            ->join('users', 'users.id = payments.user_id')
            ->join('courses', 'courses.id = payments.course_id', 'left')
            ->orderBy('payments.id', 'DESC')
            ->findAll(100);
        return $this->respond(['payments' => $payments]);
    }

    /** GET /api/admin/quizzes (courses_manage or quizzes_manage) */
    public function quizzes()
    {
        if (!$this->can('courses_manage') && !$this->can('quizzes_manage') && !$this->can('questions_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $quizModel = new QuizModel();
        $quizzes = $quizModel->select('quizzes.*, courses.title as course_title')
            ->join('courses', 'courses.id = quizzes.course_id', 'left')
            ->orderBy('quizzes.updated_at', 'DESC')
            ->findAll(200);
        $questionModel = new QuizQuestionModel();
        foreach ($quizzes as &$q) {
            $q['question_count'] = (int) $questionModel->where('quiz_id', $q['id'])->countAllResults();
        }
        unset($q);

        return $this->respond(['quizzes' => $quizzes]);
    }

    /** GET /api/admin/quizzes/(:num)/questions (questions_manage) */
    public function quizQuestions($quizId)
    {
        if (!$this->can('questions_manage') && !$this->can('quizzes_manage') && !$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $questionModel = new QuizQuestionModel();
        $questions = $questionModel->where('quiz_id', $quizId)->orderBy('position', 'ASC')->findAll();
        return $this->respond(['questions' => $questions]);
    }

    /** POST /api/admin/quizzes/(:num)/questions (questions_manage) */
    public function questionCreate($quizId)
    {
        if (!$this->can('questions_manage') && !$this->can('quizzes_manage') && !$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $quizModel = new QuizModel();
        if (!$quizModel->find($quizId)) return $this->respond(['error' => 'Quiz not found'], 404);
        $data = $this->request->getJSON(true) ?? [];
        $question = trim($data['question'] ?? '');
        if (!$question) return $this->respond(['error' => 'Question text required'], 400);
        $questionModel = new QuizQuestionModel();
        $maxPos = $questionModel->where('quiz_id', $quizId)->selectMax('position')->first();
        $position = 1 + (isset($maxPos['position']) ? (int) $maxPos['position'] : 0);
        $rawOpt = strtoupper(substr(trim((string) ($data['correct_option'] ?? 'A')), 0, 1));
        $correct = in_array($rawOpt, ['A', 'B', 'C', 'D'], true) ? $rawOpt : 'A';
        $id = $questionModel->insert([
            'quiz_id' => $quizId,
            'question' => $question,
            'option_a' => trim($data['option_a'] ?? ''),
            'option_b' => trim($data['option_b'] ?? ''),
            'option_c' => trim($data['option_c'] ?? ''),
            'option_d' => trim($data['option_d'] ?? ''),
            'correct_option' => $correct,
            'marks' => (int) ($data['marks'] ?? 1),
            'position' => $position,
        ]);
        return $this->respond(['id' => $id, 'question' => $questionModel->find($id)]);
    }

    /** PUT /api/admin/questions/(:num) (questions_manage) */
    public function questionUpdate($id)
    {
        if (!$this->can('questions_manage') && !$this->can('quizzes_manage') && !$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $questionModel = new QuizQuestionModel();
        $q = $questionModel->find($id);
        if (!$q) return $this->respond(['error' => 'Not found'], 404);
        $data = $this->request->getJSON(true) ?? [];
        $allowed = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'marks', 'position'];
        $update = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                if ($k === 'correct_option') {
                    $rawOpt = strtoupper(substr(trim((string) $data[$k]), 0, 1));
                    $update[$k] = in_array($rawOpt, ['A', 'B', 'C', 'D'], true) ? $rawOpt : 'A';
                } else {
                    $update[$k] = $data[$k];
                }
            }
        }
        if (!empty($update)) $questionModel->update($id, $update);
        return $this->respond(['ok' => true, 'question' => $questionModel->find($id)]);
    }

    /** DELETE /api/admin/questions/(:num) (questions_manage) */
    public function questionDelete($id)
    {
        if (!$this->can('questions_manage') && !$this->can('quizzes_manage') && !$this->can('courses_manage')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $questionModel = new QuizQuestionModel();
        if (!$questionModel->find($id)) return $this->respond(['error' => 'Not found'], 404);
        $questionModel->delete($id);
        return $this->respond(['ok' => true]);
    }

    /** GET /api/admin/reports/analytics (reports_view) */
    public function reportsAnalytics()
    {
        if (!$this->can('reports_view')) {
            return $this->respond(['error' => 'Forbidden'], 403);
        }
        $userModel = new UserModel();
        $certModel = new CertificateModel();
        $courseModel = new CourseModel();
        $attemptModel = new QuizAttemptModel();
        $paymentModel = new PaymentModel();
        $db = $userModel->db;

        $revenueByMonth = [];
        if ($db->tableExists('payments')) {
            $paidAt = $db->fieldExists('paid_at', 'payments') ? 'paid_at' : 'created_at';
            $rows = $db->table('payments')->select("DATE_FORMAT({$paidAt}, '%Y-%m') as month")->selectSum('amount', 'total')->groupBy('month')->orderBy('month', 'DESC')->get(12)->getResultArray();
            foreach ($rows as $r) $revenueByMonth[$r['month']] = (float) ($r['total'] ?? 0);
        }
        $completionByMonth = [];
        if ($db->tableExists('quiz_attempts')) {
            $attDateCol = $db->fieldExists('created_at', 'quiz_attempts') ? 'created_at' : 'attempted_at';
            $rows = $db->table('quiz_attempts')
                ->select("DATE_FORMAT({$attDateCol}, '%Y-%m') as month")
                ->selectCount('id', 'cnt')
                ->groupBy('month')
                ->orderBy('month', 'DESC')
                ->get(12)
                ->getResultArray();
            foreach ($rows as $r) {
                $completionByMonth[$r['month']] = (int) ($r['cnt'] ?? 0);
            }
        }

        return $this->respond([
            'total_users' => (int) $userModel->countAll(),
            'total_certificates' => (int) $certModel->countAll(),
            'total_courses' => (int) $courseModel->countAll(),
            'total_attempts' => (int) $attemptModel->countAll(),
            'revenue_by_month' => $revenueByMonth,
            'attempts_by_month' => $completionByMonth,
        ]);
    }
}
