(function($) {

    friendsReports.ActivitiesByDay.chart = function() {
        var chart = c3.generate({
            bindto: '#ActivitiesByDay',
            data: {
                x: 'x',
                columns: this.data,
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
                    ratio: 0.7
                }
            }
        });
    };

})(window.jQuery);