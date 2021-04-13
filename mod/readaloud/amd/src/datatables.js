define(['jquery', 'core/log', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'], function ($, log, datatables) {
    "use strict"; // jshint ;_;

    /*
    This file contains class and ID definitions.
     */

    log.debug('Readaloud Teacher Datatables helper: initialising');

    return {
        //pass in config, amd set up table
        init: function (props) {
            //pick up opts from html
            var that = this;
            var thetable = $('#' + props.tableid);
            this.dt = thetable.DataTable(props.tableprops);
        }


    };//end of return value
});