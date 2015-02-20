(function($) {
    $(document).on('ActivitiesDataReady', function() {
        var chart = c3.generate({
            bindto: '#ActivitiesByDay',
            data: {
                x: 'x',
                columns: window.graphActivitiesByDay,
                names: {
                    data: '# of Activities Completed',
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
                    label: '# of Activities'
                },
            },
            bar: {
                width: {
                    ratio: 0.2
                }
            }
        });
    });

})(window.jQuery);