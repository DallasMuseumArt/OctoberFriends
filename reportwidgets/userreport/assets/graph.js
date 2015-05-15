(function($) {
    $(document).ready(function() {
        $('.UsersReport').height("350");
    });

    friendsReports.graphs.UsersReport = {
        chart: function() {
            var chart = c3.generate({
                bindto: '#UsersReport',
                color: {
                    pattern: ['#95B753', '#CC3300'],
                },
                data: {
                    x: 'x',
                    columns: this.data,
                    names: {
                        totalUsers: '# of Total Users',
                        newUsers: '# of New Users',
                    },
                    types: {
                        totalUsers: 'bar',
                        newUsers: 'bar',   
                    },
                    groups: [
                        ['totalUsers', 'newUsers']
                    ]
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
                        label: '# of Users'
                    },
                },
                bar: {
                    width: {
                        ratio: 0.2
                    }
                }
            });
        }
    };

})(window.jQuery);