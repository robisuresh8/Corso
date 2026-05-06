<!DOCTYPE html>
<html>
<head>
<style>
body {
    margin: 0;
    padding: 0;
    background-image: url("<?= base_url('uploads/templates/certificate_template.png') ?>");
    background-size: cover;
    width: 100%;
    height: 100%;
    position: relative;
}

.name {
    position: absolute;
    top: 350px;
    width: 100%;
    text-align: center;
    font-size: 32px;
    font-weight: bold;
}

.course {
    position: absolute;
    top: 420px;
    width: 100%;
    text-align: center;
    font-size: 24px;
}

.score {
    position: absolute;
    top: 470px;
    width: 100%;
    text-align: center;
    font-size: 20px;
}

.certno {
    position: absolute;
    bottom: 100px;
    left: 100px;
    font-size: 16px;
}

.qr {
    position: absolute;
    bottom: 80px;
    right: 100px;
}
</style>
</head>
<body>

<div class="name"><?= $user_name ?></div>
<div class="course"><?= $course_name ?></div>
<div class="score">Score: <?= $score ?>%</div>
<div class="certno">Certificate No: <?= $certificate_number ?></div>

<div class="qr">
    <img src="<?= $qr_path ?>" width="100">
</div>

</body>
</html>
