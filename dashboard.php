<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$where = "es.user_id = ? AND es.folder = ?";
$params = [$user_id, $folder];

if ($search) {
    $where .= " AND (e.subject LIKE ? OR e.body LIKE ? OR u2.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter == 'unread') {
    $where .= " AND es.is_read = FALSE";
} elseif ($filter == 'starred') {
    $where .= " AND e.is_starred = TRUE";
}

$stmt = $pdo->prepare("
    SELECT e.*, es.is_read, u2.username as sender_name
    FROM emails e
    JOIN email_status es ON e.id = es.email_id
    JOIN users u ON e.recipient_id = u.id
    JOIN users u2 ON e.sender_id = u2.id
    WHERE $where
    ORDER BY e.created_at DESC
");
$stmt->execute($params);
$emails = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Dashboard</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            background: #f1f3f4;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #202124;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 4px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #e8f0fe;
            color: #1a73e8;
        }
        .main {
            flex: 1;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar {
            width: 50%;
            padding: 10px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
        }
        .filter {
            padding: 10px;
            border: 1px solid #dadce0;
            border-radius: 4px;
        }
        .email-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-item {
            padding: 15px;
            border-bottom: 1px solid #dadce0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .email-item.unread {
            font-weight: bold;
            background: #f7faff;
        }
        .email-item a {
            color: #202124;
            text-decoration: none;
        }
        .compose-btn {
            background: #1a73e8;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .compose-btn:hover {
            background: #1557b0;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                box-shadow: none;
            }
            .search-bar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <button class="compose-btn" onclick="window.location.href='compose.php'">Compose</button>
            <a href="?folder=inbox" class="<?php echo $folder == 'inbox' ? 'active' : ''; ?>">Inbox</a>
            <a href="?folder=sent" class="<?php echo $folder == 'sent' ? 'active' : ''; ?>">Sent</a>
            <a href="?folder=drafts" class="<?php echo $folder == 'drafts' ? 'active' : ''; ?>">Drafts</a>
            <a href="?folder=trash" class="<?php echo $folder == 'trash' ? 'active' : ''; ?>">Trash</a>
            <a href="javascript:window.location.href='logout.php'">Logout</a>
        </div>
        <div class="main">
            <div class="header">
                <input type="text" class="search-bar" placeholder="Search emails" onkeyup="if(event.keyCode==13) window.location.href='?folder=<?php echo $folder; ?>&search='+this.value">
                <select class="filter" onchange="window.location.href='?folder=<?php echo $folder; ?>&filter='+this.value">
                    <option value="">All</option>
                    <option value="unread">Unread</option>
                    <option value="starred">Starred</option>
                </select>
            </div>
            <div class="email-list">
                <?php foreach ($emails as $email): ?>
                    <div class="email-item <?php echo $email['is_read'] ? '' : 'unread'; ?>">
                        <div>
                            <a href="view_email.php?id=<?php echo $email['id']; ?>">
                                <strong><?php echo htmlspecialchars($email['sender_name']); ?></strong> - 
                                <?php echo htmlspecialchars($email['subject']); ?>
                            </a>
                        </div>
                        <div><?php echo date('M d, Y', strtotime($email['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
