<?php
session_start();

// إزالة جميع البيانات المخزنة في الجلسة
session_unset();

// إنهاء الجلسة
session_destroy();

// إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: login.php");
exit();
?>