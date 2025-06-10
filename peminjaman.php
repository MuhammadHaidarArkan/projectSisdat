<?php
session_start();

// Only allow logged-in users with role 'user'
if (!isset($_SESSION['logged_in'], $_SESSION['role']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit;
}

// Room facilities mock data keyed by room id (in real app, come from db)
$room_facilities_data = [
    1 => ['Projector', 'Sound System', 'Air Conditioning', 'Seating for 200'],
    2 => ['Whiteboard', 'Wi-Fi', 'Projector', 'Seating for 150'],
    3 => ['Conference Table', 'Video Conferencing', 'Private Access'],
    4 => ['Stage', 'Lighting System', 'Large Screen', 'Seating for 300'],
];

// Get room id from GET params
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
if (!$room_id || !isset($room_facilities_data[$room_id])) {
    header('Location: userDashboard.php');
    exit;
}

$selected_room_facilities = $room_facilities_data[$room_id];
$selected_room_name = "Gedung Serba Guna A";
switch ($room_id) {
    case 1: $selected_room_name = "Gedung Serba Guna A"; break;
    case 2: $selected_room_name = "Gedung Serba Guna B"; break;
    case 3: $selected_room_name = "Ruang Meeting VIP"; break;
    case 4: $selected_room_name = "Auditorium"; break;
}

$error = '';
$success = '';

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_start = trim($_POST['date_start'] ?? '');
    $date_end = trim($_POST['date_end'] ?? '');
    $event_name = trim($_POST['event_name'] ?? '');
    $uploaded_file = $_FILES['letter_file'] ?? null;

    if (!$date_start || !$date_end || !$event_name || !$uploaded_file || $uploaded_file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please fill all fields and upload the letter file.';
    } elseif (strtotime($date_start) > strtotime($date_end)) {
        $error = 'Start date must be before or equal to end date.';
    } else {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = basename($uploaded_file['name']);
        $safe_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        $target_path = $upload_dir . $safe_filename;

        if (!move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
            $error = 'Failed to upload the letter file.';
        } else {
            $request_id = time() + rand(1000, 9999);
            if (!isset($_SESSION['booking_requests'])) {
                $_SESSION['booking_requests'] = [];
            }

            $_SESSION['booking_requests'][] = [
                'id' => $request_id,
                'username' => $_SESSION['username'],
                'room_id' => $room_id,
                'room_name' => $selected_room_name,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'event_name' => $event_name,
                'letter_file' => $safe_filename,
                'status' => 'pending',
            ];

            $success = 'Booking request submitted successfully!';
        }
    }
}

$logged_username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Book <?php echo htmlspecialchars($selected_room_name); ?> - Room Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            color: #6b7280;
            padding-top: 72px;
            min-height: 100vh;
            line-height: 1.6;
        }
        header {
            position: fixed;
            top: 0;
            width: 100%;
            height: 72px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            user-select: none;
            z-index: 1000;
        }
        .logo {
            font-weight: 800;
            font-size: 1.75rem;
            color: #2563eb;
            letter-spacing: 0.05em;
        }
        main {
            max-width: 600px;
            margin: 4rem auto 3rem;
            background: #fff;
            border-radius: 0.75rem;
            padding: 2.5rem 3rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
            user-select: none;
        }
        h1 {
            font-weight: 700;
            font-size: 2.75rem;
            color: #111827;
            margin-bottom: 1.5rem;
        }
        ul.facilities-list {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 2rem;
            font-size: 1rem;
            color: #374151;
        }
        label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            color: #111827;
        }
        input[type="date"], input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1.5px solid #d1d5db;
            border-radius: 0.75rem;
            background-color: #f9fafb;
            font-size: 1rem;
            margin-bottom: 1.25rem;
        }
        input[type="date"]:focus, input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.3);
        }
        button.btn-primary {
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1.125rem;
            padding: 0.8rem;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        button.btn-primary:hover {
            background-color: #1d4ed8;
        }
        .alert {
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.125rem;
        }
        a.back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">RoomBooking</div>
    <nav>
        <a href="userDashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </nav>
</header>

<main>
    <h1>Book "<?php echo htmlspecialchars($selected_room_name); ?>"</h1>
    <h2>Facilities</h2>
    <ul class="facilities-list">
        <?php foreach ($selected_room_facilities as $facility): ?>
            <li><?php echo htmlspecialchars($facility); ?></li>
        <?php endforeach; ?>
    </ul>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
        <label for="date_start">Start Date</label>
        <input type="date" id="date_start" name="date_start" required />

        <label for="date_end">End Date</label>
        <input type="date" id="date_end" name="date_end" required />

        <label for="event_name">Event Name</label>
        <input type="text" id="event_name" name="event_name" placeholder="What event will you hold?" required />

        <label for="letter_file">Upload Letter</label>
        <input type="file" id="letter_file" name="letter_file" accept=".pdf,.doc,.docx,.jpg,.png" required />

        <button type="submit" class="btn btn-primary">Submit Booking Request</button>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
