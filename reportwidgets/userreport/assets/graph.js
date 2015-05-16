(function($) {
    $(document).ready(function() {
        $('.UsersReport').height("350");
    });

    friendsReports.graphs.UsersReport = {
        chart: function() {
            var settings = {
                bindto: '#UsersReport',
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
            };

            friendsReports.initGraph(this, settings);
        }
    };

})(window.jQuery);