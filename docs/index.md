---
title: PHP File Converters
---

PHP File Converters provides a unified interface to various tools that PHP developers use on a regular basis for file conversions.

## Engines Currently Supported

<ol><li>
		Convert Engines
		<ol><li>
				<div>
					AbiWord</div>
			</li>
			<li>
				<div>
					Catdoc</div>
			</li>
			<li>
				<div>
					Docverter</div>
			</li>
			<li>
				<div>
					GhostScript</div>
			</li>
			<li>
				<div>
					Htmldoc</div>
			</li>
			<li>
				<div>
					ImageMagick</div>
			</li>
			<li>
				<div>
					LibreOffice</div>
			</li>
			<li>
				<div>
					Pandoc</div>
			</li>
			<li>
				<div>
					PhantomJs</div>
			</li>
			<li>
				<div>
					Ted</div>
			</li>
			<li>
				<div>
					Unoconv</div>
			</li>
			<li>
				<div>
					Unrtf</div>
			</li>
			<li>
				<div>
					WkHtmlToPdf</div>
			</li>
			<li>
				<div>
					Xhtml2Pdf</div>
			</li>
		</ol></li>
	<li>
		Optimize Engines
		<ol><li>
				JpegOptim</li>
			<li>
				Pdftk</li>
		</ol></li>
	<li>
		ReplaceString
		<ol><li>
				Native (custom for PFC!)</li>
		</ol></li>
	<li>
		... and more are coming soon ...</li>
</ol>

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

<pre class="brush:php">
&lt;?php
$fc = \Witti\FileConverter\FileConverter::factory();
$fc-&gt;convertFile($source, $destination);</pre>
<h3>
	CLI: STDIN/STDOUT</h3>
<p>Use a hyphen to indicate STDIN (for input) or STDOUT (for output).</p>
<pre class="brush:bash">
prompt&gt; echo "## hi ##" | fileconverter - - --conversion=md:html
&lt;h2 id="hi"&gt;hi&lt;/h2&gt;</pre>
