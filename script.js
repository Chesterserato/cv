document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('profile_pic');
    const imagePreview = document.getElementById('img-preview');

    // 1. Terminal-Style Image Preview 
    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            if (!file.type.startsWith('image/')) {
                alert("ERR: Invalid file type. Requires image/jpeg or image/png.");
                this.value = ""; 
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("ERR: Payload too large. Limit is 2048 KB.");
                this.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                
                // Glitchy/Terminal reveal effect
                imagePreview.style.opacity = 0;
                let opacity = 0;
                let interval = setInterval(() => {
                    opacity += 0.2;
                    imagePreview.style.opacity = opacity;
                    if(opacity >= 1) clearInterval(interval);
                }, 50);
            };

            reader.readAsDataURL(file);
        }
    });

    // 2. Cyber Input Focus Effect
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            const label = input.previousElementSibling;
            if(label) label.style.color = 'var(--accent-green)';
        });
        
        input.addEventListener('blur', () => {
            const label = input.previousElementSibling;
            if(label) label.style.color = 'var(--accent-blue)';
        });
    });
});
