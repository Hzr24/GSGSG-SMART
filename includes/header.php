<?php
/**
 * Shared Header Include
 * GSGSG Scholarship Tracking System
 */
if (!isset($pageTitle)) $pageTitle = 'GSGSG SMART &mdash; Scholarship Management &amp; Tracking System';
if (!isset($pageSubtitle)) $pageSubtitle = 'Academic Year 2025/2026 &mdash; Semester II';
if (!isset($currentPage)) $currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Academic administrator dashboard to track and manage student scholarship statuses based on CGPA/GPA performance." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>" />
</head>
<body>

<div class="header-accent"></div>
