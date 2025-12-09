<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

 $user_id = $_SESSION['user_id'];
 $post_id = $_GET['post_id'] ?? 0;

// Ambil Data User login
 $stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $current_user = $stmt->get_result()->fetch_assoc();

// Handle Delete Comment
if(isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    
    // Cek apakah komentar milik user yang login
    $check = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $check->bind_param("i", $comment_id);
    $check->execute();
    $comment_owner = $check->get_result()->fetch_assoc();
    
    if($comment_owner && $comment_owner['user_id'] == $user_id) {
        // Hapus likes dulu
        $stmt_del = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ?");
        $stmt_del->bind_param("i", $comment_id);
        $stmt_del->execute();
        
        // Hapus replies (child comments)
        $get_replies = $conn->prepare("SELECT id FROM comments WHERE parent_id = ?");
        $get_replies->bind_param("i", $comment_id);
        $get_replies->execute();
        $replies = $get_replies->get_result();
        
        while($reply = $replies->fetch_assoc()) {
            $stmt_del_reply_likes = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ?");
            $stmt_del_reply_likes->bind_param("i", $reply['id']);
            $stmt_del_reply_likes->execute();
        }
        
        $stmt_del_replies = $conn->prepare("DELETE FROM comments WHERE parent_id = ?");
        $stmt_del_replies->bind_param("i", $comment_id);
        $stmt_del_replies->execute();
        
        // Hapus komentar utama
        $stmt_del_comment = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt_del_comment->bind_param("i", $comment_id);
        $stmt_del_comment->execute();
    }
    
    header("Location: comments.php?post_id=$post_id");
    exit;
}

// Handle Like/Unlike Comment via AJAX
if(isset($_POST['action']) && $_POST['action'] == 'toggle_like') {
    $comment_id = $_POST['comment_id'];
    
    $check = $conn->prepare("SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $check->bind_param("ii", $comment_id, $user_id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    
    if($exists) {
        $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $liked = false;
    } else {
        $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $liked = true;
    }
    
    $count = $conn->prepare("SELECT COUNT(*) as total FROM comment_likes WHERE comment_id = ?");
    $count->bind_param("i", $comment_id);
    $count->execute();
    $like_count = $count->get_result()->fetch_assoc()['total'];
    
    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $like_count]);
    exit;
}

// Handle Comment Submission
if(isset($_POST['submit_comment'])) {
    $comment_text = mysqli_real_escape_string($conn, $_POST['comment_text']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    
    if($parent_id) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $post_id, $user_id, $comment_text, $parent_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_text);
    }
    $stmt->execute();
    $comment_id = $conn->insert_id;
    
    // Logika Mentions
    preg_match_all('/@(\w+)/', $comment_text, $mentions);
    if(!empty($mentions[1])) {
        foreach(array_unique($mentions[1]) as $mentioned) {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $mentioned);
            $check->execute();
            $res = $check->get_result();
            if($res->num_rows > 0) {
                $mentioned_user = $res->fetch_assoc();
                if($mentioned_user['id'] != $user_id) {
                    $notif = $conn->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, message) VALUES (?, ?, 'mention', ?, ?, ?)");
                    $msg = $current_user['username'] . " menyebut Anda dalam komentar";
                    $notif->bind_param("iiiis", $mentioned_user['id'], $user_id, $post_id, $comment_id, $msg);
                    $notif->execute();
                }
            }
        }
    }
    header("Location: comments.php?post_id=$post_id");
    exit;
}

// Ambil Data Post
 $stmt = $conn->prepare("SELECT posts.*, users.username, users.title, users.level, users.profile_pic FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
 $stmt->bind_param("i", $post_id);
 $stmt->execute();
 $post = $stmt->get_result()->fetch_assoc();

if(!$post) { echo "Post tidak ditemukan."; exit; }

// Ambil Total Komentar (parent + replies)
 $total_comments_stmt = $conn->prepare("SELECT COUNT(*) as total FROM comments WHERE post_id = ?");
 $total_comments_stmt->bind_param("i", $post_id);
 $total_comments_stmt->execute();
 $total_comments = $total_comments_stmt->get_result()->fetch_assoc()['total'];

// Ambil Komentar Parent
 $stmt = $conn->prepare("
    SELECT c.*, u.username, u.title, u.level, u.profile_pic as comment_profile_pic,
    (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as like_count,
    (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked,
    (SELECT COUNT(*) FROM comments WHERE parent_id = c.id) as reply_count
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at DESC
");
 $stmt->bind_param("ii", $user_id, $post_id);
 $stmt->execute();
 $comments = $stmt->get_result();

 $all_users = $conn->query("SELECT id, username FROM users ORDER BY username");
 $users_list = [];
while($u = $all_users->fetch_assoc()) { $users_list[] = $u; }

function getReplies($conn, $parent_id, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.*, u.username, u.title, u.level, u.profile_pic as reply_profile_pic,
        (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as like_count,
        (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.parent_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->bind_param("ii", $user_id, $parent_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thread Discussion - Knowledge Battle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0f1e 0%, #1a1a2e 50%, #16213e 100%);
        }
        .glass { 
            background: rgba(255,255,255,0.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .gradient-text { 
            background: linear-gradient(to right, #ef4444, #dc2626); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
        .mention { 
            color: #3b82f6; 
            font-weight: 600; 
            cursor: pointer; 
        }
        .mention:hover { 
            text-decoration: underline; 
        }
        .reply-form { 
            display: none; 
            margin-top: 1rem; 
        }
        .reply-indent { 
            margin-left: 3rem; 
            border-left: 2px solid rgba(239, 68, 68, 0.3); 
            padding-left: 1rem; 
        }
        .like-btn { 
            transition: all 0.3s ease; 
            cursor: pointer; 
            position: relative;
            overflow: hidden;
        }
        .like-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        .like-btn.liked::before {
            width: 100px;
            height: 100px;
        }
        .like-btn.liked { 
            color: #ef4444; 
            animation: pulse 0.5s;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .like-btn:hover { 
            transform: scale(1.1); 
        }
        .show-replies-btn { 
            color: #60a5fa; 
            font-size: 0.875rem; 
            cursor: pointer; 
            transition: all 0.2s;
        }
        .show-replies-btn:hover { 
            text-decoration: underline; 
        }
        .replies-container { 
            display: none; 
        }
        .replies-container.show { 
            display: block; 
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .delete-btn { 
            opacity: 0; 
            transition: opacity 0.2s; 
        }
        .comment-card:hover .delete-btn { 
            opacity: 1; 
        }
        .comment-card {
            transition: all 0.3s ease;
        }
        .comment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(239, 68, 68, 0.1);
        }
        .floating-heart {
            position: absolute;
            color: #ef4444;
            animation: float 1s ease-in-out forwards;
            pointer-events: none;
        }
        @keyframes float {
            0% { transform: translateY(0) scale(1); opacity: 1; }
            100% { transform: translateY(-50px) scale(1.5); opacity: 0; }
        }
        .typing-indicator {
            display: inline-flex;
            align-items: center;
        }
        .typing-indicator span {
            height: 8px;
            width: 8px;
            background-color: #60a5fa;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out both;
        }
        .typing-indicator span:nth-child(1) {
            animation-delay: -0.32s;
        }
        .typing-indicator span:nth-child(2) {
            animation-delay: -0.16s;
        }
        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(34, 197, 94, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            display: none;
            animation: slideIn 0.3s ease-out;
            z-index: 1000;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body class="text-white min-h-screen">

<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10 shadow-xl">
    <nav class="max-w-7xl mx-auto flex justify-between items-center py-4 px-8">
        <a href="halaman_utama.php" class="flex items-center gap-2 glass px-4 py-2 rounded-full hover:bg-red-600/20 transition-all hover:scale-105">
            <i class="bi bi-arrow-left text-lg"></i><span class="font-medium">Kembali</span>
        </a>
        <div class="text-2xl font-bold">
            <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
        </div>
    </nav>
</header>

<main class="pt-28 pb-12 max-w-5xl mx-auto px-4">
    <!-- Post Card -->
    <div class="glass p-8 rounded-3xl mb-8 shadow-2xl hover:shadow-red-500/10 transition-all">
        <div class="flex items-start gap-4 mb-6">
            <?php if(!empty($post['profile_pic']) && file_exists("../uploads/profile/".$post['profile_pic'])): ?>
                <img src="../uploads/profile/<?= htmlspecialchars($post['profile_pic']) ?>" 
                     class="w-16 h-16 rounded-full object-cover border-2 border-red-500/50 flex-shrink-0 shadow-lg">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-2xl font-bold flex-shrink-0 border-2 border-white/10 shadow-lg">
                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>

            <div class="flex-1">
                <div class="font-bold text-xl mb-2"><?=htmlspecialchars($post['username'])?></div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="px-3 py-1 bg-purple-600/30 rounded-full border border-purple-500/30"><?=htmlspecialchars($post['title'])?></span>
                    <span class="px-3 py-1 bg-yellow-500/30 rounded-full border border-yellow-500/30">
                        </i> Lvl <?= $post['level'] ?>
                    </span>
                    <span class="text-gray-400">
                        <i class="bi bi-clock"></i> <?=date('d M Y, H:i', strtotime($post['created_at']))?>
                    </span>
                </div>
            </div>
        </div>

        <?php if($post['image']): ?>
            <img src="../uploads/<?=htmlspecialchars($post['image'])?>" class="w-full rounded-2xl mb-6 shadow-lg">
        <?php endif; ?>

        <p class="text-gray-200 text-lg leading-relaxed"><?=htmlspecialchars($post['caption'])?></p>
    </div>

    <!-- Form Tambah Komentar -->
    <div class="glass p-6 rounded-2xl mb-8 shadow-xl">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
            <i class="bi bi-chat-left-text text-red-500 text-2xl"></i>
            <span>Tambah Komentar</span>
        </h3>
        <form action="" method="post" id="comment-form">
            <div class="relative mb-4">
                <textarea name="comment_text" id="comment-input" placeholder="Tulis komentar Anda... (gunakan @ untuk mention)" 
                    class="w-full border border-white/10 p-4 rounded-xl bg-gray-900/50 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 transition min-h-[120px] resize-none" required></textarea>
                <div id="mention-dropdown" class="hidden absolute z-10 glass rounded-xl p-2 w-64 mt-1 overflow-y-auto max-h-48 shadow-xl"></div>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400">
                    <i class="bi bi-info-circle"></i> Tekan @ untuk mention pengguna
                </span>
                <button type="submit" name="submit_comment" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 px-6 py-3 rounded-xl font-semibold flex items-center gap-2 transition-all hover:scale-105 shadow-lg">
                    <i class="bi bi-send-fill"></i>Kirim Komentar
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Komentar -->
    <div class="space-y-4">
        <h3 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <div class="bg-red-600/20 p-3 rounded-xl border border-red-500/30">
                <i class="bi bi-chat-dots-fill text-red-500 text-2xl"></i>
            </div>
            <span><?= $total_comments ?> Komentar</span>
        </h3>

        <?php if($comments->num_rows == 0): ?>
            <div class="glass p-8 rounded-2xl text-center">
                <i class="bi bi-chat-left-text text-6xl text-gray-600 mb-4"></i>
                <p class="text-gray-400 text-lg">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
            </div>
        <?php endif; ?>

        <?php while($comment = $comments->fetch_assoc()): 
            $content = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', htmlspecialchars($comment['comment']));
            $replies = getReplies($conn, $comment['id'], $user_id);
            $reply_count = $replies->num_rows;
        ?>
            <div class="glass p-6 rounded-2xl shadow-xl hover:shadow-red-500/5 transition-all comment-card" id="comment-<?= $comment['id'] ?>">
                <div class="flex items-start gap-4">
                    <?php if(!empty($comment['comment_profile_pic']) && file_exists("../uploads/profile/".$comment['comment_profile_pic'])): ?>
                        <img src="../uploads/profile/<?= $comment['comment_profile_pic'] ?>" class="w-12 h-12 rounded-full object-cover border-2 border-blue-500/30 shadow-md flex-shrink-0">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center font-bold shadow-md flex-shrink-0">
                            <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-lg"><?=htmlspecialchars($comment['username'])?></span>
                                <?php if($comment['title']): ?>
                                    <span class="px-2 py-0.5 bg-purple-600/20 rounded-full text-xs border border-purple-500/30"><?=htmlspecialchars($comment['title'])?></span>
                                <?php endif; ?>
                                <span class="text-gray-400 text-xs flex items-center gap-1">
                                    <i class="bi bi-clock"></i> <?=date('d M Y • H:i', strtotime($comment['created_at']))?>
                                </span>
                            </div>
                            
                            <?php if($comment['user_id'] == $user_id): ?>
                                <a href="?post_id=<?= $post_id ?>&delete_comment=<?= $comment['id'] ?>" 
                                   onclick="return confirm('Yakin ingin menghapus komentar ini beserta semua balasannya?')"
                                   class="delete-btn text-red-400 hover:text-red-300 transition p-2 rounded-lg hover:bg-red-600/20">
                                    <i class="bi bi-trash text-lg"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-gray-200 mb-4 leading-relaxed break-words"><?=$content?></p>
                        
                        <div class="flex gap-6 text-sm items-center">
                            <button onclick="toggleLike(<?= $comment['id'] ?>)" 
                                class="like-btn flex items-center gap-2 hover:text-red-400 <?= $comment['user_liked'] ? 'liked' : 'text-gray-400' ?>" 
                                id="like-btn-<?= $comment['id'] ?>">
                                <i class="bi <?= $comment['user_liked'] ? 'bi-heart-fill' : 'bi-heart' ?> text-lg" id="like-icon-<?= $comment['id'] ?>"></i>
                                <span id="like-count-<?= $comment['id'] ?>"><?= $comment['like_count'] ?></span>
                            </button>
                            
                            <button onclick="toggleReply(<?= $comment['id'] ?>)" class="flex items-center gap-2 text-gray-400 hover:text-blue-400 transition">
                                <i class="bi bi-reply-fill text-lg"></i> Balas
                            </button>

                            <?php if($reply_count > 0): ?>
                                <button onclick="toggleReplies(<?= $comment['id'] ?>)" class="show-replies-btn flex items-center gap-2">
                                    <i class="bi bi-chevron-down" id="replies-icon-<?= $comment['id'] ?>"></i>
                                    <span id="replies-text-<?= $comment['id'] ?>"><?= $reply_count ?> Balasan</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Form Balas -->
                        <div id="reply-form-<?= $comment['id'] ?>" class="reply-form">
                            <form action="" method="post">
                                <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                <div class="flex gap-3 items-start">
                                    <?php if(!empty($current_user['profile_pic']) && file_exists("../uploads/profile/".$current_user['profile_pic'])): ?>
                                        <img src="../uploads/profile/<?= htmlspecialchars($current_user['profile_pic']) ?>" class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-sm font-bold">
                                            <?= strtoupper(substr($current_user['username'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-1">
                                        <textarea name="comment_text" placeholder="Tulis balasan Anda..." class="w-full bg-gray-900/50 border border-white/10 rounded-xl p-3 text-sm mb-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition min-h-[80px] resize-none" required></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" name="submit_comment" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                                                <i class="bi bi-send-fill"></i> Kirim
                                            </button>
                                            <button type="button" onclick="toggleReply(<?= $comment['id'] ?>)" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Container Balasan -->
                        <?php if($reply_count > 0): ?>
                            <div id="replies-<?= $comment['id'] ?>" class="replies-container mt-4">
                                <?php while($reply = $replies->fetch_assoc()): 
                                    $reply_content = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', htmlspecialchars($reply['comment']));
                                ?>
                                    <div class="reply-indent mt-3 glass p-4 rounded-xl comment-card">
                                        <div class="flex items-start gap-3">
                                            <?php if(!empty($reply['reply_profile_pic']) && file_exists("../uploads/profile/".$reply['reply_profile_pic'])): ?>
                                                <img src="../uploads/profile/<?= $reply['reply_profile_pic'] ?>" class="w-10 h-10 rounded-full object-cover border border-blue-500/30">
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center text-sm font-bold">
                                                    <?= strtoupper(substr($reply['username'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-bold"><?=htmlspecialchars($reply['username'])?></span>
                                                        <?php if($reply['title']): ?>
                                                            <span class="px-2 py-0.5 bg-purple-600/20 rounded-full text-xs"><?=htmlspecialchars($reply['title'])?></span>
                                                        <?php endif; ?>
                                                        <span class="text-gray-400 text-xs">
                                                            <i class="bi bi-clock"></i> <?=date('d M Y • H:i', strtotime($reply['created_at']))?>
                                                        </span>
                                                    </div>
                                                    
                                                    <?php if($reply['user_id'] == $user_id): ?>
                                                        <a href="?post_id=<?= $post_id ?>&delete_comment=<?= $reply['id'] ?>" 
                                                           onclick="return confirm('Yakin ingin menghapus balasan ini?')"
                                                           class="delete-btn text-red-400 hover:text-red-300 transition p-2 rounded-lg hover:bg-red-600/20">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <p class="text-gray-300 text-sm mb-2 break-words"><?=$reply_content?></p>
                                                
                                                <div class="flex gap-4 text-xs">
                                                    <button onclick="toggleLike(<?= $reply['id'] ?>)" 
                                                        class="like-btn flex items-center gap-1 hover:text-red-400 <?= $reply['user_liked'] ? 'liked' : 'text-gray-400' ?>" 
                                                        id="like-btn-<?= $reply['id'] ?>">
                                                        <i class="bi <?= $reply['user_liked'] ? 'bi-heart-fill' : 'bi-heart' ?>" id="like-icon-<?= $reply['id'] ?>"></i>
                                                        <span id="like-count-<?= $reply['id'] ?>"><?= $reply['like_count'] ?></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<div id="notification" class="notification"></div>

<script>
    function toggleReply(id) {
        const form = document.getElementById('reply-form-' + id);
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
        if (form.style.display === 'block') {
            form.querySelector('textarea').focus();
        }
    }

    // Toggle Replies Display
    function toggleReplies(id) {
        const container = document.getElementById('replies-' + id);
        const icon = document.getElementById('replies-icon-' + id);
        
        container.classList.toggle('show');
        icon.classList.toggle('bi-chevron-down');
        icon.classList.toggle('bi-chevron-up');
    }

    // Toggle Like - Fixed version
    function toggleLike(commentId) {
        const formData = new FormData();
        formData.append('action', 'toggle_like');
        formData.append('comment_id', commentId);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const btn = document.getElementById('like-btn-' + commentId);
                const icon = document.getElementById('like-icon-' + commentId);
                const count = document.getElementById('like-count-' + commentId);
                
                if(data.liked) {
                    btn.classList.add('liked');
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    
                    const heart = document.createElement('i');
                    heart.className = 'bi bi-heart-fill floating-heart';
                    heart.style.left = '50%';
                    heart.style.top = '50%';
                    btn.appendChild(heart);
                    setTimeout(() => heart.remove(), 1000);
                } else {
                    btn.classList.remove('liked');
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                }
                
                count.textContent = data.count;
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Mention Autocomplete
    const users = <?= json_encode($users_list) ?>;
    const input = document.getElementById('comment-input');
    const dd = document.getElementById('mention-dropdown');

    input.addEventListener('input', () => {
        const val = input.value;
        const cursorPos = input.selectionStart;
        const textBeforeCursor = val.substring(0, cursorPos);
        const match = textBeforeCursor.match(/@(\w*)$/);
        
        if(match) {
            const query = match[1].toLowerCase();
            const filtered = users.filter(u => u.username.toLowerCase().startsWith(query));
            
            if(filtered.length > 0) {
                dd.innerHTML = filtered.map(u => 
                    `<div class="p-2 hover:bg-white/10 cursor-pointer rounded-lg transition flex items-center gap-2" onclick="addMention('${u.username}')">
                        <i class="bi bi-person-circle"></i> @${u.username}
                    </div>`
                ).join('');
                dd.classList.remove('hidden');
            } else {
                dd.classList.add('hidden');
            }
        } else {
            dd.classList.add('hidden');
        }
    });

    function addMention(name) {
        const val = input.value;
        const cursorPos = input.selectionStart;
        const textBeforeCursor = val.substring(0, cursorPos);
        const textAfterCursor = val.substring(cursorPos);
        
        const newTextBefore = textBeforeCursor.replace(/@\w*$/, '@' + name + ' ');
        input.value = newTextBefore + textAfterCursor;
        
        const newCursorPos = newTextBefore.length;
        input.setSelectionRange(newCursorPos, newCursorPos);
        
        dd.classList.add('hidden');
        input.focus();
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if(!input.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });

    // Show notification when comment is submitted
    document.getElementById('comment-form').addEventListener('submit', function() {
        showNotification('Komentar berhasil dikirim!');
    });

    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.style.display = 'block';
        
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
</script>
</body>
</html>