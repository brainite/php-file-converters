---
title: PHP File Converters
---

PHP File Converters provides a unified interface to various tools that PHP developers use on a regular basis for file conversions.

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

<p>Option 1: Add the "wittiws/php-file-converters" requirement to your composer.json configuration.</p>
<p>Option 2: From the command-line, execute: composer create-project wittiws/php-file-converters</p>
<p>Option 3: Download the source code from <a href="https://github.com/wittiws/php-file-converters">Github</a> and then run `composer update`.</p>
<h3>
	CLI: Command Line Example</h3>
<pre class="brush:bash">
&lt;path&gt;/bin/fileconverter &lt;source&gt; &lt;dest&gt;</pre>

### PHP Example with Composer Autoload

```php
<?php
$fc = \Witti\FileConverter\FileConverter::factory();
$fc->convertFile($source, $destination);
```

### CLI: STDIN/STDOUT

Use a hyphen to indicate STDIN (for input) or STDOUT (for output).

```bash
prompt> echo "## hi ##" | fileconverter - - --conversion=md:html
<h2 id="hi">hi</h2>
```
