/*  ---------------------------------------------------
    Theme Name: Cake
    Description: Cake e-commerce tamplate
    Author: Colorib
    Author URI: https://www.colorib.com/
    Version: 1.0
    Created: Colorib
---------------------------------------------------------  */

'use strict';

(function ($) {
  // Helper: safe plugin check
  const hasPlugin = (name) => !!($ && $.fn && typeof $.fn[name] === 'function');

  // Shared: load products with robust fallback and caching
  if (!window.loadProducts) {
    window.__productsCache = null;
    window.loadProducts = async function loadProducts() {
      if (Array.isArray(window.__productsCache)) return window.__productsCache;
      const rel = './database/product.json';
      // Try fetch first (works over http/https)
      try {
        const res = await fetch(rel);
        if (res.ok) {
          const data = await res.json();
          if (Array.isArray(data)) {
            window.__productsCache = data;
            return data;
          }
        }
      } catch (e) { /* likely file:// or network */ }
      // Fallback: inline JSON if present on the page
      try {
        const inline = document.getElementById('inline-products');
        if (inline && inline.textContent.trim()) {
          const data = JSON.parse(inline.textContent);
          if (Array.isArray(data)) {
            window.__productsCache = data;
            return data;
          }
        }
      } catch {}
      // Final fallback: empty array
      return [];
    };
  }

  /*------------------
      Preloader
  --------------------*/
  $(window).on('load', function () {
    try { $(".loader").fadeOut(); } catch {}
    try { $("#preloder").delay(200).fadeOut("slow"); } catch {}
  });

  /*------------------
      Background Set
  --------------------*/
  $('.set-bg').each(function () {
    var bg = $(this).data('setbg');
    $(this).css('background-image', 'url(' + bg + ')');
  });

  //Search Switch
  $('.search-switch').on('click', function () {
    $('.search-model').fadeIn(400);
  });

  $('.search-close-switch').on('click', function () {
    $('.search-model').fadeOut(400, function () {
      $('#search-input').val('');
    });
  });

  //Canvas Menu
  $(".canvas__open").on('click', function () {
    $(".offcanvas-menu-wrapper").addClass("active");
    $(".offcanvas-menu-overlay").addClass("active");
  });

  $(".offcanvas-menu-overlay").on('click', function () {
    $(".offcanvas-menu-wrapper").removeClass("active");
    $(".offcanvas-menu-overlay").removeClass("active");
  });

  /*------------------
      Navigation
  --------------------*/
  if (hasPlugin('slicknav')) {
    $(".mobile-menu").slicknav({
      prependTo: '#mobile-menu-wrap',
      allowParentLinks: true
    });
  }

  /*-----------------------
      Hero Slider
  ------------------------*/
  if (hasPlugin('owlCarousel')) {
    $(".hero__slider").owlCarousel({
      loop: true,
      margin: 0,
      items: 1,
      dots: true,
      nav: false,
      mouseDrag: true,
      touchDrag: true,
      pullDrag: true,
      autoplayHoverPause: true,
      animateOut: 'fadeOut',
      animateIn: 'fadeIn',
      smartSpeed: 1200,
      autoHeight: false,
      autoplay: false
    });
  }
  // Fallback: nếu không có Owl, hiển thị slide đầu tiên và bỏ class owl-carousel
  if (!hasPlugin('owlCarousel')) {
    try {
      const $hero = $('.hero__slider');
      if ($hero.length) {
        $hero.removeClass('owl-carousel');
        const $items = $hero.find('.hero__item');
        if ($items.length) {
          $items.hide().first().show();
        }
      }
    } catch {}
  }

  /*--------------------------
      Categories Slider
  ----------------------------*/
  if (hasPlugin('owlCarousel')) {
    $(".categories__slider").owlCarousel({
      loop: true,
      margin: 22,
      items: 5,
      dots: false,
      nav: true,
      navText: ["<span class='arrow_carrot-left'><span/>", "<span class='arrow_carrot-right'><span/>"] ,
      smartSpeed: 1200,
      autoHeight: false,
      autoplay: false,
      responsive: {
        0: { items: 1, margin: 0 },
        480: { items: 2 },
        768: { items: 3 },
        992: { items: 4 },
        1200: { items: 5 }
      }
    });
  }

  /*-----------------------------
      Testimonial Slider
  -------------------------------*/
  if (hasPlugin('owlCarousel')) {
    $(".testimonial__slider").owlCarousel({
      loop: true,
      margin: 0,
      items: 2,
      dots: true,
      nav: false,
      mouseDrag: true,
      touchDrag: true,
      pullDrag: true,
      smartSpeed: 1200,
      autoHeight: false,
      autoplay: true,
      responsive: {
        0: { items: 1 },
        768: { items: 2 }
      }
    });
  }
  // Fallback layout for testimonials when Owl is absent
  if (!hasPlugin('owlCarousel')) {
    try {
      const $t = $('.testimonial__slider');
      if ($t.length) {
        $t.removeClass('owl-carousel');
        $t.css({
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))',
          gap: '30px',
          alignItems: 'stretch'
        });
        // Ensure children behave like cards
        $t.children().css({ width: '100%' });
      }
    } catch {}
  }

  /*---------------------------------
      Related Products Slider
  ----------------------------------*/
  if (hasPlugin('owlCarousel')) {
    $(".related__products__slider").owlCarousel({
      loop: true,
      margin: 0,
      items: 4,
      dots: false,
      nav: true,
      navText: ["<span class='arrow_carrot-left'><span/>", "<span class='arrow_carrot-right'><span/>"] ,
      smartSpeed: 1200,
      autoHeight: false,
      autoplay: true,
      responsive: {
        0: { items: 1 },
        480: { items: 2 },
        768: { items: 3 },
        992: { items: 4 }
      }
    });
  }

  /*--------------------------
      Select
  ----------------------------*/
  if (hasPlugin('niceSelect')) {
    $("select").niceSelect();
  }

  /*------------------
      Magnific
  --------------------*/
  if (hasPlugin('magnificPopup')) {
    $('.video-popup').magnificPopup({ type: 'iframe' });
  }

  /*------------------
      Barfiller
  --------------------*/
  if (hasPlugin('barfiller')) {
    $('#bar1').barfiller({ barColor: '#111111', duration: 2000 });
    $('#bar2').barfiller({ barColor: '#111111', duration: 2000 });
    $('#bar3').barfiller({ barColor: '#111111', duration: 2000 });
  }

  /*------------------
      Single Product
  --------------------*/
  $('.product__details__thumb img').on('click', function () {
    $('.product__details__thumb .pt__item').removeClass('active');
    $(this).addClass('active');
    var imgurl = $(this).data('imgbigurl');
    var bigImg = $('.big_img').attr('src');
    if (imgurl != bigImg) {
      $('.big_img').attr({ src: imgurl });
    }
  });

  /*-------------------
      Quantity change
  --------------------- */
  var proQty = $('.pro-qty');
  proQty.prepend('<span class="dec qtybtn">-</span>');
  proQty.append('<span class="inc qtybtn">+</span>');
  proQty.on('click', '.qtybtn', function () {
    var $button = $(this);
    var oldValue = $button.parent().find('input').val();
    var newVal;
    if ($button.hasClass('inc')) {
      newVal = parseFloat(oldValue) + 1;
    } else {
      // Don't allow decrementing below zero
      if (oldValue > 0) {
        newVal = parseFloat(oldValue) - 1;
      } else {
        newVal = 0;
      }
    }
    $button.parent().find('input').val(newVal);
  });

  if (hasPlugin('niceScroll')) {
    $(".product__details__thumb").niceScroll({
      cursorborder: "",
      cursorcolor: "rgba(0, 0, 0, 0.5)",
      boxzoom: false
    });
  }

})(jQuery);

(function ensureCartLink() {
  const normalize = (href) => {
    try { return new URL(href, window.location.href); } catch { return null; }
  };
  const fix = (a) => {
    const u = normalize(a.getAttribute('href'));
    if (!u) return;
    const file = u.pathname.split('/').pop(); // chỉ tên file
    if (file === 'cart.html') {
      const parts = u.pathname.split('/'); parts.pop(); parts.push('view_cart.html');
      a.href = parts.join('/') + u.search + u.hash;
    }
  };
  const retarget = () => document.querySelectorAll('a[href]').forEach(fix);
  document.addEventListener('DOMContentLoaded', retarget);
  const mo = new MutationObserver(retarget);
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();