function renderHtmlParser(htmlParser) {
    let htmlArea = $(htmlParser).find(".html-parser.html-area").val();
    console.info("html-area: ", htmlArea);
    let styleTag = document.createElement("style");
    styleTag.textContent = $(htmlParser).find(".html-parser.css-area").val();
    let scriptTag = document.createElement("script");
    scriptTag.textContent = $(htmlParser).find(".html-parser.js-area").val();

    $(htmlArea).find("html").find("head").append(styleTag);
    $(htmlArea).find("html").find("body").append(scriptTag);
    console.info("Rendering HTML area: ", htmlArea);

    $(htmlParser).find(".rendered-html").attr('srcdoc', htmlArea);
}

async function monitorHtmlAreaChange(htmlParser) {
    $(htmlParser).find(".html-parser.html-area").on('change keyup paste input', function () {
        console.info('HTML Parser HTML Change:', $(htmlParser).find(".html-parser.html-area").val());
        renderHtmlParser(htmlParser);
    });
 }

 async function monitorCssAreaChange(htmlParser) {
    $(htmlParser).find(".html-parser.css-area").on('change keyup paste input', function () {
        console.info('HTML Parser CSS Change:', $(htmlParser).find(".html-parser.css-area").val());
        renderHtmlParser(htmlParser);
    });
 }

 async function monitorJsAreaChange(htmlParser) {
    $(htmlParser).find(".html-parser.js-area").on('change keyup paste input', function () {
        console.info('HTML Parser JS Change:', $(htmlParser).find(".html-parser.js-area").val());
        renderHtmlParser(htmlParser);
    })
 }

require(['jquery'], function ($) {
    $(window).on('load', function() {
        // Check if a html-parser-wrapper is present.
        if (document.getElementsByClassName("html-parser-wrapper").length > 0) {
            console.log("Detected a html-parser-wrapper, loading ACE.");
            require.config({paths: { "ace" : "/theme/saylor/javascript/ace/v1.4.13"}});
            require(['ace/ace'], function (ace) {
                ace.EditSession.prototype.$useWorker=false;
                // Get html-parser wrappers.
                $('.html-parser-wrapper').each( function(index, htmlParser) {
                    // Remove loading message.
                  $(htmlParser).find('textarea[name="crui_html"]').empty();
                  var htmlArea = $(htmlParser).find('textarea[name="crui_html"]').hide();
                  // Set unique ID.
                  $(htmlParser).find('.html-question').attr('id', 'ace-editor-html' + index);
                  var htmlEditor = ace.edit("ace-editor-html" + index);
                  htmlEditor.setTheme("ace/theme/solarized_dark");
                  htmlEditor.getSession().setMode("ace/mode/html");
                  htmlEditor.setOptions({
                  fontSize: "16pt",
                  showLineNumbers: true,
                  showGutter: true,
                  vScrollBarAlwaysVisible:true,
                  //enableBasicAutocompletion: false, 
                  //enableLiveAutocompletion: false
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
                  cssEditor.setTheme("ace/theme/solarized_dark");
                  cssEditor.getSession().setMode("ace/mode/css");
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
                    jsEditor.setTheme("ace/theme/solarized_dark");
                    jsEditor.getSession().setMode("ace/mode/javascript");
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