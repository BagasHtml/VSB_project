<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Knowledge Battle - Forum Diskusi Anime</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    
    html { scroll-behavior: smooth; }
    
    .gradient-text {
      background: linear-gradient(90deg, #ef4444, #dc2626, #991b1b);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .float-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .hero-pattern {
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(239, 68, 68, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(220, 38, 38, 0.1) 0%, transparent 50%);
    }
  </style>
</head>
<body class="bg-gray-900 text-white overflow-x-hidden">

  <!-- NAVBAR -->
  <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="navbar">
    <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-md border-b border-white/10"></div>
    <nav class="relative z-10 max-w-7xl mx-auto flex justify-between items-center py-4 px-4 lg:px-8">
      <!-- Logo -->
      <div class="text-2xl md:text-3xl font-bold">
        <span class="text-gray-100">Knowledge</span><span class="text-red-600">Battle</span>
      </div>
      
      <!-- Desktop Navigation -->
      <div class="hidden md:flex items-center gap-8">
        <a href="#beranda" class="text-gray-300 hover:text-white transition-colors font-medium">Beranda</a>
        <a href="#tentang" class="text-gray-300 hover:text-white transition-colors font-medium">Tentang</a>
        <a href="#fitur" class="text-gray-300 hover:text-white transition-colors font-medium">Fitur</a>
        <a href="#kontak" class="text-gray-300 hover:text-white transition-colors font-medium">Kontak</a>
      </div>
      
      <!-- CTA Buttons -->
      <div class="hidden md:flex gap-3">
        <a href="../View/login_register/form_login.php" 
           class="px-6 py-2.5 rounded-xl glass hover:bg-white/10 text-white font-semibold transition-all duration-300 hover:scale-105">
          Masuk
        </a>
        <a href="../View/login_register/form_register.php" 
           class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg shadow-red-600/30">
          Daftar Sekarang
        </a>
      </div>
      
      <!-- Mobile Menu Button -->
      <button id="mobile-menu-btn" class="md:hidden focus:outline-none">
        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path id="menu-open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          <path id="menu-close" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </nav>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full bg-gray-900/95 backdrop-blur-lg border-b border-white/10">
      <div class="flex flex-col p-4 gap-3">
        <a href="#beranda" class="px-4 py-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white font-medium transition">Beranda</a>
        <a href="#tentang" class="px-4 py-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white font-medium transition">Tentang</a>
        <a href="#fitur" class="px-4 py-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white font-medium transition">Fitur</a>
        <a href="#kontak" class="px-4 py-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white font-medium transition">Kontak</a>
        <div class="h-px bg-white/10 my-2"></div>
        <a href="../View/login_register/form_login.php" 
           class="px-4 py-3 rounded-lg glass hover:bg-white/10 text-white font-semibold text-center transition">
          Masuk
        </a>
        <a href="../View/login_register/form_register.php" 
           class="px-4 py-3 rounded-lg bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold text-center transition hover:from-red-500 hover:to-red-600">
          Daftar Sekarang
        </a>
      </div>
    </div>
  </header>

  <!-- HERO SECTION -->
  <section id="beranda" class="relative min-h-screen flex items-center justify-center hero-pattern">
    <div class="absolute inset-0 bg-gradient-to-b from-gray-900 via-gray-900/95 to-gray-900"></div>
    
    <!-- Animated circles -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-red-600/10 rounded-full blur-3xl float-animation"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-red-700/10 rounded-full blur-3xl float-animation" style="animation-delay: 2s;"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-20 text-center" data-aos="fade-up">
      <div class="mb-6">
        <span class="inline-block px-4 py-2 rounded-full glass text-sm font-semibold text-red-400 mb-8">
          🎌 Forum Diskusi & Debat Anime, Donghua, DC, Kartun
        </span>
      </div>
      
      <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
        Selamat Datang di<br/>
        <span class="gradient-text">Knowledge Battle</span>
      </h1>
      
      <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
        Platform komunitas untuk berdiskusi, melakukan analisis komparatif, serta berdebat secara terstruktur mengenai karakter dan konsep dari berbagai karya fiksi. Knowledge Battle menyediakan ruang bagi para pengguna untuk berbagi pengetahuan, menyusun argumen berbasis bukti (feats, statements, scaling), dan berinteraksi dengan komunitas analisis fiksi terbesar di Seluruh Dunia
      </p>
      
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <a href="../View/login_register/form_register.php" 
           class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold text-lg transition-all duration-300 hover:scale-105 shadow-2xl shadow-red-600/30">
          Mulai Sekarang →
        </a>
        <a href="#tentang" 
           class="w-full sm:w-auto px-8 py-4 rounded-xl glass hover:bg-white/10 text-white font-bold text-lg transition-all duration-300 hover:scale-105">
          Pelajari Lebih Lanjut
        </a>
      </div>
      
      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-20 max-w-4xl mx-auto">
        <div class="glass rounded-2xl p-6" data-aos="fade-up" data-aos-delay="100">
          <div class="text-3xl md:text-4xl font-bold text-red-500 mb-2">1000+</div>
          <div class="text-sm text-gray-400">Pengguna Aktif</div>
        </div>
        <div class="glass rounded-2xl p-6" data-aos="fade-up" data-aos-delay="200">
          <div class="text-3xl md:text-4xl font-bold text-red-500 mb-2">500+</div>
          <div class="text-sm text-gray-400">Diskusi Harian</div>
        </div>
        <div class="glass rounded-2xl p-6" data-aos="fade-up" data-aos-delay="300">
          <div class="text-3xl md:text-4xl font-bold text-red-500 mb-2">50+</div>
          <div class="text-sm text-gray-400">Topik Anime</div>
        </div>
        <div class="glass rounded-2xl p-6" data-aos="fade-up" data-aos-delay="400">
          <div class="text-3xl md:text-4xl font-bold text-red-500 mb-2">24/7</div>
          <div class="text-sm text-gray-400">Komunitas Online</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section id="tentang" class="relative py-24 md:py-32 bg-gray-800/50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="inline-block px-4 py-2 rounded-full glass text-sm font-semibold text-red-400 mb-4">
          Tentang Kami
        </span>
        <h2 class="text-3xl md:text-5xl font-bold mb-6">
          Apa Itu <span class="gradient-text">Knowledge Battle?</span>
        </h2>
        <p class="text-lg text-gray-400 max-w-3xl mx-auto leading-relaxed">
          Knowledge Battle adalah platform forum diskusi terlengkap untuk para penggemar Anime, Donghua, DC, Kartun, di seluruh Dunia. 
          Tempat berkumpulnya komunitas yang passionate untuk berdebat, berbagi teori, dan menganalisis karakter favorit.
        </p>
      </div>
      
      <div class="grid md:grid-cols-2 gap-8 items-center">
        <!-- Image/Illustration -->
        <div class="glass rounded-3xl p-8 h-full flex items-center justify-center" data-aos="fade-right">
          <div class="text-center">
            <div class="bi bi-globe2 text-8xl mb-4 "></div>
            <h3 class="text-2xl font-bold mb-3">Forum Diskusi Anime, Donghua, DC, Kartun Terbaik</h3>
            <p class="text-gray-400">Komunitas anime terbesar dan teraktif di Dunia</p>
          </div>
        </div>
        
        <!-- Features List -->
        <div class="space-y-6" data-aos="fade-left">
          <div class="glass rounded-2xl p-6 hover:bg-white/10 transition-all duration-300">
            <div class="flex items-start gap-4">
              <div class="text-3xl"><i class="bi bi-chat-dots-fill"></i></div>
              <div>
                <h4 class="text-xl font-bold mb-2">Diskusi Mendalam</h4>
                <p class="text-gray-400">Bahas karakter, plot, dan teori anime dengan detail bersama komunitas</p>
              </div>
            </div>
          </div>
          
          <div class="glass rounded-2xl p-6 hover:bg-white/10 transition-all duration-300">
            <div class="flex items-start gap-4">
              <div class="text-3xl"><i class="bi bi-fire"></i></div>
              <div>
                <h4 class="text-xl font-bold mb-2">Battle Debat</h4>
                <p class="text-gray-400">Arena debat karakter anime dengan argumen yang terstruktur dan fair</p>
              </div>
            </div>
          </div>
          
          <div class="glass rounded-2xl p-6 hover:bg-white/10 transition-all duration-300">
            <div class="flex items-start gap-4">
              <div class="text-3xl"><i class="bi bi-book-fill"></i></div>
              <div>
                <h4 class="text-xl font-bold mb-2">Knowledge Sharing</h4>
                <p class="text-gray-400">Berbagi pengetahuan, review, dan rekomendasi anime terbaik</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES SECTION -->
  <section id="fitur" class="relative py-24 md:py-32">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="inline-block px-4 py-2 rounded-full glass text-sm font-semibold text-red-400 mb-4">
          Fitur Unggulan
        </span>
        <h2 class="text-3xl md:text-5xl font-bold mb-6">
          Kenapa Memilih <span class="gradient-text">Knowledge Battle?</span>
        </h2>
      </div>
      
      <div class="grid md:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="100">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            <i class="bi bi-person-raised-hand"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4">Komunitas Aktif</h3>
          <p class="text-gray-400 leading-relaxed">
            Bergabung dengan ribuan pengguna aktif yang siap berdiskusi dan berbagi pengalaman setiap hari
          </p>
        </div>
        
        <!-- Feature 2 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="200">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4">Sistem Rating</h3>
          <p class="text-gray-400 leading-relaxed">
            Dapatkan poin dan badge eksklusif dari kontribusi diskusi dan argumen terbaikmu
          </p>
        </div>
        
        <!-- Feature 3 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="300">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
           <i class="bi bi-shield-shaded"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4">Aman & Terpercaya</h3>
          <p class="text-gray-400 leading-relaxed">
            Platform dengan moderasi ketat untuk menciptakan lingkungan diskusi yang sehat dan positif
          </p>
        </div>
        
        <!-- Feature 4 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="400">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            <i class="bi bi-phone-fill"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4">Mobile Friendly</h3>
          <p class="text-gray-400 leading-relaxed">
            Akses dari mana saja dan kapan saja dengan tampilan responsif di semua perangkat
          </p>
        </div>
        
        <!-- Feature 5 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="500">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            <i class="bi bi-signal"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4">Topik Beragam</h3>
          <p class="text-gray-400 leading-relaxed">
            Dari shonen, seinen, hingga isekai - temukan diskusi tentang anime genre favoritmu
          </p>
        </div>
        
        <!-- Feature 6 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="600">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            <i class="bi bi-lightning-fill text-3xl"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4">Update Cepat</h3>
          <p class="text-gray-400 leading-relaxed">
            Dapatkan informasi dan diskusi terbaru tentang anime yang sedang airing setiap musim
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA SECTION -->
  <section class="relative py-24 md:py-32 bg-gradient-to-br from-red-900/20 to-red-950/20">
    <div class="max-w-4xl mx-auto px-4 text-center" data-aos="zoom-in">
      <div class="glass rounded-3xl p-12 md:p-16">
        <h2 class="text-3xl md:text-5xl font-bold mb-6">
          Siap Bergabung dengan <span class="gradient-text">Komunitas?</span>
        </h2>
        <p class="text-lg text-gray-400 mb-10 leading-relaxed">
          Daftar sekarang dan mulai petualangan diskusi anime-mu bersama ribuan pengguna lainnya!
        </p>
        <a href="../View/login_register/form_register.php" 
           class="inline-block px-10 py-5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold text-lg transition-all duration-300 hover:scale-105 shadow-2xl shadow-red-600/30">
          <i class="bi bi-person-plus-fill mr-2"></i>Daftar Gratis Sekarang →
        </a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer id="kontak" class="relative py-16 bg-gray-900 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid md:grid-cols-4 gap-8 mb-12">
        <!-- Brand -->
        <div class="md:col-span-2">
          <div class="text-3xl font-bold mb-4">
            <span class="text-gray-100">Knowledge</span><span class="text-red-600">Battle</span>
          </div>
          <p class="text-gray-400 mb-4 max-w-md">
            Platform forum diskusi dan debat anime terbesar di Indonesia. Bergabunglah dengan komunitas yang passionate!
          </p>
          <div class="flex gap-4">
            <a href="#" class="w-10 h-10 rounded-lg glass hover:bg-white/10 flex items-center justify-center transition">
              <i class="bi bi-discord"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-lg glass hover:bg-white/10 flex items-center justify-center transition">
              <i class="bi bi-facebook"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-lg glass hover:bg-white/10 flex items-center justify-center transition">
              <i class="bi bi-tiktok"></i>
            </a>
          </div>
        </div>
        
        <!-- Links -->
        <div>
          <h4 class="text-lg font-bold mb-4">Navigasi</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#beranda" class="hover:text-white transition">Beranda</a></li>
            <li><a href="#tentang" class="hover:text-white transition">Tentang</a></li>
            <li><a href="#fitur" class="hover:text-white transition">Fitur</a></li>
            <li><a href="creators.php" class="hover:text-white transition">Kreator Pembuat Web</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-lg font-bold mb-4">Legal</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
            <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
            <li><a href="#" class="hover:text-white transition">Kontak</a></li>
          </ul>
        </div>
      </div>
      
      <!-- Copyright -->
      <div class="pt-8 border-t border-white/10 text-center text-gray-400">
        <p>&copy; 2025 Knowledge Battle. All rights reserved.</p>
      </div>
    </div>
  </footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 1000,
    once: true
  });

  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  const menuOpenIcon = document.getElementById('menu-open');
  const menuCloseIcon = document.getElementById('menu-close');

  menuBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
    menuOpenIcon.classList.toggle('hidden');
    menuCloseIcon.classList.toggle('hidden');
  });

  window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 10) {
      navbar.classList.add('shadow-lg', 'bg-gray-900/95');
    } else {
      navbar.classList.remove('shadow-lg', 'bg-gray-900/95');
    }
  });
</script>
 </body>
</html>