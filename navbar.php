<nav class="sticky top-0 z-50 border-b border-slate-800 shadow-xl" style="font-size: 16px; background-color: #372e96;" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center">
        <a href="home.php" class="transition hover:opacity-80">
            <div class="flex items-center space-x-3">
                <img src="images/logo2.png" alt="ConfHub Logo" class="h-8 object-contain">
                <span class="text-white text-xl font-bold tracking-tight">ConfHub</span>
            </div> 
        </a>
        <div class="hidden md:flex items-center space-x-10 text-sm font-semibold tracking-wide uppercase">
            <a href="events.php" class="text-slate-300 hover:text-[#3fcacf] transition">Add Events</a>
            <a href="post.php" class="text-slate-300 hover:text-[#3fcacf] transition">Community</a>
            <a href="index.php" class="text-slate-300 hover:text-[#3fcacf] transition">Search</a>
            <div class="flex items-center space-x-4 border-l border-slate-700 pl-10">
                <?php if (isset($user) && $user): ?>
                    <a href="profile.php" class="text-[#3fcacf] transition hover:text-[#3fcacf]"><?= htmlspecialchars($user["name"]) ?></a>
                    <a href="logout.php" class="bg-[#878cff] text-white px-5 py-2.5 rounded-full hover:bg-[#6a70e0] transition">Log out</a>
                <?php else: ?>
                    <a href="login.php" class="text-slate-300 hover:text-[#3fcacf] transition">Log in</a>
                    <a href="signup.html" class="bg-[#3fcacf] text-slate-900 px-5 py-2.5 rounded-full hover:bg-[#3fcacf] transition">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
        <!-- Mobile Toggle -->
        <div class="md:hidden"> 
            <button @click="open = !open" class="text-slate-300 focus:outline-none">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
    <!-- Mobile Menu -->
    <div x-show="open" @click.away="open = false" class="md:hidden bg-slate-900 border-t border-slate-800 px-6 py-8 space-y-6">
         <a href="events.php" class="block text-slate-300 font-semibold uppercase tracking-wide hover:text-[#3fcacf] transition">Add Events</a>
         <a href="post.php" class="block text-slate-300 font-semibold uppercase tracking-wide hover:text-[#3fcacf] transition">Community</a>
         <a href="index.php" class="block text-slate-300 font-semibold uppercase tracking-wide hover:text-[#3fcacf] transition">Search</a>
         <hr class="border-slate-800">
         <?php if (isset($user) && $user): ?>
            <a href="profile.php" class="block text-[#3fcacf] font-bold hover:text-[#3fcacf] transition"><?= htmlspecialchars($user["name"]) ?></a>
            <a href="logout.php" class="block text-rose-400 font-medium">Log out</a>
         <?php else: ?>
            <a href="login.php" class="block text-slate-300 font-medium">Log in</a>
            <a href="signup.html" class="block text-emerald-400 font-bold">Sign up</a>
         <?php endif; ?>
    </div>
</nav>