<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_email = $_POST['recipient'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $is_draft = isset($_POST['save_draft']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$recipient_email]);
    $recipient = $stmt->fetch();

    if ($recipient) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO emails (sender_id, recipient_id, subject, body, is_draft) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $recipient['id'], $subject, $body, $is_draft]);
        $email_id = $pdo->lastInsertId();

        if ($is_draft) {
            $stmt = $pdo->prepare("INSERT INTO email_status (email_id, user_id, folder) VALUES (?, ?, 'drafts')");
            $stmt->execute([$email_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO email_status (email_id, user_id, folder) VALUES (?, ?, 'inbox')");
            $stmt->execute([$email_id, $recipient['id']]);
            $stmt = $pdo->prepare("INSERT INTO email_status (email_id, user_id, folder) VALUES (?, ?, 'sent')");
            $stmt->execute([$email_id, $user_id]);
        }
        $pdo->commit();
        echo "<script>window.location.href='dashboard.php';</script>";
    } else {
        $error = "Recipient not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Compose</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f1f3f4;
            margin: 0;
            padding: 20px;
        }
        .compose-container {
            background: white;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .compose-container h2 {
            margin: 0 0 20px;
            color: #202124;
        }
        .compose-container input, .compose-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
        }
        .compose-container textarea {
            height: 200px;
            resize: vertical;
        }
        .compose-container button {
            padding: 10px 20px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .compose-container button:hover {
            background: #1557b0;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            .compose-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="compose-container">
        <h2>New Message</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="recipient" placeholder="To" required>
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="body" placeholder="Message" required></textarea>
            <button type="submit">Send</button>
            <button type="submit" name="save_draft" value="1">Save Draft</button>
            <button type="button" onclick="window.location.href='dashboard.php'">Cancel</button>
        </form>
    </div>
</body>
</html>
