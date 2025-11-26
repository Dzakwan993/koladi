<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Nunito:wght@400;600&display=swap');

    .swal-custom-popup {
        border: 1.8px solid #2f8cff;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(47, 140, 255, 0.25);
        padding: 1.7rem;
        backdrop-filter: blur(10px);
        transform: scale(0.95);
    }

    .swal-custom-title {
        font-family: 'Poppins', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 0.4rem;
    }

    .swal-custom-text {
        font-family: 'Nunito', sans-serif;
        font-size: 1.05rem;
        color: #2b2b2b;
    }

    .swal2-timer-progress-bar {
        background: linear-gradient(90deg, #007bff, #33b3ff);
        height: 4px;
        border-radius: 10px;
    }

    .swal-fade-in {
        animation: fadeInSmooth 0.45s ease-out forwards;
    }

    .swal-fade-out {
        animation: fadeOutSmooth 0.45s ease-in forwards;
    }

    @keyframes fadeInSmooth {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes fadeOutSmooth {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.92);
        }
    }
</style>
