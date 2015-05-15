(function($) {
    $(document).ready(function() {
        $('.RewardReport').height("350");
    });

    friendsReports.graphs.RewardReport = {
        chart: function() {
            var chart = c3.generate({
                bindto: '#RewardReport',
                data: {
                    x: 'x',
                    columns: this.data,
                    names: {
                        count: '# of Rewards Redeemed',
                    },
                    types: {
                        count: 'bar',
                    },
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
                        label: '# of Rewards'
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