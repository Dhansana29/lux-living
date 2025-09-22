document.addEventListener('DOMContentLoaded', () => {

    // Initialize and add the map
    window.initMap = () => {
        const luxLiving = { lat: 34.052235, lng: -118.243683 }; // Example coordinates
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 14,
            center: luxLiving,
            gestureHandling: "cooperative", // Zoom with two fingers on mobile
        });

        const marker = new google.maps.Marker({
            position: luxLiving,
            map: map,
        });
    };

    // Handle Contact Form Submission
    const contactForm = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault()

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                formMessage.classList.remove('d-none', 'text-danger', 'text-success');
                if (data.status === 'success') {
                    formMessage.classList.add('text-success');
                    formMessage.textContent = 'Thank you! Your message has been sent successfully.';
                    contactForm.reset(); // Clear the form
                } else {
                    formMessage.classList.add('text-danger');
                    formMessage.textContent = data.message || 'An unexpected error occurred.';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                formMessage.classList.remove('d-none', 'text-danger', 'text-success');
                formMessage.classList.add('text-danger');
                formMessage.textContent = 'Failed to send message. Please try again later.';
            });
        });
    }

});