<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($quiz['title']) ?> - Quiz</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; color: #333; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 16px; }

        /* Quiz Header */
        .quiz-header {
            background: #fff;
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .quiz-header h1 { font-size: 1.5rem; color: #1a1a2e; }
        .quiz-meta { display: flex; gap: 20px; flex-wrap: wrap; }
        .badge {
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge.timer-badge { background: #fef3c7; color: #d97706; }

        /* Timer */
        #timer { font-size: 1.1rem; font-weight: 700; }
        #timer.warning { color: #dc2626 !important; animation: pulse 1s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }

        /* Progress */
        .progress-bar-wrap { background: #e5e7eb; border-radius: 99px; height: 8px; margin-bottom: 24px; }
        .progress-bar-fill { height: 8px; border-radius: 99px; background: #4f46e5; transition: width .3s; }

        /* Question Card */
        .question-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .question-num {
            font-size: 0.78rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 10px;
        }
        .question-text {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        /* Options */
        .options { display: flex; flex-direction: column; gap: 10px; }
        .option-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s;
            font-size: 0.97rem;
        }
        .option-label:hover { border-color: #4f46e5; background: #f5f3ff; }
        .option-label input[type="radio"] { accent-color: #4f46e5; width: 18px; height: 18px; }
        .option-label.selected { border-color: #4f46e5; background: #eef2ff; }

        /* Submit */
        .submit-section {
            background: #fff;
            border-radius: 12px;
            padding: 24px 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            text-align: center;
        }
        .btn-submit {
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: 14px 48px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-submit:hover { background: #4338ca; }
        .btn-submit:disabled { background: #9ca3af; cursor: not-allowed; }
        .submit-note { margin-top: 10px; font-size: 0.85rem; color: #6b7280; }

        /* Alerts */
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #d1fae5; color: #065f46; }

        /* Responsive */
        @media(max-width: 600px) {
            .quiz-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="container">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <!-- Quiz Header -->
    <div class="quiz-header">
        <div>
            <h1><?= esc($quiz['title']) ?></h1>
        </div>
        <div class="quiz-meta">
            <span class="badge">Total Marks: <?= esc($quiz['total_marks']) ?></span>
            <span class="badge">Passing: <?= esc($quiz['passing_marks']) ?></span>
            <span class="badge">Questions: <?= count($questions) ?></span>
            <?php if (!empty($quiz['duration'])): ?>
                <span class="badge timer-badge">
                    &#9200; <span id="timer"><?= esc($quiz['duration']) ?>:00</span>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressBar" style="width:0%"></div>
    </div>

    <!-- Quiz Form -->
    <form method="POST" action="<?= base_url('student/quiz/' . $quiz['id'] . '/submit') ?>" id="quizForm">
        <?= csrf_field() ?>

        <?php if (empty($questions)): ?>
            <div class="alert alert-error">
                Is quiz mein abhi koi questions nahi hain.
            </div>
        <?php else: ?>
            <?php foreach ($questions as $i => $q): ?>
                <div class="question-card">
                    <div class="question-num">Question <?= $i + 1 ?> of <?= count($questions) ?>
                        <?php if (!empty($q['marks'])): ?>
                            &nbsp;·&nbsp; <?= esc($q['marks']) ?> mark<?= $q['marks'] != 1 ? 's' : '' ?>
                        <?php endif; ?>
                    </div>
                    <div class="question-text"><?= esc($q['question']) ?></div>

                    <div class="options">
                        <?php
                        $opts = [
                            'a' => $q['option_a'],
                            'b' => $q['option_b'],
                            'c' => $q['option_c'],
                            'd' => $q['option_d'],
                        ];
                        foreach ($opts as $key => $val):
                            if (empty($val)) continue;
                        ?>
                            <label class="option-label" id="opt-<?= $q['id'] ?>-<?= $key ?>">
                                <input
                                    type="radio"
                                    name="answers[<?= $q['id'] ?>]"
                                    value="<?= $key ?>"
                                    onchange="onOptionChange(this, <?= $q['id'] ?>, '<?= $key ?>')"
                                >
                                <span><?= esc($val) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="submit-section">
                <button type="button" class="btn-submit" id="submitBtn" onclick="confirmSubmit()">
                    Submit Quiz
                </button>
                <p class="submit-note" id="submitNote">
                    0 of <?= count($questions) ?> questions answered
                </p>
            </div>
        <?php endif; ?>
    </form>

</div>

<script>
    const TOTAL_QUESTIONS = <?= count($questions) ?>;
    const DURATION_MINS   = <?= !empty($quiz['duration']) ? (int)$quiz['duration'] : 0 ?>;
    let answered = 0;

    // Track answered per question
    const answeredMap = {};

    function onOptionChange(radio, questionId, optionKey) {
        // Highlight selected
        ['a','b','c','d'].forEach(k => {
            const lbl = document.getElementById('opt-' + questionId + '-' + k);
            if (lbl) lbl.classList.remove('selected');
        });
        const selected = document.getElementById('opt-' + questionId + '-' + optionKey);
        if (selected) selected.classList.add('selected');

        // Count answered
        if (!answeredMap[questionId]) {
            answeredMap[questionId] = true;
            answered++;
            updateProgress();
        }
    }

    function updateProgress() {
        const pct = Math.round((answered / TOTAL_QUESTIONS) * 100);
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('submitNote').textContent =
            answered + ' of ' + TOTAL_QUESTIONS + ' questions answered';
    }

    function confirmSubmit() {
        const unanswered = TOTAL_QUESTIONS - answered;
        let msg = 'Are you sure you want to submit the quiz?';
        if (unanswered > 0) {
            msg = unanswered + ' question(s) unanswered. ' + msg;
        }
        if (confirm(msg)) {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('quizForm').submit();
        }
    }

    // Timer (if duration set)
    if (DURATION_MINS > 0) {
        let totalSecs = DURATION_MINS * 60;
        const timerEl = document.getElementById('timer');

        const interval = setInterval(() => {
            totalSecs--;
            const m = Math.floor(totalSecs / 60).toString().padStart(2,'0');
            const s = (totalSecs % 60).toString().padStart(2,'0');
            timerEl.textContent = m + ':' + s;

            if (totalSecs <= 60) timerEl.classList.add('warning');

            if (totalSecs <= 0) {
                clearInterval(interval);
                alert('Time is up! Quiz auto-submitting.');
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('quizForm').submit();
            }
        }, 1000);
    }
</script>
</body>
</html>