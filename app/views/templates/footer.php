</div> <!-- End min-vh-100 container -->

<!-- Premium Soft Footer -->
<footer class="mt-5 pt-5 pb-3" style="background-color: var(--color-surface-soft); border-top: 1px solid var(--color-border);">
    <div class="container">
        <div class="row g-5 mb-5">
            <div class="col-lg-4" data-aos="fade-up">
                <a class="navbar-brand fw-bold fs-3 d-flex align-items-center mb-3" href="<?= BASEURL; ?>" style="letter-spacing: 1.5px;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:40px; height:40px; background:var(--color-primary); color:var(--color-accent);">
                        <i class="bi bi-gem"></i>
                    </div>
                    <span>NNACO<span style="color:var(--color-taupe); font-weight:300;">SA</span></span>
                </a>
                <p class="text-muted pe-lg-4 lh-lg" style="font-size: 15px;">Destinasi utama untuk perhiasan dan aksesoris fashion eksklusif. Temukan gaya anggun dan kecantikan abadimu dengan koleksi terbaik kami.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="bi bi-tiktok"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="bi bi-pinterest"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-6" data-aos="fade-up" data-aos-delay="100">
                <h5 class="fw-bold mb-4">Belanja</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 text-muted">
                    <li><a href="<?= BASEURL; ?>/catalog" class="text-decoration-none text-reset hover-primary">Semua Produk</a></li>
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">Koleksi Baru</a></li>
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">Best Seller</a></li>
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">Promo Diskon</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-6" data-aos="fade-up" data-aos-delay="200">
                <h5 class="fw-bold mb-4">Layanan</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 text-muted">
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">Cara Belanja</a></li>
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">Lacak Pesanan</a></li>
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">Garansi & Retur</a></li>
                    <li><a href="#" class="text-decoration-none text-reset hover-primary">FAQ</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <h5 class="fw-bold mb-4">Daftar Newsletter</h5>
                <p class="text-muted mb-4" style="font-size: 15px;">Jadilah yang pertama tahu tentang promo eksklusif dan koleksi terbaru kami.</p>
                <form class="d-flex shadow-sm rounded-pill p-1 border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                    <input type="email" class="form-control border-0 bg-transparent shadow-none ms-2" placeholder="Masukkan Email..." required style="color:var(--color-text);">
                    <button class="btn btn-primary-soft rounded-pill px-4 m-1" type="button">Subscribe</button>
                </form>
            </div>
        </div>
        
        <hr class="border-secondary opacity-25">
        <div class="text-center text-muted small py-2 d-flex justify-content-between flex-wrap">
            <span>&copy; <?= date('Y'); ?> NNACO SA. Hak Cipta Dilindungi.</span>
            <span>Crafted with <i class="bi bi-heart-fill text-danger mx-1"></i> for elegance.</span>
        </div>
    </div>
</footer>

<!-- AI CHATBOT WIDGET -->
<div id="aiChatbotBtn" class="shadow-lg" style="position:fixed; bottom:30px; right:30px; width:65px; height:65px; border-radius:50%; background:var(--color-primary); color:var(--color-accent); border: 2px solid var(--color-accent); display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:9999; font-size:26px; transition:transform 0.3s;" data-aos="zoom-in" data-aos-delay="500">
    <i class="bi bi-stars"></i>
</div>

<div id="aiChatbotWindow" class="card shadow-lg border-0 d-none" style="position:fixed; bottom:110px; right:30px; width:360px; max-width:90vw; z-index:9999; border-radius:20px; overflow:hidden; transition:all 0.3s; background:var(--color-surface); border:1px solid var(--color-border) !important;">
    <div class="card-header d-flex justify-content-between align-items-center py-3 border-0" style="background:var(--color-primary); color:#FFFFFF;">
        <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-2 shadow-sm" style="width:34px; height:34px; font-size:16px; background:var(--color-accent); color:var(--color-primary);"><i class="bi bi-robot"></i></div>
            <h6 class="mb-0 fw-bold text-white">NNACO Assistant</h6>
        </div>
        <button class="btn-close btn-close-white shadow-none" id="closeChatbot"></button>
    </div>
    
    <div class="card-body" id="chatboxBody" style="height:350px; overflow-y:auto; background:var(--color-surface-soft); scroll-behavior: smooth;">
        <div class="text-start mb-3">
            <span class="badge p-3 shadow-sm text-wrap" style="background:var(--color-surface); color:var(--color-text); border:1px solid var(--color-border); border-radius:15px 15px 15px 0; max-width:85%; font-weight:normal; text-align:left; font-size:14px; line-height:1.5;">
                Halo<?= isset($_SESSION['user_name']) ? ' ' . htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : ' cantik' ?>! 👋 Saya AI Assistant dari NNACO SA. Ada yang bisa saya bantu terkait produk, promo, atau pesanan Anda hari ini?
            </span>
        </div>
    </div>
    
    <div class="card-footer p-2" style="background:var(--color-surface); border-top:1px solid var(--color-border);">
        <form id="chatForm" class="d-flex rounded-pill p-1 border" style="background:var(--color-surface-soft); border-color:var(--color-border) !important;">
            <input type="text" id="chatInput" class="form-control border-0 bg-transparent shadow-none px-3" placeholder="Tanyakan sesuatu..." required autocomplete="off" style="color:var(--color-text);">
            <button type="submit" class="btn btn-primary-soft rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; padding:0;"><i class="bi bi-send-fill" style="margin-left:-2px"></i></button>
        </form>
    </div>
</div>

<style>
    @keyframes chatMsgIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .chat-msg-animate {
        animation: chatMsgIn 0.25s ease-out forwards;
    }
    #chatboxBody::-webkit-scrollbar {
        width: 6px;
    }
    #chatboxBody::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.15);
        border-radius: 4px;
    }
</style>

<!-- JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize Animations
    AOS.init({ once: true, offset: 100 });

    // Chatbot Logic
    const btn = document.getElementById('aiChatbotBtn');
    const win = document.getElementById('aiChatbotWindow');
    const close = document.getElementById('closeChatbot');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const body = document.getElementById('chatboxBody');

    btn.addEventListener('click', () => { 
        win.classList.toggle('d-none'); 
        btn.style.transform = win.classList.contains('d-none') ? 'scale(1)' : 'scale(0.9)';
        if(!win.classList.contains('d-none')) {
            input.focus();
            scrollToBottom();
        }
    });
    close.addEventListener('click', () => { 
        win.classList.add('d-none');
        btn.style.transform = 'scale(1)';
    });

    function scrollToBottom() {
        setTimeout(() => {
            body.scrollTop = body.scrollHeight;
        }, 40);
    }

    function formatChatMsg(text, isUser) {
        if (!text) return '';
        if (isUser) {
            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');
        }
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/(?:^|\n)[*-]\s+/g, '<br>• ')
            .replace(/\n/g, '<br>');
    }

    function addMsg(msg, isUser=true) {
        const align = isUser ? 'text-end' : 'text-start';
        const bg = isUser ? 'background:var(--color-accent); color:var(--color-primary); font-weight:500;' : 'background:var(--color-surface); color:var(--color-text); border:1px solid var(--color-border);';
        const radius = isUser ? '15px 15px 0 15px' : '15px 15px 15px 0';
        
        const wrapper = document.createElement('div');
        wrapper.className = `${align} mb-3 chat-msg-animate`;
        wrapper.innerHTML = `
            <span class="badge p-3 shadow-sm text-wrap" style="${bg} border-radius:${radius}; max-width:85%; font-weight:normal; text-align:left; font-size:14px; line-height:1.5;">
                ${formatChatMsg(msg, isUser)}
            </span>`;
        body.appendChild(wrapper);
        scrollToBottom();
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = input.value.trim();
        if(!msg) return;
        
        addMsg(msg, true);
        input.value = '';
        input.disabled = true;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        
        const loadingId = Date.now();
        const loader = document.createElement('div');
        loader.className = 'text-start mb-3 chat-msg-animate';
        loader.id = `load-${loadingId}`;
        loader.innerHTML = `
            <span class="badge text-muted p-3 shadow-sm" style="background:var(--surface); border-radius:15px 15px 15px 0;">
                <span class="spinner-grow spinner-grow-sm" role="status"></span> Berpikir...
            </span>`;
        body.appendChild(loader);
        scrollToBottom();

        const activeConvId = sessionStorage.getItem('nnaco_chat_conv_id') || '';

        fetch('<?= BASEURL ?>/ai/chat_api', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                message: msg,
                conversation_id: activeConvId
            })
        })
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById(`load-${loadingId}`);
            if (el) el.remove();
            if (data.conversation_id) {
                sessionStorage.setItem('nnaco_chat_conv_id', data.conversation_id);
            }
            addMsg(data.reply || 'Maaf, terjadi kesalahan.', false);
        })
        .catch(() => {
            const el = document.getElementById(`load-${loadingId}`);
            if (el) el.remove();
            addMsg('Koneksi terputus.', false);
        })
        .finally(() => {
            input.disabled = false;
            if (submitBtn) submitBtn.disabled = false;
            input.focus();
            scrollToBottom();
        });
    });

    // Dark Mode Toggle Logic
    const themeToggle = document.getElementById('themeToggle');
    const htmlTag = document.documentElement;
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Set initial theme
    htmlTag.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
        let theme = htmlTag.getAttribute('data-theme');
        let newTheme = theme === 'light' ? 'dark' : 'light';
        htmlTag.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateIcon(newTheme);
    });

    function updateIcon(theme) {
        const icon = themeToggle.querySelector('i');
        if(theme === 'dark') {
            icon.classList.remove('bi-moon-stars-fill');
            icon.classList.add('bi-sun-fill', 'text-warning');
        } else {
            icon.classList.remove('bi-sun-fill', 'text-warning');
            icon.classList.add('bi-moon-stars-fill');
        }
    }

    // Wishlist AJAX Logic
    document.querySelectorAll('.btn-wishlist').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            <?php if(!isset($_SESSION['user_id'])): ?>
                alert('Silakan login terlebih dahulu untuk menyimpan wishlist!');
                window.location.href = '<?= BASEURL; ?>/auth/login';
                return;
            <?php else: ?>
            const productId = this.getAttribute('data-id');
            const icon = this.querySelector('i');
            
            // Animasi klik
            icon.style.transform = 'scale(1.3)';
            setTimeout(() => icon.style.transform = 'scale(1)', 200);

            fetch('<?= BASEURL; ?>/wishlist/toggle', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    if(data.action === 'added') {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        this.classList.add('active');
                    } else {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        this.classList.remove('active');
                        
                        // Jika ada di halaman wishlist, reload atau hilangkan item
                        if(window.location.href.includes('wishlist')) {
                            this.closest('.col-lg-3, .col-md-4').style.display = 'none';
                        }
                    }
                }
            })
            .catch(err => console.error(err));
            <?php endif; ?>
        });
    });
</script>


</body>
</html>
