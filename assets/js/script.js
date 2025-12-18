// Persistent Logic (Tumatakbo ng isang beses lang pag-load ng site, hindi paulit-ulit)
function initPersistent() {
    console.log("Footporium Loaded - Persistent Init");

    // Navbar Scroll Effect (Global Listener)
    // Palitan ang kulay ng navbar kapag nag-scroll pababa (nagiging transparent/glass effect)
    window.addEventListener('scroll', function () {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    // Theme Toggle Logic: Logic para sa pagpapalit ng theme (Dark/Light Mode)
    const body = document.documentElement;

    function updateIcons(theme) {
        // Palitan ang icon (Araw kung dark mode, Buwan kung light mode)
        document.querySelectorAll('.theme-toggle-btn i').forEach(icon => {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
    }

    // Initialize Theme (Kung ano ang huling sinet ng user, i-apply agad)
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        body.setAttribute('data-theme', 'dark');
        updateIcons('dark');
    }

    // Add listeners to theme buttons (Pindutan para magpalit ng mode)
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (body.hasAttribute('data-theme')) {
                // Switch to Light Mode
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                updateIcons('light');
            } else {
                // Switch to Dark Mode
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                updateIcons('dark');
            }
        });
    });

    // Cart Page Interactions
    // Gamit ang delegation para gumana kahit magbago ang content ng cart dynamically
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.cart-update-btn') || e.target.closest('.cart-remove-btn')) {
            const btn = e.target.closest('.btn');
            const cartItem = btn.closest('.cart-item');
            const productId = cartItem.dataset.id;

            // Tukuyin kung anong action ang gagawin (remove, increase, o decrease)
            if (btn.classList.contains('cart-remove-btn')) {
                action = 'remove';
            } else {
                action = btn.dataset.action;
            }

            // Ihanda ang data para sa request
            const formData = new FormData();
            formData.append('action', action);
            formData.append('product_id', productId);

            // Fetch request sa server
            fetch('actions/update_cart_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Success: I-update ang Cart Count Badge sa header
                        const cartBadge = document.getElementById('cart-badge');
                        if (cartBadge) {
                            cartBadge.innerText = data.cart_count;
                            cartBadge.style.display = data.cart_count > 0 ? 'block' : 'none';
                        }

                        // Siguraduhin ding updated ang ibang badges kung meron
                        const otherBadges = document.querySelectorAll('.badge.bg-danger');
                        otherBadges.forEach(b => {
                            if (b.id !== 'cart-badge') {
                                b.innerText = data.cart_count;
                                b.style.display = data.cart_count > 0 ? 'block' : 'none';
                            }
                        });


                        // I-update ang Total Amount na nakadisplay sa page
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) totalEl.innerText = data.cart_total;

                        // I-update ang itsura sa cart (pag tinanggal o binawasan)
                        if (action === 'remove') {
                            // Animation: Mag-fade out effect muna bago mawala
                            cartItem.classList.add('fade-out');

                            // Tanggalin sa DOM pagkatapos ng animation
                            setTimeout(() => {
                                cartItem.remove();
                                if (data.cart_empty) location.reload(); // Refresh kung empty na
                            }, 500);

                        } else {
                            // Kung increase/decrease, update lang ang input value
                            const input = cartItem.querySelector('.quantity-input');
                            let currentQty = parseInt(input.value);
                            if (action === 'increase') input.value = currentQty + 1;
                            if (action === 'decrease') {
                                if (currentQty > 1) {
                                    input.value = currentQty - 1;
                                } else {
                                    // Kapag binawasan hanggang 0, tanggalin na parang ni-remove
                                    cartItem.classList.add('fade-out');
                                    setTimeout(() => {
                                        cartItem.remove();
                                        if (data.cart_empty) location.reload();
                                    }, 500);
                                }
                            }
                        }
                    }
                });
        }
    });



    // Audio Persistence (Tuloy-tuloy na tugtog kahit lumipat ng page)
    const audio = document.getElementById("bgMusic");
    if (audio) {
        audio.volume = 0.5;

        // I-restore ang dating oras kung saan tumigil
        const savedTime = localStorage.getItem('bgm_time');
        const shouldPlay = localStorage.getItem('bgm_playing');

        if (savedTime) {
            audio.currentTime = parseFloat(savedTime);
        }

        const playAudio = () => {
            // Subukang i-play ang audio
            audio.play().then(() => {
                localStorage.setItem('bgm_playing', 'true');
            }).catch(e => console.log("Audio waiting for interaction"));
        };

        if (shouldPlay === 'true') {
            playAudio();
        } else {
            // Mag-play lang kapag may interaction na (click) ang user para di autoplay violation
            const startOnce = () => {
                playAudio();
                document.removeEventListener('click', startOnce);
            };
            document.addEventListener('click', startOnce);
        }

        // I-save ang oras kada segundo para sa "resume" functionality
        setInterval(() => {
            if (!audio.paused) {
                localStorage.setItem('bgm_time', audio.currentTime);
                localStorage.setItem('bgm_playing', 'true');
            }
        }, 1000);

        // I-save din bago umalis sa page
        window.addEventListener('beforeunload', () => {
            localStorage.setItem('bgm_time', audio.currentTime);
        });
    }

    // Initialize Barba.js (Para mabilis ang loading na parang Single Page App)
    if (typeof barba !== 'undefined') {
        barba.init({
            prevent: ({ el }) => {
                const href = el.closest('a').href;
                // Huwag gamitin ang Barba sa mga sumusunod na pages:
                return el.classList.contains('no-barba') ||
                    href.includes('/admin/') ||
                    href.includes('logout.php') ||
                    href.includes('my_orders.php') || // Force reload para fresh data
                    href.includes('order_details.php') || // Force reload
                    href.includes('cart.php') || // Force reload
                    href.includes('checkout.php'); // Force reload
            },
            debug: true,
            transitions: [{
                name: 'opacity-transition',
                leave(data) {
                    return new Promise(resolve => {
                        data.current.container.style.opacity = 0;
                        data.current.container.style.transition = 'opacity 0.4s';
                        setTimeout(() => resolve(), 400);
                    });
                },
                enter(data) {
                    data.next.container.style.opacity = 0;
                    // Force reflow
                    data.next.container.offsetHeight;
                    data.next.container.style.transition = 'opacity 0.4s';
                    data.next.container.style.opacity = 1;
                }
            }]
        });

        barba.hooks.before(() => {
            // Linisin ang cache para hindi lumabas ang lumang data
            if (barba.cache) {
                barba.cache.clear();
            }
        });

        barba.hooks.after(() => {
            initDynamic(); // I-run ulit ang dynamic scripts (tulad ng buttons)
            // Scroll sa taas paglipat ng page
            window.scrollTo(0, 0);

            // Update active nav link (highlight kung nasaang page ka)
            const currentPath = window.location.pathname.split('/').pop();
            document.querySelectorAll('.nav-link').forEach(link => {
                const linkPath = link.getAttribute('href');
                if (linkPath === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    }
}

// Dynamic Logic (Tumatakbo kada lipat ng page, kailangan i-reinitialize)
function initDynamic() {
    console.log("Footporium Page Init - Dynamic");

    // Scroll Animation - Optimized
    // Gamit ang IntersectionObserver para malaman kung kita na element sa screen
    if (window.scrollObserver) {
        window.scrollObserver.disconnect();
    } else {
        const observerOptions = {
            threshold: 0.1
        };
        window.scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);
    }

    document.querySelectorAll('.reveal').forEach(el => window.scrollObserver.observe(el));

    // Add click event sa mga "Add to Cart" buttons
    const addButtons = document.querySelectorAll('.add-to-cart-btn');

    addButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // Huwag hayaang mag-refresh ang page

            const form = this.closest('form');
            if (!form) return;

            const formData = new FormData(form);
            formData.append('ajax', '1');

            // 1. Gawing "Loading..." ang button (Feedback sa user)
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            this.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 2. Ipakita ang "Added" message
                        this.innerHTML = '<i class="fas fa-check"></i> Added';
                        this.classList.add('btn-success');
                        this.classList.remove('btn-primary-custom');

                        // 3. I-update ang Cart Badge value
                        const cartBadge = document.getElementById('cart-badge');
                        if (cartBadge) {
                            cartBadge.innerText = data.cart_count;
                            cartBadge.style.display = 'block';
                        }

                        // Redundancy check para sa ibang badges
                        const badges = document.querySelectorAll('.badge.bg-danger');
                        badges.forEach(b => {
                            if (b.id !== 'cart-badge') {
                                b.innerText = data.cart_count;
                                b.style.display = 'block';
                            }
                        });

                        // 4. "Fly to Cart" Animation (Lumalipad na image papuntang cart icon)
                        const cartIcon = document.querySelector('.fa-shopping-cart'); // Targetin ang cart icon
                        if (cartIcon) {
                            const productCard = this.closest('.product-card') || this.closest('.col-md-6');
                            const productImg = productCard ? productCard.querySelector('img') : null;

                            if (productImg) {
                                const imgClone = productImg.cloneNode(true); // Kopyahin ang image
                                const imgRect = productImg.getBoundingClientRect();
                                const cartRect = cartIcon.getBoundingClientRect();

                                // Set initial position (kung nasaan ang original image)
                                imgClone.style.position = 'fixed';
                                imgClone.style.top = imgRect.top + 'px';
                                imgClone.style.left = imgRect.left + 'px';
                                imgClone.style.width = imgRect.width + 'px';
                                imgClone.style.height = imgRect.height + 'px';
                                imgClone.style.opacity = '0.8';
                                imgClone.style.zIndex = '1000';
                                imgClone.style.transition = 'all 0.8s ease-in-out';
                                imgClone.style.borderRadius = '50%';
                                imgClone.style.pointerEvents = 'none';

                                document.body.appendChild(imgClone);

                                // Simulan ang animation papunta sa cart
                                setTimeout(() => {
                                    imgClone.style.top = cartRect.top + 'px';
                                    imgClone.style.left = cartRect.left + 'px';
                                    imgClone.style.width = '20px';
                                    imgClone.style.height = '20px';
                                    imgClone.style.opacity = '0';
                                }, 10);

                                setTimeout(() => imgClone.remove(), 800);
                            }
                        }

                        // Ipakita ang Toast message (SweetAlert notification sa gilid)
                        if (typeof Swal !== 'undefined') {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Added to cart successfully'
                            });
                        }

                        // Ibalik ang button sa dati pagkatapos ng ilang segundo
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-primary-custom');
                            this.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = originalText;
                    this.disabled = false;
                    alert('Something went wrong. Please try again.');
                });
        });
    });

    // Product Details Quantity Logic (Plus/Minus buttons functionality)
    const qtyInput = document.getElementById('quantity');
    const qtyMinus = document.querySelector('.qty-decrease');
    const qtyPlus = document.querySelector('.qty-increase');

    if (qtyInput && qtyMinus && qtyPlus) {
        qtyMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
        qtyPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            qtyInput.value = val + 1;
        });
    }

    // Checkout Form Handler: Pagpindot ng "Place Order"
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            // Loading State (Ipakita na nagpoproseso)
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
            btn.disabled = true;

            // SweetAlert Loading
            Swal.fire({
                title: 'Processing Order',
                text: 'Please wait while we secure your feet...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('actions/place_order_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Success Redirect: Papunta sa success page
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Placed!',
                            text: 'Redirecting to confirmation...',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'success.php?order_id=' + data.order_id;
                        });
                    } else {
                        // Error handling
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Something went wrong.'
                        });
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Failed to communicate with the server. Please try again.'
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    }

    // Profile Edit Logic (Added for Barba support)
    const editBtn = document.getElementById('edit-profile-btn');
    if (editBtn) {
        const form = document.getElementById('profileForm');
        const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="file"])');
        const editFlag = document.getElementById('is_edit_mode');
        let isEditing = false;

        editBtn.addEventListener('click', function (e) {
            e.preventDefault();

            if (!isEditing) {
                // Switch to Edit Mode (Pwede na mag-type at mag-edit)
                isEditing = true;
                inputs.forEach(input => input.disabled = false);
                if (editFlag) editFlag.disabled = false;

                editBtn.innerText = 'Save Changes';
                editBtn.classList.remove('btn-outline-primary');
                editBtn.classList.add('btn-primary');

                if (inputs.length > 0) inputs[0].focus();
            } else {
                // Save Changes (Submit form kapag nasa edit mode na)
                inputs.forEach(input => input.disabled = false);
                if (editFlag) editFlag.disabled = false;
                form.submit();
            }
        });
    }
}

// Master Init (Simulan ang lahat pag-load ng website)
document.addEventListener('DOMContentLoaded', function () {
    initPersistent();
    initDynamic();
});

// Password Toggle Logic (Global para gumana kahit mag-Barba transition, para sa mata icon)
window.togglePassword = function (inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
};
