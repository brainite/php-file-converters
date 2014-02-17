# Overriding Converters

## Custom Binary Paths and Installations

The FileConverter engines look in the bin/ directory before searching the system path.
The system path variable is searched using the 'which' command ('where' on Windows).
Two basic methods for installing a custom binary and/or leveraging a binary that is
not discoverable are:

1. Installing the executable file in the bin/ directory.
2. Creating a symlink in the bin/ directory to where the executable is stored.
