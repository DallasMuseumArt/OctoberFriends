(function($) {
    $(document).ready(function() {
        $('.UsersReport').height("350");
    });

    friendsReports.UsersReport = {
        chart: function() {
            var chart = c3.generate({
                bindto: '#UsersReport',
                data: {
                    x: 'x',
                    columns: this.data,
                    names: {
                        totalUsers: '# of Total Users',
                        newUsers: '# of New Users',
                    },
                    types: {
                        totalUsers: 'area',
                        newUsers: 'line',   
                    },
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
                        ratio: 0.2
                    }
                }
            });
        }
    };

})(window.jQuery);