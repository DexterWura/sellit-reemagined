(function ($) {
  "use strict";

  $(document).ready(function () {
    // Mobile Menu Dropdown
    const mobileNavToggler = document.querySelector(".nav--toggle");
    const body = document.querySelector("body");
    if (mobileNavToggler) {
      mobileNavToggler.addEventListener("click", function () {
        body.classList.toggle("nav-toggler");
      });
    }
    // Mobile Menu Dropdown End

    // ============== Header Hide Click On Js Start ========
    $(".header-button").on("click", function () {
      if ($(".overlay").hasClass("show")) {
        $(".overlay").removeClass("show");
      } else {
        $(".overlay").addClass("show");
      }
    });
    $(".overlay").on("click", function () {
      $(".header-button").trigger("click");
    });
    // =============== Header Hide Click On Js End =========

    // Mobile Submenu
    $(".primary-menu__list.has-sub .primary-menu__link").on(
      "click",
      function (e) {
        e.preventDefault();
        body.classList.add("primary-submenu-toggler");
      }
    );
    $(".primary-menu__list.has-sub.active .primary-menu__link").on(
      "click",
      function (e) {
        e.preventDefault();
        body.classList.remove("primary-submenu-toggler");
      }
    );
    $(".primary-menu__list.has-sub").on("click", function () {
      $(this).toggleClass("active").siblings().removeClass("active");
    });
    // Mobile Submenu End

    //   Nice Select Inititate
    $(".custom--select").niceSelect();
    //   Nice Select End

    // Search Popup
    var bodyOvrelay = $("#body-overlay");
    var searchPopup = $("#search-popup");

    $(document).on("click", "#body-overlay", function (e) {
      e.preventDefault();
      bodyOvrelay.removeClass("active");
      searchPopup.removeClass("active");
    });


    // Testimonial Slider
    let testimonialSlider = $(".testimonial-slider");
    if (testimonialSlider) {
      testimonialSlider.slick({
        mobileFirst: true,
        arrows: false,
        autoplay: true,
        slidesToShow: 1,
        responsive: [
          {
            breakpoint: 767,
            settings: {
              autoplay: false,
              arrows: true,
              prevArrow:
                '<button type="button" class="testimonial-slider__btn testimonial-slider__btn--prev"><span class="testimonial-slider__btn-content"><i class="fas fa-chevron-left"></i></span></button>',
              nextArrow:
                '<button type="button" class="testimonial-slider__btn testimonial-slider__btn--next"><span class="testimonial-slider__btn-content"><i class="fas fa-chevron-right"></i></span></button>',
            },
          },
        ],
      });
    }
    // Testimonial Slider End

    Array.from(document.querySelectorAll("table")).forEach((table) => {
      let heading = table.querySelectorAll("thead tr th");
      Array.from(table.querySelectorAll("tbody tr")).forEach((row) => {
        Array.from(row.querySelectorAll("td")).forEach((colum, i) => {
          colum.setAttribute("data-label", heading[i].innerText);
        });
      });
    });
    //Input class

    // Password Show Hide Toggle
    let passTypeToggle = $(".pass-toggle");
    if (passTypeToggle) {
      passTypeToggle.each(function () {
        $(this).on("click", function () {
          $(this)
            .children()
            .toggleClass("fas fa-eye-slash")
            .toggleClass("fas fa-eye");
          var input = $(this).parent().find("input");
          if (input.attr("type") == "password") {
            input.attr("type", "text");
          } else {
            input.attr("type", "password");
          }
        });
      });
    }
    // Password Show Hide Toggle End

    // Animate the scroll to top
  });
})(jQuery);

// Header Fixed On Scroll
var bodySelector = document.querySelector("body");
const header = document.querySelector(".header-fixed");

if (bodySelector.contains(header)) {
  const headerTop = header.offsetTop;
  function fixHeader() {
    if (window.scrollY > headerTop) {
      document.body.classList.add("fixed-header");
    } else if (window.scrollY <= headerTop) {
      document.body.classList.remove("fixed-header");
    } else {
      document.body.classList.remove("fixed-header");
    }
  }
  $(window).on("scroll", function () {
    fixHeader();
  });
}

// Header Fixed On Scroll End
$(window).on("scroll", function () {
  var ScrollTop = $(".back-to-top");
  if ($(window).scrollTop() > 1200) {
    ScrollTop.fadeIn(1000);
  } else {
    ScrollTop.fadeOut(1000);
  }
});

$(window).on("load", function () {
  // Preloader
  var preLoder = $(".preloader");
  preLoder.fadeOut(1000);
});

// Client Slider
let clientSlider = $(".client-slider");
if (clientSlider) {
  clientSlider.slick({
    mobileFirst: true,
    arrows: false,
    autoplay: true,
    slidesToShow: 2,
    autoplaySpeed: 1000,
    speed: 2000,
    responsive: [
      {
        breakpoint: 567,
        settings: {
          slidesToShow: 3,
        },
      },
      {
        breakpoint: 767,
        settings: {
          slidesToShow: 3,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 4,
        },
      },
      {
        breakpoint: 1199,
        settings: {
          slidesToShow: 4,
        },
      },
      {
        breakpoint: 1399,
        settings: {
          slidesToShow: 5,
        },
      },
    ],
  });
}
// Client Slider End

var btn = $(".back-to-top");

$(window).scroll(function () {
  if ($(window).scrollTop() > 300) {
    btn.addClass("show");
  } else {
    btn.removeClass("show");
  }
});

btn.on("click", function (e) {
  e.preventDefault();
  $("html, body").animate({ scrollTop: 0 }, "300");
});
