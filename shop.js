document.addEventListener('DOMContentLoaded', () => {
    // View Toggle Functionality
    const gridViewBtn = document.getElementById('grid-view');
    const listViewBtn = document.getElementById('list-view');
    const productsContainer = document.getElementById('products-container');

    if (gridViewBtn && listViewBtn && productsContainer) {
        gridViewBtn.addEventListener('click', () => {
            productsContainer.classList.remove('list-view');
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        });

        listViewBtn.addEventListener('click', () => {
            productsContainer.classList.add('list-view');
            listViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
        });
    }

    // Quick View Modal Functionality
    const quickViewModal = document.getElementById('quickViewModal');
    const quickViewContent = document.getElementById('quickViewContent');

    // Use event delegation for dynamically loaded content
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('quick-view') || e.target.closest('.quick-view')) {
            e.preventDefault();
            const button = e.target.classList.contains('quick-view') ? e.target : e.target.closest('.quick-view');
            const productId = button.getAttribute('data-product-id');
            
            // Show loading state
            quickViewContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading plant details...</p>
                </div>
            `;

            // Show modal
            const modal = new bootstrap.Modal(quickViewModal);
            modal.show();

            // Fetch product details
            fetch(`get_product_details.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        quickViewContent.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="${data.product.image_url}" alt="${data.product.product_name}" class="img-fluid rounded">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-3">${data.product.product_name}</h4>
                                    <div class="mb-3">
                                        <span class="badge bg-success">${data.product.category}</span>
                                    </div>
                                    <div class="product-rating mb-3">
                                        ${generateStars(data.product.rating)}
                                        <span class="rating-text">(${data.product.rating})</span>
                                    </div>
                                    <p class="text-muted mb-3">${data.product.description || 'No description available.'}</p>
                                    <div class="product-price mb-4">
                                        <h3 class="text-success">$${parseFloat(data.product.price).toFixed(2)}</h3>
                                    </div>
                                    <div class="product-actions">
                                        ${data.product.stock_quantity > 0 ? 
                                            `<button class="btn btn-success btn-lg add-to-cart" data-product-id="${data.product.product_id}">
                                                <i class="bi bi-bag-plus"></i> Add to Cart
                                            </button>` :
                                            `<button class="btn btn-secondary btn-lg" disabled>
                                                <i class="bi bi-x-circle"></i> Out of Stock
                                            </button>`
                                        }
                                        <button class="btn btn-outline-primary btn-lg add-to-wishlist" data-product-id="${data.product.product_id}">
                                            <i class="bi bi-heart"></i> 
                                        </button>
                                    </div>
                                    ${data.product.care_instructions ? `
                                        <div class="mt-4">
                                            <h6>Care Instructions:</h6>
                                            <p class="text-muted">${data.product.care_instructions}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    } else {
                        quickViewContent.innerHTML = `
                            <div class="text-center py-4">
                                <i class="bi bi-exclamation-triangle text-warning display-4"></i>
                                <h5 class="mt-3">Error Loading Product</h5>
                                <p class="text-muted">${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    quickViewContent.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-triangle text-danger display-4"></i>
                            <h5 class="mt-3">Error Loading Product</h5>
                            <p class="text-muted">Unable to load product details. Please try again.</p>
                        </div>
                    `;
                });
        }
    });

    // Auto-submit search form on input
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (e.target.value.length >= 3 || e.target.value.length === 0) {
                    e.target.closest('form').submit();
                }
            }, 500);
        });
    }

    // Price range validation
    const priceInputs = document.querySelectorAll('input[name="price_min"], input[name="price_max"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', (e) => {
            const value = parseFloat(e.target.value);
            if (isNaN(value) || value < 0) {
                e.target.value = e.target.name === 'price_min' ? '0' : '1000';
            }
        });
    });

    // Smooth scroll to products when filters are applied
    const filterForms = document.querySelectorAll('.search-form, .price-form, .sort-form');
    filterForms.forEach(form => {
        form.addEventListener('submit', () => {
            setTimeout(() => {
                const productsSection = document.querySelector('.products-grid');
                if (productsSection) {
                    productsSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }, 100);
        });
    });

    // Add to cart and wishlist functionality (inherited from main script.js)
    // These will work automatically since the buttons have the same classes

    // Load more functionality (for future pagination)
    const loadMoreBtn = document.getElementById('load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            // This would be implemented for pagination
            console.log('Load more functionality would be implemented here');
        });
    }

    // Product card hover effects
    const productCards = document.querySelectorAll('.shop-product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });

    // Filter sidebar toggle for mobile
    const filterToggle = document.createElement('button');
    filterToggle.className = 'btn btn-success d-lg-none mb-3 w-100';
    filterToggle.innerHTML = '<i class="bi bi-funnel"></i> Toggle Filters';
    
    const sidebar = document.querySelector('.shop-sidebar');
    if (sidebar && window.innerWidth < 992) {
        sidebar.style.display = 'none';
        sidebar.parentNode.insertBefore(filterToggle, sidebar);
        
        filterToggle.addEventListener('click', () => {
            if (sidebar.style.display === 'none') {
                sidebar.style.display = 'block';
                filterToggle.innerHTML = '<i class="bi bi-x"></i> Hide Filters';
            } else {
                sidebar.style.display = 'none';
                filterToggle.innerHTML = '<i class="bi bi-funnel"></i> Show Filters';
            }
        });
    }

    // Initialize tooltips for better UX
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add loading states to buttons
    const actionButtons = document.querySelectorAll('.add-to-cart, .add-to-wishlist');
    actionButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Adding...';
            button.disabled = true;
            
            // Re-enable after a delay (the main script will handle the actual action)
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        });
    });
});

// Helper function to generate star ratings
function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="bi bi-star-fill text-warning"></i>';
        } else if (i - 0.5 <= rating) {
            stars += '<i class="bi bi-star-half text-warning"></i>';
        } else {
            stars += '<i class="bi bi-star text-warning"></i>';
        }
    }
    return stars;
}

// Smooth animations for product cards
function animateProductCards() {
    const cards = document.querySelectorAll('.shop-product-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

// Initialize animations when page loads
document.addEventListener('DOMContentLoaded', animateProductCards);

// Search suggestions (optional enhancement)
function initSearchSuggestions() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;

    let suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'search-suggestions position-absolute bg-white border rounded shadow-sm';
    suggestionsContainer.style.display = 'none';
    suggestionsContainer.style.zIndex = '1000';
    suggestionsContainer.style.width = '100%';
    suggestionsContainer.style.top = '100%';
    suggestionsContainer.style.left = '0';

    searchInput.parentNode.style.position = 'relative';
    searchInput.parentNode.appendChild(suggestionsContainer);

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        if (query.length >= 2) {
            // This would fetch suggestions from the server
            // For now, we'll use a simple client-side approach
            const suggestions = ['Spider Plant', 'Monstera', 'Snake Plant', 'Fiddle Leaf Fig', 'Pothos'];
            const filtered = suggestions.filter(s => s.toLowerCase().includes(query.toLowerCase()));
            
            if (filtered.length > 0) {
                suggestionsContainer.innerHTML = filtered.map(suggestion => 
                    `<div class="suggestion-item p-2 border-bottom" style="cursor: pointer;">${suggestion}</div>`
                ).join('');
                suggestionsContainer.style.display = 'block';
            } else {
                suggestionsContainer.style.display = 'none';
            }
        } else {
            suggestionsContainer.style.display = 'none';
        }
    });

    // Handle suggestion clicks
    suggestionsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('suggestion-item')) {
            searchInput.value = e.target.textContent;
            suggestionsContainer.style.display = 'none';
            searchInput.closest('form').submit();
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

// Initialize search suggestions
document.addEventListener('DOMContentLoaded', initSearchSuggestions);
