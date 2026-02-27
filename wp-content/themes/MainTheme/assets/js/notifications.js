jQuery(function ($) {

    if (typeof wgtNotify === 'undefined') return;

    const sound = new Audio(wgtNotify.sound);
    sound.preload = 'auto';

    let lastCount = 0;

    function loadNotifications(playSound = false) {

        $.post(wgtNotify.ajaxurl, { action: 'get_notifications' }, function (data) {

            const $list = $('.notification-list');
            $list.empty();

            const count = data.length;
            $('.wgt-count').text(count > 99 ? '100+' : count);

            if (playSound && count > lastCount) {
                sound.play().catch(() => { });
            }

            lastCount = count;

            if (!count) {
                $list.append('<li style="padding:5px;color:#777">No notifications</li>');
                return;
            }

            data.forEach(item => {
                $list.append(`
            <li data-id="${item.id}" class="notification-item" style="padding: 1px 2px; border-bottom: 1px solid #eee; cursor: pointer;"
            onmouseover="this.style.background='#f0f8ff';" 
            onmouseout="this.style.background='transparent';">
            <div class="msg" style="color: #666">${item.message}</div>
            <small class="time" style="display: block; font-size: 12px; color: grey; margin-top: 2px;">On ${item.date}</small>
            </li>
        `);
            });
        });
    }

    // Initial load
    loadNotifications();

    // Poll every 15 seconds
    setInterval(() => loadNotifications(true), 15000);

    // Toggle dropdown (ADMIN BAR SAFE)
    $('#wpadminbar').on('mouseenter', '.wgt-bell, #wgt-admin-dropdown', function () {
        $('#wgt-admin-dropdown').stop(true, true).fadeIn(200); // show dropdown
    });

    $('#wpadminbar').on('mouseleave', '.wgt-bell, #wgt-admin-dropdown', function () {
        $('#wgt-admin-dropdown').stop(true, true).fadeOut(200); // hide dropdown
    });

    // Click → mark read
    $(document).on('click', '.notification-list li', function () {
        const id = $(this).data('id');

        $.post(wgtNotify.ajaxurl, {
            action: 'mark_notification_read',
            id: id
        });

        $(this).remove();

        const count = parseInt($('.wgt-count').text()) - 1;
        $('.wgt-count').text(count > 99 ? '100+' : count);
    });

});
