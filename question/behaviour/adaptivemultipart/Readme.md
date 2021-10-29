# Adaptive question behaviour for multi-part questions.

This Moodle question behaviour was created by Tim Hunt of the Open University.

It is like the standard adaptive behaviour, but for questions that are considered
to be made up of a number of separate parts. Each part of the question can register
a try at different times (whenever its inputs are complete, valid and have changed
since the last try). This question behaviour was created for use with STACK
https://github.com/maths/moodle-qtype_stack/

To install, either [download the zip file](https://github.com/maths/moodle-qbehaviour_adaptivemultipart/zipball/master),
unzip it, and place it in the directory `moodle\question\behaviour\dfcbmexplicitvaildate`.
(You will need to rename the directory `moodle-qbehaviour_adaptivemultipart -> adaptivemultipart`.)
Alternatively, get the code using git by running the following command in the
top level folder of your Moodle install:

    git clone git://github.com/maths/moodle-qbehaviour_adaptivemultipart.git question/behaviour/adaptivemultipart

For full install instructions, see the [STACK install instructions](https://github.com/maths/moodle-qtype_stack/blob/master/doc/en/Installation/index.md).
