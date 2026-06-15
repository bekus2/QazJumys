/**
 * Project: QazJumys
 * File: app.js
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Controls mobile navigation, AJAX forms, and user feedback.
 * RU: Управляет мобильной навигацией, AJAX-формами и обратной связью.
 */

(function ($) {
    'use strict';

    /**
     * EN: Shows a temporary toast message after AJAX actions.
     * RU: Показывает временное toast-сообщение после AJAX-действий.
     *
     * @param {string} message User-facing message / Сообщение для пользователя
     * @param {string} type Message type / Тип сообщения
     * @returns {void}
     */
    function showToast(message, type) {
        var $toast = $('.toast');

        $toast
            .removeClass('is-error is-success')
            .addClass(type === 'error' ? 'is-error' : 'is-success')
            .text(message)
            .addClass('is-visible');

        window.clearTimeout($toast.data('timer'));
        $toast.data('timer', window.setTimeout(function () {
            $toast.removeClass('is-visible');
        }, 4200));
    }

    /**
     * EN: Locks a submit button while the request is in progress.
     * RU: Блокирует кнопку отправки на время выполнения запроса.
     *
     * @param {jQuery} $button Submit button / Кнопка отправки
     * @param {boolean} isLoading Loading state / Состояние загрузки
     * @returns {void}
     */
    function setLoading($button, isLoading) {
        if (!$button.length) {
            return;
        }

        if (isLoading) {
            $button.data('label', $button.text());
            $button.addClass('is-loading').prop('disabled', true).text('Жіберілуде...');
            return;
        }

        $button.removeClass('is-loading').prop('disabled', false).text($button.data('label') || $button.text());
    }

    $(function () {
        var $body = $('body');
        var $nav = $('#site-menu');

        $('.nav-toggle').on('click', function () {
            var isOpen = $nav.toggleClass('is-open').hasClass('is-open');
            $body.toggleClass('nav-open', isOpen);
            $(this).attr('aria-expanded', isOpen ? 'true' : 'false');
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.site-header').length && $nav.hasClass('is-open')) {
                $nav.removeClass('is-open');
                $body.removeClass('nav-open');
                $('.nav-toggle').attr('aria-expanded', 'false');
            }
        });

        $('.js-ajax-form').on('submit', function (event) {
            event.preventDefault();

            var $form = $(this);
            var $button = $form.find('[type="submit"]').first();
            var token = $('meta[name="csrf-token"]').attr('content') || '';

            setLoading($button, true);

            $.ajax({
                url: $form.attr('action') || 'ajax.php',
                method: $form.attr('method') || 'POST',
                data: $form.serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            }).done(function (response) {
                showToast(response.message || 'Сәтті орындалды.', 'success');

                if (response.redirect || $form.data('success-redirect')) {
                    window.setTimeout(function () {
                        window.location.href = response.redirect || $form.data('success-redirect');
                    }, 550);
                }
            }).fail(function (xhr) {
                var response = xhr.responseJSON || {};
                showToast(response.message || 'Қате пайда болды. Кейін қайталап көріңіз.', 'error');
            }).always(function () {
                setLoading($button, false);
            });
        });
    });
})(jQuery);
