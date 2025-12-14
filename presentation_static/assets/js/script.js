// Persistent Logic (Runs once lang pag-load ng site)
function initPersistent() {
    console.log("Footporium Loaded - Persistent Init");

    // Navbar Scroll Effect (Global Listener)
    // Change navbar color pag nag-scroll down
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

    // Theme Toggle Logic
    const body = document.documentElement;

    function updateIcons(theme) {
        document.querySelectorAll('.theme-toggle-btn i').forEach(icon => {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
    }

    // Initialize Theme
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        body.setAttribute('data-theme', 'dark');
        updateIcons('dark');
    }

    // Add listeners to theme buttons (Nav is persistent)
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (body.hasAttribute('data-theme')) {
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                updateIcons('light');
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                updateIcons('dark');
            }
        });
    });

    // Cart Page Interactions (Delegation - Body is persistent)
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.cart-update-btn') || e.target.closest('.cart-remove-btn')) {
            const btn = e.target.closest('.btn');
            const cartItem = btn.closest('.cart-item');
            const productId = cartItem.dataset.id;
            let action = '';

            if (btn.classList.contains('cart-remove-btn')) {
                action = 'remove';
                if (!confirm('Are you sure?')) return;
            } else {
                action = btn.dataset.action;
            }

            const formData = new FormData();
            formData.append('action', action);
            formData.append('product_id', productId);

            fetch('actions/update_cart_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update Badges
                        const badges = document.querySelectorAll('.badge.bg-danger');
                        badges.forEach(b => {
                            b.innerText = data.cart_count;
                            b.style.display = data.cart_count > 0 ? 'block' : 'none';
                        });

                        // Update Total
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) totalEl.innerText = data.cart_total;

                        // Update UI Item
                        if (action === 'remove') {
                            cartItem.remove();
                            if (data.cart_empty) location.reload();
                        } else {
                            const input = cartItem.querySelector('.quantity-input');
                            let currentQty = parseInt(input.value);
                            if (action === 'increase') input.value = currentQty + 1;
                            if (action === 'decrease') {
                                if (currentQty > 1) input.value = currentQty - 1;
                                else cartItem.remove();
                            }
                        }
                    }
                });
        }
    });

    // Initialize Barba
    // Makes the site fast (SPA feel) without full refresh
    if (typeof barba !== 'undefined') {
        barba.init({
            prevent: ({ el }) => el.classList.contains('no-barba') || el.closest('a').href.includes('/admin/') || el.closest('a').href.includes('logout.php'),
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

        barba.hooks.after(() => {
            initDynamic();
            // Scroll to top
            window.scrollTo(0, 0);

            // Update active nav link
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

// Dynamic Logic (Runs on every page switch/transition)
function initDynamic() {
    console.log("Footporium Page Init - Dynamic");

    // Scroll Animation
    // Show elements animation when scrolling
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // Add click event to "Add to Cart" buttons (These are re-rendered)
    const addButtons = document.querySelectorAll('.add-to-cart-btn');

    addButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default submission

            const form = this.closest('form');
            if (!form) return;

            const formData = new FormData(form);
            formData.append('ajax', '1');

            // 1. Button Loading State
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
                        // 2. Success Feedback
                        this.innerHTML = '<i class="fas fa-check"></i> Added';
                        this.classList.add('btn-success');
                        this.classList.remove('btn-primary-custom');

                        // 3. Update Badge
                        // Update all badges (mobile/desktop)
                        const badges = document.querySelectorAll('.badge.bg-danger');
                        badges.forEach(b => {
                            b.innerText = data.cart_count;
                            b.style.display = 'block';
                        });

                        // 4. Fly to Cart Animation
                        const cartIcon = document.querySelector('.fa-shopping-cart'); // Target first one
                        if (cartIcon) {
                            const productCard = this.closest('.product-card') || this.closest('.col-md-6'); // Support detail page too
                            const productImg = productCard ? productCard.querySelector('img') : null;

                            if (productImg) {
                                const imgClone = productImg.cloneNode(true);
                                const imgRect = productImg.getBoundingClientRect();
                                const cartRect = cartIcon.getBoundingClientRect();

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

                        // SweetAlert Toast
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

                        // Reset Button
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
}

// Master Init
document.addEventListener('DOMContentLoaded', function () {
    initPersistent();
    initDynamic();
});


