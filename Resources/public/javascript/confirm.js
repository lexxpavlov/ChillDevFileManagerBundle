/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

$(document).on("dom:loaded", function() {
    $(document).on("submit", "form.confirm-required", function(event, form) {
        if (!confirm(form.readAttribute("data-confirm"))) {
            event.preventDefault();
        }
    });
});
