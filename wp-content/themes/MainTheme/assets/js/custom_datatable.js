jQuery(document).ready(function($) {
    $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "info": true,
        "responsive": true
    });
});

jQuery(document).ready(function($){
    $('.wr-tabs a').click(function(e){
        e.preventDefault();
        var target = $(this).attr('href');

        // Hide all tab contents
        $('.tab-content').hide();

        // Show selected tab
        $(target).show();

        // Optional: active tab styling
        $('.wr-tabs a').removeClass('active');
        $(this).addClass('active');
    });

    // Optionally: show first tab by default
    $('.wr-tabs a:first').click();
});

jQuery(document).ready(function ($) {
    // Submit on filter change
    $('#activity-filter, #year-filter').on('change', function () {
        $('#explore-filters').submit();
    });

    // Submit on Enter in search
    $('#explore-filters input[name="search"]').on('keypress', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#explore-filters').submit();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('galleryModalImage') === null) {
        return;
    }
    const images = document.querySelectorAll('.gallery-image');
    const modalImage = document.getElementById('galleryModalImage');
    const prevButton = document.getElementById('prevImage');
    const nextButton = document.getElementById('nextImage');
    let currentIndex = 0;

    // Open modal and set the image
    images.forEach((image, index) => {
        image.addEventListener('click', function() {
            currentIndex = index;
            modalImage.src = this.src;
        });
    });

    // Show previous image
    prevButton.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        modalImage.src = images[currentIndex].src;
    });

    // Show next image
    nextButton.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % images.length;
        modalImage.src = images[currentIndex].src;
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("newsletter-form");
    const messageBox = document.getElementById("newsletter-message");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const email = document.getElementById("notification_email").value;

        fetch(cd_data.ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "wgt_newsletter_subscribe",
                email: email
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                messageBox.innerHTML = "<span style='color:green;'>✅ " + data.data + "</span>";
                form.reset();
            } else {
                messageBox.innerHTML = "<span style='color:red;'>❌ " + data.data + "</span>";
            }
        })
        .catch(err => {
            messageBox.innerHTML = "<span style='color:red;'>❌ Request failed. Please try again.</span>";
            console.error("Newsletter AJAX error:", err);
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('contact-form') === null) {
        return;
    }
    const form = document.getElementById("contact-form");
    const messageBox = document.getElementById("contact-message");
    const button = form.querySelector("button");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        button.disabled = true;
        messageBox.innerHTML = "⏳ Sending...";

        const formData = new FormData(form);

        fetch(cd_data.ajaxurl, {
            method: "POST",
            body: new URLSearchParams({
                action: "wgt_send_contact",
                first_name: formData.get("first_name"),
                last_name: formData.get("last_name"),
                email: formData.get("email"),
                phone: formData.get("phone"),
                message: formData.get("message"),
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                messageBox.innerHTML = "<span style='color:green;'>✅ " + data.data + "</span>";
                form.reset();
            } else {
                messageBox.innerHTML = "<span style='color:red;'>❌ " + data.data + "</span>";
            }
            button.disabled = false;
        })
        .catch(err => {
            messageBox.innerHTML = "<span style='color:red;'>❌ Request failed. Please try again.</span>";
            console.error("Contact AJAX error:", err);
            button.disabled = false;
        });
    });
});


