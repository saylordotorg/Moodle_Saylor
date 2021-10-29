# Adding support for Maxima packages

Local code, or links to local libraries, can be placed in `maxima\local.mac`.

Maxima has a very wide range of optional packages.  Some of these write to the server file system, and so we do not permit question authors to load arbitrary packages into Maxima.  Instead the developers maintain a "whitelist" of packages.

There is currently no support for a question by question loading of packages.  This may be included in the future, subject to demand/need.  Currently there is a site-wide option to load packages.

Adding in support for additional Maxima packages needs to be done by a developer on the server.

1. The list of supported packages is defined `stack/cas/installhelper.class.php` in the setting

   `public static $maximalibraries = '...';`

2. The default settings are defined in 

   `qtype_stack | maximalibraries`

   It is probably sensible to make the default to load *all* the available packages, so new installations can see what is supported. 

3. Just because the package is loaded into Maxima does not mean that STACK users will be permitted to use the function names in questions.
   * STACK only allows student users to input a certain restricted list of commands.
   * STACK allows teachers to use a much wider list of commands, and to define functions of their own.  However unless a function has been checked, it is likely the teacher will _not_ be permitted to use this.  We periodically work through all the function names in all maxima packages and add these to the variable `security-map.json`.

Some notes.

1. The developers welcome proposals for support of packages.  Mostly we haven't needed them ourselves, and so didn't add them yet!  Where individual functions are needed, it is sometimes better just to always load the package anyway rather than adding it as a site-wide option.  One historic reason for having site-wide options was to ensure back compatibility with older versions of Maxima.
2. Some packages do make Maxima significantly slower to load and run.  We will check this before allowing a particular package.  (This is a good reason to allow question to load packages of course...)
3. Beware of new global variables, changes/side effects in other functions.

## Maxima graph theory package

For use with STACK, Maxima needs to connect to the web server (PHP scripts) and this is actually done by passing text files back and forward.  So every expression to the CAS, and every result back, must have a simple string format.

Some of the contributed packages, including the very nice graph theory package, have developed their own internal representation for mathematical objects.  For example,

    gf_set_data(2,x^4+x+1);

returns

    "Structure [GF-DATA]"

As of Nov 2020, the STACK developers have not investigated what a "Structure [GF-DATA]" is or how that could be communicated between PHP and Maxima!  By way of contrast a simple polynomial x^4+x+1 has an internal (LISP) tree structure of

    ((MPLUS SIMP) 1 $X ((MEXPT SIMP) $X 4))

which you can read this as

   ((+) 1 x ((^) x 4)

We must have some kind of string like that to communicate and STACK basically uses the Maxima `string` command to do this.  It is very likely that without re-writing the internals of the graph theory package it is not compatible with the way we connect to Maxima.
