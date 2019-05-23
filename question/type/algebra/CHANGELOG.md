#### Version 1.9 (March 27th, 2019)

The algebra question type can now be used as a subquestion of a combined question (see https://moodle.org/plugins/qtype_combined)
There are some limitations when you insert an algebra question in a combined question:

  - There is no Answer box prefix because you can write what you want in the combined question text before the algebra subquestion.
  - Allowed Functions is always "All" as this feature was never implemented (I must remove it one day or finish it, it is here like that from the beginning when Roger Moore created this question type)
  - Each answer is always 100% and there is no answer feedback, only a global feedback for the subquestion when the student response is not correct (All combinable question types seems to have the same limitations so I don't know if this is a limitation of the combined API)

The algebra question now support Moodle mobile (only available with Moodle 3.5 and ulterior versions, will not work with Moodle previous versions). A big thank you to Marcus Green for all his work on adding mobile support to question types.

#### Version 1.0 (September 30th 2012)
  - New setting to select the default comparison method
  - MathJax will be used for TeX rendering if MathJax is installed
  - Additionnal HTML in HEAD (will not work if Mathjax is installed using a theme)

#### Version 0.0.4
Improvements
  - Export and import to Moodle XML format
  - Backup and restore functions added
Bug fixes
  - Fixed parser problem with negative numbers

#### Version 0.0.3
Improvements
  - Added danish localizations based on forum feedback: mltiplication now
    uses 'cdot' and decimal points are rendered as commas when Danish is
    selected as a language
Bug fixes
  - Operator priority, BODMAS, not quite implemented correctly. */ and +- not
    implemented as equal priority - now fixed

#### Version 0.0.2
Significant changes as a result of the first round of feedback!
  - Renamed parser classes to conform to coding guidelines
  - Moved all parser strings into a language pack
  - Switched a lot of double quoted string to single as per guidelines
  - added automatic formatted comments as required by coding guidelines
  - changed treatment of variable names to help reduce confusion. Now
    the first letter is treated as the name and the rest are subscripted.
    Greek letter names are treated as a single character i.e. theta1
    becomes \theta_{1} in LaTeX.
  - Added option to specify text which goes in front of response box
  - Added support for specified variable names in the parser to improve
    parsing in some situations e.g. 'xy' will now get treated as 'x * y' if
    there are two variables 'x' and 'y' defined.
Bug fixes
  - fixed bug when evaluating special constants in the parser
  - fixed incorrect rendering of sqrt in LaTeX by the parser
  - fixed incorrect sage-server.py file in the ZIP

#### Version 0.0.1 released
