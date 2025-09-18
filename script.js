document.addEventListener('DOMContentLoaded', () => {
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    // Stats counter animation
    const counters = document.querySelectorAll('.stats-section h2');

    const options = {
        threshold: 0.5 // Trigger animation when 50% of the section is visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const value = parseInt(target.getAttribute('data-target'));
                const duration = 2000; // 2-second animation
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
});