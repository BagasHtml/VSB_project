<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'] ?? 0;

$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

// Handle comment submission
if(isset($_POST['submit_comment'])) {
    $comment_text = $_POST['comment_text'];
    $parent_id = $_POST['parent_id'] ?? null;
    
    if($parent_id) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $post_id, $user_id, $comment_text, $parent_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_text);
    }
    $stmt->execute();
    $comment_id = $conn->insert_id;
    
    // Create notifications for mentions
    preg_match_all('/@(\w+)/', $comment_text, $mentions);
    if(!empty($mentions[1])) {
        foreach(array_unique($mentions[1]) as $mentioned) {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $mentioned);
            $check->execute();
            $mentioned_user = $check->get_result()->fetch_assoc();
            
            if($mentioned_user && $mentioned_user['id'] != $user_id) {
                $notif = $conn->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, message) VALUES (?, ?, 'mention', ?, ?, ?)");
                $msg = $current_user['username'] . " mentioned you in a comment";
                $notif->bind_param("iiiis", $mentioned_user['id'], $user_id, $post_id, $comment_id, $msg);
                $notif->execute();
            }
        }
    }
    
    // Notify post owner
    if(!$parent_id) {
        $post_owner = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
        $post_owner->bind_param("i", $post_id);
        $post_owner->execute();
        $owner = $post_owner->get_result()->fetch_assoc();
        
        if($owner['user_id'] != $user_id) {
            $notif = $conn->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, message) VALUES (?, ?, 'comment', ?, ?, ?)");
            $msg = $current_user['username'] . " commented on your post";
            $notif->bind_param("iiiis", $owner['user_id'], $user_id, $post_id, $comment_id, $msg);
            $notif->execute();
        }
    }
    
    header("Location: comments.php?post_id=$post_id");
    exit;
}

// Handle comment like
if(isset($_POST['like_comment'])) {
    $comment_id = $_POST['comment_id'];
    $check = $conn->prepare("SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $check->bind_param("ii", $comment_id, $user_id);
    $check->execute();
    
    if($check->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $comment_id, $user_id);
        
        // Create notification
        $comment_owner = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
        $comment_owner->bind_param("i", $comment_id);
        $comment_owner->execute();
        $owner = $comment_owner->get_result()->fetch_assoc();
        
        if($owner['user_id'] != $user_id) {
            $notif = $conn->prepare("INSERT INTO notifications (user_id, from_user_id, type, comment_id, message) VALUES (?, ?, 'like', ?, ?)");
            $msg = $current_user['username'] . " liked your comment";
            $notif->bind_param("iiis", $owner['user_id'], $user_id, $comment_id, $msg);
            $notif->execute();
        }
    }
    $stmt->execute();
    header("Location: comments.php?post_id=$post_id");
    exit;
}

// Get post details
$stmt = $conn->prepare("
    SELECT posts.*, users.username, users.title, users.level
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post) {
    header("Location: forum.php");
    exit;
}

// Get comments with likes and replies
$stmt = $conn->prepare("
    SELECT c.*, u.username, u.title, u.level,
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

// Get all users for mention
$all_users = $conn->query("SELECT id, username FROM users ORDER BY username");
$users_list = [];
while($u = $all_users->fetch_assoc()) {
    $users_list[] = $u;
}

// Function to get replies
function getReplies($conn, $parent_id, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.*, u.username, u.title, u.level,
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
  .mention-dropdown { max-height: 200px; overflow-y: auto; }
  .reply-form { display: none; }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen">

<!-- Header -->
<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto flex justify-between items-center py-3 md:py-4 px-3 md:px-8">
    <a href="forum.php" class="flex items-center gap-2 glass px-3 py-2 rounded-full hover:bg-red-600/20 transition text-sm md:text-base">
      <i class="bi bi-arrow-left"></i>
      <span>Kembali</span>
    </a>
    <div class="text-xl md:text-3xl font-bold">
      <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
    </div>
    <div class="w-20 md:w-32"></div>
  </nav>
</header>

<main class="pt-20 md:pt-28 pb-12 max-w-5xl mx-auto px-3 md:px-4">

  <!-- Original Post -->
  <div class="glass p-4 md:p-8 rounded-2xl md:rounded-3xl mb-6 md:mb-8">
    <div class="flex items-center gap-2 md:gap-3 mb-4 md:mb-6">
      <div class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-lg md:text-2xl font-bold flex-shrink-0">
        <?= strtoupper(substr($post['username'], 0, 1)) ?>
      </div>
      <div>
        <div class="font-bold text-base md:text-xl"><?=htmlspecialchars($post['username'])?></div>
        <div class="flex items-center gap-1 md:gap-2 text-xs md:text-sm">
          <span class="px-2 py-0.5 md:px-3 md:py-1 bg-purple-600/30 rounded-full"><?=htmlspecialchars($post['title'])?></span>
          <span class="px-2 py-0.5 md:px-3 md:py-1 bg-yellow-500/30 rounded-full">Lvl <?= $post['level'] ?></span>
          <span class="text-gray-400 hidden md:inline">• <?=date('d M Y, H:i', strtotime($post['created_at']))?></span>
        </div>
      </div>
    </div>

    <?php if($post['image']): ?>
      <img src="../uploads/<?=htmlspecialchars($post['image'])?>" class="w-full rounded-xl md:rounded-2xl mb-4 md:mb-6">
    <?php endif; ?>

    <p class="text-gray-200 text-sm md:text-lg leading-relaxed mb-3 md:mb-4"><?=htmlspecialchars($post['caption'])?></p>

    <?php if($post['tags']): ?>
      <div class="flex flex-wrap gap-1 md:gap-2 pt-3 md:pt-4 border-t border-white/10">
        <?php
        $tags = array_filter(explode(' ', $post['tags']));
        foreach($tags as $tag):
        ?>
          <span class="text-red-400 text-xs md:text-sm bg-red-900/30 px-2 py-0.5 md:px-3 md:py-1 rounded-full">
            <?=htmlspecialchars($tag)?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Comment Form -->
  <div class="glass p-4 md:p-6 rounded-xl md:rounded-2xl mb-6 md:mb-8">
    <h3 class="text-base md:text-xl font-bold mb-3 md:mb-4 flex items-center gap-2">
      <i class="bi bi-chat-left-text text-red-500"></i>
      Tambah Komentar
    </h3>
    <form action="" method="post">
      <input type="hidden" name="parent_id" value="">
      <div class="relative mb-3 md:mb-4">
        <textarea name="comment_text" id="comment-input" placeholder="Tulis komentar... (@ untuk mention)" 
          class="w-full border border-white/10 p-3 md:p-4 rounded-xl bg-gray-900/50 focus:outline-none focus:border-red-500 transition min-h-[80px] md:min-h-[100px] text-sm md:text-base" required></textarea>
        <div id="mention-dropdown" class="hidden absolute z-10 glass rounded-xl p-2 w-64 mention-dropdown mt-1"></div>
      </div>
      <button type="submit" name="submit_comment" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 px-4 md:px-6 py-2 md:py-3 rounded-xl font-semibold transition flex items-center gap-2 text-sm md:text-base">
        <i class="bi bi-send"></i>
        Kirim Komentar
      </button>
    </form>
  </div>

  <!-- Comments List -->
  <div class="space-y-3 md:space-y-4">
    <h3 class="text-lg md:text-2xl font-bold mb-4 md:mb-6 flex items-center gap-2">
      <i class="bi bi-chat-dots text-red-500"></i>
      <?= $comments->num_rows ?> Komentar
    </h3>

    <?php while($comment = $comments->fetch_assoc()): 
      $comment_content = htmlspecialchars($comment['comment'] ?? '');
      $comment_content = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', $comment_content);
    ?>
      <div class="glass p-4 md:p-6 rounded-xl md:rounded-2xl hover:border-red-500/30 transition">
        <div class="flex items-start gap-2 md:gap-3">
          <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-base md:text-lg font-bold flex-shrink-0">
            <?= strtoupper(substr($comment['username'], 0, 1)) ?>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1 md:gap-2 mb-1 md:mb-2 flex-wrap">
              <span class="font-bold text-sm md:text-base"><?=htmlspecialchars($comment['username'])?></span>
              <span class="px-2 py-0.5 bg-purple-600/30 rounded-full text-xs"><?=htmlspecialchars($comment['title'])?></span>
              <span class="px-2 py-0.5 bg-yellow-500/30 rounded-full text-xs">Lvl <?= $comment['level'] ?></span>
              <span class="text-gray-400 text-xs">• <?=date('d M, H:i', strtotime($comment['created_at']))?></span>
            </div>
            <p class="text-gray-200 leading-relaxed mb-2 md:mb-3 text-sm md:text-base break-words"><?=$comment_content?></p>
            
            <div class="flex items-center gap-2 md:gap-4">
              <form method="post" class="inline">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <button type="submit" name="like_comment" class="text-xs md:text-sm text-gray-400 hover:text-red-400 transition flex items-center gap-1 <?= $comment['user_liked'] ? 'text-red-500' : '' ?>">
                  <i class="bi bi-heart<?= $comment['user_liked'] ? '-fill' : '' ?>"></i>
                  <span><?= $comment['like_count'] ?></span>
                </button>
              </form>
              <button onclick="toggleReply(<?= $comment['id'] ?>)" class="text-xs md:text-sm text-gray-400 hover:text-blue-400 transition flex items-center gap-1">
                <i class="bi bi-reply"></i>
                <span>Balas<?= $comment['reply_count'] > 0 ? ' ('.$comment['reply_count'].')' : '' ?></span>
              </button>
              
              <!-- Delete button untuk comment sendiri -->
              <?php if($comment['user_id'] == $user_id): ?>
                <a href="delete_comment.php?id=<?= $comment['id'] ?>&post_id=<?= $post_id ?>" 
                  onclick="return confirm('Yakin ingin menghapus komentar ini?')"
                  class="text-xs md:text-sm text-gray-400 hover:text-red-400 transition flex items-center gap-1">
                  <i class="bi bi-trash"></i>
                  <span>Hapus</span>
                </a>
              <?php endif; ?>
            </div>

            <!-- Reply Form -->
            <div id="reply-form-<?= $comment['id'] ?>" class="reply-form mt-3 md:mt-4">
              <form action="" method="post">
                <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                <textarea name="comment_text" placeholder="Tulis balasan..." 
                  class="w-full border border-white/10 p-2 md:p-3 rounded-xl bg-gray-900/50 focus:outline-none focus:border-red-500 transition min-h-[60px] text-sm md:text-base mb-2" required></textarea>
                <button type="submit" name="submit_comment" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-3 md:px-4 py-1.5 md:py-2 rounded-lg font-semibold transition text-xs md:text-sm">
                  Kirim Balasan
                </button>
              </form>
            </div>

            <!-- Replies -->
            <?php 
            $replies = getReplies($conn, $comment['id'], $user_id);
            if($replies->num_rows > 0):
            ?>
              <div class="mt-3 md:mt-4 space-y-2 md:space-y-3 pl-3 md:pl-4 border-l-2 border-red-500/30">
                <?php while($reply = $replies->fetch_assoc()): 
                  $reply_content = htmlspecialchars($reply['comment'] ?? '');
                  $reply_content = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', $reply_content);
                ?>
                  <div class="glass p-3 md:p-4 rounded-xl">
                    <div class="flex items-start gap-2">
                      <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-sm md:text-base font-bold flex-shrink-0">
                        <?= strtoupper(substr($reply['username'], 0, 1)) ?>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1 md:gap-2 mb-1 flex-wrap">
                          <span class="font-bold text-xs md:text-sm"><?=htmlspecialchars($reply['username'])?></span>
                          <span class="text-gray-400 text-xs">• <?=date('d M, H:i', strtotime($reply['created_at']))?></span>
                        </div>
                        <p class="text-gray-200 text-xs md:text-sm break-words"><?=$reply_content?></p>
                        
                        <form method="post" class="inline mt-2">
                          <input type="hidden" name="comment_id" value="<?= $reply['id'] ?>">
                          <button type="submit" name="like_comment" class="text-xs text-gray-400 hover:text-red-400 transition flex items-center gap-1 <?= $reply['user_liked'] ? 'text-red-500' : '' ?>">
                            <i class="bi bi-heart<?= $reply['user_liked'] ? '-fill' : '' ?>"></i>
                            <span><?= $reply['like_count'] ?></span>
                          </button>
                        </form>
                        
                        <!-- Delete reply -->
                        <?php if($reply['user_id'] == $user_id): ?>
                          <a href="delete_comment.php?id=<?= $reply['id'] ?>&post_id=<?= $post_id ?>" 
                            onclick="return confirm('Yakin ingin menghapus balasan ini?')"
                            class="text-xs text-gray-400 hover:text-red-400 transition inline-flex items-center gap-1 mt-2">
                            <i class="bi bi-trash"></i>
                            <span>Hapus</span>
                          </a>
                        <?php endif; ?>
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

    <?php if($comments->num_rows == 0): ?>
      <div class="text-center py-8 md:py-12 text-gray-400">
        <i class="bi bi-chat-dots text-4xl md:text-6xl mb-3 md:mb-4"></i>
        <p class="text-base md:text-lg">Belum ada komentar. Jadilah yang pertama!</p>
      </div>
    <?php endif; ?>
  </div>

</main>

<script>
function toggleReply(commentId) {
  const form = document.getElementById('reply-form-' + commentId);
  form.style.display = form.style.display === 'none' || !form.style.display ? 'block' : 'none';
}

// Mention autocomplete
const usersList = <?= json_encode($users_list) ?>;
const textarea = document.getElementById('comment-input');
const dropdown = document.getElementById('mention-dropdown');

textarea.addEventListener('input', function(e) {
  const text = this.value;
  const cursorPos = this.selectionStart;
  const textBeforeCursor = text.substring(0, cursorPos);
  const match = textBeforeCursor.match(/@(\w*)$/);
  
  if(match) {
    const query = match[1].toLowerCase();
    const filtered = usersList.filter(u => u.username.toLowerCase().startsWith(query));
    
    if(filtered.length > 0) {
      dropdown.innerHTML = filtered.map(u => 
        `<div class="p-2 hover:bg-white/10 rounded cursor-pointer" onclick="insertMention('${u.username}')">
          @${u.username}
        </div>`
      ).join('');
      dropdown.classList.remove('hidden');
    } else {
      dropdown.classList.add('hidden');
    }
  } else {
    dropdown.classList.add('hidden');
  }
});

function insertMention(username) {
  const text = textarea.value;
  const cursorPos = textarea.selectionStart;
  const textBeforeCursor = text.substring(0, cursorPos);
  const textAfterCursor = text.substring(cursorPos);
  const newTextBefore = textBeforeCursor.replace(/@\w*$/, '@' + username + ' ');
  
  textarea.value = newTextBefore + textAfterCursor;
  textarea.focus();
  textarea.selectionStart = textarea.selectionEnd = newTextBefore.length;
  dropdown.classList.add('hidden');
}

document.addEventListener('click', function(e) {
  if(!textarea.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.add('hidden');
  }
});
</script>

</body>
</html>