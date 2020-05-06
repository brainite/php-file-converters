# PHP File Converters

[![Build Status](https://travis-ci.org/brainite/php-file-converters.png?branch=master)](https://travis-ci.org/brainite/php-file-converters)

This PSR-4 library provides a unified interface for various file conversion utilities.

## Engines Currently Supported

### Convert Engines

- AbiWord
- Catdoc
- Docverter
- GhostScript
- Htmldoc
- ImageMagick
- LibreOffice
- Pandoc
- PhantomJs
- Ted
- Unoconv
- Unrtf
- WkHtmlToPdf
- Xhtml2Pdf

### Optimize Engines

- JpegOptim
- Pdftk

### ReplaceString

- Native (custom for FileConverter!)

## Getting Started

### Installation

<p>Option 1: Add the "brainite/fileconverter" requirement to your composer.json configuration.</p>
<p>Option 2: From the command-line, execute: composer create-project brainite/fileconverter</p>
<p>Option 3: Download the source code from <a href="https://github.com/brainite/php-file-converters">Github</a> and then run `composer update`.</p>

### CLI: Command Line Example
```bash
<path>/bin/fileconverter <source> <dest>
```

### PHP Example with Composer Autoload

```php
<?php
$fc = \Brainite\FileConverter\FileConverter::factory();
$fc->convertFile($source, $destination);
```

### CLI: STDIN/STDOUT

Use a hyphen to indicate STDIN (for input) or STDOUT (for output).

```bash
prompt> echo "## hi ##" | fileconverter - - --conversion=md:html
<h2 id="hi">hi</h2>
```

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
