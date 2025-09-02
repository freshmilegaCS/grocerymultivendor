<!-- Required meta tags -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/x-icon" href="<?= base_url($settings['logo']) ?>" />

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&amp;display=swap" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet" href="<?= base_url('/assets/website/css/custom.css') ?>">
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-chubby/css/uicons-solid-chubby.css'>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-brands/css/uicons-brands.css'>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

<style>
    /* Minimal custom CSS for animations */
    .animate-bounce {
        animation: bounce 1.5s infinite ease-in-out;
    }
    
    .fadeInDrop {
        opacity: 0;
        animation: fadeInDrop 1.5s infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50% { transform: translateX(-50%) translateY(-0.75rem); }
    }
    
    @keyframes fadeInDrop {
        0% { opacity: 0; transform: translateY(1rem); }
        20%, 80% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-1rem); }
    }
    
    .fade-out {
        animation: fadeOut 0.5s ease forwards;
    }
    
    @keyframes fadeOut {
        to { opacity: 0; visibility: hidden; }
    }
</style>