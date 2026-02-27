jQuery(document).ready(function ($) {
    "use strict";

    if ($('#custom-date-range').length) {
        // Dashboard stats
        const dateFilter = document.getElementById('date-filter');
        const customDateRange = document.getElementById('custom-date-range');
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const applyButton = document.getElementById('apply-date-filter');

        // Initialize Flatpickr for date inputs
        flatpickr(startDateInput, { dateFormat: "Y-m-d" });
        flatpickr(endDateInput, { dateFormat: "Y-m-d" });
        // Apply filter on select change also
        dateFilter.addEventListener('change', function () {
            if (this.value === 'custom') {
                customDateRange.style.display = 'block';
                customDateRange.style.padding = '20px 0px';
            } else {
                customDateRange.style.display = 'none';
                getDashboardStats(this.value); // fetch on change
            }
        });

        // Handle custom date range filter
        applyButton.addEventListener('click', function () {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                getDashboardStats('custom', startDate, endDate);
            } else {
                alert('Please select both start and end dates.');
            }
        });

        function getDashboardStats(type = 'today', start = '', end = '') {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_dashboard_stats',
                    type: type,
                    start_date: start,
                    end_date: end,
                    security: $('#dashboard_stats_nonce').val()
                },
                success: function (response) {
                    if (response.success) {
                        $('.stats-grid').html(response.data.html);
                    } else {
                        console.error('Failed to fetch dashboard stats');
                    }
                },
                error: function () {
                    console.error('AJAX error while fetching dashboard stats');
                }
            });
        }

        if ($('.stats-grid').length) {
            getDashboardStats();
        }
    }


    // Handle role filter change
    $('.filter-section select').on('change', function () {
        $(this).closest('form').submit();
    });

    // Password strength meter
    $('#password').on('input', function () {
        var password = $(this).val();
        var strength = 0;

        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]+/)) strength += 1;
        if (password.match(/[A-Z]+/)) strength += 1;
        if (password.match(/[0-9]+/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;

        var strengthText = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
        var strengthClass = ['very-weak', 'weak', 'medium', 'strong', 'very-strong'];

        $('#password-strength').removeClass().addClass(strengthClass[strength - 1]).text(strengthText[strength - 1]);
    });

    // Password confirmation check
    $('#confirm_password').on('input', function () {
        var password = $('#password').val();
        var confirmPassword = $(this).val();

        if (password === confirmPassword) {
            $('#password-match').removeClass('error').addClass('success').text('Passwords match');
        } else {
            $('#password-match').removeClass('success').addClass('error').text('Passwords do not match');
        }
    });

    // Profile photo upload
    // ----- 1. Media Uploader -----
    function initMediaUploader(buttonId, previewId, urlInputId) {
        $('#' + buttonId).on('click', function (e) {
            e.preventDefault();
            if (window.mediaUploader) {
                window.mediaUploader.open();
                return;
            }
            window.mediaUploader = wp.media({
                title: 'Choose Profile Photo',
                button: { text: 'Use this image' },
                multiple: false
            });
            window.mediaUploader.on('select', function () {
                var attach = window.mediaUploader.state().get('selection').first().toJSON();
                $('#' + urlInputId).val(attach.id);
                $('#' + previewId).html('<img src="' + attach.url + '" alt="Profile Photo">');
            });
            window.mediaUploader.open();
        });
    }
    initMediaUploader('profile_photo', 'profile-preview', 'profile_photo_id');
    initMediaUploader('edit_profile_photo', 'edit-profile-preview', 'edit_profile_photo_id');


    if ($('.intl-mobile').length) {
        // ----- 2. intl-tel-input Setup -----
        function setupIntlTelInput($input, countrySelector, numberSelector) {
            if (!$input.length || $input.data('iti')) return;
            var iti = window.intlTelInput($input[0], {
                strictMode: true,
                initialCountry: "us",
                separateDialCode: true,
                utilsScript: wgtData.themeUrl + "/assets/js/utils.js"
            });
            $input.data('iti', iti);

            function clearError() {
                $input.removeClass('validate-error');
            }

            $input.on('blur', function () {
                clearError();
                if ($input.val().trim() && !iti.isValidNumber()) {
                    $input.addClass('validate-error');
                    $('html,body').animate({ scrollTop: $input.offset().top - 20 }, 600);
                }
            });

            $input.on('change keyup', function () {
                clearError();
                var full = iti.getNumber();
                var code = iti.getSelectedCountryData().dialCode;
                countrySelector && $(countrySelector).val(code);
                numberSelector && $(numberSelector).val(full.replace('+' + code, '').replace(/\D/g, ''));
            });
        }
        setupIntlTelInput($('#mobile'), '#country_code', '#phone_number');
        setupIntlTelInput($('#edit_mobile'), '#edit_country_code', '#edit_phone_number');
    }


    // ----- 3. Validation Setup -----
    function initValidation(formId) {
        console.log('initValidation', formId);
        if (typeof $.validator === 'undefined') {
            return setTimeout(function () { initValidation(formId); }, 100);
        }

        var $form = $('#' + formId);

        // If already initialized, skip
        if ($form.data('validator')) {
            return;
        }

        // custom methods
        $.validator.addMethod('validPhone', function (v, e) {
            var iti = $(e).data('iti');
            return iti && iti.isValidNumber();
        }, 'Please enter a valid phone number');

        $.validator.addMethod('strongPassword', function (v, e) {
            return this.optional(e) ||
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/.test(v);
        }, 'Password must be 8+ chars, upper, lower, number & special');

        // shared rules/messages
        var rules = {
            name: { required: true, minlength: 2 },
            email: { required: true, email: true },
            mobile: { required: true, validPhone: true },
            role: { required: true },
            status: { required: true },
            // gender: { required: true }
        };
        var messages = {
            name: { required: 'Name required', minlength: 'Min 2 chars' },
            email: { required: 'Email required', email: 'Invalid email' },
            mobile: { required: 'Phone required' },
            role: { required: 'Select role' },
            status: { required: 'Select status' },
            gender: { required: 'Select gender' }
        };

        if (formId === 'add-user-form') {
            $.extend(rules, {
                password: { required: true, strongPassword: true },
                confirm_password: { required: true, equalTo: '#password' }
            });
            $.extend(messages, {
                password: { required: 'Password required' },
                confirm_password: { required: 'Confirm password', equalTo: 'Passwords must match' }
            });
        }
        if (formId === 'edit-user-form') {
            $.extend(rules, {
                edit_password: { strongPassword: true },
                edit_confirm_password: { equalTo: '#edit_password' }
            });
            $.extend(messages, {
                edit_confirm_password: { equalTo: 'Passwords must match' }
            });
        }

        $form.validate({
            rules: rules,
            messages: messages,
            errorElement: 'div',
            errorClass: 'error-message',
            validClass: 'valid',
            errorPlacement: function (error, element) {
                // If it's inside an intl-tel-input, place after that
                var $container = (element.attr('type') === 'tel' && element.closest('.iti').length)
                    ? element.closest('.iti')
                    : element;

                // Remove previous error added by validate only (not manually hardcoded ones)
                $container.siblings('div.error-message[generated="true"]').remove();

                // Mark this one so we know it's generated
                error.attr('generated', 'true');
                error.insertAfter($container);
            },
            onkeyup: function (el) { $(el).valid(); },
            onchange: function (el) { $(el).valid(); },
            onfocusout: function (el) { $(el).valid(); },
            onkeydown: function (el) { $(el).valid(); },
            submitHandler: commonSubmitHandler
        });
    }

    // ----- 4. Modal Logic -----
    function openModal(modalId) {
        var $m = $('#' + modalId),
            $f = $m.find('form');

        // always clear errors/messages
        $f.find('.error,.valid').removeClass('error valid');
        $f.find('.error-message').text('');

        if (modalId === 'add-user-modal') {
            // fresh slate
            $f[0].reset();
            var $tel = $f.find('input[type="tel"]');
            if ($tel.length && $tel.data('iti')) $tel.data('iti').setNumber('');
            $f.find('#profile-preview').empty();
            $f.find('#password_fields').removeClass('show');
        }
        else if (modalId === 'edit-user-modal') {
            // preserve loaded values
            $f.find('#edit-profile-preview').removeClass('error');
            //   initValidation('edit-user-form');
        }

        initValidation($f.attr('id'));
        $m.show();
    }

    function commonSubmitHandler(form) {
        var $tel = $(form).find('input[type="tel"]'),
            iti = $tel.data('iti');

        if (!iti || !iti.isValidNumber()) {
            $tel.addClass('validate-error');
            $('html, body').animate({ scrollTop: $tel.offset().top - 40 }, 400);
            return false;
        }

        const $form = $(form);
        const formId = $form.attr('id');

        if (formId === 'add-user-form') {
            handleAddUserAJAX($form);
        } else if (formId === 'edit-user-form') {
            handleEditUserAJAX($form);
        }
    }

    function handleAddUserAJAX($form) {
        const data = $form.serializeArray();
        const formData = {};
        data.forEach(field => formData[field.name] = field.value);

        $.ajax({
            url: wgtData.ajaxurl,
            method: 'POST',
            data: {
                action: 'wgt_create_role',
                ...formData
            },
            beforeSend: function () {
                $form.find('button[type="submit"]').prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    alert(res.data.message || 'User created');
                    location.reload();
                } else {
                    alert(res.data.message || 'Error occurred');
                }
            },
            complete: function () {
                $form.find('button[type="submit"]').prop('disabled', false);
            }
        });
    }

    function handleEditUserAJAX($form) {

        const data = $form.serializeArray();
        const formData = {};
        data.forEach(field => formData[field.name] = field.value);

        $.ajax({
            url: wgtData.ajaxurl,
            method: 'POST',
            data: {
                action: 'wgt_update_user_data',
                // nonce: wgtData.editUserNonce, // Make sure you localize this
                ...formData
            },
            beforeSend: function () {
                $form.find('button[type="submit"]').prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    alert(res.data.message || 'User updated');
                    location.reload();
                } else {
                    alert(res.data.message || 'Error occurred');
                }
            },
            complete: function () {
                $form.find('button[type="submit"]').prop('disabled', false);
            }
        });
    }



    function closeModal(modalId) { $('#' + modalId).hide(); }

    // ----- 5. Trigger Handlers -----
    // Add New
    $('#add-new-user-btn').on('click', function () {
        openModal('add-user-modal');
    });

    // Edit User
    $('.edit-user').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('user-id'),
            nonce = $(this).data('nonce');

        $.post(wgtData.ajaxurl, { action: 'wgt_get_user_data', user_id: id, nonce: nonce })
            .done(function (res) {
                if (res.success) {
                    var u = res.data;
                    $('#edit_user_id').val(u.ID);
                    $('#edit_name').val(u.display_name);
                    $('#edit_email').val(u.user_email);
                    $('#edit_mobile').val('+' + u.phone_code + u.phone_number);
                    $('#edit_phone_number').val(u.phone_number);
                    $('#edit_country_code').val(u.phone_code);
                    $('#edit_gender').val(u.gender);
                    $('#edit_role').val(u.roles[0]);
                    $('#edit_status').val(u.status);

                    const $editMobile = $('#edit_mobile');
                    const iti = $editMobile.data('iti');

                    if (iti && u.phone_code && u.phone_number) {
                        iti.setNumber('+' + u.phone_code + u.phone_number);
                    } else {
                        $editMobile.val(u.phone_number);
                    }

                    $('#edit_country_code').val(u.phone_code);
                    $('#edit_phone_number').val(u.phone_number);
                    $('#edit_profile_photo_id').val(u.profile_image_id);
                    $('#edit-profile-preview').html('<img src="' + u.profile_image_url + '" alt="">');
                    openModal('edit-user-modal');
                }
            });
    });

    // View User
    $('.view-user').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('user-id');
        var nonce = $(this).data('nonce');
        $.post(wgtData.ajaxurl, { action: 'wgt_get_user_data', user_id: id, nonce: nonce })
            .done(function (res) {
                if (res.success) {
                    var u = res.data;
                    console.log(u);
                    $('#view_name').text(u.display_name);
                    $('#view_email').text(u.user_email);
                    $('#view_mobile').text('+' + u.phone_code + u.phone_number);
                    $('#view_gender').text(u.gender);
                    $('#view_role').text(u.roles[0]);
                    $('#view_status').html('<span class="status-' + u.status + '">' + u.status.charAt(0).toUpperCase() + u.status.slice(1) + '</span>');
                    $('#view_profile_photo').html('<img src="' + u.profile_image_url + '" style="max-width:150px;">');

                    openModal('view-user-modal');
                }
            });
    });

    // Close buttons & outside click
    $('.close').on('click', function () { $(this).closest('.modal').hide(); });
    $(window).on('click', function (e) {
        if ($(e.target).hasClass('modal')) $('.modal').hide();
    });

    $(document).off('click', '.delete-user'); // guarantee no duplicates
    $(document).on('click', '.delete-user', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const name = $(this).data('user-name');

        if (confirm(`Delete user "${name}"?`)) {
            window.location.href = href;
        }
    });

    //   // Form Submit: re-init validation (catches enter/JS submits)
    //   $('#add-user-form, #edit-user-form').on('submit', function(e){
    //     e.preventDefault();
    //     initValidation(this.id);
    //   });

    // Certificate fees

    $(document).on('click', '.edit-fee', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $(`.fee-display-${id}`).hide();
        $(`.fee-edit-${id}`).show();
    });

    // on clicking save
    $(document).on('click', '.save-fee', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const newFee = $(`.fee-input-${id}`).val();

        // optional: simple validation
        if (newFee === '' || isNaN(newFee)) {
            alert("Please enter a valid number.");
            return;
        }

        var data = {
            action: "update_certificate_fee",
            id: id,
            new_fee: newFee,
            _ajax_nonce: wgtData.nonce
        };

        $.ajax({
            url: wgtData.ajaxurl,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.success) {
                    $(`.fee-display-${id}`).html(`${parseFloat(newFee).toFixed(2)} ${wgtData.currency}`);
                    $(`.fee-edit-${id}`).html(`
                        <a href='javascript:void(0);' class='edit-fee' data-id='${id}'>
                            <span class='dashicons dashicons-edit'></span>
                        </a>`);
                    $(`.fee-display-${id}`).show();
                    $(`.fee-edit-${id}`).hide();
                } else {
                    alert("Failed to update fee: " + response.data);
                }
            },
            error: function () {
                alert("AJAX error.");
            }
        });
    });

    $(document).on('click', '.cancel-fee', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $(`.fee-edit-${id}`).hide();
        $(`.fee-display-${id}`).show();
    });

    $('.toggle-status').on('change', function () {
        const checkbox = $(this);
        const recordId = checkbox.data('id');
        const isChecked = checkbox.is(':checked');
        const newStatus = isChecked ? 'Active' : 'Inactive';

        $.post(ajaxurl, {
            action: 'toggle_record_status',
            record_id: recordId,
            status: newStatus,
            _ajax_nonce: wgtData.nonce
        }, function (response) {
            if (response.success) {
                $('#status-label-' + recordId).text(newStatus);
            } else {
                alert("Failed to update status.");
                checkbox.prop('checked', !isChecked); // rollback checkbox
            }
        });
    });

    const $form = $('#wgt-filter-form');

    // Submit on any <select> or date <input> change
    $form.find('select, input[type="date"]').on('change', function () {
        $form.submit();
    });

    // Submit on Enter in the search input
    $form.find('input[name="s"]').on('keypress', function (e) {
        if (e.which === 13) { // 13 = Enter key
            e.preventDefault();
            $form.submit();
        }
    });

    // $('.tab-pane').first().show();
    // Tab click handler
    $('.nav-link').on('click', function () {
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        let tabId = $(this).data('tab');
        $('.tab-pane').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    //Image Upload
    let frame;
    $(document).on('click', '.upload-media-button', function (e) {
        e.preventDefault();

        const button = $(this);
        const target = button.data('target');
        const input = $('#' + target);
        const previewContainer = $('#preview_' + target);

        // Initialize or reuse media frame
        if (frame) {
            frame.close();
        }

        frame = wp.media({
            title: 'Select Images',
            button: { text: 'Use These' },
            multiple: true,
            lwgtary: {
                type: 'image' // ✅ restricts to images only
            }
        });

        frame.on('select', function () {
            const selection = frame.state().get('selection');
            let currentIDs = input.val() ? input.val().split(',') : [];
            currentIDs = currentIDs.filter(id => id && id !== '[]');

            selection.each(function (attachment) {
                const id = attachment.id.toString();

                if (!currentIDs.includes(id)) {
                    currentIDs.push(id);

                    const thumb = attachment.attributes.sizes?.medium?.url || attachment.attributes.url;
                    const fullUrl = attachment.attributes.url;

                    previewContainer.append(`
                <div class="preview-item" data-id="${id}" style="border:1px solid #ddd; padding:10px; border-radius:8px; width:200px; text-align:center; background:#fff; margin-bottom:10px;">
                    
                    <input type="checkbox" class="select-image" style="margin-bottom:8px;">
                    
                    <div style="margin-bottom:10px;">
                        <img src="${thumb}" style="width:100%; border-radius:4px;">
                    </div>
                    
                    <div style="font-size:14px; margin-top:5px;">
                        <a href="${fullUrl}" download style="color:#0066cc; margin:0 10px;">Download</a> |
                        <a href="#" class="remove-image" style="color:red; margin-left:10px;">Delete</a>
                    </div>
                </div>
            `);
                }
            });

            input.val(currentIDs.join(','));
        });


        frame.open();
    });

    // Remove image
    $(document).on('click', '.remove-image', function () {
        const wrapper = $(this).closest('.preview-item');
        const id = wrapper.data('id');
        const previewContainer = wrapper.closest('.media-preview');
        const inputID = previewContainer.attr('id').replace('preview_', '');
        const input = $('#' + inputID);

        wrapper.remove();

        let currentIDs = input.val() ? input.val().split(',') : [];
        currentIDs = currentIDs.filter(val => val !== id.toString());
        input.val(currentIDs.join(','));
    });

    /**************** World Record Edit Page JS ******************/
    if ($('#record-edit-form').length > 0) {
        $('#assigned_user').select2({
            placeholder: 'Search or select a user',
            allowClear: true
        });

        /**************** World Record Edit Form Logic ******************/

        const select = document.getElementById("applying_as");
        const fields = document.querySelectorAll("[data-applying-as]");

        function toggleFields() {
            // get selected option's data-type
            const selectedType = select.options[select.selectedIndex].dataset.type;

            fields.forEach(field => {
                const types = field.dataset.applyingAs.split(" ");
                if (types.includes(selectedType)) {
                    field.style.display = "block";
                } else {
                    field.style.display = "none";
                }
            });
        }

        // run on load + when select changes
        toggleFields();
        select.addEventListener("change", toggleFields);

        // Evidence video logic

        const container = $("#video-preview-container");
        const inputField = $("#evidence_videos");

        function updateHiddenField() {
            let videos = [];
            container.find(".video-input").each(function () {
                let val = $(this).val().trim();
                if (val) videos.push(val);
            });
            inputField.val(JSON.stringify(videos));
        }

        // Add new video field
        $(".add-video-button").on("click", function () {
            let count = container.find(".video-item").length;
            if (count >= 5) {
                alert("Maximum 5 videos allowed");
                return;
            }

            let newIndex = count + 1;
            let newVideo = `
            <div class="video-item" style="margin-bottom:20px;">
                <label>Youtube Video #${newIndex}</label>
                <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;">
                    <input type="text" class="form-control video-input" placeholder="Paste Youtube URL or Embed Code Here" style="flex:1;">
                    <button type="button" class="button remove-video" style="background:#f44336;color:#fff;">Remove</button>
                </div>
                <div class="video-embed" style="margin-top:10px;"></div>
            </div>
        `;
            container.append(newVideo);
        });

        // Remove video
        container.on("click", ".remove-video", function () {
            $(this).closest(".video-item").remove();
            updateHiddenField();
        });

        // Live preview when typing/pasting
        container.on("input", ".video-input", function () {
            let val = $(this).val().trim();
            let embedDiv = $(this).closest(".video-item").find(".video-embed");

            if (!val) {
                embedDiv.html("");
                updateHiddenField();
                return;
            }

            if (val.includes("<iframe")) {
                // directly render iframe
                embedDiv.html(val);
            } else if (val.includes("youtube.com") || val.includes("youtu.be")) {
                // extract ID from URL
                let videoId = null;
                if (val.includes("v=")) {
                    videoId = val.split("v=")[1].split("&")[0];
                } else if (val.includes("youtu.be/")) {
                    videoId = val.split("youtu.be/")[1].split("?")[0];
                }

                if (videoId) {
                    let embedUrl = "https://www.youtube.com/embed/" + videoId;
                    embedDiv.html(`<iframe width="560" height="315" src="${embedUrl}" frameborder="0" allowfullscreen></iframe>`);
                } else {
                    embedDiv.html(""); // invalid URL
                }
            } else {
                embedDiv.html(""); // not supported
            }

            updateHiddenField();
        });

        // banner and feature image upload
        let frame1;

        // Upload single image
        $(".upload-single-image").on("click", function (e) {
            e.preventDefault();

            const button = $(this);
            const target = button.data("target");
            const input = $("#" + target);
            const previewContainer = $("#preview_" + target);

            // Open WP media uploader
            if (frame1) {
                frame1.close();
            }

            frame1 = wp.media({
                title: "Select or Upload Image",
                button: { text: "Use this image" },
                multiple: false,
                lwgtary: {
                    type: 'image' // ✅ restricts to images only
                }
            });

            frame1.on("select", function () {
                const attachment = frame1.state().get("selection").first().toJSON();
                input.val(attachment.id);

                const url = attachment.url;

                previewContainer.html(`
                <div class="preview-item" data-id="${attachment.id}" style="position:relative;">
                    <img src="${url}" style="max-width:100%;height:auto;">
                    <div style="margin-top:5px;">
                        <a href="${url}" download class="button button-primary">Download</a>
                        <button type="button" class="button button-danger remove-single-image">Delete</button>
                    </div>
                </div>
            `);
            });

            frame1.open();
        });

        // Remove image
        $(document).on("click", ".remove-single-image", function () {
            const section = $(this).closest(".image-upload-section");

            // Clear preview
            section.find(".image-preview").empty();

            // Clear hidden input inside the same section
            section.find("input[type='hidden']").val("");
        });

        // Form validation and submission
        const form = document.querySelector('#record-edit-form'); // adjust form id

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(form);
            // formData.append('_ajax_nonce', wgt-admin-script.nonce); // localized from wp_enqueue_script

            fetch(wgtData.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Saved successfully!');
                        window.location.reload();
                    } else {
                        alert('❌ Error: ' + (data.data || 'Unknown error'));
                    }
                })
                .catch(err => alert('❌ Request failed: ' + err.message));
        });

    }

    // Concent form upload
    let consentUploader;

    $(document).on('click', '.upload-consent-form', function (e) {
        e.preventDefault();
        const inputField = $('#consent_form');
        const previewContainer = $('#consent-form-preview');

        // If frame already exists, reopen
        if (consentUploader) {
            consentUploader.open();
            return;
        }

        // Create new media frame restricted to PDFs
        consentUploader = wp.media({
            title: 'Select Consent Form (PDF Only)',
            lwgtary: { type: 'application/pdf' }, // ✅ Only PDFs allowed
            button: { text: 'Use This File' },
            multiple: false
        });

        // On file selection
        consentUploader.on('select', function () {
            const attachment = consentUploader.state().get('selection').first().toJSON();

            // Extra security check (in case user hacks the media frame)
            if (attachment.mime !== 'application/pdf') {
                alert('❌ Only PDF files are allowed.');
                return;
            }

            // Save file ID
            inputField.val(attachment.id);

            // Preview section
            previewContainer.html(`
                <div class="preview-item" data-id="${attachment.id}" style="border:1px solid #ddd;padding:15px;border-radius:6px;background:#fafafa;">
                    <p><strong>Uploaded Consent Form:</strong></p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                        <a href="${attachment.url}" target="_blank" class="button button-primary">📄 View</a>
                        <a href="${attachment.url}" download class="button">⬇ Download</a>
                        <button type="button" class="button button-danger remove-consent-form">❌ Delete</button>
                    </div>
                </div>
            `);
        });

        consentUploader.open();
    });

    // Remove consent form
    $(document).on('click', '.remove-consent-form', function (e) {
        e.preventDefault();
        $('#consent_form').val('');
        $('#consent-form-preview').html('<p>No consent form uploaded.</p>');
    });


    //     document.addEventListener('click', function(e) {
    //         console.log("adfed");
    //     if (e.target && e.target.classList.contains('toggle-highlight')) {
    //         e.preventDefault();
    //         const preview = e.target.closest('.preview-item');
    //         let ribbon = preview.querySelector('.highlight-ribbon');
    //         console.log(ribbon);
    //         if (ribbon) {
    //             // remove highlight
    //             ribbon.remove();
    //             e.target.textContent = 'Mark Highlighted';
    //         } else {
    //             // add highlight
    //             ribbon = document.createElement('span');
    //             ribbon.className = 'highlight-ribbon';
    //             ribbon.textContent = 'HIGHLIGHTED';
    //             preview.appendChild(ribbon);
    //             e.target.textContent = 'Unmark Highlighted';
    //         }
    //     }
    // });
});
if (document.querySelector("#mobile_display")) {
    const input = document.querySelector("#mobile_display");
    const iti = window.intlTelInput(input, {
        separateDialCode: true,
        allowDropdown: true,
        preferredCountries: ["in", "us"], // optional
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
    });

    let lockedCountry = input.getAttribute('data-locked-country');
    input.addEventListener("keyup", function (e) {
        const lockedCountry = $country.val().toLowerCase();
        if (iti.getSelectedCountryData().iso2 !== lockedCountry) {
            iti.setCountry(lockedCountry);
        }
    });

    const countryField = document.querySelector("#phone_country_code");
    const numberField = document.querySelector("#phone_number");

    function updatePhoneFields() {
        const countryData = iti.getSelectedCountryData();
        const fullNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164); // +919876543210

        if (countryData && countryData.dialCode) {
            countryField.value = countryData.dialCode; // 91
            numberField.value = fullNumber.replace("+" + countryData.dialCode, ""); // 9876543210
        }
    }

    // Update hidden fields on init
    updatePhoneFields();

    // On typing or country change
    input.addEventListener("input", updatePhoneFields);
    input.addEventListener("countrychange", updatePhoneFields);

    const $country = jQuery('#country');
    const $stateWrapper = jQuery('select[name="rstate"], input[name="rstate"]').closest('.mb-3');

    function updateStates(isInitialLoad = false) {
        const selectedCountry = $country.val();
        const states = window.wgtData?.woocommerce_states?.[selectedCountry] || {};

        // Get existing value only on initial load
        const currentValue = isInitialLoad
            ? $stateWrapper.find('[name="rstate"]').val() || ''
            : '';

        // Remove any existing state element
        $stateWrapper.find('select[name="rstate"], input[name="rstate"]').remove();

        if (Object.keys(states).length > 0) {
            // Create dropdown if states exist
            const $select = jQuery('<select name="rstate" class="form-select" required></select>');

            // Add options
            jQuery.each(states, function (code, name) {
                const $option = jQuery('<option>', { value: code, text: name });
                if (
                    isInitialLoad &&
                    (code.toLowerCase() === currentValue.toLowerCase() ||
                        name.toLowerCase() === currentValue.toLowerCase())
                ) {
                    $option.prop('selected', true);
                }
                $select.append($option);
            });

            $stateWrapper.append($select);
        } else {
            // Fallback to text input
            const $input = jQuery('<input>', {
                type: 'text',
                name: 'rstate',
                class: 'form-control mt-2',
                placeholder: 'Enter your state',
                required: true,
                value: isInitialLoad ? currentValue : '',
            });
            $stateWrapper.append($input);
        }
        if (typeof iti !== 'undefined') {
            iti.setCountry(selectedCountry.toLowerCase());
        }
    }

    // 🔹 On page load → show current saved state
    jQuery(document).ready(() => updateStates(true));

    // 🔹 On country change → clear and rebuild state field
    $country.on('change', () => updateStates(false));
}

// wp_listtables inline edit - lead owner

jQuery(document).ready(function ($) {
    $(document).on('change', '.update_record', function () {
        let $input = $(this);
        let action_type = $input.data('action_type');
        let recordId = $input.data('id');
        let value = $input.val();
        let module = $input.data('module');
        let $row = $input.closest('tr');
        let data = {
            action: 'update_record_table_columns',
            action_type: action_type,
            record_id: recordId,
            module: module,
        };
        if (action_type == 'lead_owner') {
            data['lead_owner'] = value;
        }
        if (action_type == 'status') {
            data['status'] = value;
        }
        if (action_type == 'lead_stage') {
            data['lead_stage'] = value;
        }
        if (action_type == 'email_triggers') {
            data['email_triggers'] = value;
        }
        if (action_type == 'approval_status') {
            data['approval_status'] = value;
        }
        if (action_type == 'remark') {
            data['remark'] = value;
        }

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    if (data.action_type == 'status') {
                        if (value == 'Inactive') {
                            $row.find('.active_disable').prop('disabled', true);
                        } else {
                            $row.find('.active_disable').prop('disabled', false);
                        }
                    }
                    $input.css('border', '2px solid green');
                } else {
                    $input.css('border', '2px solid red');
                    alert(response.data || 'Error saving lead owner.');
                }
            }
        });
    });
});

jQuery(document).ready(function ($) {

    // Auto-submit file upload
    $('#participants_file').on('change', function () {
        if ($(this).val()) {
            $('#participants-upload-form').submit();
        }
    });

    // Search filter
    $("#searchBox").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#participantTable tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Helper function: reset row view
    function resetRow(row) {
        row.find('.participant-name').show();
        row.find('.edit-input-participant').hide();
        row.find('.save-btn-participant, .cancel-btn-participant').hide();
        row.find('.edit-btn-participant').show();
    }

    // Edit button click
    $(document).on('click', '.edit-btn-participant', function (e) {
        e.preventDefault();
        let row = $(this).closest('tr');
        row.find('.participant-name').hide();
        row.find('.edit-input-participant').show().focus();
        row.find('.save-btn-participant, .cancel-btn-participant').show();
        $(this).hide();
    });

    // Cancel button click
    $(document).on('click', '.cancel-btn-participant', function (e) {
        e.preventDefault();
        let row = $(this).closest('tr');
        resetRow(row);
    });

    // Save button click (AJAX)
    $(document).on('click', '.save-btn-participant', function () {
        let row = $(this).closest('tr');
        let id = $(this).data('id');
        let newName = row.find('.edit-input-participant').val();

        if (!newName.trim()) {
            alert('Name cannot be empty');
            return;
        }

        $.post(ajaxurl, {
            action: 'update_participant',
            id: id,
            name: newName,
            _ajax_nonce: wpApiSettings.nonce
        }, function (response) {
            if (response.success) {
                row.find('.participant-name').text(newName);
                resetRow(row);
            } else {
                alert(response.data || 'Update failed');
            }
        });
    });

    // Delete button click (AJAX)
    $(document).on('click', '.delete-btn-participant', function (e) {
        e.preventDefault();
        if (!confirm('Delete this participant?')) return;

        var id = $(this).data('id');
        var row = $(this).closest('tr');

        $.post(ajaxurl, {
            action: 'delete_participants',
            ids: [id],
            // _ajax_nonce: participants_nonce
        }, function (response) {
            if (response.success) {
                row.fadeOut(function () { $(this).remove(); });
            } else {
                alert('Delete failed');
            }
        });
    });

    // Bulk delete
    $('#doaction, #doaction2').on('click', function (e) {
        e.preventDefault();
        var ids = [];
        $('input[name="bulk-delete[]"]:checked').each(function () { ids.push($(this).val()); });
        if (ids.length === 0) return alert('No participants selected');

        if (!confirm('Delete selected participants?')) return;

        $.post(ajaxurl, {
            action: 'delete_participants',
            ids: ids,
            // _ajax_nonce: participants_nonce
        }, function (response) {
            if (response.success) {
                ids.forEach(function (id) {
                    $('input[name="bulk-delete[]"][value="' + id + '"]').closest('tr').fadeOut(function () { $(this).remove(); });
                });
            } else {
                alert('Bulk delete failed');
            }
        });
    });

});

jQuery(document).ready(function ($) {
    if ($('.openPaymentForm').length) {

        // Open modal when any .openPaymentForm button clicked
        $(document).on("click", ".openPaymentForm", function () {
            $("#paymentModal").show();
            // Prefill modal fields
            $("#payment_id").val($(this).data("payment-id"));
            $("#country").val($(this).data("country"));
            $("#currency").val($(this).data("currency"));
            $("#amount").val($(this).data("amount"));
            $("#bank_name").val($(this).data("bank_name"));
            $("#payment_mode").val($(this).data("payment_mode"));
            $("#reference_no").val($(this).data("reference_no"));
            $("#payment_date").val($(this).data("payment_date"));
        });

        // Close modal
        $("#closeModal, #cancelBtn").on("click", function () {
            $("#paymentModal").hide();
        });

        // Submit form via AJAX
        $("#paymentForm").on("submit", function (e) {
            e.preventDefault();

            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
                        alert("Payment saved successfully!");
                        window.location.reload();
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function () {
                    alert("Unexpected error occurred.");
                }
            });
        });

        $(document).on("click", ".mark_as_unpaid", function () {
            let paymentId = $(this).data("payment-id");
            let action = $(this).data("action");
            let nonce = $(this).data("nonce");

            if (!paymentId || !action) {
                alert("Invalid request");
                return;
            }

            if (confirm("Are you sure you want to mark this payment as Unpaid?")) {
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: "update_payment_status",
                        security: nonce,
                        payment_id: paymentId,
                    },
                    success: function (response) {
                        if (response.success) {
                            alert("Payment status updated successfully!");
                            location.reload(); // reload page to reflect changes
                        } else {
                            alert("Error: " + response.data);
                        }
                    },
                    error: function () {
                        alert("Unexpected error occurred.");
                    }
                });
            }
        });
    }
});
