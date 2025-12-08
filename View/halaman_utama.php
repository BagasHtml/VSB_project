<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login_register/form_login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, title, level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check apakah user adalah developer/admin (level >= 50)
$is_developer = ($user['level'] >= 50);

if(isset($_POST['upload'])) {
  $caption = $_POST['caption'];
  $tags = $_POST['tags'] ?? '';
  $image = $_FILES['image']['name'] ?? '';
  $tmp = $_FILES['image']['tmp_name'] ?? '';

  if($image && move_uploaded_file($tmp, "../uploads/" . $image)){
    $stmt = $conn->prepare("INSERT INTO posts (user_id,image,caption,tags) VALUES(?,?,?,?)");
    $stmt->bind_param("isss",$user_id,$image,$caption,$tags);
  } else {
    $stmt = $conn->prepare("INSERT INTO posts (user_id,caption,tags) VALUES(?,?,?)");
    $stmt->bind_param("iss",$user_id,$caption,$tags);
  }
  $stmt->execute();
  $post_id = $conn->insert_id;

  preg_match_all('/@(\w+)/',$caption,$mentions);
  if(!empty($mentions[1])) {
    foreach(array_unique($mentions[1]) as $mentioned) {
      $check=$conn->prepare("SELECT id FROM users WHERE username=?");
      $check->bind_param("s",$mentioned);
      $check->execute();
      $m_user=$check->get_result()->fetch_assoc();

      if($m_user && $m_user['id'] != $user_id){
        $notif=$conn->prepare("INSERT INTO notifications(user_id,from_user_id,type,post_id,message) VALUES (?,?, 'mention', ?,?)");
        $msg = $user['username']." mentioned you in a post";
        $notif->bind_param("iiis",$m_user['id'],$user_id,$post_id,$msg);
        $notif->execute();
      }
    }
  }
  header("Location: halaman_utama.php"); exit;
}

if(isset($_POST['like_post'])){
  $post_id=$_POST['post_id'];

  $check=$conn->prepare("SELECT id FROM post_likes WHERE post_id=? AND user_id=?");
  $check->bind_param("ii",$post_id,$user_id);
  $check->execute();

  if($check->get_result()->num_rows>0){
    $stmt=$conn->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?");
    $stmt->bind_param("ii",$post_id,$user_id);
  } else {
    $stmt=$conn->prepare("INSERT INTO post_likes(post_id,user_id) VALUES(?,?)");
    $stmt->bind_param("ii",$post_id,$user_id);

    $ownerQ=$conn->prepare("SELECT user_id FROM posts WHERE id=?");
    $ownerQ->bind_param("i",$post_id);
    $ownerQ->execute();
    $owner=$ownerQ->get_result()->fetch_assoc();

    if($owner['user_id'] != $user_id){
      $notif=$conn->prepare("INSERT INTO notifications(user_id,from_user_id,type,post_id,message) VALUES (?,?, 'like', ?,?)");
      $msg=$user['username']." liked your post";
      $notif->bind_param("iiis",$owner['user_id'],$user_id,$post_id,$msg);
      $notif->execute();
    }
  }
  $stmt->execute();
  header("Location: halaman_utama.php"); exit;
}

$posts = $conn->query("
  SELECT posts.*, users.username, users.title, users.level, users.profile_pic,
  (SELECT COUNT(*) FROM comments WHERE post_id=posts.id AND parent_id IS NULL) AS comment_count,
  (SELECT COUNT(*) FROM post_likes WHERE post_id=posts.id) AS like_count,
  (SELECT COUNT(*) FROM post_likes WHERE post_id=posts.id AND user_id=$user_id) AS user_liked
  FROM posts JOIN users ON posts.user_id=users.id ORDER BY posts.is_pinned DESC, posts.created_at DESC
");

$notifs = $conn->query("
  SELECT n.*,u.username AS from_username
  FROM notifications n JOIN users u ON n.from_user_id=u.id
  WHERE n.user_id=$user_id AND n.is_read=0 ORDER BY n.created_at DESC LIMIT 10
");
$unread_count = $notifs->num_rows;

$users_list=[];
$all=$conn->query("SELECT id,username FROM users ORDER BY username");
while($u=$all->fetch_assoc()){ $users_list[]=$u; } // 🔥 sudah benar — tidak masuk JS lagi

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Knowledge Battle - Forum Diskusi</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  body { font-family: 'Poppins', sans-serif; }
  .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
  .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  .mention-dropdown { max-height: 200px; overflow-y: auto; }
  .mention { color: #3b82f6; font-weight: 600; cursor: pointer; }
  .mention:hover { text-decoration: underline; }
  @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
  .float-animation { animation: float 3s ease-in-out infinite; }
  .notif-badge { animation: pulse 2s infinite; }
  @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen">

<!-- Header -->
<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto flex justify-between items-center py-3 px-3 lg:px-8">
    <div class="text-xl md:text-3xl font-bold">
      <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
    </div>
    
    <!-- Weather & Time - Hidden on mobile -->
    <div class="hidden lg:flex items-center gap-4 glass px-4 py-2 rounded-full text-sm">
      <div id="weather" class="flex items-center gap-2">
        <i class="bi bi-cloud-sun text-yellow-400"></i>
        <span id="temp">--°C</span>
      </div>
      <div class="w-px h-6 bg-white/20"></div>
      <div id="clock" class="flex items-center gap-2">
        <i class="bi bi-clock text-blue-400"></i>
        <span id="time">--:--</span>
      </div>
    </div>

    <div class="flex gap-2 items-center">
      <div class="relative">
        <button onclick="toggleNotif()" class="glass hover:bg-red-600/20 transition px-3 py-2 rounded-full flex items-center gap-2 relative">
          <i class="bi bi-bell text-lg"></i>
          <?php if($unread_count > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center notif-badge"><?= $unread_count ?></span>
          <?php endif; ?>
        </button>
        
        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 glass rounded-2xl p-4 max-h-96 overflow-y-auto">
          <h3 class="font-bold mb-3 flex items-center justify-between">
            <span>Notifikasi</span>
            <?php if($unread_count > 0): ?>
              <span class="text-xs bg-red-600 px-2 py-1 rounded-full"><?= $unread_count ?> baru</span>
            <?php endif; ?>
          </h3>
          <?php if($notifs->num_rows > 0): ?>
            <?php while($notif = $notifs->fetch_assoc()): ?>
              <a href="<?= $notif['post_id'] ? 'comments.php?post_id='.$notif['post_id'] : '#' ?>&read_notif=<?= $notif['id'] ?>" 
                 class="block p-3 hover:bg-white/10 rounded-xl mb-2 transition">
                <div class="flex items-start gap-2">
                  <i class="bi bi-<?= $notif['type'] == 'mention' ? 'at' : ($notif['type'] == 'like' ? 'heart-fill text-red-500' : 'chat-dots') ?> mt-1"></i>
                  <div class="flex-1">
                    <p class="text-sm"><?= htmlspecialchars($notif['message']) ?></p>
                    <span class="text-xs text-gray-400"><?= date('d M, H:i', strtotime($notif['created_at'])) ?></span>
                  </div>
                </div>
              </a>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-center text-gray-400 py-4">Tidak ada notifikasi</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="hidden md:flex items-center gap-2 glass px-3 py-2 rounded-full text-sm">
        <div class="font-semibold"><?= htmlspecialchars($user['username']) ?></div>
        <span class="px-2 py-1 bg-gradient-to-r from-purple-600 to-purple-500 rounded-full text-xs"><?= htmlspecialchars($user['title']) ?></span>
        <span class="px-2 py-1 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full text-xs font-bold">Lvl <?= $user['level'] ?></span>
      </div>

      <div class="md:hidden w-8 h-8 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-sm font-bold">
        <?= strtoupper(substr($user['username'], 0, 1)) ?>
      </div>

      <a href="index.php" class="glass hover:bg-red-600/20 transition px-3 py-2 rounded-full flex items-center gap-2 text-sm">
        <i class="bi bi-box-arrow-right"></i>
        <span class="hidden md:inline">Logout</span>
      </a>
      
      <a href="settings.php" class="glass hover:bg-blue-600/20 transition px-3 py-2 rounded-full flex items-center gap-2 text-sm">
        <i class="bi bi-gear"></i>
      </a>
    </div>
  </nav>
</header>

<main class="pt-20 pb-12 max-w-5xl mx-auto px-3 md:px-4">

  <!-- Stats Cards - Mobile Responsive -->
  <div class="grid grid-cols-3 gap-2 md:gap-4 mb-6 md:mb-8">
    <div class="glass p-3 md:p-6 rounded-xl md:rounded-2xl hover:scale-105 transition float-animation">
      <div class="flex flex-col md:flex-row items-center md:justify-between text-center md:text-left">
        <div>
          <p class="text-gray-400 text-xs md:text-sm">Threads</p>
          <h3 class="text-xl md:text-3xl font-bold gradient-text"><?= $posts->num_rows ?></h3>
        </div>
        <i class="bi bi-chat-dots text-2xl md:text-4xl text-red-500 hidden md:block"></i>
      </div>
    </div>
    <div class="glass p-3 md:p-6 rounded-xl md:rounded-2xl hover:scale-105 transition float-animation" style="animation-delay: 0.1s;">
      <div class="flex flex-col md:flex-row items-center md:justify-between text-center md:text-left">
        <div>
          <p class="text-gray-400 text-xs md:text-sm">Level</p>
          <h3 class="text-xl md:text-3xl font-bold text-yellow-400"><?= $user['level'] ?></h3>
        </div>
        <i class="bi bi-trophy text-2xl md:text-4xl text-yellow-400 hidden md:block"></i>
      </div>
    </div>
    <div class="glass p-3 md:p-6 rounded-xl md:rounded-2xl hover:scale-105 transition float-animation" style="animation-delay: 0.2s;">
      <div class="flex flex-col md:flex-row items-center md:justify-between text-center md:text-left">
        <div>
          <p class="text-gray-400 text-xs md:text-sm">Title</p>
          <h3 class="text-xs md:text-lg font-bold text-purple-400 truncate"><?= htmlspecialchars($user['title']) ?></h3>
        </div>
        <i class="bi bi-award text-2xl md:text-4xl text-purple-400 hidden md:block"></i>
      </div>
    </div>
  </div>

  <!-- Form Upload Thread - Mobile Responsive -->
  <div class="glass p-4 md:p-8 rounded-2xl md:rounded-3xl mb-6 md:mb-8 hover:border-red-500/50 transition">
    <h2 class="text-lg md:text-2xl font-bold mb-4 md:mb-6 flex items-center gap-2">
      <i class="bi bi-plus-circle text-red-500"></i>
      Buat Thread
    </h2>
    <form action="" method="post" enctype="multipart/form-data" class="flex flex-col gap-3 md:gap-4">
      <div class="relative">
        <textarea name="caption" id="caption" placeholder="Tulis diskusi... (@ untuk mention)" 
          class="w-full border border-white/10 p-3 md:p-4 rounded-xl bg-gray-900/50 focus:outline-none focus:border-red-500 transition min-h-[100px] text-sm md:text-base" required></textarea>
        <div id="mention-dropdown" class="hidden absolute z-10 glass rounded-xl p-2 w-64 mention-dropdown mt-1"></div>
      </div>
      
      <label class="flex items-center gap-3 border-2 border-dashed border-white/20 p-3 md:p-4 rounded-xl hover:border-red-500 transition cursor-pointer text-sm md:text-base">
        <i class="bi bi-image text-xl md:text-2xl text-red-500"></i>
        <span class="text-gray-400">Upload Gambar (Optional)</span>
        <input type="file" name="image" class="hidden" accept="image/*">
      </label>
      
      <input type="text" name="tags" placeholder="#tag1 #tag2" 
        class="border border-white/10 p-3 md:p-4 rounded-xl bg-gray-900/50 focus:outline-none focus:border-red-500 transition text-sm md:text-base">
      
      <button type="submit" name="upload" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 px-4 md:px-6 py-2 md:py-3 rounded-xl text-white font-semibold transition flex items-center justify-center gap-2 text-sm md:text-base">
        <i class="bi bi-send"></i>
        Posting
      </button>
    </form>
  </div>

  <!-- Threads List - Mobile Responsive -->
  <div class="space-y-4 md:space-y-6">
    <?php 
    $posts->data_seek(0);
    while($post = $posts->fetch_assoc()): 
      $caption = htmlspecialchars($post['caption']);
      $caption = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', $caption);
    ?>
      <div class="glass p-4 md:p-8 rounded-2xl md:rounded-3xl hover:border-red-500/30 transition relative">
        <?php if($post['is_pinned']): ?>
          <div class="absolute top-3 right-3 md:top-4 md:right-4 bg-gradient-to-r from-yellow-500 to-amber-500 px-2 md:px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
            <i class="bi bi-pin-fill"></i>
            <span>Pinned</span>
          </div>
        <?php endif; ?>
        <div class="flex justify-between items-start mb-3 md:mb-4">
          <div class="flex items-center gap-2 md:gap-3">
            <?php if(!empty($post['profile_pic'])): ?>
              <img src="../uploads/profile/<?= htmlspecialchars($post['profile_pic']) ?>" 
                class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover border border-white/10">
            <?php else: ?>
              <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-red-500 to-purple-600 
                flex items-center justify-center text-base md:text-xl font-bold">
                  <?= strtoupper(substr($post['username'], 0, 1)) ?>
              </div>
            <?php endif; ?>
            <div>
              <div class="font-bold text-sm md:text-lg"><?=htmlspecialchars($post['username'])?></div>
              <div class="flex items-center gap-1 md:gap-2 text-xs">
                <span class="px-2 py-0.5 bg-purple-600/30 rounded-full"><?=htmlspecialchars($post['title'])?></span>
                <span class="px-2 py-0.5 bg-yellow-500/30 rounded-full">Lvl <?= $post['level'] ?></span>
              </div>
            </div>
          </div>
          <div class="text-gray-400 text-xs md:text-sm flex items-center gap-1 md:gap-2">
            <i class="bi bi-clock"></i>
            <span class="hidden md:inline"><?=date('d M Y, H:i', strtotime($post['created_at']))?></span>
            <span class="md:hidden"><?=date('d M', strtotime($post['created_at']))?></span>
          </div>
        </div>

        <?php if($post['image']): ?>
          <img src="../uploads/<?=htmlspecialchars($post['image'])?>" class="w-full rounded-xl md:rounded-2xl mb-3 md:mb-4 hover:scale-[1.02] transition">
        <?php endif; ?>

        <p class="text-gray-200 mb-3 md:mb-4 leading-relaxed text-sm md:text-base"><?=$caption?></p>

        <!-- Tags -->
        <div class="flex flex-wrap gap-1 md:gap-2 mb-3 md:mb-4">
          <?php
          $tags_string = $post['tags'] ?? '';
          $tags = array_filter(explode(' ', $tags_string));
          foreach($tags as $tag):
          ?>
            <a href="halama_utama.php?tag=<?=urlencode($tag)?>" 
              class="text-red-400 text-xs bg-red-900/30 hover:bg-red-900/50 px-2 py-0.5 md:px-3 md:py-1 rounded-full transition">
              <?=htmlspecialchars($tag)?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Actions - Mobile Responsive -->
        <div class="flex items-center gap-2 md:gap-4 pt-3 md:pt-4 border-t border-white/10 flex-wrap">
          <a href="comments.php?post_id=<?= $post['id'] ?>" 
            class="flex items-center gap-1 md:gap-2 glass hover:bg-red-600/20 px-3 py-1.5 md:px-4 md:py-2 rounded-full transition text-xs md:text-base">
            <i class="bi bi-chat-dots"></i>
            <span><?= $post['comment_count'] ?></span>
          </a>
          <form method="post" class="inline">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <button type="submit" name="like_post" class="flex items-center gap-1 md:gap-2 glass hover:bg-red-600/20 px-3 py-1.5 md:px-4 md:py-2 rounded-full transition text-xs md:text-base <?= $post['user_liked'] ? 'text-red-500' : '' ?>">
              <i class="bi bi-heart<?= $post['user_liked'] ? '-fill' : '' ?>"></i>
              <span><?= $post['like_count'] ?></span>
            </button>
          </form>
          <button class="flex items-center gap-1 md:gap-2 glass hover:bg-green-600/20 px-3 py-1.5 md:px-4 md:py-2 rounded-full transition text-xs md:text-base">
            <i class="bi bi-share"></i>
            <span class="hidden md:inline">Share</span>
          </button>
          
          <!-- Pin button untuk developer/admin -->
          <?php if($is_developer): ?>
            <button type="button" class="flex items-center gap-1 md:gap-2 glass hover:bg-yellow-600/20 px-3 py-1.5 md:px-4 md:py-2 rounded-full transition text-xs md:text-base pin-btn <?= $post['is_pinned'] ? 'text-yellow-400' : '' ?>" 
              data-post-id="<?= $post['id'] ?>" 
              data-pinned="<?= $post['is_pinned'] ?>">
              <i class="bi bi-pin<?= $post['is_pinned'] ? '-fill' : '' ?>"></i>
              <span class="hidden md:inline"><?= $post['is_pinned'] ? 'Unpin' : 'Pin' ?></span>
            </button>
          <?php endif; ?>
          
          <!-- Delete button untuk post sendiri -->
          <?php if($post['user_id'] == $user_id): ?>
            <a href="delete_post.php?id=<?= $post['id'] ?>" 
              onclick="return confirm('Yakin ingin menghapus post ini?')"
              class="flex items-center gap-1 md:gap-2 glass hover:bg-red-600/50 px-3 py-1.5 md:px-4 md:py-2 rounded-full transition text-xs md:text-base text-red-400">
              <i class="bi bi-trash"></i>
              <span class="hidden md:inline">Hapus</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</main>

<script>
// Pin/Unpin Handler
document.querySelectorAll('.pin-btn').forEach(btn => {
  btn.addEventListener('click', async function(e) {
    e.preventDefault();
    const postId = this.dataset.postId;
    const formData = new FormData();
    formData.append('post_id', postId);

    try {
      const response = await fetch('../service/api/pin_post.php', {
        method: 'POST',
        body: formData
      });
      const data = await response.json();

      if(data.status === 'ok') {
        // Update button state
        const isPinned = data.is_pinned;
        const icon = this.querySelector('i');
        const span = this.querySelector('span');
        
        if(isPinned) {
          this.classList.add('text-yellow-400');
          icon.className = 'bi bi-pin-fill';
          if(span) span.textContent = 'Unpin';
        } else {
          this.classList.remove('text-yellow-400');
          icon.className = 'bi bi-pin';
          if(span) span.textContent = 'Pin';
        }
        
        // Reload page untuk update ordering
        setTimeout(() => {
          location.reload();
        }, 300);
      } else {
        alert('Error: ' + (data.error || 'Gagal mengupdate pin status'));
      }
    } catch(error) {
      console.error('Error:', error);
      alert('Terjadi kesalahan');
    }
  });
});

// Notification toggle
function toggleNotif() {
  const dropdown = document.getElementById('notif-dropdown');
  dropdown.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
  const dropdown = document.getElementById('notif-dropdown');
  const button = e.target.closest('button[onclick="toggleNotif()"]');
  if(!button && !dropdown.contains(e.target)) {
    dropdown.classList.add('hidden');
  }
});

// Clock & Weather
function updateTime() {
  const now = new Date();
  const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  document.getElementById('time').textContent = time;
}
setInterval(updateTime, 1000);
updateTime();

fetch('https://api.open-meteo.com/v1/forecast?latitude=-6.2&longitude=106.8&current_weather=true')
  .then(r => r.json())
  .then(data => {
    document.getElementById('temp').textContent = Math.round(data.current_weather.temperature) + '°C';
  })
  .catch(() => {
    document.getElementById('temp').textContent = '28°C';
  });

// Mention autocomplete
const usersList = <?= json_encode($users_list) ?>;
const textarea = document.getElementById('caption');
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

fetch('https://api.open-meteo.com/v1/forecast?latitude=-6.2&longitude=106.8&current_weather=true')
  .then(r => r.json())
  .then(data => {
    document.getElementById('temp').textContent = Math.round(data.current_weather.temperature) + '°C';
  })
  .catch(error => console.log("Gagal memuat cuaca", error));

const captionInput = document.getElementById("caption");
const mentionBox = document.getElementById("mention-dropdown");

let users = <?php echo json_encode($users_list); ?>;

captionInput.addEventListener("keyup", () => {
    const val = captionInput.value;
    const trigger = val.match(/@(\w*)$/);

    if(trigger){
        const search = trigger[1].toLowerCase();
        const filtered = users.filter(u => u.username.toLowerCase().includes(search));

        if(filtered.length){
            mentionBox.innerHTML = filtered.map(u=>`<div class="mention-item p-2 hover:bg-white/10 rounded cursor-pointer">@${u.username}</div>`).join("");
            mentionBox.classList.remove("hidden");

            document.querySelectorAll(".mention-item").forEach(item=>{
                item.onclick = ()=>{
                    captionInput.value = val.replace(/@\w*$/, item.textContent + " ");
                    mentionBox.classList.add("hidden");
                }
            });
        } else mentionBox.classList.add("hidden");
    } else mentionBox.classList.add("hidden");
});
</script>
</body>
</html>