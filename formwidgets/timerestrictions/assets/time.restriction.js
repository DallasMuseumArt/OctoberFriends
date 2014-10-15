(function($) {
    $(document).ready(function() {
        $('input.time-restriction').timeEntry({
            timeSteps: [1, 15, 0]
        });
    });
})(jQuery);
