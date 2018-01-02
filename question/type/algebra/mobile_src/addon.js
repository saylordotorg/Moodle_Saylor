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
 * @package    qtype_algebra
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

angular.module('mm.addons.qtype_algebra', ['mm.core'])
.config(["$mmQuestionDelegateProvider", function($mmQuestionDelegateProvider) {
    $mmQuestionDelegateProvider.registerHandler('mmaQtypeAlgebra', 'qtype_algebra', '$mmaQtypeAlgebraHandler');
}]);

angular.module('mm.addons.qtype_algebra')
.directive('mmaQtypeAlgebra', ["$log", "$mmQuestionHelper", function($log, $mmQuestionHelper) {
	$log = $log.getInstance('mmaQtypeAlgebra');
    return {
        restrict: 'A',
        priority: 100,
        templateUrl: 'addons/qtype/algebra/template.html',
        link: function(scope) {
        	$mmQuestionHelper.inputTextDirective(scope, $log);
        }
    };
}]);

angular.module('mm.addons.qtype_algebra')
.factory('$mmaQtypeAlgebraHandler', ["$mmUtil", function($mmUtil) {
    var self = {};
        self.isCompleteResponse = function(question, answers) {
        return answers['answer'] || answers['answer'] === 0;
    };
        self.isEnabled = function() {
        return true;
    };
        self.isGradableResponse = function(question, answers) {
        return self.isCompleteResponse(question, answers);
    };
        self.isSameResponse = function(question, prevAnswers, newAnswers) {
        return $mmUtil.sameAtKeyMissingIsBlank(prevAnswers, newAnswers, 'answer');
    };
        self.getDirectiveName = function(question) {
        return 'mma-qtype-multichoice-set';
    };
    return self;
}]);
