function renderHtmlParser(htmlParser) {
    // Get the value of the html area.
    let htmlArea = $(htmlParser).find(".html-parser.html-area").val();
    // Create style and script objects for the CSS and JS areas. Append values.
    let styleTag = document.createElement("style");
    $(styleTag).append($(htmlParser).find(".html-parser.css-area").val());
    let scriptTag = document.createElement("script");
    $(scriptTag).append($(htmlParser).find(".html-parser.js-area").val());

    console.info("Rendering HTML Parser: ");
    console.info("HTML: ", htmlArea);
    console.info("CSS: ", styleTag);
    console.info("JS: ", scriptTag);

    // Append the CSS and JS to the document.
    // Using outerHTML to stringify the object.
    htmlArea += styleTag.outerHTML;
    htmlArea += scriptTag.outerHTML;
    console.info("Rendered HTML Parser: ", htmlArea);

    $(htmlParser).find(".rendered-html").attr('srcdoc', htmlArea);
}

async function monitorHtmlAreaChange(htmlParser) {
    $(htmlParser).find(".html-parser.html-area").on('change keyup paste input', function () {
        console.info('Registered HTML Parser HTML area change:', $(htmlParser).find(".html-parser.html-area").val());
        renderHtmlParser(htmlParser);
    });
}

async function monitorCssAreaChange(htmlParser) {
    $(htmlParser).find(".html-parser.css-area").on('change keyup paste input', function () {
        console.info('Registered HTML Parser CSS area change:', $(htmlParser).find(".html-parser.css-area").val());
        renderHtmlParser(htmlParser);
    });
}

async function monitorJsAreaChange(htmlParser) {
    $(htmlParser).find(".html-parser.js-area").on('change keyup paste input', function () {
        console.info('Registered HTML Parser JS area change:', $(htmlParser).find(".html-parser.js-area").val());
        renderHtmlParser(htmlParser);
    })
}

require(['jquery'], function ($) {
    $(window).on('load', function() {
        // Check if a html-parser-wrapper is present.
        if (document.getElementsByClassName("html-parser-wrapper").length > 0 && !(window.location.href.indexOf("/question/question.php") > -1)) {
            console.log("Detected a html-parser-wrapper, loading ACE.");
            require.config({paths: { "ace" : "/theme/saylor/javascript/ace/v1.4.13"}});
            require(['ace/ace'], function (ace) {
                ace.EditSession.prototype.$useWorker=true;
                // Get html-parser wrappers.
                $('.html-parser-wrapper').each( function(index, htmlParser) {
                    // Remove loading message.
                    $(htmlParser).find('textarea[name="crui_html"]').empty();
                    var htmlArea = $(htmlParser).find('textarea[name="crui_html"]').hide();
                    // Set unique ID.
                    $(htmlParser).find('.html-question').attr('id', 'ace-editor-html' + index);
                    var htmlEditor = ace.edit("ace-editor-html" + index);
                    htmlEditor.setTheme("ace/theme/saylor");
                    htmlEditor.getSession().setMode("ace/mode/html");
                    ace.config.loadModule('ace/ext/language_tools', function () {
                        htmlEditor.setOptions({
                            fontSize: "16pt",
                            showLineNumbers: true,
                            showGutter: true,
                            vScrollBarAlwaysVisible:true,
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true
                        });
                    });
                    htmlEditor.getSession().setValue(htmlArea.val());
                    htmlEditor.getSession().on('change', function() {
                    htmlArea.val(htmlEditor.getSession().getValue());
                    htmlArea.change();
                    });

                    // Remove loading message.
                    $(htmlParser).find('textarea[name="crui_css"]').empty();
                    var cssArea = $(htmlParser).find('textarea[name="crui_css"]').hide();
                    // Set unique ID.
                    $(htmlParser).find('.css-question').attr('id', 'ace-editor-css' + index);
                    var cssEditor = ace.edit("ace-editor-css" + index);
                    cssEditor.setTheme("ace/theme/saylor");
                    cssEditor.getSession().setMode("ace/mode/css");
                    ace.config.loadModule('ace/ext/language_tools', function () {
                        cssEditor.setOptions({
                            fontSize: "16pt",
                            showLineNumbers: true,
                            showGutter: true,
                            vScrollBarAlwaysVisible:true,
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true
                        });
                    });
                    cssEditor.getSession().setValue(cssArea.val());
                    cssEditor.getSession().on('change', function() {
                      cssArea.val(cssEditor.getSession().getValue());
                      cssArea.change();
                    });

                    // Remove loading message.
                    $(htmlParser).find('textarea[name="crui_js"]').empty();
                    var jsArea = $(htmlParser).find('textarea[name="crui_js"]').hide();
                    // Set unique ID.
                    $(htmlParser).find('.js-question').attr('id', 'ace-editor-js' + index);
                    var jsEditor= ace.edit("ace-editor-js" + index);
                    jsEditor.setTheme("ace/theme/saylor");
                    jsEditor.getSession().setMode("ace/mode/javascript");
                    ace.config.loadModule('ace/ext/language_tools', function () {
                        jsEditor.setOptions({
                            fontSize: "16pt",
                            showLineNumbers: true,
                            showGutter: true,
                            vScrollBarAlwaysVisible:true,
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true
                        });
                    });
                    jsEditor.getSession().setValue(jsArea.val());
                    jsEditor.getSession().on('change', function() {
                        jsArea.val( jsEditor.getSession().getValue());
                        jsArea.change();
                      });
                    
                    // Initial render of the html parser questions.
                    renderHtmlParser(htmlParser);

                    // Initialize async monitoring functions to render any changes in the editor.
                    monitorHtmlAreaChange(htmlParser);
                    monitorCssAreaChange(htmlParser);
                    monitorJsAreaChange(htmlParser);
                });

            });

        }
    });
});