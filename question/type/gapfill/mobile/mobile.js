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
 * support for the mdl35+ mobile app. PHP calls this from within
 * classes/output/mobile.php
 */
/* jshint esversion: 6 */
/* eslint-disable no-console */

var that = this;
var result = {

    componentInit: function() {
      debugger;
        /**
         * If the question is in a readonly state, e.g. after being
         * answered or in the review page then stop any further
         * selections.
         *
         * @param {NodeList} draggables
         * @param {MouseEvent} event
         * @return {string} value of target
         **/
        function pickAnswerOption(draggables, event) {
            /* If the question is in a readonly state, e.g. after being
             * answered or in the review page then stop any further
             * selections.
             */
            if (event.currentTarget.classList.contains('readonly')) {
                return false;
            }
            event.currentTarget.classList.toggle('picked');
            for (var i = 0; i < draggables.length; i++) {
                if (draggables[i].id == event.currentTarget.id) {
                    /* Continue if this is just picked draggable */
                    continue;
                }
                /* Remove picked class from everything else*/
                draggables[i].classList.remove('picked');
            }
            return event.currentTarget;
        }
        this.questionRendered = function questionRendered() {
            var self = this;
	          var picked = {};
            var LastItemClicked = '';
            self.LastItemClicked = LastItemClicked;
            var draggables = this.componentContainer.querySelectorAll('.draggable');
            var i;
            for (i = 0; i < draggables.length; i++) {
                /* Optionsaftertext reference is to stop the listener being applied twice */
                if (draggables[i].id && !this.question.optionsaftertext) {
                    draggables[i].addEventListener('click', function() {
                     picked  = pickAnswerOption(draggables, event);
                     self.LastItemClicked = picked.innerHTML;
                    });
                }
            }
            var droptargets = this.componentContainer.querySelectorAll('.droptarget');
            for (i = 0; i < droptargets.length; i++) {
                       /* Paste text from last click into the droptarger */
                       droptargets[i].addEventListener('click', function(event) {
                        event.currentTarget.value = self.LastItemClicked;
                        hideDropped(draggables, event);
                   })
                    /* Clear contents on double click */
                    droptargets[i].addEventListener('dblclick', function(event) {
                       // event.currentTarget.value = '';
                          showCleared(draggables,event);
                    })
            }
        };
        function hideDropped(draggables, event) {
          for (i = 0; i < draggables.length; i++) {
            if (draggables[i].innerHTML == event.currentTarget.value) {
              draggables[i].style.display = "none";
            } else {
              draggables[i].style.display = "inline-block";
            }
          }
        };

        function showCleared(draggables, event) {
          debugger;
          for (i = 0; i < draggables.length; i++) {
            if(draggables[i].innerHTML == event.target.value){
              draggables[i].style.display = "inline-block";
              draggables[i].classList.remove('picked');
            }
          }
          event.currentTarget.value = '';

        };

        if (!this.question) {
            console.warn('Aborting because of no question received.');
            return that.CoreQuestionHelperProvider.showComponentError(that.onAbort);
        }
        var div = document.createElement('div');
        div.innerHTML = this.question.html;
         // Get question questiontext.
        var questiontext = div.querySelector('.qtext');

        // Replace Moodle's correct/incorrect and feedback classes with our own.
        this.CoreQuestionHelperProvider.replaceCorrectnessClasses(div);
        this.CoreQuestionHelperProvider.replaceFeedbackClasses(div);

         // Treat the correct/incorrect icons.
        this.CoreQuestionHelperProvider.treatCorrectnessIcons(div);

         // Get answeroptions/draggables.
        var answeroptions = div.querySelector('.answeroptions');

        if (div.querySelector('.readonly') !== null) {
            this.question.readOnly = true;
        }
        if (div.querySelector('.feedback') !== null) {
            this.question.feedback = div.querySelector('.feedback');
            this.question.feedbackHTML = true;
        }

        /* Set all droptargets to disabled but remove the faded look shown on ios
         * This prevents the keyboard popping up when a droppable is dropped onto
         * a droptarget.
         */
        if (answeroptions !== null) {
            var droptargets = questiontext.querySelectorAll('.droptarget');
            for (var i = 0; i < droptargets.length; i++) {
                droptargets[i].style.webkitOpacity = 1;
                droptargets[i].readOnly = true;
            }
            this.question.answeroptions = answeroptions.innerHTML;
        }

        this.question.text = this.CoreDomUtilsProvider.getContentsOfElement(div, '.qtext');

        if (typeof this.question.text == 'undefined') {
            this.logger.warn('Aborting because of an error parsing question.', this.question.name);
            return this.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }

        // @codingStandardsIgnoreStart
        // see MDL-64516
        // Wait for the DOM to be rendered.
        setTimeout(()=> {
            /* Set isdragdrop to true if it is a dragdrop question. This will then be used
            * in template.html to determine when to show the  blue "tap to select..." prompt
            */
            if (div.querySelectorAll('.draggable').length > 0 ) {
                this.question.isdragdrop = true;
            }
            if (div.querySelector('#gapfill_optionsaftertext') !== null) {
                this.question.optionsaftertext = true;
            }

            if (div.querySelector('#gapfill_singleuse') !== null) {
                this.question.singleuse = true;
            }
        });
        // @codingStandardsIgnoreEnd
        return true;
    }
};
result;
