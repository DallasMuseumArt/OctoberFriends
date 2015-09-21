(function($) {
    $(document).ready(function() {
        $('.ActivitiesByDay').height("350");
    });

    friendsReports.graphs.ActivitiesByDay = {
        chart: function() {
            var settings = {
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
                            format: '%m/%d/%Y'
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
            };

            friendsReports.initGraph(this, settings);
        }
    };

})(window.jQuery);