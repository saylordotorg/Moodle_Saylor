/**
 * Apps4EFL module.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - poodll.com
 * (based on Paul Raine's APPs 4 EFL)
 */

define([
  'jquery',
  'core/log',
  'core/ajax',
  'mod_wordcards/flip',
  'mod_wordcards/textfit',
  'core/templates',
  'mod_wordcards/pollyhelper',
], function($, log, Ajax, flip, textFit, templates, polly) {

  var a4e = {

    face: true,

    register_events: function() {
      console.log("register_events within a4e.js");
    },

    init_audio: function(token, region, owner){

        //Init Polly TTS
        polly.init(token, region, owner);

        //play what was returned in polly.fetch_polly_url (callback)
        polly.onnewpollyurl = function(theurl) {
            var theplayer = $("#poodll_vocabplayer");
            theplayer.attr('src', theurl);
            theplayer[0].play();
        };

        //register button event handler to play audio
        $(document.body).on('click','.a4e-flashcards-container .play-tts, span.model-sentence-play-tts,.definitions-container .definition-play-tts ',function() {
            var theplayer = $("#poodll_vocabplayer");

            //if we have model audio use that, otherwise TTS
            var audiourl=$(this).attr('data-modelaudio');
            if(audiourl && (audiourl.indexOf('http:')===0 ||audiourl.indexOf('https:')===0)){
                theplayer.attr('src', audiourl);
                theplayer[0].play();
            }else{
                polly.fetch_polly_url($(this).attr('data-tts'), 'text', $(this).attr('data-ttsvoice'));
            }
        });
    },

    progress_dots: function(results, terms) {

      var code = "",
        color;
      terms.forEach(function(o, i) {
        var color = "darkgray";
        if (results[i] !== undefined) {
          if (results[i].points) {
            color = "green";
          } else {
            color = "red";
          }
        }
        code += '<i style="color:' + color + ';" class="fa fa-circle"></i>';
      });

      $("#wordcards-progress-dots").html(code);

    },

    shuffle: function(a) {
      var j, x, i;
      for (i = a.length; i; i -= 1) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
      }
    },
    pretty_print_secs: function(time) {
      var minutes = Math.floor(time / 60);
      var seconds = time - minutes * 60;
      return a4e.str_pad_left(minutes, '0', 2) + ':' + a4e.str_pad_left(seconds, '0', 2);
    },

    list_vocab: function(target, terms, frontfaceflip) {

      //template data
      var tdata = [];
      tdata['terms'] = terms;
      templates.render('mod_wordcards/vocablist', tdata).then(
        function(html, js) {
          $(target).html(html);

          var cards = $(".a4e-flashcards-container .a4e-card");
          var faces = $(".front,.back");

          setTimeout(function() {
            cards.flip({axis:'x'});
            textFit(faces, {
              multiLine: true,
              maxFontSize: 50,
              alignHoriz: true,
              alignVert: true
            });
          }, 100);

          $('.a4e-flashcards-container .mod_wordcards_matching_frontbtn').on('click', function() {
            $(".a4e-flashcards-container .a4e-card").flip(a4e.face);
            a4e.face = !a4e.face;
          });

        }
      );

    },
    calc_total_points: function(results) {
      var total = 0;
      $.each(results, function(i, o) {
        if (o.points != undefined) {
          total += o.points;
        }
      });
      return total;
    },
    basic_feedback: function(results) {

      var total = 0;
      var total_time = 0;

      $.each(results, function(i, o) {
        if (o.time != null) {
          total_time += o.time;
        }
        if (o.points != undefined) {
          total += o.points;
        }
      });
      var code = "<div class='a4e-basic_feedback'><h2>";
      code += "<i class='fa fa-trophy'></i> " + total + " points";
      if (total_time != 0) {
        code += "<hr/><i class='fa fa-clock-o'></i> " + a4e.pretty_print_secs(total_time)
      };
      code += "</h2></div>";
      return code;

    },
    detailed_feedback: function(results) {

      var code = "<div style='text-align:center;'>",
        color;

      $.each(results, function(i, o) {

        color = "";

        if (o.points > 0) {
          color = "a4e-correct";
        } else {
          color = "a4e-incorrect";
        }

        code += "<div class='a4e-detailed_feedback_div " + color + "'>";
        code += "<h3 style='margin-bottom:10px;margin-top:5px;'>" + (i + 1) + "</h3>";
        $.each(o, function(k, v) {
          if (k == "time") {
            code += "<p><strong>" + a4e.ucfirst(k) + "</strong>:<br/>" + a4e.pretty_print_secs(v) + "</p>";
          } else {
            code += "<p><strong>" + a4e.ucfirst(k) + "</strong>:<br/>" + (v == "" ? "N/A" : v) + "</p>";
          }
        });
        code += "</div>";
      });

      code += "</div>";

      return code;

    },

    str_pad_left: function(string, pad, length) {
      return (new Array(length + 1).join(pad) + string).slice(-length);
    },

    ucfirst: function(str) {
      str += ''
      var f = str.charAt(0).toUpperCase()
      return f + str.substr(1)
    }

  };

  return a4e;

});