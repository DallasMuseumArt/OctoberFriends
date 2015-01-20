(function($) {

    var idleTime = 0;
    var timer = modalTimeout;

    $(document).ready(function() {
        //Automatically logout authorized kiosks after a specific amount of inactivity
        $('a.usertimeoutHandler').hide();

        //Increment the idle time counter every minute.
        var idleInterval = setInterval(idleTimeout, timer * 1000); // 10 seconds

        //Zero the idle timer on mouse movement.
        $(this).mousemove(function (e) {
            idleTime = 0;
        });
        $(this).keypress(function (e) {
            idleTime = 0;
        });

        function idleTimeout() {
            $('a.usertimeoutHandler').click();

            $(document).on('modal.open', function(e) {
                var countDown = modalTimeout;
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