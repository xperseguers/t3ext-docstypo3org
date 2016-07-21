docs.typo3.org
==============

This is a proof of concept extension to get https://docs.typo3.org using TYPO3 v8 (or possibly v7) instead
of a bunch of static html files.


Requirements
------------

* EXT:restdoc
* EXT:sphinx (only for some helper functions, not for the actual Python/Sphinx environment)
* EXT:realurl (only for nice URL)


Installation
------------

* Put your extension manuals generated as JSON (`.fjson`) into the following directory structure:

```
/path/to/website
|-- documents
|   `-- extensions
|       |-- <extkey-1>
|       |   |-- en-us
|       |   |   |-- <version-1>
|       |   |   |-- ...
|       |   |   `-- <version-n>
|       |   `-- fr-fr
|       |       |-- ...
|       |       `-- ...
|       |-- ...
|       `-- <extkey-n>
|           |-- ...
|           `-- ...
|-- index.php
`-- ...
```

For example, rendered manual for EXT:cloudflare 2.0.3 would be available as:

```
$ ls /path/to/website/documents/extensions/cloudflare/en-us/2.0.3
total 136
drwxr-xr-x   5 xavier  staff    170 Jul 20 15:48 AdministratorManual
drwxr-xr-x   3 xavier  staff    102 Jul 20 15:48 ChangeLog
-rw-r--r--   1 xavier  staff   4903 Jul 20 15:48 Index.fjson
drwxr-xr-x   3 xavier  staff    102 Jul 20 15:48 Introduction
drwxr-xr-x   3 xavier  staff    102 Jul 20 15:48 KnownProblems
-rw-r--r--   1 xavier  staff   6997 Jul 20 15:48 Links.fjson
drwxr-xr-x   3 xavier  staff    102 Jul 20 15:48 ToDoList
drwxr-xr-x   6 xavier  staff    204 Jul 20 15:48 UsersManual
drwxr-xr-x   6 xavier  staff    204 Jul 20 15:48 _images
drwxr-xr-x  10 xavier  staff    340 Jul 20 15:48 _sources
drwxr-xr-x   3 xavier  staff    102 Jul 20 15:48 _static
-rw-r--r--   1 xavier  staff  20680 Jul 20 15:48 environment.pickle
-rw-r--r--   1 xavier  staff    171 Jul 20 15:48 genindex.fjson
-rw-r--r--   1 xavier  staff    695 Jul 20 15:48 globalcontext.json
-rw-r--r--   1 xavier  staff      0 Jul 20 15:48 last_build
-rw-r--r--   1 xavier  staff    851 Jul 20 15:48 objects.inv
-rw-r--r--   1 xavier  staff    102 Jul 20 15:48 search.fjson
-rw-r--r--   1 xavier  staff   9845 Jul 20 15:48 searchindex.json
```

Now put a frontend plugin on some page of your website and enjoy!
