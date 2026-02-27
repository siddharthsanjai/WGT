jQuery(function($){
    "use strict";
    var s;
    $(window).on("load", function(){
        $(".se-pre-con").fadeOut("slow")
    });
    $(".mean-menu").meanmenu({
        meanScreenWidth: "992"
    });
    $(window).on("scroll", function(){
        $(this).scrollTop() > 100 ? $(".navbar-area").addClass("is-sticky") : $(".navbar-area").removeClass("is-sticky")
    });
    $('a[href="#search"]').on("click", function(s){
        s.preventDefault();
        $("#search").addClass("open");
        $('#search > form > input[type="search"]').focus()
    });
    $("#search, #search button.close").on("click keyup", function(s){
        (s.target == this || "close" == s.target.className || 27 == s.keyCode) && $(this).removeClass("open")
    });
    $(window).scroll(function(){
        $(this).scrollTop() >= 100 ? $("#return-to-top").fadeIn(200) : $("#return-to-top").fadeOut(200)
    });
    $("#return-to-top").on("click", function(){
        $("body,html").animate({scrollTop: 0}, 500)
    });
    $("#options").flagStrap({
        countries: {AU: "Australia", FR: "France", US: "English"},
        buttonSize: "btn-sm",
        buttonType: "btn-info",
        labelMargin: "10px",
        scrollable: !1,
        scrollableHeight: "350px"
    });
    $(".quantity-button").off("click").on("click", function(){
        if($(this).hasClass("quantity-add")){
            var s = parseInt($(this).parent().find("input").val()) + 1;
            $(this).parent().find("input").val(s).trigger("change")
        }
        if($(this).hasClass("quantity-remove")){
            var o = parseInt($(this).parent().find("input").val()) - 1;
            0 == o && (o = 1);
            $(this).parent().find("input").val(o).trigger("change")
        }
    });
    $(".video-btn").on("click", function(){
        s = $(this).data("src")
    });
    $("#myModalVideo").on("shown.bs.modal", function(o){
        $("#video").attr("src", s + "?autoplay=1&modestbranding=1&showinfo=0")
    });
    $("#myModalVideo").on("hide.bs.modal", function(o){
        $("#video").attr("src", s)
    });

    $(".testimonial-slider-slick").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !1,
        focusOnSelect: !0,
        dots: !0,
        autoplay: !1,
        autoplaySpeed: 5000,
        appendDots: $(".custom-paging-testi"),
        responsive: [
            {breakpoint: 767, settings: {autoplay: !1}},
            {breakpoint: 420, settings: {autoplay: !1}}
        ]
    });

    $("a[data-slide]").click(function(s){
        s.preventDefault();
        var o = $(this).data("slide");
        $(".testimonial-slider-slick").slick("slickGoTo", o - 1)
    });

    $(".portfolio-slider-wrap").slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: 1,
        arrows: !0,
        prevArrow: '<button class="prev-port"><i class="bx bx-arrow-back"></i></button>',
        nextArrow: '<button class="next-port"><i class="bx bx-arrow-back bx-rotate-180" ></i></button>',
        focusOnSelect: !0,
        dots: !1,
        autoplay: !1,
        autoplaySpeed: 5000,
        responsive: [
            {breakpoint: 1200, settings: {slidesToShow: 3}},
            {breakpoint: 991, settings: {slidesToShow: 2}},
            {breakpoint: 720, settings: {slidesToShow: 1}}
        ]
    });

    $(".partner-logo-slider").slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        arrows: !1,
        focusOnSelect: !0,
        dots: !1,
        autoplay: !1,
        autoplaySpeed: 5000,
        responsive: [
            {breakpoint: 992, settings: {slidesToShow: 3, autoplay: !0}},
            {breakpoint: 768, settings: {slidesToShow: 2, autoplay: !0}},
            {breakpoint: 420, settings: {slidesToShow: 1, autoplay: !0}}
        ]
    });

    $(".portfolio-item-slider").slick({
        arrows: !1,
        cssEase: "cubic-bezier(0.7, 0, 0.3, 1)",
        slidesToShow: 1,
        centerMode: !0,
        centerPadding: "25%",
        responsive: [
            {breakpoint: 1100, settings: {slidesToShow: 1, centerMode: !0, centerPadding: "20%"}},
            {breakpoint: 991, settings: {slidesToShow: 1, centerMode: !0, centerPadding: "10%"}},
            {breakpoint: 480, settings: {slidesToShow: 1, centerPadding: "5%"}}
        ]
    });

    $(document).on("click", ".prevSlide", function(){
        $(".portfolio-item-slider").slick("slickPrev")
    });
    $(document).on("click", ".nextSlide", function(){
        $(".portfolio-item-slider").slick("slickNext")
    });
    $(document).on("click", ".filter-option-portfolio li a", function(){
        $(".filter-option-portfolio li a").removeClass("active");
        $(this).addClass("active");
        var s = $(this).attr("data-category");
        if("all" !== s){
            $(".portfolio-item-slider").slick("slickUnfilter");
            $(".portfolio-item-slider li").each(function(){
                $(this).removeClass("slide-shown")
            });
            $(".portfolio-item-slider li[data-match=" + s + "]").addClass("slide-shown");
            $(".portfolio-item-slider").slick("slickFilter", ".slide-shown")
        } else {
            $(".portfolio-item-slider li").each(function(){
                $(this).removeClass("slide-shown")
            });
            $(".portfolio-item-slider").slick("slickUnfilter")
        }
    });

    $(".testimonial-slider-v3").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !0,
        prevArrow: ".prev-testim",
        nextArrow: ".next-testim",
        focusOnSelect: !0,
        dots: !1,
        autoplay: !1,
        autoplaySpeed: 5000
    });

    $(".similar-slider-wrap").slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        arrows: !0,
        prevArrow: ".prev-similar",
        nextArrow: ".next-similar",
        focusOnSelect: !0,
        dots: !1,
        autoplay: !1,
        autoplaySpeed: 5000,
        infinite: !1,
        responsive: [
            {breakpoint: 992, settings: {slidesToShow: 2, autoplay: !0}},
            {breakpoint: 768, settings: {slidesToShow: 1, autoplay: !0}},
            {breakpoint: 420, settings: {slidesToShow: 1, autoplay: !0}}
        ]
    });

    $(".testimonial-slider-v3_1").slick({
        slidesToShow: 2,
        slidesToScroll: 1,
        arrows: !1,
        focusOnSelect: !0,
        dots: !0,
        autoplay: !1,
        autoplaySpeed: 5000,
        responsive: [
            {breakpoint: 992, settings: {slidesToShow: 2, autoplay: !0}},
            {breakpoint: 768, settings: {slidesToShow: 1, autoplay: !0}},
            {breakpoint: 420, settings: {slidesToShow: 1, autoplay: !0}}
        ]
    });

    $(".testimonial-slider-v3_2").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !0,
        prevArrow: '<button class="prev-testi_v3"><i class="bx bx-arrow-back"></i></button>',
        nextArrow: '<button class="next-testi_v3"><i class="bx bx-arrow-back bx-rotate-180" ></i></button>',
        focusOnSelect: !0,
        dots: !1,
        autoplay: !1,
        autoplaySpeed: 5000
    });

    $(".product-main-image-slider").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !1,
        fade: !0,
        asNavFor: ".product-navigation-slider",
        touchMove: !1,
        verticalSwiping: !0,
        focusOnSelect: !0
    });

    $(".product-navigation-slider").slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        asNavFor: ".product-main-image-slider",
        dots: !1,
        arrows: !1,
        centerMode: !1,
        focusOnSelect: !0,
        vertical: !0,
        swipe: !1,
        responsive: [
            {breakpoint: 992, settings: {vertical: !0}},
            {breakpoint: 768, settings: {vertical: !1, slidesToShow: 4}},
            {breakpoint: 580, settings: {vertical: !1, slidesToShow: 3}},
            {breakpoint: 380, settings: {vertical: !1, slidesToShow: 3}}
        ]
    });

    $(".related-products-slider").slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        arrows: !0,
        prevArrow: ".prev-rlts",
        nextArrow: ".next-rlts",
        focusOnSelect: !0,
        dots: !1,
        autoplay: !1,
        autoplaySpeed: 5000,
        infinite: !1,
        responsive: [
            {breakpoint: 1100, settings: {slidesToShow: 3, autoplay: !0}},
            {breakpoint: 991, settings: {slidesToShow: 2, autoplay: !0}},
            {breakpoint: 500, settings: {slidesToShow: 1, autoplay: !0}}
        ]
    });

    if (document.getElementById("slider-range") != null) {
        $("#slider-range").slider({
            range: !0,
            min: 0,
            max: 500,
            values: [100, 250],
            slide: function(s, o){
                $("#amount").val("$" + o.values[0] + " - $" + o.values[1])
            }
        });
        $("#amount").val("$" + $("#slider-range").slider("values", 0) + " - $" + $("#slider-range").slider("values", 1))
    }

    if (document.getElementById("slider-range2") != null) {
        $("#slider-range2").slider({
            range: !0,
            min: 0,
            max: 500,
            values: [100, 250],
            slide: function(s, o){
                $("#amount2").val("$" + o.values[0] + " - $" + o.values[1])
            }
        });
        $("#amount2").val("$" + $("#slider-range2").slider("values", 0) + " - $" + $("#slider-range2").slider("values", 1))
    }

    AOS.init({
        duration: 1000,
        mirror: !1,
        disable: function(){
            return window.innerWidth < 1100
        }
    });

    if ($("#accountRedirect").length > 0) {
        document.getElementById('accountRedirect').addEventListener('change', function () {
            const url = this.value;
            if (url) {
                window.location.href = url;
            }
        });
    }
    var $scrollContainer = $('#scroll-content');
    var clone = $scrollContainer.html();
    $scrollContainer.append(clone); // duplicate for seamless loop

    $('.spot-judgement #category').on('change', function () {
        const value = $(this).val();

        if (value === 'group' || value === 'mass' || value === 'commercial') {
        $('#participants-field').slideDown();
        } else {
        $('#participants-field').slideUp();
        $('#participants-field input').val('');
        }
    });

  if ($('#spot-judgement-form').length > 0) {
    const $form = $('#spot-judgement-form');
    const $messageBox = $('#spot-judgement-form-message');
    const $button = $form.find('button[type="submit"]');

    $form.on('submit', function (e) {
    e.preventDefault();

    $button.prop('disabled', true);
    $messageBox.html('⏳ Sending...');

    $.ajax({
      url: cd_data.ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'wgt_onspot_form',

        record_holder_name: $form.find('[name="record_holder_name"]').val(),
        attempt_date: $form.find('[name="attempt_date"]').val(),
        category: $form.find('[name="category"]').val(),
        participants: $form.find('[name="participants"]').val(),
        city: $form.find('[name="city"]').val(),
        state: $form.find('[name="state"]').val(),
        country: $form.find('[name="country"]').val(),
        contact_number: $form.find('[name="contact_number"]').val(),
        description: $form.find('[name="description"]').val(),
      },
      success: function (response) {
        if (response.success) {
          $messageBox.html(
            "<span style='color:green;'>✅ " + response.data + "</span>"
          );
          $form[0].reset();
        } else {
          $messageBox.html(
            "<span style='color:red;'>❌ " + response.data + "</span>"
          );
        }
        $button.prop('disabled', false);
      },
      error: function (xhr, status, error) {
        console.error('Contact AJAX error:', error);
        $messageBox.html(
          "<span style='color:red;'>❌ Request failed. Please try again.</span>"
        );
        $button.prop('disabled', false);
      }
    });
    });
  }

});
