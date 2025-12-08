document.addEventListener('DOMContentLoaded', function() {
    // Progress Circle Animation
    document.querySelectorAll('.progress-circle').forEach(circle => {
        const progress = parseInt(circle.getAttribute('data-progress')) || 0;
        const path = circle.querySelector('.progress-bar');
        const text = circle.querySelector('.progress-text');
        const value = Math.min(progress, 100);

        const radius = 15.9155;
        const circumference = 2 * Math.PI * radius;

        path.style.strokeDasharray = `${circumference}`;
        path.style.strokeDashoffset = `${circumference - (value / 100) * circumference}`;

        text.textContent = value + '%';
    });
});