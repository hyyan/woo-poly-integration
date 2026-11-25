/* global ClipboardJS */
(function () {
    'use strict';

    var clipboard = new ClipboardJS('.woopoly-copy-post');

    var resetTimers = new WeakMap();

    function announce(event, result) {
        var button = event.trigger;
        var icon = button.querySelector('.dashicons');
        var status = button.parentNode.querySelector('.woopoly-copy-status');
        var copied = result === 'success';

        clearTimeout(resetTimers.get(button));
        icon.classList.toggle('dashicons-yes', copied);
        icon.classList.toggle('dashicons-admin-page', !copied);
        status.classList.toggle('screen-reader-text', copied);
        status.textContent = button.getAttribute('data-' + result);

        if (copied) {
            resetTimers.set(button, setTimeout(function () {
                icon.classList.remove('dashicons-yes');
                icon.classList.add('dashicons-admin-page');
                status.textContent = '';
                resetTimers.delete(button);
            }, 1500));
        }
    }

    clipboard.on('success', function (event) {
        announce(event, 'success');
    });
    clipboard.on('error', function (event) {
        announce(event, 'error');
    });
}());
