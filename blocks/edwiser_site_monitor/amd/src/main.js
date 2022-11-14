// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript used to save the user's tab preference.
 *
 * @package    block_edwiser_site_monitor
 * @copyright  Wisdmlabs 2019
 * @author     Yogesh Shirsath
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/modal_factory',
    'core/chartjs'
], function(
    $,
    ajax,
    notification,
    ModalFactory
) {

    var SELECTORS = {
        USAGEBAR: '.usage-progress-bar',
        LOADER: '.edwiser_site_monitor #edwiser_site_monitor_plugins .edwiser-server-monitor-loader',
        CONTATUSLOADER: '.edwiser_site_monitor #edwiser_site_monitor_contactus .edwiser-server-monitor-loader',
        LAST24HOURSUSAGELOADER: '.edwiser_site_monitor #edwiser_site_monitor_last_24_hours_usage .edwiser-server-monitor-loader',
        ROOT: '.block_edwiser_site_monitor.block',
        SERVERPLUGINLIST: '.edwiser_site_monitor .server-plugins-list'
    };

    var EVENTS = {
        PROGRESS_STATUS: 'progress-status'
    };

    var init = function() {
        var liveusage = "";
        var chart = null;
        var fetchtingstatus = fetchtinglastusage = fetchtingplugins = sendingemail = false;
        $(SELECTORS.USAGEBAR).bind(EVENTS.PROGRESS_STATUS, function(event, width) {
            let allclasses = 'bg-success bg-warning bg-danger';
            let currentclass = 'bg-danger';
            if (width < 30) {
                currentclass = 'bg-success';
            } else if (width < 80) {
                currentclass = 'bg-warning';
            }
            $(this).removeClass(allclasses).addClass(currentclass);
        });
        /**
         * Get values ratio in used and total value
         *
         * @param  integer $percent usage value
         * @return boolean
         */
        function get_values_ratio(percent, total) {
            return (total * percent / 100).toFixed(2) + "G/" + total.toFixed(2) + "G";
        }
        var functions = {
            get_live_status: function() {
                if (fetchtingstatus) {
                    return;
                }
                fetchtingstatus = true;
                var promise = ajax.call([{
                    methodname: 'block_edwiser_site_monitor_get_live_status',
                    args: {}
                }]);
                promise[0].done(function(response) {
                    $(SELECTORS.ROOT).find('#esm_cpu_usage_bar').css('width', response.cpu + '%').trigger(EVENTS.PROGRESS_STATUS, [response.cpu]);
                    $(SELECTORS.ROOT).find('#esm_memory_usage_bar').css('width', response.memory + '%').trigger(EVENTS.PROGRESS_STATUS, [response.memory]);
                    $(SELECTORS.ROOT).find('#esm_storage_usage_bar').css('width', response.storage + '%').trigger(EVENTS.PROGRESS_STATUS, [response.storage]);
                    $(SELECTORS.ROOT).find('#esm_cpu_usage_label').text(response.cpu + '%');
                    $(SELECTORS.ROOT).find('#esm_memory_usage_label').text(response.memory + '% (' + get_values_ratio(response.memory, totalmemory) + ')');
                    $(SELECTORS.ROOT).find('#esm_storage_usage_label').text(response.storage + '% (' + get_values_ratio(response.storage, totalstorage) + ')');
                    $(SELECTORS.ROOT).find('#esm_live_users_label').text(response.liveusers);
                    fetchtingstatus = false;
                }).fail(function(ex) {
                    alert(ex.message);
                    fetchtingstatus = false;
                });
            },
            get_last_24_hours_usage: function() {
                if (fetchtinglastusage) {
                    return;
                }
                fetchtinglastusage = true;
                $(SELECTORS.LAST24HOURSUSAGELOADER).addClass('show');
                var timestamp = $(SELECTORS.ROOT).find('#esm_usage_date_selector').val() || 0;
                var promise = ajax.call([{
                    methodname: 'block_edwiser_site_monitor_get_last_24_hours_usage',
                    args: {
                        timestamp: timestamp
                    }
                }]);
                promise[0].done(function(response) {
                    fetchtinglastusage = false;
                    $(SELECTORS.LAST24HOURSUSAGELOADER).removeClass('show');
                    if (chart != null) {
                        chart.data.labels = JSON.parse(response.time);
                        chart.data.datasets[0].data = JSON.parse(response.cpu);
                        chart.data.datasets[1].data = JSON.parse(response.memory);
                        chart.data.datasets[2].data = JSON.parse(response.storage);
                        chart.update();
                        return;
                    }
                    let graph = {
                        type: 'line',
                        data: {
                            labels: JSON.parse(response.time),
                            datasets: [{
                                label: M.util.get_string('cpuusage', 'block_edwiser_site_monitor'),
                                data: JSON.parse(response.cpu),
                                borderColor: "#ff4c52",
                                borderWidth: 1,
                                fill: false,
                                pointRadius: 0,
                                hoverPointRadius: 1,
                            }, {
                                label: M.util.get_string('memoryusage', 'block_edwiser_site_monitor'),
                                data: JSON.parse(response.memory),
                                borderColor: "#11c26d",
                                borderWidth: 1,
                                fill: false,
                                pointRadius: 0,
                                hoverPointRadius: 1,
                            }, {
                                label: M.util.get_string('storageusage', 'block_edwiser_site_monitor'),
                                data: JSON.parse(response.storage),
                                borderColor: "#667afa",
                                borderWidth: 1,
                                fill: false,
                                pointRadius: 0,
                                hoverPointRadius: 1,
                            }]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    position: 'right',
                                    ticks: {
                                        beginAtZero: false
                                    },
                                    gridLines: {
                                        display: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: M.util.get_string('yaxistitle', 'block_edwiser_site_monitor')
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: M.util.get_string('xaxistitle', 'block_edwiser_site_monitor')
                                    }
                                }]
                            },
                            tooltips: {
                                position: 'nearest',
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += tooltipItem.yLabel + '%';
                                        switch (tooltipItem.datasetIndex) {
                                            case 1:
                                                label += ' (' + get_values_ratio(tooltipItem.yLabel, totalmemory) + ')';
                                                break;
                                            case 2:
                                                label += ' (' + get_values_ratio(tooltipItem.yLabel, totalstorage) + ')';
                                                break;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                    };
                    chart = new Chart($('#esm_usage_chart')[0].getContext('2d'), graph);
                }).fail(function(ex) {
                    $(SELECTORS.LAST24HOURSUSAGELOADER).removeClass('show');
                    alert(ex.message);
                    fetchtinglastusage = false;
                });
            },
            get_plugins: function() {
                if (fetchtingplugins) {
                    return;
                }
                fetchtingplugins = true;
                $(SELECTORS.LOADER).addClass('show');
                var promise = ajax.call([{
                    methodname: 'block_edwiser_site_monitor_get_plugins_update',
                    args: {}
                }]);
                promise[0].done(function(response) {
                    $(SELECTORS.LOADER).removeClass('show');
                    $(SELECTORS.SERVERPLUGINLIST).html(response.plugins);
                    $(SELECTORS.ROOT).find('.edwiser_site_monitor .lasttimefetched').text(response.lasttimefetched);
                    fetchtingplugins = false;
                }).fail(function(ex) {
                    $(SELECTORS.LOADER).removeClass('show');
                    notification.exception(ex);
                    fetchtingplugins = false;
                });
            },
            send_contactus_email: function(parameters) {
                if (sendingemail) {
                    return;
                }
                sendingemail = true;
                $(SELECTORS.CONTATUSLOADER).addClass('show');
                var promise = ajax.call(parameters);
                promise[0].done(function(response) {
                    $(SELECTORS.CONTATUSLOADER).removeClass('show');
                    var trigger = $('#create-modal');
                    ModalFactory.create({
                        title: response.header,
                        body: response.message
                    }, trigger).done(function(modal) {
                        modal.header.addClass(response.status ? 'bg-success' : 'bg-danger');
                        modal.header.children().addClass('text-white');
                        modal.show();
                    });
                    if (response.status) {
                        $(SELECTORS.ROOT).find('#contactus_form button').prop('disabled', true);
                        $(SELECTORS.ROOT).find('#contactus_form_succes').addClass('show');
                    }
                    sendingemail = false;
                }).fail(function(ex) {
                    $(SELECTORS.CONTATUSLOADER).removeClass('show');
                    notification.exception(ex);
                    sendingemail = false;
                });
            }
        };
        $(document).ready(function() {
            liveusage = setInterval(function() {
                functions.get_live_status();
            }, refreshrate * 1000);
            $('#esm_usage_date_selector').change(function() {
                functions.get_last_24_hours_usage();
            });
            $('.edwiser_site_monitor .nav-link').click(function() {
                $(this).removeClass('active').parents('.nav-item').siblings().removeClass('active');
                var tags = $(this).parents(SELECTORS.ROOT).find('.tab-pane').removeClass('show active');
                var target = $(this).data('container');
                $(this).parents('.nav-item').addClass('active');
                $(this).parents(SELECTORS.ROOT).find('#' + target).addClass('show active');
                switch ($(this).data('container')) {
                    case "edwiser_site_monitor_view_live_status":
                        liveusage = setInterval(function() {
                            functions.get_live_status();
                        }, refreshrate * 1000);
                        break;
                    case "edwiser_site_monitor_last_24_hours_usage":
                        functions.get_last_24_hours_usage();
                        clearInterval(liveusage);
                        break;
                    case "edwiser_site_monitor_plugins":
                        if ($.trim($(SELECTORS.SERVERPLUGINLIST).text()) == '') {
                            $(SELECTORS.ROOT).find('.edwiser_site_monitor .refresh-plugin-list').trigger('click');
                        }
                    case "edwiser_site_monitor_recommendation":
                    case "edwiser_site_monitor_contactus":
                        clearInterval(liveusage);
                        break;
                }
            });
            $('body').on('click', SELECTORS.ROOT + ' .refresh-plugin-list', function() {
                functions.get_plugins();
            }).on('click', SELECTORS.ROOT + ' .edwiser-plugin-filter', function(event) {
                event.preventDefault();
                $(this).parent('.server-plugins-list').removeClass('all edwiser other update').addClass($(this).data('filter'));
                $(SELECTORS.ROOT).find('#plugins-control-panel').removeClass('all edwiser other update').addClass($(this).data('filter'));
                $(SELECTORS.ROOT).find('.edwiser-plugins-current-list').text($(this).data('heading'));
                $(SELECTORS.ROOT).find('.edwiser-plugin-filter').removeClass('active');
                $(this).addClass('active');
                return;
            }).on('click', '.showchangelog', function(event) {
                event.preventDefault();
                var trigger = $('#create-modal');
                ModalFactory.create({
                    title: M.util.get_string('changelog', 'block_edwiser_site_monitor'),
                    body: $(this).data('log').changelog
                }, trigger).done(function(modal) {
                    modal.show();
                });
                return;
            });
            $('#contactus_form').submit(function(event) {
                event.preventDefault();
                var array = $(this).serializeArray();
                var paramter = {
                    methodname: 'block_edwiser_site_monitor_send_contactus_email',
                    args: {}
                };
                $.each(array, function(i, obj) {
                    paramter.args[obj.name] = obj.value;
                });
                functions.send_contactus_email([paramter]);
                return;
            });
        });
    };

    return {
        init: init
    }
});
