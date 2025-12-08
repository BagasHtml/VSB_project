<?php
session_start();
include '../service/db.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'] ?? 0;

// 2. Ambil Data User login
$stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

// 3. Handle Comment Submission
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

// 4. Ambil Data Post & Komentar
$stmt = $conn->prepare("SELECT posts.*, users.username, users.title, users.level, users.profile_pic FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post) { echo "Post tidak ditemukan."; exit; }

$stmt = $conn->prepare("
    SELECT c.*, u.username, u.title, u.level, u.profile_pic as comment_profile_pic,
    (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as like_count,
    (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked,
    (SELECT COUNT(*) FROM comments WHERE parent_id = c.id) as reply_count
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at ASC
");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$comments = $stmt->get_result();

$all_users = $conn->query("SELECT id, username FROM users ORDER BY username");
$users_list = [];
while($u = $all_users->fetch_assoc()) { $users_list[] = $u; }

function getReplies($conn, $parent_id, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.*, u.username, u.profile_pic as reply_profile_pic,
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
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .mention { color: #3b82f6; font-weight: 600; }
        .reply-form { display: none; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen">

<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
    <nav class="max-w-7xl mx-auto flex justify-between items-center py-4 px-8">
        <a href="halaman_utama.php" class="flex items-center gap-2 glass px-3 py-2 rounded-full hover:bg-red-600/20 transition">
            <i class="bi bi-arrow-left"></i><span>Kembali</span>
        </a>
        <div class="text-2xl font-bold">
            <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
        </div>
        <div class="w-10"></div>
    </nav>
</header>

<main class="pt-28 pb-12 max-w-5xl mx-auto px-4">
    <div class="glass p-8 rounded-3xl mb-8">
        <div class="flex items-center gap-4 mb-6">
            <?php if(!empty($post['profile_pic']) && file_exists("../uploads/profile/".$post['profile_pic'])): ?>
                <img src="../uploads/profile/<?= htmlspecialchars($post['profile_pic']) ?>" 
                     class="w-16 h-16 rounded-full object-cover border-2 border-red-500/50 flex-shrink-0">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-2xl font-bold flex-shrink-0 border-2 border-white/10">
                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>

            <div>
                <div class="font-bold text-xl"><?=htmlspecialchars($post['username'])?></div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="px-3 py-1 bg-purple-600/30 rounded-full"><?=htmlspecialchars($post['title'])?></span>
                    <span class="px-3 py-1 bg-yellow-500/30 rounded-full">Lvl <?= $post['level'] ?></span>
                    <span class="text-gray-400">• <?=date('d M Y, H:i', strtotime($post['created_at']))?></span>
                </div>
            </div>
        </div>

        <?php if($post['image']): ?>
            <img src="../uploads/<?=htmlspecialchars($post['image'])?>" class="w-full rounded-2xl mb-6">
        <?php endif; ?>

        <p class="text-gray-200 text-lg leading-relaxed mb-4"><?=htmlspecialchars($post['caption'])?></p>
    </div>

    <div class="glass p-6 rounded-2xl mb-8">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
            <i class="bi bi-chat-left-text text-red-500"></i>Tambah Komentar
        </h3>
        <form action="" method="post">
            <div class="relative mb-4">
                <textarea name="comment_text" id="comment-input" placeholder="Tulis komentar... (@ untuk mention)" 
                    class="w-full border border-white/10 p-4 rounded-xl bg-gray-900/50 focus:outline-none focus:border-red-500 transition min-h-[100px]" required></textarea>
                <div id="mention-dropdown" class="hidden absolute z-10 glass rounded-xl p-2 w-64 mt-1 overflow-y-auto max-h-40"></div>
            </div>
            <button type="submit" name="submit_comment" class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
                <i class="bi bi-send"></i>Kirim Komentar
            </button>
        </form>
    </div>

    <div class="space-y-4">
        <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="bi bi-chat-dots text-red-500"></i><?= $comments->num_rows ?> Komentar
        </h3>

        <?php while($comment = $comments->fetch_assoc()): 
            $content = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', htmlspecialchars($comment['comment']));
        ?>
            <div class="glass p-6 rounded-2xl">
                <div class="flex items-start gap-3">
                    <?php if(!empty($comment['comment_profile_pic']) && file_exists("../uploads/profile/".$comment['comment_profile_pic'])): ?>
                        <img src="../uploads/profile/<?= $comment['comment_profile_pic'] ?>" class="w-12 h-12 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                            <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold"><?=htmlspecialchars($comment['username'])?></span>
                            <span class="text-gray-400 text-xs">• <?=date('H:i', strtotime($comment['created_at']))?></span>
                        </div>
                        <p class="text-gray-200 mb-3"><?=$content?></p>
                        
                        <div class="flex gap-4 text-sm text-gray-400">
                            <button class="hover:text-red-400"><i class="bi bi-heart"></i> <?= $comment['like_count'] ?></button>
                            <button onclick="toggleReply(<?= $comment['id'] ?>)" class="hover:text-blue-400">Balas</button>
                        </div>

                        <div id="reply-form-<?= $comment['id'] ?>" class="reply-form mt-4">
                            <form action="" method="post">
                                <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                <textarea name="comment_text" class="w-full bg-gray-900/50 border border-white/10 rounded-xl p-2 text-sm mb-2"></textarea>
                                <button type="submit" name="submit_comment" class="bg-blue-600 px-4 py-1 rounded-lg text-xs">Kirim</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<script>
    function toggleReply(id) {
        const f = document.getElementById('reply-form-' + id);
        f.style.display = f.style.display === 'block' ? 'none' : 'block';
    }

    const users = <?= json_encode($users_list) ?>;
    const input = document.getElementById('comment-input');
    const dd = document.getElementById('mention-dropdown');

    input.addEventListener('input', () => {
        const val = input.value;
        const match = val.substring(0, input.selectionStart).match(/@(\w*)$/);
        if(match) {
            const q = match[1].toLowerCase();
            const filtered = users.filter(u => u.username.toLowerCase().startsWith(q));
            dd.innerHTML = filtered.map(u => `<div class="p-2 hover:bg-white/10 cursor-pointer" onclick="addMention('${u.username}')">@${u.username}</div>`).join('');
            dd.classList.toggle('hidden', filtered.length === 0);
        } else { dd.classList.add('hidden'); }
    });

    function addMention(name) {
        const val = input.value;
        const start = val.substring(0, input.selectionStart).replace(/@\w*$/, '@' + name + ' ');
        input.value = start + val.substring(input.selectionStart);
        dd.classList.add('hidden');
        input.focus();
    }
</script>
</body>
</html>