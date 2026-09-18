<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'DeliveryPerson') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../db_connection.php';

$delivery_person_id = $_SESSION['user_id'];

// 訂單評論是針對整筆訂單，不代表顧客對外送員的個別評分。
$sql = "
    SELECT r.review_id, r.order_id, r.rating, r.comment, o.order_time
    FROM Review r
    JOIN Delivers d ON r.order_id = d.order_id
    JOIN `order` o ON o.order_id = r.order_id
    WHERE d.user_id = :user_id
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $delivery_person_id);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>相關訂單評論</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="../assets/css/site.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">相關訂單評論</h2>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">以下評分是顧客對整筆訂單的評論，並非外送員個人評分。</h5>
                <?php if (count($reviews) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>訂單編號</th>
                                <th>評分</th>
                                <th>評論內容</th>
                                <th>評論時間</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= htmlspecialchars($review['order_id']) ?></td>
                                    <td><?= htmlspecialchars($review['rating']) ?></td>
                                    <td><?= htmlspecialchars($review['comment']) ?></td>
                                    <td><?= htmlspecialchars($review['order_time']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>目前沒有評論。</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 回主頁按鈕 -->
        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-secondary" style="margin-bottom: 20px">回主頁</a>
        </div>
    </div>
</body>
</html>
