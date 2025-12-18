<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link rel="stylesheet" href="assets/styles_dashboard.css">
</head>
<body>
    <div class="container">
        <h2>Safe Locker<br>🔒</h2>
        <p>คุณเข้าสู่ระบบสำเร็จแล้ว</p>
        <a href="admin_users.php">แก้ไขข้อมูลส่วนตัว</a>
        <a href="lockers.php">จัดการตู้ฝากของ</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <!-- ส่วนแสดงวันที่และเวลา -->
    <div id="datetime"></div>

    <script>
        function updateDateTime() {
            const now = new Date();
            
            // ตัวเลือกสำหรับวันที่ภาษาไทย
            const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateThai = now.toLocaleDateString('th-TH', optionsDate);
            
            // เวลา
            const time = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            document.getElementById('datetime').innerHTML = `${dateThai}<br>${time}`;
        }

        // อัปเดตทันทีและทุก 1 วินาที
        updateDateTime();
        setInterval(updateDateTime, 1000);
 
 </script>
 
</body>
</html>