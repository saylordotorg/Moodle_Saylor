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
         // Get question input.
        const input = div.querySelector('input[type="text"][name*=answer]');

        if (div.querySelector('.readonly') !== null) {
            this.question.readonly = true;
        }
        if (div.querySelector('.feedback') !== null) {
            this.question.feedback = div.querySelector('.feedback');
            this.question.feedbackHTML = true;
        }

        this.question.text = questiontext.innerHTML;
        this.question.input = input;

        if (typeof this.question.text == 'undefined') {
            this.logger.warn('Aborting because of an error parsing question.', this.question.name);
            return this.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }

        // Check if question is marked as correct.
        if (input.classList.contains('incorrect')) {
            this.question.input.correctClass = 'qtype-algebra question-incorrect';
        } else if (input.classList.contains('correct')) {
            this.question.input.correctClass = 'qtype-algebra question-correct';
        } else if (input.classList.contains('partiallycorrect')) {
            this.question.input.correctClass = 'qtype-algebra question-partiallycorrect';
        }

        // @codingStandardsIgnoreStart
        // Wait for the DOM to be rendered.
        setTimeout(() => {

        });
        // @codingStandardsIgnoreEnd
        return true;
    }
};
result;