<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('DeliveryPerson', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$delivery_user_id = current_user_id();

$sql = "
SELECT DISTINCT
    o.order_id,
    r.restaurant_id,
    d.current_lat,
    d.current_lng,
    d.dropoff_lat,
    d.dropoff_lng,
    r.latitude AS pickup_lat,
    r.longitude AS pickup_lng
FROM delivers d
JOIN `order` o ON d.order_id = o.order_id
JOIN orderitem oi ON o.order_id = oi.order_id
JOIN contains c ON oi.order_id = c.order_id AND oi.sqNo = c.sqNo
JOIN menuitem m ON c.item_id = m.item_id
JOIN has h ON m.item_id = h.item_id
JOIN restaurant r ON h.restaurant_id = r.restaurant_id
WHERE
    d.user_id = ?
    AND o.order_status IN ('assigned_to_deliveryman', 'ready_for_pickup', 'delivering')
    AND d.current_lat IS NOT NULL AND d.current_lat != ''
    AND d.current_lng IS NOT NULL AND d.current_lng != ''
    AND d.dropoff_lat IS NOT NULL AND d.dropoff_lat != ''
    AND d.dropoff_lng IS NOT NULL AND d.dropoff_lng != ''
    AND r.latitude IS NOT NULL AND r.latitude != ''
    AND r.longitude IS NOT NULL AND r.longitude != ''
ORDER BY o.order_id, r.restaurant_id;
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$delivery_user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orders_json = json_encode($orders, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Route</title>
    <style>
        #map { height: 90vh; width: 100%; }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
            <h2 class="mb-4 text-center">
                <div class="bg-dark p-3 shadow-sm rounded">
                    <a class="navbar-brand fw-bold text-white" style="text-shadow: 0 0 5px #ffd700; color: orange;">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 最佳路徑導航
                    </a>
                </div>
            </h2>
    <?php if (google_maps_api_key() === ''): ?>
        <p>請先設定 Google Maps API 金鑰，才能使用路線規劃。</p>
    <?php else: ?>
        <div id="map"></div>
    <?php endif; ?>

    <script>
    const orders = <?= $orders_json ?>;

    let map;
    let directionsService;
    let directionsRenderer;

    let markers = [];
    let infoWindows = [];
    let currentPositionMarker = null;
    let watchId = null;

    function clearMarkers() {
        markers.forEach(marker => marker.setMap(null));
        markers = [];
        infoWindows = [];
    }

    function drawRouteWithCurrentPosition(position) {
        if (orders.length === 0) return;

        // 同一餐廳可能出現在多筆訂單中；每個取餐點只加入一次。
        const waypoints = [];
        const pickupStops = new Map();
        const dropoffStops = new Map();
        orders.forEach(order => {
            const restaurantId = String(order.restaurant_id);
            const orderId = String(order.order_id);
            if (!pickupStops.has(restaurantId)) {
                pickupStops.set(restaurantId, {
                    location: { lat: parseFloat(order.pickup_lat), lng: parseFloat(order.pickup_lng) },
                    type: '餐廳取餐點',
                    orderIds: []
                });
            }
            pickupStops.get(restaurantId).orderIds.push(orderId);
            if (!dropoffStops.has(orderId)) {
                dropoffStops.set(orderId, {
                    location: { lat: parseFloat(order.dropoff_lat), lng: parseFloat(order.dropoff_lng) },
                    type: '客戶送餐地',
                    orderIds: [orderId]
                });
            }
        });
        waypoints.push(...pickupStops.values(), ...dropoffStops.values());

        const start = {
            lat: position.lat,
            lng: position.lng
        };
        const destinationStop = waypoints[waypoints.length - 1];
        const intermediateStops = waypoints.slice(0, -1);

        directionsService.route({
            origin: start,
            destination: destinationStop.location,
            waypoints: intermediateStops.map(wp => ({ location: wp.location, stopover: true })),
            optimizeWaypoints: true,
            travelMode: google.maps.TravelMode.DRIVING
        }, (response, status) => {
            if (status === "OK") {
                directionsRenderer.setDirections(response);

                // 更新外送員標記位置
                if (!currentPositionMarker) {
                    currentPositionMarker = new google.maps.Marker({
                        position: start,
                        map: map,
                        icon: {
                            url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
                        },
                        title: "外送員目前位置"
                    });
                } else {
                    currentPositionMarker.setPosition(start);
                }

                // 清除舊標記(除外送員標記外)
                clearMarkers();

                // waypoint_order 只包含中途停靠點，不包含終點。
                const route = response.routes[0];
                const orderedStops = route.waypoint_order.map(index => intermediateStops[index]);
                orderedStops.push(destinationStop);
                orderedStops.forEach((wp, i) => {
                    const marker = new google.maps.Marker({
                        position: wp.location,
                        map: map,
                        label: (i + 1).toString()
                    });
                    const infoWindow = new google.maps.InfoWindow({
                        content: `<div>訂單ID: ${wp.orderIds.join(', ')}<br>點類型: ${wp.type}</div>`
                    });
                    marker.addListener('click', () => {
                        infoWindows.forEach(iw => iw.close());
                        infoWindow.open(map, marker);
                    });
                    markers.push(marker);
                    infoWindows.push(infoWindow);
                });
            } else {
                alert("路線規劃失敗：" + status);
            }
        });
    }

    function initMap() {
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            preserveViewport: true
        });


        map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: 25.0330, lng: 121.5654 },
            zoom: 13
        });
        directionsRenderer.setMap(map);

        if (orders.length === 0) {
            alert("你目前沒有進行中的訂單");
            return;
        }

        // 用第一筆訂單的 current_lat, current_lng 做初始起點
        const initialPos = {
            lat: parseFloat(orders[0].current_lat),
            lng: parseFloat(orders[0].current_lng)
        };

        drawRouteWithCurrentPosition(initialPos);

        // 啟動GPS動態監聽
        if (navigator.geolocation) {
            watchId = navigator.geolocation.watchPosition(pos => {
                const newPos = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude
                };
                drawRouteWithCurrentPosition(newPos);
                map.panTo(newPos);
            }, err => {
                console.warn('取得定位失敗:', err);
            }, {
                enableHighAccuracy: true,
                maximumAge: 5000,
                timeout: 10000
            });
        } else {
            alert("你的瀏覽器不支援定位功能");
        }
    }
    </script>

    <?php if (google_maps_api_key() !== ''): ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode(google_maps_api_key()) ?>&callback=initMap" async defer></script>
    <?php endif; ?>
</body>
</html>
