document.addEventListener('DOMContentLoaded', () => {
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // Stats animation (from index.php)
    const counters = document.querySelectorAll('.stats-section h2');
    if (counters.length > 0) {
        const options = {
            threshold: 0.5
        };
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const value = parseInt(target.getAttribute('data-target'));
                    const duration = 2000;
                    let startTimestamp = null;
                    
                    const step = (timestamp) => {
                        if (!startTimestamp) startTimestamp = timestamp;
                        const progress = timestamp - startTimestamp;
                        let current = Math.floor(progress / duration * value);

                        if (current > value) {
                            current = value;
                        }

                        if (value === 10000) {
                            target.innerText = (current / 1000).toFixed(0) + 'K+';
                            if (current >= 10000) {
                                target.innerText = '10K+';
                                observer.unobserve(entry.target);
                            }
                        } else if (value === 500) {
                            target.innerText = current + '+';
                            if (current >= 500) {
                                target.innerText = '500+';
                                observer.unobserve(entry.target);
                            }
                        } else {
                            target.innerText = current;
                            if (current >= 5) {
                                target.innerText = '5';
                                observer.unobserve(entry.target);
                            }
                        }
                        
                        if (progress < duration) {
                            window.requestAnimationFrame(step);
                        }
                    };
                    window.requestAnimationFrame(step);
                }
            });
        }, options);

        counters.forEach(counter => {
            observer.observe(counter);
        });
    }

    // Function to handle product actions (Add to Cart/Wishlist)
    const handleProductAction = (button, action) => {
        const productId = button.getAttribute('data-product-id');
        if (!productId) {
            console.error('Product ID not found for button:', button);
            return;
        }

        fetch('handle_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            console.log(data);
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    };

    // Add to Cart listener
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            handleProductAction(button, 'add_to_cart');
        });
    });

    // Add to Wishlist listener
    document.querySelectorAll('.add-to-wishlist').forEach(button => {
        button.addEventListener('click', () => {
            handleProductAction(button, 'add_to_wishlist');
        });
    });

    // Handle Review Submission
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(reviewForm);
            formData.append('action', 'add_review');
            
            fetch('handle_actions.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    // Refresh the reviews section
                    window.location.reload(); 
                }
            })
            .catch(error => {
                console.error('Error submitting review:', error);
                alert('An error occurred while submitting your review.');
            });
        });
    }

    // Cart Page Functionality (from cart.php)
    const cartContainer = document.getElementById('cart-container');
    if (cartContainer) {
        const cartSubtotal = document.getElementById('cart-subtotal');
        const cartTotal = document.getElementById('cart-total');
        const updateCartTotal = () => {
            let total = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.card-text').innerText.replace('$', ''));
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                total += price * quantity;
            });
            if(cartSubtotal && cartTotal) {
                cartSubtotal.innerText = `$${total.toFixed(2)}`;
                cartTotal.innerText = `$${total.toFixed(2)}`;
            }
        };
        const handleCartAction = (action, productId, quantity = null) => {
            fetch('handle_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&product_id=${productId}${quantity !== null ? `&quantity=${quantity}` : ''}`
            })
            .then(response => response.json())
            .then(data => {
                if (action === 'remove_item') {
                    location.reload();
                }
                updateCartTotal();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        };
        cartContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-increase') || e.target.closest('.btn-increase')) {
                const button = e.target.closest('.btn-increase');
                const productId = button.getAttribute('data-product-id');
                const item = button.closest('.cart-item');
                const input = item.querySelector('.quantity-input');
                const newQuantity = parseInt(input.value) + 1;
                input.value = newQuantity;
                const price = parseFloat(item.querySelector('.card-text').innerText.replace('$', ''));
                item.querySelector('.item-total').innerText = `$${(price * newQuantity).toFixed(2)}`;
                handleCartAction('update_quantity', productId, newQuantity);
            } else if (e.target.classList.contains('btn-decrease') || e.target.closest('.btn-decrease')) {
                const button = e.target.closest('.btn-decrease');
                const productId = button.getAttribute('data-product-id');
                const item = button.closest('.cart-item');
                const input = item.querySelector('.quantity-input');
                const currentQuantity = parseInt(input.value);
                if (currentQuantity > 1) {
                    const newQuantity = currentQuantity - 1;
                    input.value = newQuantity;
                    const price = parseFloat(item.querySelector('.card-text').innerText.replace('$', ''));
                    item.querySelector('.item-total').innerText = `$${(price * newQuantity).toFixed(2)}`;
                    handleCartAction('update_quantity', productId, newQuantity);
                }
            } else if (e.target.classList.contains('remove-item')) {
                const button = e.target;
                const productId = button.getAttribute('data-product-id');
                if (confirm("Are you sure you want to remove this item?")) {
                    handleCartAction('remove_item', productId);
                }
            }
        });
    }
});