<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Knowledge Battle - Forum Diskusi Anime</title>
  
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- AOS CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
  <style>
    body { font-family: 'Poppins', sans-serif; }
    
    /* Smooth scroll */
    html { scroll-behavior: smooth; }
    
    /* Custom gradient */
    .gradient-text {
      background: linear-gradient(90deg, #ef4444, #dc2626, #991b1b);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Animated background */
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .float-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    /* Glassmorphism */
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Hero pattern */
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
          🎌 Forum Diskusi & Debat Anime
        </span>
      </div>
      
      <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
        Selamat Datang di<br/>
        <span class="gradient-text">Knowledge Battle</span>
      </h1>
      
      <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
        Platform komunitas untuk berdiskusi, berdebat tentang karakter anime favorit, dan berbagi pengetahuan dengan sesama penggemar anime di Indonesia.
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
          Knowledge Battle adalah platform forum diskusi terlengkap untuk para penggemar anime di Indonesia. 
          Tempat berkumpulnya komunitas yang passionate untuk berdebat, berbagi teori, dan menganalisis karakter anime favorit.
        </p>
      </div>
      
      <div class="grid md:grid-cols-2 gap-8 items-center">
        <!-- Image/Illustration -->
        <div class="glass rounded-3xl p-8 h-full flex items-center justify-center" data-aos="fade-right">
          <div class="text-center">
            <div class="text-8xl mb-4">🎌</div>
            <h3 class="text-2xl font-bold mb-3">Forum Anime Terbaik</h3>
            <p class="text-gray-400">Komunitas anime terbesar dan teraktif di Indonesia</p>
          </div>
        </div>
        
        <!-- Features List -->
        <div class="space-y-6" data-aos="fade-left">
          <div class="glass rounded-2xl p-6 hover:bg-white/10 transition-all duration-300">
            <div class="flex items-start gap-4">
              <div class="text-3xl">💬</div>
              <div>
                <h4 class="text-xl font-bold mb-2">Diskusi Mendalam</h4>
                <p class="text-gray-400">Bahas karakter, plot, dan teori anime dengan detail bersama komunitas</p>
              </div>
            </div>
          </div>
          
          <div class="glass rounded-2xl p-6 hover:bg-white/10 transition-all duration-300">
            <div class="flex items-start gap-4">
              <div class="text-3xl">⚔️</div>
              <div>
                <h4 class="text-xl font-bold mb-2">Battle Debat</h4>
                <p class="text-gray-400">Arena debat karakter anime dengan argumen yang terstruktur dan fair</p>
              </div>
            </div>
          </div>
          
          <div class="glass rounded-2xl p-6 hover:bg-white/10 transition-all duration-300">
            <div class="flex items-start gap-4">
              <div class="text-3xl">📚</div>
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
            👥
          </div>
          <h3 class="text-2xl font-bold mb-4">Komunitas Aktif</h3>
          <p class="text-gray-400 leading-relaxed">
            Bergabung dengan ribuan pengguna aktif yang siap berdiskusi dan berbagi pengalaman setiap hari
          </p>
        </div>
        
        <!-- Feature 2 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="200">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            🏆
          </div>
          <h3 class="text-2xl font-bold mb-4">Sistem Rating</h3>
          <p class="text-gray-400 leading-relaxed">
            Dapatkan poin dan badge eksklusif dari kontribusi diskusi dan argumen terbaikmu
          </p>
        </div>
        
        <!-- Feature 3 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="300">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            🔒
          </div>
          <h3 class="text-2xl font-bold mb-4">Aman & Terpercaya</h3>
          <p class="text-gray-400 leading-relaxed">
            Platform dengan moderasi ketat untuk menciptakan lingkungan diskusi yang sehat dan positif
          </p>
        </div>
        
        <!-- Feature 4 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="400">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            📱
          </div>
          <h3 class="text-2xl font-bold mb-4">Mobile Friendly</h3>
          <p class="text-gray-400 leading-relaxed">
            Akses dari mana saja dan kapan saja dengan tampilan responsif di semua perangkat
          </p>
        </div>
        
        <!-- Feature 5 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="500">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            🎯
          </div>
          <h3 class="text-2xl font-bold mb-4">Topik Beragam</h3>
          <p class="text-gray-400 leading-relaxed">
            Dari shonen, seinen, hingga isekai - temukan diskusi tentang anime genre favoritmu
          </p>
        </div>
        
        <!-- Feature 6 -->
        <div class="glass rounded-3xl p-8 hover:bg-white/10 transition-all duration-300 hover:scale-105" data-aos="fade-up" data-aos-delay="600">
          <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center text-3xl mb-6">
            ⚡
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
          Daftar Gratis Sekarang →
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
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="#" class="w-10 h-10 rounded-lg glass hover:bg-white/10 flex items-center justify-center transition">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            <a href="#" class="w-10 h-10 rounded-lg glass hover:bg-white/10 flex items-center justify-center transition">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
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
            <li><a href="creators.php" class="hover:text-white transition">Kreator</a></li>
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