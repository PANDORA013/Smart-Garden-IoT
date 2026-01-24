/*icon hotline*/
document.addEventListener('DOMContentLoaded', () => {
    const floatingBtn = document.querySelector('.floating-btn .btn-img');
    const icons = document.querySelectorAll('.floating-btn .social-icons .icon');
    let isOpen = false;
  
    floatingBtn.addEventListener('click', () => {
      isOpen = !isOpen;
      const radius = 120; // Radius of the semi-circle
      const totalIcons = icons.length;
  
      icons.forEach((icon, index) => {
        if (isOpen) {
          // Adjust angle for half-circle on the left side
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
  
    //ini javascript untuk mengatur interval dan auto fade pada carousel kontak

    document.addEventListener('DOMContentLoaded', function () {
      const carouselElement = document.querySelector('#carouselExampleFade');
      
      const carouselInstance = new bootstrap.Carousel(carouselElement, {
          interval: 2000,  // Durasi antar slide dalam milidetik (misal 2000 = 2 detik)
          ride: 'carousel'
      });
  });

  //fungsi untuk drag hotline
  const btn = document.getElementById("draggableBtn");
let isDragging = false;
let offsetY;

// Event listener ketika mouse ditekan pada tombol
btn.addEventListener("mousedown", (e) => {
    isDragging = true;
    offsetY = e.clientY - btn.getBoundingClientRect().top;
    btn.style.cursor = 'grabbing';
});

// Event listener untuk menangani mouse bergerak
document.addEventListener("mousemove", (e) => {
    if (isDragging) {
        const newYPosition = e.clientY - offsetY;
        // Mencegah tombol keluar dari batas atas atau bawah layar
        if (newYPosition >= 0 && (newYPosition + btn.offsetHeight) <= window.innerHeight) {
            btn.style.top = `${newYPosition}px`;
        }
    }
});

// Event listener untuk menangani mouse dilepas
document.addEventListener("mouseup", () => {
    isDragging = false;
    btn.style.cursor = 'grab';
});

     
      
  
  