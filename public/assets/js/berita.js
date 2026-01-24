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

  /* tombol floating drag */
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

  
  //isi berita
  $(document).ready(function(){
    var owl = $('.owl-carousel');
    owl.owlCarousel({
        loop: true,
        margin: 10,
        nav: false,
        center: true,
        items: 3,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 3
            },
            1000: {
                items: 3
            }
        }
    });

    // Sembunyikan semua deskripsi di awal kecuali yang pertama
    $('.description').hide();

    // Fungsi untuk tombol "Baca Selengkapnya" dengan scroll otomatis
    $('.read-more-btn').on('click', function() {
        var targetDesc = $(this).data('target');

        // Tampilkan deskripsi yang sesuai
        $('.description').hide();
        $('#' + targetDesc).show();

        // Scroll ke deskripsi yang dipilih
        $('html, body').animate({
            scrollTop: $('#' + targetDesc).offset().top - 100
        }, 800); // Durasi scrolling dalam milidetik
    });

    // Fungsi untuk update deskripsi ketika carousel bergeser
    owl.on('changed.owl.carousel', function(event) {
        var currentIndex = event.item.index; // Ambil index item yang ditampilkan saat ini
        var targetDesc = $('.owl-item').eq(currentIndex).find('.read-more-btn').data('target');

        // Sembunyikan semua deskripsi dan tampilkan yang sesuai
        $('.description').hide();
        $('#' + targetDesc).show();
    });

    // Fungsi untuk tombol prev dan next di carousel
    $('.next-btn').click(function() {
        owl.trigger('next.owl.carousel');
    });

    $('.prev-btn').click(function() {
        owl.trigger('prev.owl.carousel');
    });
});

//menambah tombol unduh pada fancybox
Fancybox.bind("[data-fancybox]", {
    Toolbar: {
        display: [
            "zoom",
            "download",  // Tambahkan tombol download
            "close"
        ],
    },
    Thumbs: false, // Nonaktifkan thumbnail
});



  