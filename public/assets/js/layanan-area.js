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
  
      //script untuk konten mengapa memilih kami
      document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll('.why-choose-us-btn');
        const sections = document.querySelectorAll('.why-choose-us-content-section');
  
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove 'active' class from all buttons
                buttons.forEach(btn => btn.classList.remove('active'));
                // Add 'active' class to the clicked button
                button.classList.add('active');
  
                // Hide all sections
                sections.forEach(section => section.classList.remove('active'));
  
                // Show the target section
                const target = document.querySelector(button.getAttribute('data-target'));
                target.classList.add('active');
            });
        });
    });
  
      
  
  