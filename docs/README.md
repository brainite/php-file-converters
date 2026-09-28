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
- MsgConvert
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

### Requirements

- PHP 8.3 or later (since v0.2.0).
- The command-line tool behind each engine you use, e.g. `pandoc` for Pandoc; Pandoc needs LaTeX (`pdflatex`) only to write PDF.
- mPDF 8.2 or later (`composer require mpdf/mpdf`) only for the Mpdf html->pdf engine; it is suggested rather than required.

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

## Word to HTML (docx->html)

The default `docx->html` path runs Pandoc with `--from=docx --to=html5 --shift-heading-level-by=1`, so Word's Heading 1 becomes `<h2>` (leaving `<h1>` to the page title). Tracked changes are accepted (Pandoc's default). Pandoc only needs LaTeX (`pdflatex`) when the destination is PDF; HTML and other outputs need the `pandoc` binary alone.

To keep embedded images, name a directory for Pandoc's `extract-media` option; the images are written to `<dir>/media/` and the HTML's `<img src>` points there:

```php
<?php
$fc = \FileConverter\FileConverter::factory();
$engines = $fc->getEngines('docx->html', NULL, FALSE);
$configuration = $engines[0]->getConfiguration();
$configuration['extract-media'] = $media_dir;
$configuration['#final'] = TRUE;
$fc->setConverter('docx->html', $configuration);
$fc->convertFile('handbook.docx', 'handbook.html');
```

## Default Configured Converters

This table shows the number of converters configured by default between file extensions. This markdown is generated from the command-line:

    fileconverter info extension-table

source | asciidoc | context | dbk | directory/jpg | directory/png | directory/slideshow | docbook | docx | eml | epub | epub3 | fb2 | gif | html | jpg | json | latex | man | markdown | md | mediawiki | mobi | odt | opml | org | pdf | pdf/grayscale | png | ps | rtf | svg | texinfo | textile | tiff | txt | webp | wmf | zip/jpg | zip/png | zip/slideshow
--- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | ---
bib |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
dbk |  |  | 1 |  |  |  |  | 1 |  | 1 | 1 | 1 |  | 1 |  |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  |  |  | 1 |  |  |  |  | 
doc |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  | 1 |  |  |  |  | 
docbook | 1 | 1 |  |  |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  | 1 |  | 1 |  | 1 | 1 |  |  | 1 | 1 |  |  |  | 1 |  | 1 | 1 |  |  |  |  |  |  | 
docx |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 
gif |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  | 1 | 1 |  |  | 
html | 1 | 1 | 1 |  |  |  | 1 | 2 | 1 | 2 | 1 | 1 |  | 4 | 1 |  | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 10 |  |  |  | 2 |  | 1 | 1 |  | 1 |  |  |  |  | 
jpg |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 2 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  | 1 | 1 |  |  | 
latex | 1 | 1 |  |  |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  | 1 |  | 1 |  | 1 | 1 |  |  | 1 | 1 |  |  |  | 1 |  | 1 | 1 |  |  |  |  |  |  | 
ltx |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
markdown | 1 | 1 |  |  |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  | 1 |  | 1 |  | 1 | 1 |  |  | 1 | 1 |  |  |  | 1 |  | 1 | 1 |  |  |  |  |  |  | 
md |  |  | 1 |  |  |  |  | 1 |  | 1 | 1 | 1 |  | 1 |  |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  |  |  | 1 |  |  |  |  | 
msg |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 
opml |  |  | 1 |  |  |  |  | 1 |  | 1 | 1 | 1 |  | 1 |  |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  |  |  | 1 |  |  |  |  | 
pdb |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
pdf |  |  |  | 1 | 1 |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  | 1 | 1 | 1 |  |  |  |  |  |  |  |  |  | 1 | 1 | 
png |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  | 1 | 1 |  |  | 
ppt |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 2 |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 
pptx |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  | 2 |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1
ps |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
psw |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
rst | 1 | 1 | 1 |  |  |  | 1 | 2 |  | 2 | 1 | 1 |  | 2 |  |  | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 2 |  |  |  | 2 |  | 1 | 1 |  | 1 |  |  |  |  | 
rtf |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 4 |  |  | 2 |  |  |  |  |  |  |  |  |  |  | 
sdw |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
svg |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  | 1 | 1 |  |  | 
sxw |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
tex |  |  | 1 |  |  |  |  | 1 |  | 1 | 1 | 1 |  | 1 |  |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  |  |  | 1 |  |  |  |  | 
textile | 1 | 1 | 1 |  |  |  | 1 | 2 |  | 2 | 1 | 1 |  | 2 |  |  | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 2 |  |  |  | 2 |  | 1 | 1 |  | 1 |  |  |  |  | 
tiff |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  | 1 | 1 |  |  | 
txt |  |  | 1 |  |  |  |  | 1 |  | 1 | 1 | 1 |  | 1 |  |  |  | 1 |  | 1 |  |  | 1 | 1 |  | 2 |  |  |  | 1 |  |  |  |  | 1 |  |  |  |  | 
vor |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  |  | 
webp |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  |  | 1 |  |  | 
wiki |  |  | 1 |  |  |  |  | 1 |  | 1 | 1 | 1 |  | 2 |  |  |  | 1 |  | 2 |  |  | 1 | 1 |  | 1 |  |  |  | 1 |  |  |  |  | 1 |  |  |  |  | 
wmf |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  | 1 |  |  |  |  |  |  |  |  |  |  |  |  | 1 |  |  | 1 |  |  | 1 |  | 1 | 1 |  |  | 
