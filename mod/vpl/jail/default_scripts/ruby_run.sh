#!/bin/bash
# This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
# Script for running Ruby language
# Copyright (C) 2012 Juan Carlos Rodríguez-del-Pino
# License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
# Author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>

# @vpl_script_description Using default ruby
# load common script and check programs
. common_script.sh
check_program ruby
if [ "$1" == "version" ] ; then
	echo "#!/bin/bash" > vpl_execution
	echo "ruby --version" >> vpl_execution
	chmod +x vpl_execution
	exit
fi
get_first_source_file ruby rb
cat common_script.sh > vpl_execution
echo "ruby \"$FIRST_SOURCE_FILE\" \$@" >>vpl_execution
chmod +x vpl_execution
