<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$email_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT e.*, es.is_read, es.folder, u2.username as sender_name
    FROM emails e
    JOIN email_status es ON e.id = es.email_id
    JOIN users u2 ON e.sender_id = u2.id
    WHERE e.id = ? AND es.user_id = ?
");
$stmt->execute([$email_id, $user_id]);
$email = $stmt->fetch();

if (!$email) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

// Mark as read
if (!$email['is_read']) {
    $stmt = $pdo->prepare("UPDATE email_status SET is_read = TRUE WHERE email_id = ? AND user_id = ?");
    $stmt->execute([$email_id, $user_id]);
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['star'])) {
        $stmt = $pdo->prepare("UPDATE emails SET is_starred = NOT is_starred WHERE id = ?");
        $stmt->execute([$email_id]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("UPDATE email_status SET folder = 'trash' WHERE email_id = ? AND user_id = ?");
        $stmt->execute([$email_id, $user_id]);
    }
    echo "<script>window.location.href='dashboard.php?folder=" . $email['folder'] . "';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - View Email</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f1f3f4;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            background: white;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .email-header {
            border-bottom: 1px solid #dadce0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .email-header h2 {
            margin: 0;
            color: #202124;
        }
        .email-meta {
            color: #5f6368;
            font-size: 14px;
        }
        .email-body {
            margin-top: 20px;
            font-size: 16px;
            color: #202124;
        }
        .actions {
            margin-top: 20px;
        }
        .actions button {
            padding: 10px 20px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .actions button:hover {
            background: #1557b0;
        }
        @media (max-width: 600px) {
            .email-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2><?php echo htmlspecialchars($email['subject']); ?></h2>
            <div class="email-meta">
                From: <?php echo htmlspecialchars($email['sender_name']); ?> <br>
                Date: <?php echo date('M d, Y, h:i A', strtotime($email['created_at'])); ?>
            </div>
        </div>
        <div class="email-body">
            <?php echo nl2br(htmlspecialchars($email['body'])); ?>
        </div>
        <div class="actions">
            <form method="POST" style="display:inline;">
                <button type="submit" name="star"><?php echo $email['is_starred'] ? 'Unstar' : 'Star'; ?></button>
                <button type="submit" name="delete">Move to Trash</button>
                <button type="button" onclick="window.location.href='dashboard.php?folder=<?php echo $email['folder']; ?>'">Back</button>
            </form>
        </div>
    </div>
</body>
</html>
