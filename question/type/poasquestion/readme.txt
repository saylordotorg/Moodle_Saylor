POAS question
---------------------------------------
This question type is an abstract one, i.e. you are never supposed to create such question in user interface.
It contains several pieces of code, useful to question authoring, developed by POAS (software engineering)
department of Volgograd State Technical University. Please write as (poas@vstu.ru) if you use and like it.

Main code segments:
* support for backuping and restoring extra_question_fields (written by Zhorin Stanislav, Sychev Oleg, Streltsov Valeriy)
    If you question contains only one DB table - as many questions does - than you need not to write backup/restore code at all!
    Just inherit from these classes.
* abstract hint classes (written by Sychev Oleg)
    If you need advanced hinting in you question, you could inherit hint definitions from these classes and use
    "adaptivehints" and "adaptivehintsnopenalties" behaviours
* Unicode string class (written by Streltsov Valeriy)
    allows to use [] on UTF-8 string without problems and call most core_text functions (and some more, like ord)
    automatically converts to string
* support for string tokenizing using JLex PHP
    - JLex PHP base file, modified to work with UTF-8 correctly
    - stringstream library, useful to open strings like files to JLex, even when site security don't allows you to use data:// protocol
