<?php
session_start();
require 'db_connect.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];

// ดึงข้อมูลตู้ทั้งหมด
$sql = "SELECT l.locker_id, l.status, l.phone_owner, l.deposit_time, l.user_id AS locker_user_id,
        u.fullname, u.room_number
        FROM lockers l
        LEFT JOIN users u ON l.user_id = u.user_id
        ORDER BY l.locker_id ASC";
$stmt = $pdo->query($sql);
$lockers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะตู้ฝากของ - Safe Locker</title>
    <link rel="stylesheet" href="assets/styles_lockers.css">
    <style>
        .btn-action.disabled {
            background-color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
            text-decoration: none;
        }
        .search-box {
            margin: 20px 0;
            text-align: center;
        }
        .search-box input {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-box button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .no-my-locker {
            text-align: center;
            color: #666;
            font-size: 18px;
            margin: 40px 0;
            display: none; /* ซ่อนไว้ก่อน */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>สถานะตู้ฝากของทั้งหมด</h2>
        <p class="subtitle">ตู้สีเขียว = ว่าง | ตู้สีแดง = มีคนใช้งาน</p>

        <!-- ช่องค้นหาตู้ของตัวเอง -->
        <div class="search-box">
            <input type="text" id="searchMyLocker" placeholder="พิมพ์ ตู้ของฉัน แล้วกดค้นหา"ตู้ของฉัน\" แล้วกดค้นหา เพื่อดูตู้ที่กำลังใช้งานอยู่">
            <button onclick="searchMyLocker()">ค้นหาตู้ของฉัน</button>
        </div>

        <div class="no-my-locker" id="noMyLocker">
            <strong>คุณยังไม่มีตู้ที่กำลังใช้งานอยู่</strong><br>
            เลือกตู้สีเขียวเพื่อฝากของได้เลย
        </div>

        <div class="lockers-grid" id="lockersGrid">
            <?php foreach ($lockers as $locker): ?>
                <div class="locker-card <?php echo $locker['status'] ? 'occupied' : 'available'; ?>"
                     data-my-locker="<?php echo ($locker['status'] && $locker['locker_user_id'] == $current_user_id) ? 'yes' : 'no'; ?>">
                    <div class="locker-number">#<?php echo sprintf('%02d', $locker['locker_id']); ?></div>
                    <div class="locker-status">
                        <?php if ($locker['status']): ?>
                            <strong>ใช้งานอยู่</strong><br>
                            เบอร์: <?php echo htmlspecialchars($locker['phone_owner']); ?><br>
                            <?php if ($locker['fullname']): ?>
                                ชื่อ: <?php echo htmlspecialchars($locker['fullname']); ?><br>
                                ห้อง: <?php echo htmlspecialchars($locker['room_number']); ?><br>
                            <?php endif; ?>
                            ฝากเมื่อ: <?php echo date('d/m/Y H:i', strtotime($locker['deposit_time'])); ?>
                        <?php else: ?>
                            <strong>ว่าง</strong><br>
                            พร้อมใช้งาน
                        <?php endif; ?>
                    </div>

                    <?php 
                    if (!$locker['status']) { 
                    ?>
                        <a href="action_locker.php?id=<?php echo $locker['locker_id']; ?>&action=deposit" class="btn-action">
                            ฝากของ
                        </a>
                    <?php 
                    } elseif ($locker['locker_user_id'] == $current_user_id) { 
                    ?>
                        <a href="action_locker.php?id=<?php echo $locker['locker_id']; ?>&action=manage" class="btn-action">
                            จัดการตู้
                        </a>
                    <?php 
                    } else { 
                    ?>
                        <span class="btn-action disabled">
                            ไม่สามารถจัดการได้
                        </span>
                    <?php } ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="back-link">
            <a href="dashboard.php">← กลับหน้าหลัก</a>
        </div>
    </div>

    <script>
        function searchMyLocker() {
            const input = document.getElementById('searchMyLocker').value.trim().toLowerCase();
            
            // ตรวจสอบว่าผู้ใช้พิมพ์คำที่เกี่ยวข้องกับ "ตู้ของฉัน"
            if (input.includes('ตู้ของฉัน') || input.includes('ตู้ฉัน') || input.includes('my') || input.includes('ของฉัน')) {
                const grid = document.getElementById('lockersGrid');
                const cards = grid.getElementsByClassName('locker-card');
                const noMyLockerMsg = document.getElementById('noMyLocker');
                
                let hasMyLocker = false;

                for (let card of cards) {
                    if (card.getAttribute('data-my-locker') === 'yes') {
                        card.style.display = 'block';
                        hasMyLocker = true;
                    } else {
                        card.style.display = 'none';
                    }
                }

                // ถ้าไม่มีตู้ของตัวเอง แสดงข้อความแจ้ง
                noMyLockerMsg.style.display = hasMyLocker ? 'none' : 'block';
            } else {
                alert('กรุณาพิมพ์คำว่า "ตู้ของฉัน" เพื่อค้นหาตู้ที่คุณกำลังใช้งานอยู่');
            }
        }

        // กด Enter ในช่องค้นหาก็ค้นหาได้
        document.getElementById('searchMyLocker').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchMyLocker();
            }
        });
    </script>
</body>
</html>