(function($) {
    $(document).on('dataReady', function() {
        var chart = c3.generate({
            bindto: '#newFriendsByDay',
            data: {
                x: 'x',
                columns: window.graphFriendsByDay,
                names: {
                    data: '# of users',
                },
                type: 'bar',
            },
            axis: {
                x: {
                    type: 'timeseries',
                    tick: {
                        count: 15,
                        format: '%m/%d'
                    }
                },
                y: {
                    label: '# of Users'
                },
            },
            bar: {
                width: {
                    ratio: 0.7
                }
            }
        });
    });

})(window.jQuery);
