const carousel = document.querySelector('#carouselExampleFade');
    const dots = document.querySelectorAll('.carousel-controls .dot');

    carousel.addEventListener('slid.bs.carousel', function (event) {
    const index = Array.from(event.relatedTarget.parentNode.children).indexOf(event.relatedTarget);
    dots.forEach((dot, i) => {
        dot.style.backgroundColor = i === index ? '#0454a3' : 'white';
    });
});

// Add event listeners to dots
dots.forEach((dot) => {
    dot.addEventListener('click', (e) => {
        const index = e.target.getAttribute('data-bs-slide-to');
        const carouselInstance = bootstrap.Carousel.getInstance(carousel);
        carouselInstance.to(index);
    });
});


      /* Tentang Kami */
document.querySelectorAll('#aboutTab .nav-link').forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Mencegah aksi default dari link

        document.querySelectorAll('#aboutTab .nav-link').forEach(function(nav) {
            nav.style.backgroundColor = '';
            nav.style.color = '#0454a3';
        });

        link.style.backgroundColor = '#0454a3';
        link.style.color = 'white';
    });
});

//draggable hotline
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById("draggableBtn");
    const floatingBtn = document.querySelector('.floating-btn .btn-img');
    const icons = document.querySelectorAll('.floating-btn .social-icons .icon');
    let isDragging = false;
    let offsetY;
    let isOpen = false;
  
    // Fungsi untuk toggle icon pop-up
    function toggleIcons() {
      isOpen = !isOpen;
      const radius = 120; // Radius dari setengah lingkaran
      const totalIcons = icons.length;
  
      icons.forEach((icon, index) => {
        if (isOpen) {
          const angle = Math.PI / 2 + (Math.PI / (totalIcons - 1)) * index;
          const x = Math.cos(angle) * radius;
          const y = Math.sin(angle) * radius;
          icon.style.transform = `translate(${x}px, ${y}px)`;
          icon.style.opacity = '1';
        } else {
          icon.style.transform = 'translate(0, 0)';
          icon.style.opacity = '0';
        }
      });
    }
  
    // Fungsi untuk memulai drag
    function startDrag(e) {
      e.preventDefault();
      isDragging = true;
      offsetY = (e.clientY || e.touches[0].clientY) - btn.getBoundingClientRect().top;
      btn.style.cursor = 'grabbing';
    }
  
    // Fungsi untuk melakukan drag
    function drag(e) {
      if (isDragging) {
        const newYPosition = (e.clientY || e.touches[0].clientY) - offsetY;
        if (newYPosition >= 0 && (newYPosition + btn.offsetHeight) <= window.innerHeight) {
          btn.style.top = `${newYPosition}px`;
        }
      }
    }
  
    // Fungsi untuk mengakhiri drag
    function endDrag() {
      isDragging = false;
      btn.style.cursor = 'grab';
    }
  
    // Event listener untuk memulai drag (mouse atau touch)
    btn.addEventListener("mousedown", (e) => startDrag(e));
    btn.addEventListener("touchstart", (e) => startDrag(e));
  
    // Event listener untuk menangani drag
    document.addEventListener("mousemove", (e) => drag(e));
    document.addEventListener("touchmove", (e) => drag(e));
  
    // Event listener untuk mengakhiri drag
    document.addEventListener("mouseup", endDrag);
    document.addEventListener("touchend", endDrag);
  
    // Event listener untuk klik pada icon agar membuka/tutup icon
    floatingBtn.addEventListener('click', (e) => {
      // Cegah toggle saat sedang drag
      if (!isDragging) {
        toggleIcons();
      }
    });
  });
  

// ini buat hamburger open and close responsive
document.querySelector('.navbar-toggler').addEventListener('click', function() {
    var navbarCollapse = document.getElementById('navbarNav');
    navbarCollapse.classList.toggle('show');
});

document.querySelector('.close-btn').addEventListener('click', function() {
    var navbarCollapse = document.getElementById('navbarNav');
    navbarCollapse.classList.remove('show');
});

    const floatingBtn = document.querySelector('.floating-btn');
    const socialMenu = document.querySelector('.social-menu');

    floatingBtn.addEventListener('click', () => {
        socialMenu.classList.toggle('show');
    });