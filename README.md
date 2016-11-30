PHP File Converters
===================

[![Build Status](https://travis-ci.org/wittiws/php-file-converters.png?branch=master)](https://travis-ci.org/wittiws/php-file-converters)

This PSR-4 library provides a unified interface for various file conversion utilities. For installation instructions, visit the project home page at http://www.witti.ws/project/php-file-converters.


## Default Configured Converters

This table shows the number of converters configured by default between file extensions. This markdown is generated from the command-line:

    fileconverter list extension-table

source | asciidoc | context | dbk | docbook | docx | eml | epub | epub3 | fb2 | html | jpg | latex | man | markdown | md | mediawiki | mobi | odt | opml | org | pdf | pdf/grayscale | ps | rtf | texinfo | textile | txt
--- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | ---
bib |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
dbk |  |  | 1 |  | 1 |  | 1 | 1 | 1 | 1 |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  | 1 |  |  | 1
doc |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 1
docbook | 1 | 1 |  | 1 | 1 |  | 1 |  |  | 1 |  | 1 |  | 1 |  | 1 | 1 |  |  | 1 | 1 |  |  | 1 | 1 | 1 | 
html | 1 | 1 | 1 | 1 | 2 | 1 | 2 | 1 | 1 | 4 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 9 |  |  | 2 | 1 | 1 | 1
jpg |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 
latex | 1 | 1 |  | 1 | 1 |  | 1 |  |  | 1 |  | 1 |  | 1 |  | 1 | 1 |  |  | 1 | 1 |  |  | 1 | 1 | 1 | 
ltx |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
markdown | 1 | 1 |  | 1 | 1 |  | 1 |  |  | 1 |  | 1 |  | 1 |  | 1 | 1 |  |  | 1 | 1 |  |  | 1 | 1 | 1 | 
md |  |  | 1 |  | 1 |  | 1 | 1 | 1 | 1 |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  | 1 |  |  | 1
opml |  |  | 1 |  | 1 |  | 1 | 1 | 1 | 1 |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  | 1 |  |  | 1
pdb |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
pdf |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  | 1 | 1 |  |  |  |  | 
ps |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
psw |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
rst | 1 | 1 | 1 | 1 | 2 |  | 2 | 1 | 1 | 2 |  | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 2 |  |  | 2 | 1 | 1 | 1
rtf |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 4 |  | 2 |  |  |  | 
sdw |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
sxw |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
tex |  |  | 1 |  | 1 |  | 1 | 1 | 1 | 1 |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  | 1 |  |  | 1
textile | 1 | 1 | 1 | 1 | 2 |  | 2 | 1 | 1 | 2 |  | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 2 |  |  | 2 | 1 | 1 | 1
txt |  |  | 1 |  | 1 |  | 1 | 1 | 1 | 1 |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 2 |  |  | 1 |  |  | 1
vor |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  | 
wiki |  |  | 1 |  | 1 |  | 1 | 1 | 1 | 2 |  |  | 1 |  | 2 |  |  | 1 | 1 |  | 1 |  |  | 1 |  |  | 1
