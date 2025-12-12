document.addEventListener('DOMContentLoaded', function () {
    console.log("Footporium Loaded!");

    // Add click event ONLY to "Add to Cart" buttons
    const addButtons = document.querySelectorAll('.add-to-cart-btn');

    addButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default if it's inside a form or link

            // 1. Button Animation
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Added';
            this.classList.add('btn-success');
            this.classList.remove('btn-primary-custom');

            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('btn-success');
                this.classList.add('btn-primary-custom');
            }, 2000);

            // 2. Fly to Cart Animation
            const cartIcon = document.querySelector('.fa-shopping-cart');
            if (cartIcon) {
                // Find the product image
                const productCard = this.closest('.product-card');
                const productImg = productCard.querySelector('img');

                if (productImg) {
                    const imgClone = productImg.cloneNode(true);
                    const imgRect = productImg.getBoundingClientRect();
                    const cartRect = cartIcon.getBoundingClientRect();

                    // Style the clone
                    imgClone.style.position = 'fixed';
                    imgClone.style.top = imgRect.top + 'px';
                    imgClone.style.left = imgRect.left + 'px';
                    imgClone.style.width = imgRect.width + 'px';
                    imgClone.style.height = imgRect.height + 'px';
                    imgClone.style.opacity = '0.8';
                    imgClone.style.zIndex = '1000';
                    imgClone.style.transition = 'all 0.8s ease-in-out';
                    imgClone.style.borderRadius = '50%';

                    document.body.appendChild(imgClone);

                    // Trigger animation
                    setTimeout(() => {
                        imgClone.style.top = cartRect.top + 'px';
                        imgClone.style.left = cartRect.left + 'px';
                        imgClone.style.width = '20px';
                        imgClone.style.height = '20px';
                        imgClone.style.opacity = '0';
                    }, 10);

                    // Remove clone after animation
                    setTimeout(() => {
                        imgClone.remove();
                    }, 800);
                }
            }
        });
    });
});
