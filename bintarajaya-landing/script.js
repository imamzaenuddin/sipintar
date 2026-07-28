let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const slider = document.getElementById('slider');
const totalSlides = slides.length;

function updateSlider() {
    // Geser container sebesar 100% dari lebar container per slide
    slider.style.transform = `translateX(-${currentSlide * 100}%)`;
}

function moveSlide(direction) {
    currentSlide += direction;
    
    // Loop kembali ke awal jika melewati batas
    if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    } else if (currentSlide >= totalSlides) {
        currentSlide = 0;
    }
    
    updateSlider();
}

// Auto-slide setiap 5 detik
setInterval(() => {
    moveSlide(1);
}, 5000);
