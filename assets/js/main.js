// Navbar scroll effect
(function() {
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
})();

// Mobile menu toggle
(function() {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    }
})();

// Auto-dismiss flash alerts
(function() {
    const alert = document.getElementById('flashAlert');
    if (alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translate(-50%, -20px)';
            alert.style.transition = 'all 0.3s ease';
            setTimeout(function() { alert.remove(); }, 300);
        }, 4000);
    }
})();

// Dashboard tabs
(function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        });
    });
})();

// Image upload preview
(function() {
    const imageInput = document.getElementById('imageInput');
    const previewGrid = document.getElementById('imagePreviewGrid');
    if (imageInput && previewGrid) {
        imageInput.addEventListener('change', function() {
            previewGrid.innerHTML = '';
            const files = Array.from(this.files);
            files.forEach(function(file, idx) {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.innerHTML = '<img src="' + e.target.result + '">' +
                        '<button type="button" class="remove-preview" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';
                    previewGrid.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });
        });
    }
})();

// Wishlist toggle
function toggleWishlist(e, propertyId) {
    e.preventDefault();
    e.stopPropagation();
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    fetch(SITE_URL + '/api/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: 'property_id=' + propertyId + '&csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            const btn = e.currentTarget;
            if (btn.classList.contains('wishlist-btn')) {
                btn.classList.toggle('active');
            } else {
                if (data.action === 'removed') {
                    btn.innerHTML = '<i class="fas fa-heart"></i> Add to Wishlist';
                    var card = btn.closest('.property-card');
                    if (card && window.location.pathname.includes('wishlist')) {
                        card.style.opacity = '0';
                        setTimeout(function() { card.remove(); }, 300);
                    }
                } else {
                    btn.innerHTML = '<i class="fas fa-heart"></i> Remove from Wishlist';
                }
            }
        } else {
            window.location.href = SITE_URL + '/login.php';
        }
    })
    .catch(function() {
        window.location.href = SITE_URL + '/login.php';
    });
}

// Gallery image change
function changeMainImage(thumb, src) {
    document.getElementById('mainGalleryImg').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}
