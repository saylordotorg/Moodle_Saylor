/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Filter Poodll Presets initialising');

    return {

        presetdata: false,

        dataitems: ['name', 'key', 'version', 'instructions', 'showatto', 'showplayers', 'requirecss', 'requirejs', 'shim', 'defaults',
            'amd', 'body', 'bodyend', 'script', 'style', 'dataset', 'datasetvars', 'alternate', 'alternateend'],

        fetchcontrols: function (templateindex) {
            var controls = {};
            controls.name = document.getElementById('id_s_filter_poodll_templatename_' + templateindex);
            controls.key = document.getElementById('id_s_filter_poodll_templatekey_' + templateindex);
            controls.version = document.getElementById('id_s_filter_poodll_templateversion_' + templateindex);
            controls.instructions = document.getElementById('id_s_filter_poodll_templateinstructions_' + templateindex);
            controls.showatto = document.getElementById('id_s_filter_poodll_template_showatto_' + templateindex);
            controls.showplayers = document.getElementById('id_s_filter_poodll_template_showplayers_' + templateindex);
            controls.requirecss = document.getElementById('id_s_filter_poodll_templaterequire_css_' + templateindex);
            controls.requirejs = document.getElementById('id_s_filter_poodll_templaterequire_js_' + templateindex);
            controls.shim = document.getElementById('id_s_filter_poodll_templaterequire_js_shim_' + templateindex);
            controls.defaults = document.getElementById('id_s_filter_poodll_templatedefaults_' + templateindex);
            controls.amd = document.getElementById('id_s_filter_poodll_template_amd_' + templateindex);
            controls.body = document.getElementById('id_s_filter_poodll_template_' + templateindex);
            controls.bodyend = document.getElementById('id_s_filter_poodll_templateend_' + templateindex);
            controls.script = document.getElementById('id_s_filter_poodll_templatescript_' + templateindex);
            controls.style = document.getElementById('id_s_filter_poodll_templatestyle_' + templateindex);
            controls.dataset = document.getElementById('id_s_filter_poodll_dataset_' + templateindex);
            controls.datasetvars = document.getElementById('id_s_filter_poodll_datasetvars_' + templateindex);
            controls.alternate = document.getElementById('id_s_filter_poodll_templatealternate_' + templateindex);
            controls.alternateend = document.getElementById('id_s_filter_poodll_templatealternate_end_' + templateindex);
            controls.presetdata = document.getElementById('id_s_filter_poodll_presetdata_' + templateindex);
            return controls;
        },

        fetchjsonbundle: function (templateindex, controls) {
            var bundle = {};
            $.each(this.dataitems,
                function (index, item) {
                    bundle[item] = controls[item].value;
                }
            );
            var jsonbundle = JSON.stringify(bundle);
            return jsonbundle;
            window.open("data:text/json;charset=utf-8," + encodeURIComponent(jsonbundle));
        },

        exportbundle: function (templateindex) {
            var controls = this.fetchcontrols(templateindex);
            if (controls.key.value == '') {
                return;
            }
            var jsonbundle = this.fetchjsonbundle(templateindex, controls);

            var pom = document.createElement('a');
            pom.setAttribute('href', "data:text/json;charset=utf-8," + encodeURIComponent(jsonbundle));
            pom.setAttribute('download', controls['key'].value + '.txt');

            if (document.createEvent) {
                var event = document.createEvent('MouseEvents');
                event.initEvent('click', true, true);
                pom.dispatchEvent(event);
            }
            else {
                pom.click();
            }
        },

        populateform: function (templateindex, presetindex, presetdata) {
            //get all our html controls
            var controls = this.fetchcontrols(templateindex);

            //what a rip off there was no selection!!!
            if (!presetindex && !presetdata) {
                return;
            }
            if (presetindex == 0 && presetdata) {
                //this is good, we have something from dopopulate
                //from a drag drop
            } else {
                //this is a normal selection from the presets control
                presetdata = this.presetdata;
            }

            $.each(this.dataitems,
                function (index, item) {
                    // log.debug(item + ':' + presetindex + ':' + presetdata[presetindex][item]);
                    //first check we have a data item for this control(old bundles don7 have instructions etc)
                    //then set the data
                    if (presetdata[presetindex].hasOwnProperty(item)) {
                        if (presetdata[presetindex]['key'] == 'cleartemplate' && (item == 'key' || item == 'name')) {
                            var usevalue = '';
                        } else {
                            var usevalue = presetdata[presetindex][item];
                        }
                        controls[item].value = usevalue;
                    } else {
                        switch (item) {
                            case 'amd':
                            case 'showplayers':
                            case 'showatto':
                                controls[item].value = 0;
                                break;
                            default:
                                controls[item].value = '';
                        }
                    }
                }
            );

        },

        dopopulate: function (templateindex, templatedata) {
            this.populateform(templateindex, 0, new Array(templatedata));
        },

        // load all poodll stuff and stash all our variables
        init: function (opts) {
            if (!this.presetdata) {
                var controlid = '#id_s_filter_poodll_presetdata_' + opts['templateindex'];
                var presetcontrol = $(controlid).get(0);
                this.presetdata = JSON.parse(presetcontrol.value);
                $(controlid).remove();
            }

            var amdpresets = this;
            //handle the select box change event
            $("select[name='filter_poodll/presets']").change(function () {
                debugger;
                amdpresets.populateform(opts['templateindex'], $(this).val());
            });

            //drag drop square events
            var ddsquareid = '#id_s_filter_poodll_dragdropsquare_' + opts['templateindex'];

            //export the current bundle
            $(ddsquareid).on("click", function () {
                amdpresets.exportbundle(opts['templateindex']);
            });


            //handle the drop event. First cancel dragevents which prevent drop firing
            $(ddsquareid).on("dragover", function (event) {
                event.preventDefault();
                event.stopPropagation();
                $(this).addClass('filter_poodll_dragging');
            });

            $(ddsquareid).on("dragleave", function (event) {
                event.preventDefault();
                event.stopPropagation();
                $(this).removeClass('filter_poodll_dragging');
            });

            $(ddsquareid).on('drop', function (event) {

                //stop the browser from opening the file
                event.preventDefault();
                //Now we need to get the files that were dropped
                //The normal method would be to use event.dataTransfer.files
                //but as jquery creates its own event object you ave to access
                //the browser even through originalEvent.  which looks like this
                var files = event.originalEvent.dataTransfer.files;

                //if we have files, read and process them
                if (files.length) {
                    var f = files[0];
                    if (f) {
                        var r = new FileReader();
                        r.onload = function (e) {
                            var contents = e.target.result;
                            var templatedata = JSON.parse(contents);
                            if (templatedata.key) {
                                amdpresets.dopopulate(opts['templateindex'], templatedata);
                            }
                        };
                        r.readAsText(f);
                    } else {
                        alert("Failed to load file");
                    }//end of if f
                }//end of if files
                $(this).removeClass('filter_poodll_dragging');
            });
        }//end of function
    };
});
/* jshint ignore:end */