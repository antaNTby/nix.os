Package Control Messages
========================


Advanced CSV
------------

  CSV by Wade Brainerd (wadetb@gmail.com / www.wadeb.com)
  Forked from CSV by Eric Martel (emartel@gmail.com / www.ericmartel.com)
  
  Thank you for installing Advanced CSV. All commands are available through keyboard shortcuts, menus, context menus and the command palette.
  
  Please direct any feedback to wadetb@gmail.com or through GitHub at https://github.com/wadetb/Sublime-Text-Advanced-CSV.


AdvancedNewFile
---------------

  Thank you for installing the AdvancedNewFile plugin.
  
  For more information please visit https://github.com/skuroda/Sublime-AdvancedNewFile.
  
  Note you may need to restart Sublime Text after installing this plugin.
  
  If you have any questions, comments, or run into issues, please let me know! Hope you enjoy the plugin.
  
  Thank you!


SFTP
----

  Thanks for installing SFTP! Below are some quick notes to get you started with
  the plugin. Please see https://codexns.io/sftp for the full documentation.
  
  
  There are two major modes of operation:
  1. Mapping a local folder to a remote folder
  2. Working off of a server
  
  
  Mapping a Local Folder to a Remote Folder
  ----
  
  To map a local folder to a remote folder, right-click on it in the side bar
  and select the SFTP/SFTP > Map to Remote... You will enter your connection
  parameters and a new file will be created named sftp-config.json.
  
  Once this file has been saved, all files in that folder and all subfolders
  will have various operations available via the side bar context menu, editor
  context menu and command palette.
  
  
  Working off of a Server
  ----
  
  To work off of a server, use the File menu and select SFTP/FTP > Setup Server...
  You will enter your connection parameters and options then save the file with
  the name you want to give the connection.
  
  Once the server config is saved, you will be automatically connected to the
  server and you can browse and perform actions via the quick panel.
  
  Unfortunely due to limitations of the Sublime Text API, it is not possible
  to present the remote filesystem in the side bar. There are, however, key
  bindings for connecting to servers to help reduce the amount of time remote
  file operations take.
  
  
  Support
  ----
  
  To learn more about the features and settings, please visit
  https://codexns.io/sftp.
  
  If you are having trouble, please visit
  https://codexns.io/products/sftp_for_sublime/support. The support page includes
  instructions for capturing a debug log that will be useful if you believe
  you are experiencing a bug.


CodeFormatter
-------------

  Thanks for installing Sublime CodeFormatter! Below are some quick notes to get you
  started with the plugin.
  
  CodeFormatter
  =============
  
  
  CodeFormatter is a Sublime Text 2/3 plugin that supports format (beautify) source code.
  
  
  CodeFormatter has support for the following languages:
  
  * PHP - By phpfmt
  * JavaScript/JSON - By JSBeautifier
  * HTML - By JSBeautifier
  * CSS - By JSBeautifier
  * SCSS - By Nishutosh Sharma
  * Python - By PythonTidy (only ST2)
  * Visual Basic/VBScript
  
  
  Sponsors
  -----
  No sponsors yet.. :(
  
  If you like the software, don't forget to donate to further development of it!
  
  [![PayPal donate button](https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=akalongman@gmail.com&item_name=Donation to Sublime Text - CodeFormatter&item_number=1&no_shipping=1 "Donate to this project using Paypal")
  
  
  Installing
  ----------
  **With the Package Control plugin:** The easiest way to install CodeFormatter is through Package Control, which can be found at this site: http://wbond.net/sublime_packages/package_control
  
  Once you install Package Control, restart Sublime Text and bring up the Command Palette (`Command+Shift+P` on OS X, `Control+Shift+P` on Linux/Windows). Select "Package Control: Install Package", wait while Package Control fetches the latest package list, then select CodeFormatter when the list appears. The advantage of using this method is that Package Control will automatically keep CodeFormatter up to date with the latest version.
  
  **Without Git:** Download the latest source from [GitHub](https://github.com/akalongman/sublimetext-codeformatter) and copy the CodeFormatter folder to your Sublime Text "Packages" directory.
  
  **With Git:** Clone the repository in your Sublime Text "Packages" directory:
  
      git clone https://github.com/akalongman/sublimetext-codeformatter.git CodeFormatter
  
  
  The "Packages" directory is located at:
  
  * OS X:
  
          ST2: ~/Library/Application Support/Sublime Text 2/Packages/
          ST3: ~/Library/Application Support/Sublime Text 3/Packages/
  
  * Linux:
  
          ST2: ~/.config/sublime-text-2/Packages/
          ST3: ~/.config/sublime-text-3/Packages/
  
  * Windows:
  
          ST2: %APPDATA%/Sublime Text 2/Packages/
          ST3: %APPDATA%/Sublime Text 3/Packages/
  
  
  ## Formatter-specific notes
  Following are notes specific to individual formatters that you should be aware of:
  ### PHP
  PHP - Used phpfmt (https://github.com/phpfmt/fmt) by Carlos Cirello and Shaked Klein Orbach.
  
  Getting and installing PHP - http://www.php.net/manual/en/install.general.php
  
  You must install 5.6 or above
  
  On Linux/OSx after installation of package, you must set chmod +x to file fmt.phar in folder %PACKAGESDIR%/CodeFormatter/codeformatter/lib/phpbeautifier
  
  You can list all available transformations from Command Palette: CodeFormatter: Show PHP Transformations
  
  Language specific options:
  ```js
     "codeformatter_php_options":
      {
          "syntaxes": "php", // Syntax names which must process PHP formatter
          "php_path": "", // Path for PHP executable, e.g. "/usr/lib/php" or "C:/Program Files/PHP/php.exe". If empty, uses command "php" from system environments
          "format_on_save": false, // Format on save
          "psr1": false, // Activate PSR1 style
          "psr1_naming": false, // Activate PSR1 style - Section 3 and 4.3 - Class and method names case
          "psr2": true, // Activate PSR2 style
          "indent_with_space": 4, // Use spaces instead of tabs for indentation
          "enable_auto_align": true, // Enable auto align of = and =>
          "visibility_order": true, // Fixes visibiliy order for method in classes - PSR-2 4.2
          "smart_linebreak_after_curly": true, // Convert multistatement blocks into multiline blocks
  
          // Enable specific transformations. Example: ["ConvertOpenTagWithEcho", "PrettyPrintDocBlocks"]
          // You can list all available transformations from command palette: CodeFormatter: Show PHP Transformations
          "passes": [],
  
          // Disable specific transformations
          "exclude": []
      }
  ```
  
  
  
  ### Javascript/JSON
  Javascript/JSON - used [JSBeautifier] (http://jsbeautifier.org/) by Einar Lielmanis
  
  Language specific options:
  ```js
      "codeformatter_js_options":
      {
          "syntaxes": "javascript,json", // Syntax names which must process JS formatter
          "format_on_save": false, // Format on save
          "indent_size": 4, // indentation size
          "indent_char": " ", // Indent character
          "indent_with_tabs": false, // Indent with one tab (overrides indent_size and indent_char options)
          "eol": "\n", // EOL symbol
          "preserve_newlines": false, // whether existing line breaks should be preserved,
          "max_preserve_newlines": 10, // maximum number of line breaks to be preserved in one chunk
          "space_in_paren": false, // Add padding spaces within paren, ie. f( a, b )
          "space_in_empty_paren": false, // Add padding spaces within paren if parent empty, ie. f(  )
          "e4x": false, // Pass E4X xml literals through untouched
          "jslint_happy": false, // if true, then jslint-stricter mode is enforced. Example function () vs function()
          "brace_style": "collapse", // "collapse" | "expand" | "end-expand". put braces on the same line as control statements (default), or put braces on own line (Allman / ANSI style), or just put end braces on own line.
          "keep_array_indentation": false, // keep array identation.
          "keep_function_indentation": false, // keep function identation.
          "eval_code": false, // eval code
          "unescape_strings": false, // Decode printable characters encoded in xNN notation
          "wrap_line_length": 0, // Wrap lines at next opportunity after N characters
          "break_chained_methods": false, // Break chained method calls across subsequent lines
          "end_with_newline": false, // Add new line at end of file
          "comma_first": false // Add comma first
      }
  ```
  
  ### HTML
  HTML - used custom python port, please use it with caution, feature in early beta
  
  Language specific options:
  ```js
      "codeformatter_html_options":
      {
          "syntaxes": "html,asp,xml", // Syntax names which must process HTML formatter
          "format_on_save": false, // Format on save
          "indent_size": 4, // indentation size
          "indent_char": " ", // Indentation character
          "indent_with_tabs": false, // Indent with one tab (overrides indent_size and indent_char options)
          "preserve_newlines": false, // Preserve existing line-breaks
          "max_preserve_newlines": 10, // Maximum number of line-breaks to be preserved in one chunk
          "indent_tags": "html|head|body|div|nav|ul|ol|dl|li|table|thead|tbody|tr|th|td|blockquote|select|form|option|optgroup|fieldset|legend|label|header|section|aside|footer|figure|video|datalist|keygen|audio|embed|source|track|output|article|bdi|details|dialog|figcaption|main|mark|menuitem|meter|nav|progress|rp|rt|ruby|summary|time|wbr" // List of tags (defaults to indent) that should be reformatted. Example "div|p|li|table"
      }
  ```
  
  ### CSS
  CSS - used [JSBeautifier] (http://jsbeautifier.org/) by Einar Lielmanis and Style Css by Harutyun Amirjanyan
  
  Language specific options:
  ```js
      "codeformatter_css_options":
      {
          "syntaxes": "css,less", // Syntax names which must process CSS formatter
          "format_on_save": false, // Format on save
          "indent_size": 4, // Indentation size
          "indent_char": " ", // Indentation character
          "indent_with_tabs": false, // Indent with one tab (overrides indent_size and indent_char options)
          "selector_separator_newline": false, // Add new lines after selector separators
          "end_with_newline": false, // Add new line of end in file
          "newline_between_rules": false, // Add new line between rules
          "eol": "\n" // EOL symbol
      }
  ```
  ### SCSS
  SCSS - Simply modified the CSS formatter module as per the response from scss-lint (Gives way to modify further)
  
  Language specific options:
  ```js
      "codeformatter_scss_options":
      {
          "syntaxes": "scss", // Indentation size
          "indent_size": 2, // Indentation size
          "format_on_save": false, // Format on save
          "indent_char": " ", // Indentation character
          "indent_with_tabs": false, // Indent with one tab (overrides indent_size and indent_char options)
          "selector_separator_newline": true, // Add new lines after selector separators
          "newline_between_rules": true, // Add new line between rules
          "end_with_newline": true // Add new line of end in file
      }
  ```
  
  ### Python
  Python - used [PythonTidy] (https://pypi.python.org/pypi/PythonTidy/) by Chuck Rhode
  
  Language specific options:
  ```js
      "codeformatter_python_options":
      {
          "syntaxes": "python", // Syntax names which must process Python formatter
          "format_on_save": false, // Format on save
          "indent_size": 1, // indentation size
          "indent_with_tabs": true, // Indent with tabs or spaces
          "max_char": 80, // Width of output lines in characters.
          "assignment": " = ", // This is how the assignment operator is to appear.
          "function_param_assignment": "=", // This is how function-parameter assignment should appear.
          "function_param_sep": ", ", // This is how function parameters are separated.
          "list_sep": ", ", // This is how list items are separated.
          "subscript_sep": "=", // This is how subscripts are separated.
          "dict_colon": ": ", // This separates dictionary keys from values.
          "slice_colon": ":", // this separates the start:end indices of slices.
          "comment_prefix": "# ", // This is the sentinel that marks the beginning of a commentary string.
          "shebang": "#!/usr/bin/env python", // Hashbang, a line-one comment naming the Python interpreter to Unix shells.
          "boilerplate": "", // Standard code block (if any). This is inserted after the module doc string on output.
          "blank_line": "", // This is how a blank line is to appear (up to the newline character).
          "keep_blank_lines": true, // If true, preserve one blank where blank(s) are encountered.
          "add_blank_lines_around_comments": true, // If true, set off comment blocks with blanks.
          "add_blank_line_after_doc_string": true, // If true, add blank line after doc strings.
          "max_seps_func_def": 3, // Split lines containing longer function definitions.
          "max_seps_func_ref": 5, // Split lines containing longer function calls.
          "max_seps_series": 5, // Split lines containing longer lists or tuples.
          "max_seps_dict": 3, // Split lines containing longer dictionary definitions.
          "max_lines_before_split_lit": 2, // Split string literals containing more newline characters.
          "left_margin": "", // This is how the left margin is to appear.
          "normalize_doc_strings": false, // If true, normalize white space in doc strings.
          "leftjust_doc_strings": false, // If true, left justify doc strings.
          "wrap_doc_strings": false, // If true, wrap doc strings to max_char.
          "leftjust_comments": false, // If true, left justify comments.
          "wrap_comments": false, // If true, wrap comments to max_char.
          "double_quoted_strings": false, // If true, use quotes instead of apostrophes for string literals.
          "single_quoted_strings": false, // If true, use apostrophes instead of quotes for string literals.
          "can_split_strings": false, // If true, longer strings are split at the max_char.
          "doc_tab_replacement": "....", // This literal replaces tab characters in doc strings and comments.
  
          // Optionally preserve unassigned constants so that code to be tidied
          // may contain blocks of commented-out lines that have been no-op'ed
          // with leading and trailing triple quotes.  Python scripts may declare
          // constants without assigning them to a variables, but CodeFormatter
          // considers this wasteful and normally elides them.
          "keep_unassigned_constants": false,
  
          // Optionally omit parentheses around tuples, which are superfluous
          // after all.  Normal CodeFormatter behavior will be still to include them
          // as a sort of tuple display analogous to list displays, dict
          // displays, and yet-to-come set displays.
          "parenthesize_tuple_display": true,
  
          // When CodeFormatter splits longer lines because max_seps
          // are exceeded, the statement normally is closed before the margin is
          // restored.  The closing bracket, brace, or parenthesis is placed at the
          // current indent level.  This looks ugly to "C" programmers.  When
          // java_style_list_dedent is True, the closing bracket, brace, or
          // parenthesis is brought back left to the indent level of the enclosing
          // statement.
          "java_style_list_dedent": false
      }
  ```
  ### Visual Basic/VBScript
  Visual Basic/VBScript - used custom approach using the HTML beautifier as a guide
  
  Language specific options:
  ```js
      "codeformatter_vbscript_options":
      {
          "syntaxes": "vbscript", // Syntax names which must process VBScript formatter
          "format_on_save": false, // Format on save
          "indent_size": 1, // indentation size
          "indent_char": "\t", // Indentation character
          "indent_with_tabs": true, // Indent with one tab (overrides indent_size and indent_char options)
          "preserve_newlines": true, // Preserve existing line-breaks
          "max_preserve_newlines": 10, // Maximum number of line-breaks to be preserved in one chunk
          "opening_tags": "^(Function .*|Sub .*|If .* Then|For .*|Do While .*|Select Case.*)", // List of keywords which open a new block
          "middle_tags": "^(Else|ElseIf .* Then|Case .*)$", // List of keywords which divide a block, but neither open or close the block
          "closing_tags": "(End Function|End Sub|End If|Next|Loop|End Select)$" // List of keywords which close an open block
      }
  ```
  
  Usage
  -----
  Tools -> Command Palette (`Cmd+Shift+P` or `Ctrl+Shift+P`) and type `Format Code`.
  
  You can set up your own key combo for this, by going to Preferences -> Key Bindings - User, and adding a command in that huge array: `{ "keys": ["ctrl+alt+f"], "command": "code_formatter" },`. Default keybinding is `ctrl+alt+f`. You can use any other key you want, thought most of them are already taken.
  
  TODO
  -----
  Add other languages support:
  * Python (for ST3)
  * Perl
  * Ruby
  
  Pull requests are welcome.
  
  Troubleshooting
  ---------------
  If you like living on the edge, please report any bugs you find on the [CodeFormatter issues](https://github.com/akalongman/sublimetext-codeformatter/issues) page.
