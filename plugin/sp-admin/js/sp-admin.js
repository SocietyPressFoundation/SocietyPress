/**
 * SocietyPress Admin JavaScript
 *
 * Handles interactive functionality for the custom admin interface.
 * Kept intentionally simple - no frameworks, just vanilla JS.
 *
 * @package SocietyPress
 * @since 0.59
 */

(function() {
    "use strict";

    /**
     * Initialize when DOM is ready.
     */
    document.addEventListener("DOMContentLoaded", function() {
        initNotices();
        initForms();
        initConfirmations();
    });

    /**
     * Initialize notice dismissal.
     */
    function initNotices() {
        var dismissButtons = document.querySelectorAll(".sp-admin-notice-dismiss");

        dismissButtons.forEach(function(button) {
            button.addEventListener("click", function() {
                var notice = this.closest(".sp-admin-notice");
                if (notice) {
                    notice.style.opacity = "0";
                    notice.style.transform = "translateY(-10px)";
                    setTimeout(function() {
                        notice.remove();
                    }, 200);
                }
            });
        });
    }

    /**
     * Initialize form enhancements.
     */
    function initForms() {
        var forms = document.querySelectorAll("form[data-sp-form]");

        forms.forEach(function(form) {
            var isDirty = false;

            form.addEventListener("input", function() {
                isDirty = true;
            });

            form.addEventListener("change", function() {
                isDirty = true;
            });

            form.addEventListener("submit", function() {
                isDirty = false;
            });

            window.addEventListener("beforeunload", function(e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = "You have unsaved changes. Are you sure you want to leave?";
                    return e.returnValue;
                }
            });
        });
    }

    /**
     * Initialize confirmation dialogs for destructive actions.
     */
    function initConfirmations() {
        var dangerButtons = document.querySelectorAll("[data-sp-confirm]");

        dangerButtons.forEach(function(button) {
            button.addEventListener("click", function(e) {
                var message = this.getAttribute("data-sp-confirm") ||
                    "Are you sure? This action cannot be undone.";

                if (!confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    }

    /**
     * Show a notice message.
     */
    window.spShowNotice = function(message, type) {
        type = type || "success";

        var icons = {
            success: "✓",
            error: "⚠",
            warning: "⚠"
        };

        var notice = document.createElement("div");
        notice.className = "sp-admin-notice sp-admin-notice--" + type;
        notice.innerHTML =
            '<span class="sp-admin-notice-icon">' + icons[type] + '</span>' +
            '<span class="sp-admin-notice-message">' + escapeHtml(message) + '</span>' +
            '<button type="button" class="sp-admin-notice-dismiss" aria-label="Dismiss">×</button>';

        var content = document.querySelector(".sp-admin-content");
        if (content) {
            content.insertBefore(notice, content.firstChild);

            notice.querySelector(".sp-admin-notice-dismiss").addEventListener("click", function() {
                notice.remove();
            });
        }
    };

    /**
     * Escape HTML entities.
     */
    function escapeHtml(text) {
        var div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * AJAX helper for form submissions.
     */
    window.spAjax = function(action, data, callback) {
        data = data || {};
        data.action = action;
        data.nonce = spAdmin.nonce;

        var formData = new FormData();
        Object.keys(data).forEach(function(key) {
            formData.append(key, data[key]);
        });

        fetch(spAdmin.ajaxUrl, {
            method: "POST",
            body: formData,
            credentials: "same-origin"
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            if (callback) {
                callback(result);
            }
        })
        .catch(function(error) {
            console.error("AJAX error:", error);
            spShowNotice("An error occurred. Please try again.", "error");
        });
    };

})();
