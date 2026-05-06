<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    @page {
      margin: 0px;
      size: 842px 595px;
    }
    body {
      margin: 0px;
      padding: 0px;
      font-family: sans-serif;
      width: 842px;
      height: 595px;
    }
    .wrapper {
      width: 842px;
      height: 595px;
      position: relative;
      overflow: hidden;
    }
    .template {
      position: absolute;
      top: 0;
      left: 0;
      width: 842px;
      height: 595px;
      z-index: 0;
    }
    .name {
      position: absolute;
      top: 190px;
      left: 120px;
      width: 600px;
      font-size: 52px;
      text-align: center;
      font-weight: bold;
      color: #1a1a1a;
      z-index: 1;
    }
    .course {
      position: absolute;
      top: 285px;
      left: 120px;
      width: 580px;
      font-size: 23px;
      text-align: center;
      font-weight: bold;
      color: #1a1a1a;
      z-index: 1;
    }
    .date {
      position: absolute;
      top: 495px;
      left: 660px;
      font-size: 12px;
      color: #1a1a1a;
      z-index: 1;
    }
    .certid {
      position: absolute;
      top: 515px;
      left: 645px;
      font-size: 10.5px;
      color: #1a1a1a;
      z-index: 1;
    }
    .qr {
      position: absolute;
      top: 240px;
      right: 105px;
      width: 114px;
      height: 114px;
      z-index: 1;
    }
    .qr img {
      width: 100%;
      height: 100%;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <img class="template" src="<?= rtrim(base_url(), '/') ?>/assets/images/corso_certificate_template.png" alt="">

    <div class="name"><?= esc($user_name ?? 'User Name') ?></div>
    <div class="course"><?= esc($course_name ?? 'Course Name') ?></div>
    <div class="date"><?= isset($issued_at) ? date('d M Y', strtotime($issued_at)) : date('d M Y') ?></div>
    <div class="certid"><?= esc($certificate_number ?? 'CORSO-XXXXX') ?></div>

    <?php if (!empty($qr_data_uri)): ?>
    <div class="qr">
      <img src="<?= esc($qr_data_uri) ?>">
    </div>
    <?php endif; ?>
  </div>
</body>
</html>