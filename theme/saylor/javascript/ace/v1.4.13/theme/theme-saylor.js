require.config({paths: { "ace" : "/theme/saylor/javascript/ace/v1.4.13/src-min"}});
require(['ace/ace'], function (ace) {
    ace.define("ace/theme/saylor",["require","exports","module","ace/lib/dom"], function(require, exports, module) {

    exports.isDark = false;
    exports.cssClass = "ace-saylor";
    exports.cssText = ".ace-saylor .ace_gutter {\
    background: #9f9f9f;\
    color: #212121\
    }\
    .ace-saylor .ace_print-margin {\
    width: 1px;\
    background: #eeeeee\
    }\
    .ace-saylor {\
    background-color: #eeeeee;\
    color: #212121\
    }\
    .ace-saylor .ace_cursor {\
    color: #ffffff;\
    }\
    .ace-saylor .ace_marker-layer .ace_selection {\
    background: #9f9f9f\
    }\
    .ace-saylor.ace_multiselect .ace_selection.ace_start {\
    box-shadow: 0 0 3px 0px #9f9f9f;\
    }\
    .ace-saylor .ace_marker-layer .ace_step {\
    background: rgb(102, 82, 0)\
    }\
    .ace-saylor .ace_marker-layer .ace_bracket {\
    margin: -1px 0 0 -1px;\
    border: 1px solid #9f9f9f\
    }\
    .ace-saylor .ace_marker-layer .ace_active-line {\
    background: #212121;\
    color: #ffffff\
    }\
    .ace-saylor .ace_gutter-active-line {\
    background-color: #212121\
    }\
    .ace-saylor .ace_marker-layer .ace_selected-word {\
    border: 1px solid #9f9f9f\
    }\
    .ace-saylor .ace_invisible {\
    color: #eeeeee\
    }\
    .ace-saylor .ace_entity.ace_name.ace_tag,\
    .ace-saylor .ace_keyword,\
    .ace-saylor .ace_meta.ace_tag,\
    .ace-saylor .ace_storage {\
    color: #93097d;\
    }\
    .ace-saylor .ace_punctuation,\
    .ace-saylor .ace_punctuation.ace_tag {\
    color: #212121\
    }\
    .ace-saylor .ace_constant.ace_character,\
    .ace-saylor .ace_constant.ace_language,\
    .ace-saylor .ace_constant.ace_numeric,\
    .ace-saylor .ace_constant.ace_other {\
    color: #2e3daa\
    }\
    .ace-saylor .ace_invalid {\
    color: #1a1a1a;\
    background-color: #d9534f\
    }\
    .ace-saylor .ace_invalid.ace_deprecated {\
    color: #1a1a1a;\
    background-color: #d9534f\
    }\
    .ace-saylor .ace_support.ace_constant,\
    .ace-saylor .ace_support.ace_function {\
    color: #3d8bba\
    }\
    .ace-saylor .ace_fold {\
    background-color: #eeeeee;\
    border-color: #9f9f9f\
    }\
    .ace-saylor .ace_storage.ace_type,\
    .ace-saylor .ace_support.ace_class,\
    .ace-saylor .ace_support.ace_type {\
    font-style: italic;\
    color: #2e3daa\
    }\
    .ace-saylor .ace_entity.ace_name.ace_function,\
    .ace-saylor .ace_entity.ace_other,\
    .ace-saylor .ace_entity.ace_other.ace_attribute-name,\
    .ace-saylor .ace_variable {\
    color: #5cb85c\
    }\
    .ace-saylor .ace_variable.ace_parameter {\
    font-style: italic;\
    color: #FD971F\
    }\
    .ace-saylor .ace_string {\
    color: #f7db00\
    }\
    .ace-saylor .ace_comment {\
    color: #424242\
    }\
    .ace-saylor .ace_indent-guide {\
    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAEklEQVQImWPQ0FD0ZXBzd/wPAAjVAoxeSgNeAAAAAElFTkSuQmCC) right repeat-y\
    }";

    var dom = require("../lib/dom");
    dom.importCssString(exports.cssText, exports.cssClass, false);
    });                (function() {
                        ace.require(["ace/theme/saylor"], function(m) {
                            if (typeof module == "object" && typeof exports == "object" && module) {
                                module.exports = m;
                            }
                        });
                    })();
});