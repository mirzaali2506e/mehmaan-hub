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

// Image upload preview with drag-and-drop and add-more support
(function() {
    var imageInput = document.getElementById('imageInput');
    var previewGrid = document.getElementById('imagePreviewGrid');
    var uploadArea = document.getElementById('imageUploadArea');
    if (!imageInput || !previewGrid) return;

    var selectedFiles = [];

    function isImageFile(file) {
        return file.type.startsWith('image/');
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        selectedFiles.forEach(function(file, idx) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.createElement('div');
                preview.className = 'image-preview';
                var img = document.createElement('img');
                img.src = e.target.result;
                var info = document.createElement('div');
                info.className = 'image-preview-info';
                info.textContent = file.name.length > 18 ? file.name.substr(0, 15) + '...' : file.name;
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-preview';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function(ev) {
                    ev.stopPropagation();
                    selectedFiles.splice(idx, 1);
                    syncToInput();
                    renderPreviews();
                });
                preview.appendChild(img);
                preview.appendChild(info);
                preview.appendChild(removeBtn);
                previewGrid.appendChild(preview);
            };
            reader.readAsDataURL(file);
        });
        var counter = document.getElementById('imageCountLabel');
        if (counter) {
            counter.textContent = selectedFiles.length === 0 ? '' : selectedFiles.length + ' image' + (selectedFiles.length === 1 ? '' : 's') + ' selected';
        }
    }

    function syncToInput() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function(f) { dt.items.add(f); });
        imageInput.files = dt.files;
    }

    imageInput.addEventListener('change', function() {
        var newFiles = Array.from(this.files);
        newFiles.forEach(function(f) {
            if (isImageFile(f) && selectedFiles.length < 10) selectedFiles.push(f);
        });
        syncToInput();
        renderPreviews();
    });

    if (uploadArea) {
        ['dragenter','dragover'].forEach(function(ev) {
            uploadArea.addEventListener(ev, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.add('drag-over');
            });
        });
        ['dragleave','drop'].forEach(function(ev) {
            uploadArea.addEventListener(ev, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('drag-over');
            });
        });
        uploadArea.addEventListener('drop', function(e) {
            var dropped = Array.from(e.dataTransfer.files);
            dropped.forEach(function(f) {
                if (isImageFile(f) && selectedFiles.length < 10) selectedFiles.push(f);
            });
            syncToInput();
            renderPreviews();
        });
    }
})();

// Notification bell toggle + mark read
function toggleNotifPanel(e) {
    e.stopPropagation();
    var panel = document.getElementById('notifPanel');
    if (panel) panel.classList.toggle('open');
}

document.addEventListener('click', function(e) {
    var wrap = document.getElementById('navNotification');
    var panel = document.getElementById('notifPanel');
    if (panel && wrap && !wrap.contains(e.target)) panel.classList.remove('open');
});

function markNotifsRead() {
    fetch(SITE_URL + '/api/mark-notifications-read.php', { method: 'POST', credentials: 'same-origin' })
        .then(function() {
            var badge = document.querySelector('.notif-badge');
            if (badge) badge.style.display = 'none';
        });
}

// Wishlist toggle
function toggleWishlist(e, propertyId) {
    e.preventDefault();
    e.stopPropagation();
    const btn = e.currentTarget;
    fetch(SITE_URL + '/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'property_id=' + propertyId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
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
            showWishlistToast(data.action);
        } else {
            showWishlistToast('error');
        }
    })
    .catch(function() {
        showWishlistToast('error');
    });
}

// Lightweight toast — no page redirect
function showWishlistToast(action) {
    var existing = document.getElementById('wishlistToast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.id = 'wishlistToast';
    toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#0F172A;color:#fff;padding:12px 24px;border-radius:10px;font-size:14px;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.2);opacity:0;transition:opacity .3s;';
    toast.innerHTML = (action === 'added' ? '<i class="fas fa-heart" style="color:#EF4444;margin-right:6px;"></i>Added to wishlist' : action === 'removed' ? '<i class="fas fa-check" style="color:#14B8A6;margin-right:6px;"></i>Removed from wishlist' : '<i class="fas fa-exclamation-circle" style="color:#F59E0B;margin-right:6px;"></i>Something went wrong');
    document.body.appendChild(toast);
    requestAnimationFrame(function() { toast.style.opacity = '1'; });
    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() { toast.remove(); }, 300);
    }, 2500);
}

// Gallery image change
function changeMainImage(thumb, src) {
    document.getElementById('mainGalleryImg').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}
