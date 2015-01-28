(function($) {

    var idleTime = 0;
    var timer = modalTimeout * 1000;

    $(document).ready(function() {
        //Automatically logout authorized kiosks after a specific amount of inactivity
        $('a.usertimeoutHandler').hide();

        // //Increment the idle time counter every minute.
        // var idleInterval = setInterval(idleTimeout, timer);

        // //Zero the idle timer on mouse movement.
        // $(this).mousemove(function (e) {
        //     clearInterval(idleInterval);
        //     idleInterval = setInterval(idleTimeout, timer);
        // });
        // $(this).keypress(function (e) {
        //     clearInterval(idleInterval);
        //     idleInterval = setInterval(idleTimeout, timer);
        // });

        function idleTimeout() {
            $('a.usertimeoutHandler').click();

            $(document).on('modal.open', function(e) {
                var countDown = 30; // allow 30 seconds to log back in
                var interval = setInterval(function() {
                    $('span.time').html(countDown);
                    countDown--;

                    if (countDown < 0) {
                        $('.timeout-logout').click();
                        clearInterval(interval);
                    }
                }, 1000);
            });
        }

    });

})(jQuery);