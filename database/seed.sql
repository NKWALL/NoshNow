-- Anonymized demonstration data for the portfolio repository.
-- Demo password for all three accounts: Demo123!

USE `noshnow`;
START TRANSACTION;

INSERT INTO `users` (`user_id`, `password`, `username`, `user_type`) VALUES
  (1, '$2y$10$w0U5TpppKkS3cQtw5lvNS.J/hs7H4M6LA7MQPDXlgkteJu6ldnACy', 'demo_customer', 'Customer'),
  (2, '$2y$10$w0U5TpppKkS3cQtw5lvNS.J/hs7H4M6LA7MQPDXlgkteJu6ldnACy', 'demo_delivery', 'DeliveryPerson'),
  (3, '$2y$10$w0U5TpppKkS3cQtw5lvNS.J/hs7H4M6LA7MQPDXlgkteJu6ldnACy', 'demo_restaurant', 'RestaurantOwner');

INSERT INTO `customer` VALUES
  (1, 1800, '高雄市鼓山區蓮海路70號', '0900000001');
INSERT INTO `deliveryperson` VALUES
  (2, 4.80, 'Motorcycle');
INSERT INTO `restaurantowner` VALUES (3);

INSERT INTO `email` VALUES
  (1, 'demo_customer@example.com'),
  (2, 'demo_delivery@example.com'),
  (3, 'demo_restaurant@example.com');
INSERT INTO `cmnpaymethod` VALUES
  (1, 'Credit Card'),
  (1, 'LINE Pay');
INSERT INTO `allegren` VALUES (1, 'Peanuts');

INSERT INTO `restaurant`
  (`restaurant_id`, `restaurantAddress`, `restaurantName`, `operationTime`, `latitude`, `longitude`) VALUES
  (1, '高雄市鼓山區示範路1號', '八田源（示範資料）', '11:00-20:00', 22.651282, 120.286730),
  (2, '高雄市鼓山區示範路2號', '暖心食堂（示範資料）', '10:30-21:00', 22.653100, 120.289200);
INSERT INTO `owns` VALUES (1, 3), (2, 3);

INSERT INTO `menuitem`
  (`item_id`, `restaurant_id`, `price`, `foodName`, `calories`, `dessert`, `drink`, `main_dish`, `food_image`) VALUES
  (1, 1, 130.00, '雞腿肉串', 250, NULL, NULL, '雞腿肉串', NULL),
  (2, 1, 390.00, '牛筋咖哩套餐', 720, '今日甜點', '紅茶', '牛筋咖哩', NULL),
  (3, 1, 160.00, '唐揚雞定食', 680, NULL, '味噌湯', '唐揚雞', NULL),
  (4, 1, 95.00, '蔬菜咖哩', 430, NULL, NULL, '蔬菜咖哩', NULL),
  (5, 2, 120.00, '舒肥雞胸餐盒', 520, NULL, '無糖茶', '舒肥雞胸', NULL),
  (6, 2, 110.00, '烤鯖魚餐盒', 560, NULL, '無糖茶', '烤鯖魚', NULL),
  (7, 2, 85.00, '時蔬豆腐餐盒', 390, NULL, NULL, '時蔬豆腐', NULL),
  (8, 2, 65.00, '季節水果杯', 160, '季節水果', NULL, NULL, NULL);
INSERT INTO `has` VALUES
  (1, 1), (1, 2), (1, 3), (1, 4),
  (2, 5), (2, 6), (2, 7), (2, 8);

INSERT INTO `ingredients` VALUES
  (1, '雞腿肉'), (1, '青蔥'),
  (2, '牛筋'), (2, '咖哩'),
  (3, '雞肉'), (4, '季節蔬菜'),
  (5, '雞胸肉'), (6, '鯖魚'),
  (7, '豆腐'), (8, '季節水果');

INSERT INTO `category` (`category_id`, `category`) VALUES
  (1, '肉類'), (2, '咖哩'), (3, '套餐'), (4, '健康餐'), (5, '魚類'), (6, '素食'), (7, '甜點');
INSERT INTO `menuitem_category` VALUES
  (1, 1), (2, 1), (2, 2), (2, 3), (3, 1), (3, 3),
  (4, 2), (4, 6), (5, 1), (5, 4), (6, 4), (6, 5),
  (7, 4), (7, 6), (8, 7);

INSERT INTO `cart` (`cart_id`, `user_id`) VALUES
  (1, 1), (2, 1), (3, 1), (4, 1), (5, 1);
INSERT INTO `records` VALUES
  (5, 4, 1), (5, 8, 2);

INSERT INTO `order`
  (`order_id`, `weather`, `order_status`, `order_time`, `total_price`, `orderCustomer`,
   `orderPayMethod`, `total_calories`, `user_id`, `cart_id`, `order_ps`, `delivery_lat`, `delivery_lng`) VALUES
  (1, '晴', 'completed', '2026-08-01 12:00:00', 260.00, 'demo_customer', 'Credit Card', 500, 1, 1, '示範訂單', 22.656482, 120.295826),
  (2, '晴', 'ready_for_pickup', '2026-08-02 12:10:00', 390.00, 'demo_customer', 'LINE Pay', 720, 1, 2, NULL, 22.659000, 120.301000),
  (3, '多雲', 'pending', '2026-08-03 18:30:00', 255.00, 'demo_customer', 'Credit Card', 1110, 1, 3, '少鹽', 22.658200, 120.300100),
  (4, '晴', 'refund_requested', '2026-08-04 11:45:00', 230.00, 'demo_customer', 'Credit Card', 1080, 1, 4, NULL, 22.657400, 120.298800);

INSERT INTO `orderitem` VALUES
  (1, 1, 2),
  (2, 1, 1),
  (3, 1, 1), (3, 2, 1),
  (4, 1, 1), (4, 2, 1);
INSERT INTO `contains` VALUES
  (1, 1, 1),
  (2, 1, 2),
  (3, 1, 3), (3, 2, 4),
  (4, 1, 5), (4, 2, 6);

INSERT INTO `delivers`
  (`order_id`, `user_id`, `deliver_status`, `deliver_time`, `deliver_location`,
   `current_lat`, `current_lng`, `pickup_lat`, `pickup_lng`, `dropoff_lat`, `dropoff_lng`) VALUES
  (1, 2, 'completed', '2026-08-01 12:35:00', '高雄市鼓山區蓮海路70號',
   22.656100, 120.294900, 22.651282, 120.286730, 22.656482, 120.295826),
  (2, 2, 'ready_for_pickup', NULL, '高雄市鼓山區示範路3號',
   22.653000, 120.289000, 22.651282, 120.286730, 22.659000, 120.301000);

INSERT INTO `review` (`review_id`, `user_id`, `order_id`, `rating`, `comment`) VALUES
  (1, 1, 1, 4.50, '匿名示範評論：餐點準時送達。');
INSERT INTO `refundapplication`
  (`refund_id`, `refund_price`, `refundStatus`, `reason`, `user_id`, `order_id`) VALUES
  (1, 230.00, 'pending', '匿名示範退款原因', 1, 4);
INSERT INTO `requires` VALUES (1, 1, '2026-08-04 13:00:00');

INSERT INTO `weighted_recommendation` (`user_id`, `item_id`, `score`) VALUES
  (1, 2, 8.50), (1, 4, 6.20), (1, 3, 5.90), (1, 5, 5.20), (1, 7, 4.80);
INSERT INTO `recommendation` (`user_id`, `item_id`, `score`) VALUES
  (1, 6, 4.80), (1, 3, 4.60), (1, 7, 4.40), (1, 8, 4.20), (1, 4, 4.00);

COMMIT;
