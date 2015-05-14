(function($) {
    
    $(document).ready(function() {

        // Preset dates
        $('.date-range').click(function() {
            $('#datepicker-from').val($(this).data('from'));
            $('#datepicker-to').val($(this).data('to'));
             
            $('.date-range.active').removeClass('active');
            $(this).addClass('active');

            $('.apply-dates').click();
        });

        // Custom dates and picker
        var format = 'YYYY-MM-DD'
        var fromDate = new Pikaday({ 
            field: document.getElementById('datepicker-from'),
            format: format, 
        });

        var toDate = new Pikaday({ 
            field: document.getElementById('datepicker-to'),
            format: format, 
        });

        $('.apply-dates').on('click', function(e) {
            e.preventDefault();

            var from = $('#datepicker-from').val();
            var to = $('#datepicker-to').val();

            friendsReports.fromDate = from;
            friendsReports.toDate = to;

            friendsReports.init(); 

        });

        // Default to week view
        $('.date-picker .week').click();
    });

})(window.jQuery);