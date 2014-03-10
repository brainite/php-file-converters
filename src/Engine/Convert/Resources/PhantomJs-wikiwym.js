var system = require('system');
if (system.args.length != 3) {
  console.log("USAGE: <cmd> <source> <dest>" + system.args.length);
  phantom.exit(1);
}

// Load the GoogleCodeWikiParser.js.
phantom.injectJs('./GoogleCodeWikiParser.js');
var parser = new GoogleCodeWikiParser();

// Variables for managing the FileSystem operations.
var fs = require('fs'),
source_path, source_fp,
destination_path, destination_fp,
source_content, destination_content,
pre_lang = 'php';

// Handle the arguments.
source_path = system.args[1];
destination_path = system.args[2];

try {
    source_fp = fs.open(source_path, "r");
    source_content = source_fp.read();
    source_content = source_content.replace(/\n==/g, "\n\n==");
    source_content = source_content.replace(/http:\/\/code.google.com\/p\/[^\/]+\/wiki\/([^ ]+)/g, '$1.md');
    destination_content = parser.parse(source_content);
    destination_fp = fs.open(destination_path, "w");
    destination_fp.write(destination_content);
} catch (e) {
    console.log(e);
}
if (source_fp) {
  source_fp.close();
}
if (destination_fp) {
  destination_fp.close();
}
phantom.exit();
