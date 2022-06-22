#!/bin/bash
# This file is part of VPL for Moodle
# Script for debugging C# language
# Copyright (C) 2012 Juan Carlos Rodríguez-del-Pino
# License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
# Author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>

#@vpl_script_description Debug using gdb
#load common script and check programs
. common_script.sh
export MONO_ENV_OPTIONS=--gc=sgen
check_program gdb
if [ "$1" == "version" ] ; then
	get_program_version --version
fi
check_program csc mcs
get_source_files cs
# Generate file with source files
generate_file_of_files .vpl_source_files
# Compile
$PROGRAM -debug -out:output.exe @.vpl_source_files
rm .vpl_source_files
if [ -f output.exe ] ; then
    echo "handle SIGXCPU SIG33 SIG35 SIGPWR nostop noprint" >> .dbinit
	cat common_script.sh > vpl_execution
	echo "export MONO_ENV_OPTIONS=--gc=sgen" >> vpl_execution
	echo "gdb -args mono --debug output.exe" >> vpl_execution
	chmod +x vpl_execution
fi
