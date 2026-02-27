jQuery(document).ready(function ($) {
  const $form = $('#wgt-application-form');
  const $mobileInput = document.querySelector("#mobile_display");

  // --- intl-tel-input ---
  const iti = window.intlTelInput($mobileInput, {
    separateDialCode: true,
    initialCountry: "in",
    allowDropdown: false,
    utilsScript: window.wgtData?.intlTelInputUtils
  });

  let lockedCountry = $mobileInput.getAttribute('data-locked-country');
  $mobileInput.addEventListener("keyup", function (e) {
      const lockedCountry = $country.val().toLowerCase();
      if (iti.getSelectedCountryData().iso2 !== lockedCountry) {
        iti.setCountry(lockedCountry);
      }
  });

  // --- Custom Validators ---
  $.validator.addMethod("validMobile", function (value, element) {
    return iti.isValidNumber();
  }, "Please enter a valid mobile number.");

  $.validator.addMethod("dobBeforeAttempt", function (value, element) {
    const applyingAs = $('#applying_as').val();
    if (applyingAs !== 'solo') return true; // Skip validation unless Solo

    const dobVal = $('[name="dob"]').val();
    if (!dobVal || !value) return true; // Skip if either field is empty

    const dob = new Date(dobVal);
    const attempt = new Date(value);

    return attempt >= dob;
  }, "Attempt Date cannot be before Date of Birth.");

  // --- jQuery Validate ---
  $form.validate({
    ignore: ':hidden:disabled',
    errorElement: 'div',
    errorClass: 'error-message text-danger small mt-1',

    // Show error just below the field (respecting HTML structure)
    errorPlacement: function (error, element) {
      const $wrapper = element.closest('.col-md-6, .mb-3');

      if ($wrapper.length) {
        $wrapper.append(error);
      } else {
        error.insertAfter(element);
      }
    },

    // Validation rules
    rules: {
      applying_as: "required",
      purpose: "required",
      holder_name: {
        required: true,
        minlength: 2
      },
      holder_parent_name: "required",
      dob: {
        required: true,
        dobBeforeToday: true
      },
      description: {
        required: true,
        minlength: 2
      },
      country: "required",
      state: "required",
      city: {
        required: true,
        minlength: 2
      },
      zipcode: {
        required: true,
        minlength: 2
      },
      terms: "required",
      mobile_display: {
        required: true,
        validMobile: true
      }
    },
    // Custom error messages
    messages: {
      holder_name: {
        required: "Please enter the holder name.",
        minlength: "Name must be at least 6 characters."
      },
      mobile_display: {
        required: "Mobile number is required."
      },
      terms: "You must agree to the terms."
    },

    // On valid submission
    submitHandler: function (form) {

      const phoneInput = document.querySelector('#mobile_display');

      // Force intl-tel-input to update
      phoneInput.dispatchEvent(new Event('blur'));

      let fullNumber = '';
      let countryCode = '';
      let nationalNumber = '';

      if (iti && iti.isValidNumber()) {
        fullNumber = iti.getNumber(); // E164
        countryCode = iti.getSelectedCountryData().dialCode;

        if (fullNumber && countryCode) {
          nationalNumber = fullNumber.replace('+' + countryCode, '');
        }
      }

      // SAFETY FALLBACK
      if (!countryCode || !nationalNumber) {
        console.warn('IntlTelInput failed, fallback used');

        countryCode = iti.getSelectedCountryData()?.dialCode || '';
        nationalNumber = phoneInput.value.replace(/\D/g, '');
      }

      $('#phone_country_code').val(countryCode);
      $('#phone_number').val(nationalNumber);

      $('[name="wgt_appreciation_submit"]').html('<span class="spinner"></span> Please wait...');
      $('#formLoader').show();

      form.submit();
    }
  });

  // --- Real-time field revalidation ---
  $form.on('input change', 'input, select, textarea', function () {
    $(this).valid();
  });

  // --- Toggle conditional fields on Applying As change ---
  function toggleApplyingAsFields() {
    const selected = $('#applying_as option:selected').data('type');
    $('[data-applying-as]').each(function () {
      const $field = $(this);
      const types = $field.data('applying-as').split(' ');

      if (types.includes(selected)) {
        $field.show();
        $field.find('input, select, textarea').prop('disabled', false);
      } else {
        $field.hide();
        $field.find('input, select, textarea').prop('disabled', true);
      }
    });
  }

  $('#applying_as').on('change', function () {
    toggleApplyingAsFields();
    $('[name="attempt_date"]').valid(); // force revalidation
  });
  toggleApplyingAsFields();
  // --- Country/State dynamic ---
  const $country = $('#country');
  const $stateWrapper = $('select[name="state"]').closest('.col-md-6');

  function updateStates() {
    const selectedCountry = $country.val();
    const states = window.wgtData?.woocommerce_states?.[selectedCountry] || {};

    $stateWrapper.find('input[name="state"]').remove();
    let $state = $stateWrapper.find('select[name="state"]');

    if (Object.keys(states).length > 0) {
      if ($state.length === 0) {
        $stateWrapper.append('<select name="state" class="form-select" required></select>');
        $state = $stateWrapper.find('select[name="state"]');
      }
      $state.empty();
      $.each(states, function (code, name) {
        $state.append(new Option(name, code));
      });
      $state.prop('disabled', false).show();
    } else {
      $state.hide().prop('disabled', true);
      $stateWrapper.append('<input type="text" name="state" class="form-control mt-2" placeholder="Enter your state" required>');
    }

    if (typeof iti !== 'undefined') {
      iti.setCountry(selectedCountry.toLowerCase());
    }

    // ✅ Reset validation if present
    if ($form && $form.length && $form.validate) {
      $form.validate().resetForm();
      $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    }
  }

  $country.on('change', updateStates);
  updateStates();

  function calculateAge(dob) {
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    return age;
  }

  function toggleConsentValidation() {
    const dobVal = $('#dob').val();
    const cat = parseInt($('#applying_as').val(), 10);
    const $checkbox = $('#consent');
    const $dob = $('#dob');

    if (!dobVal) {
      $('.consent-form-check').addClass('d-none');
      $checkbox.prop('checked', false);
      $checkbox.rules('remove', 'required');
      // $dob.rules('remove', 'maxAge');
      return;
    }

    const age = calculateAge(dobVal);

    // 🔹 Custom rule: age must be <= 18
    // if (age >= 18) {
    //   $dob.rules('add', {
    //     maxAge: 18,
    //     messages: {
    //       maxAge: "Age should be under 18."
    //     }
    //   });
    //   $dob.valid();
    // } else {
    //   $dob.rules('remove', 'maxAge');
    // }

    // 🔹 Consent validation only if under 16 + Solo category
    if (age < 18 && cat === 1) {
      $('.consent-form-check').removeClass('d-none');

      if (!$checkbox.rules().required) {
        $checkbox.rules('add', {
          required: true,
          messages: { required: "You must agree to the consent form." }
        });
      }
    } else {
      $('.consent-form-check').addClass('d-none');
      $checkbox.prop('checked', false);
      $checkbox.rules('remove', 'required');
    }

    if ($checkbox.closest('form').length) {
      $checkbox.valid();
    }
  }

  // 🔹 Define custom validator for maxAge
  jQuery.validator.addMethod("maxAge", function (value, element, param) {
    if (!value) return true;
    return calculateAge(value) < param; // must be strictly under 16
  });

  $('#dob, #applying_as').on('change', toggleConsentValidation);

  // Call once on page load in case fields are prefilled
  toggleConsentValidation();

  //   // --- Add custom file size validator (must be declared before using) ---
  $.validator.addMethod("filesize", function (value, element, param) {
    if (element.files.length === 0) return true; // no file selected yet
    return element.files[0].size <= param;
  }, "File must not exceed the allowed size.");

  $.validator.addMethod("dobBeforeToday", function (value, element) {
    if (!value) return true; // handled by 'required' rule already
    const dob = new Date(value);
    const today = new Date();
    today.setHours(0, 0, 0, 0); // ignore time part
    return dob < today; // DOB must be before today
  }, "Date of Birth must be before today.");
});
