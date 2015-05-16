(function($) {

    friendsReports = {
        graphs: {},

        /**
         * Provides some default settings to use across all graphs
         */
        defaultGraphSettings: {
            color: {
                pattern: ['#95B753', '#CC3300'],
            },
            zoom: {
                enabled: true,
                rescale: true,
            },
        },

        init: function() {
            for (var key in friendsReports.graphs) {
                this.processRequest(friendsReports.graphs[key]);
            }

            this.handleControls();
        },

        initGraph: function(obj, settings) {
            $.extend(settings, friendsReports.defaultGraphSettings);

            obj.renderedGraph = c3.generate(settings);
        },

        processRequest: function(obj) {
            $.ajax({
                url: obj.ajaxPath,
                dataType: 'json',
                data: {
                    to: friendsReports.toDate,
                    from: friendsReports.fromDate,
                },
                type: 'GET',
                error: function() {
                    alert('something went wrong');
                },
                success: function(data) {
                    obj.data = data;
                    obj.chart();
                }
            });
        },

        handleControls: function() {
            var $this = this;

            $('.graph .controls a').on('click', function(e) {
                e.preventDefault();

                var type = $(this).data('type');
                var parentId = $(this).parents('.graph').data('graphid');

console.log($this);
                $this.graphs[parentId].renderedGraph.transform(type);
                console.log(parentId);
            });
        }
    };

})(window.jQuery);