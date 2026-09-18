<?php
$sql = "
SELECT r.review_id, r.rating, r.comment, o.order_time
FROM   review r
JOIN   `order` o ON o.order_id = r.order_id
WHERE  EXISTS (
    SELECT 1 FROM contains c
    JOIN menuitem m ON m.item_id = c.item_id
    WHERE c.order_id = o.order_id AND m.restaurant_id = ?
)
ORDER  BY o.order_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$restaurantId]);
?>

<?php foreach ($stmt as $rv): ?>
  <div class="review-card">
    <div class="card-body">
      <h5 class="card-title">⭐ <?= htmlspecialchars($rv['rating']) ?> / 5</h5>
      <h6 class="card-subtitle mb-2"><?= htmlspecialchars($rv['order_time']) ?></h6>
      <p class="card-text"><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>
    </div>
  </div>
<?php endforeach; ?>
