<?php

namespace App\Database\Seeds;

use App\Models\CategoryModel;
use App\Models\CourseModel;
use App\Models\QuizModel;
use CodeIgniter\Database\Seeder;

/**
 * Inserts homepage assessment courses + one quiz per course when the DB is empty.
 * Run: php spark db:seed HomepageCatalogSeeder
 */
class HomepageCatalogSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('categories') || !$db->tableExists('courses') || !$db->tableExists('quizzes')) {
            echo "Skip: categories, courses, or quizzes table missing.\n";

            return;
        }
        if ((int) $db->table('courses')->countAllResults() > 0) {
            echo "Skip: courses table already has rows.\n";

            return;
        }

        $cm = new CategoryModel();
        $courseModel = new CourseModel();
        $quizModel = new QuizModel();

        $ensureCat = static function (string $name, string $slug) use ($cm): int {
            $row = $cm->where('slug', $slug)->first();
            if ($row) {
                return (int) $row['id'];
            }
            $cm->insert(['name' => $name, 'slug' => $slug]);

            return (int) $cm->getInsertID();
        };

        $categories = [
            'Data & Analytics' => 'data-analytics',
            'Programming'      => 'programming',
            'Marketing'        => 'marketing',
        ];

        $rows = [
            ['title' => 'Data Science Fundamentals', 'description' => 'Core concepts, tooling, and data handling.', 'category' => 'Data & Analytics'],
            ['title' => 'Java Basics', 'description' => 'Syntax, OOP fundamentals, and debugging.', 'category' => 'Programming'],
            ['title' => 'Digital Marketing', 'description' => 'SEO, content strategy, and analytics.', 'category' => 'Marketing'],
            ['title' => 'Excel for Analysis', 'description' => 'Formulas, pivot tables, and best practices.', 'category' => 'Data & Analytics'],
            ['title' => 'Python Basics', 'description' => 'Syntax, data structures and practical scripting.', 'category' => 'Programming'],
            ['title' => 'SQL Essentials', 'description' => 'SELECT, WHERE, joins and data best practices.', 'category' => 'Data & Analytics'],
        ];

        foreach ($rows as $row) {
            $slugKey = $categories[$row['category']] ?? 'general';
            $catId = $ensureCat($row['category'], $slugKey);
            $slug = url_title($row['title'], '-', true);
            $cid = $courseModel->insert([
                'category_id' => $catId,
                'title' => $row['title'],
                'slug' => $slug,
                'description' => $row['description'],
                'status' => 'published',
                'level' => 'beginner',
                'quiz_duration_minutes' => 8,
            ]);
            if (!$cid) {
                echo "Failed to insert course: {$row['title']}\n";
                continue;
            }
            $quizModel->insert([
                'course_id' => (int) $cid,
                'title' => 'Skill check: ' . $row['title'],
                'total_marks' => 10,
                'passing_marks' => 5,
                'duration' => 8,
            ]);
        }

        echo 'Seeded ' . count($rows) . " homepage courses (and quizzes).\n";
    }
}
