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
 * This file is the equivalent of 
 * qtype/algebra/classes/algebra.ts in the core app
 * e.g.
 * https://github.com/moodlehq/moodlemobile2/blob/v3.5.0/src/addon/qtype/ddwtos/classes/ddwtos.ts
 */


var that = this;
var result = {

    componentInit: function() {
        if (!this.question) {
            console.warn('Aborting because of no question received.');
            return that.CoreQuestionHelperProvider.showComponentError(that.onAbort);
        }
        const div = document.createElement('div');
        div.innerHTML = this.question.html;
         // Get question questiontext.
        const questiontext = div.querySelector('.qtext');

        // Replace Moodle's correct/incorrect and feedback classes with our own.
        // Only do this if you want to use the standard classes
        this.CoreQuestionHelperProvider.replaceCorrectnessClasses(div);
        this.CoreQuestionHelperProvider.replaceFeedbackClasses(div);

         // Treat the correct/incorrect icons.
        this.CoreQuestionHelperProvider.treatCorrectnessIcons(div);

 
        if (div.querySelector('.readonly') !== null) {
            this.question.readonly = true;
        }
        if (div.querySelector('.feedback') !== null) {
            this.question.feedback = div.querySelector('.feedback');
            this.question.feedbackHTML = true;
        }
        
         this.question.text = this.CoreDomUtilsProvider.getContentsOfElement(div, '.qtext');

        if (typeof this.question.text == 'undefined') {
            this.logger.warn('Aborting because of an error parsing question.', this.question.name);
            return this.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }
        
        // Called by the reference in *.html to 
        // (afterRender)="questionRendered()
        this.questionRendered = function questionRendered() {
            //do stuff that needs the question rendered before it can run.
        }

        // Wait for the DOM to be rendered.
        setTimeout(() => {
            //put stuff here that will be pulled from the rendered question
        });
        return true;
    }
};
result;
