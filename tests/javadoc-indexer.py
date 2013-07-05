#!/usr/bin/env python

#
# Javadoc and Doxygen indexer.  Version 1.2.
#
# Copyright (c) 2004-2005 Ewan Mellor <sawfish@ewanmellor.org.uk>.  All rights
# reserved.
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to
# deal in the Software without restriction, including without limitation the
# rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
# sell copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
#   The above copyright notice and this permission notice shall be included in
#   all copies or substantial portions of the Software.
#
#   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
#   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL
#   THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
#   FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
#   DEALINGS IN THE SOFTWARE.

#
# Call this script with javadoc-indexer.py --help for usage.
#

#
# In order for this script to be useful, you need something to access the
# generated index.  One option is to use the lookup and javadoc Sawfish
# modules available at
#
# http://www.ewanmellor.org.uk/sawfish.html
#
# This script itself should be available there too.
#

#
# This script generates lines of pairs of double-quoted strings thus:
#
# "lang.String" "/.../api/java/lang/String.html"
#
# The first string gives the key, and the second gives the filename at which
# the documentation corresponding to that key will be found.  All partial
# suffixes of the key will be generated, so in this case there would be a line
# for "String", "lang.String", and "java.lang.String".
#
# Entries are generated for packages, too, referring to the package summary
# page.  For example, the following entry may be generated:
#
# "lang" "/.../api/java/lang/package-summary.html"
#

#
# If given the -r option, this script will follow the reindexing by reloading
# the javadoc module of all running Sawfish instances on this machine.  This
# will ensure that those Sawfish instances use this new index immediately.
#

#
# Privileged prefixes.  Fully qualified names beginning with a prefix in this
# list will have all keys placed towards the front of the output, and thus
# unqualified names used as keys will win in the case of conflicts with other
# unqualified names.
#
# This list is ordered from the highest priority to the lowest, and anything
# not matching these prefixes will be considered lowest of all.
#
# Prefixes in this list are most likely to be package names, but could equally
# be fully qualified class names too.
#
# For example, with java.util in this list, and java.awt either later in the
# list or not in the list at all, java.util.List will be considered a better
# match than java.awt.List for the key "List".
#
# Note that the entry "java.lang." will match the java.lang package itself as
# well as any of its contents.
#
# A prefix may optionally be composed of two parts, separated by a minus sign.
# This form gives an entry that is matched if the first part is matched, and
# the second part is not matched anywhere in the name.  This is useful for
# projects with separate test directories.  For example, "com.example.-test"
# matches com.example.server, but neither com.example.test nor
# com.example.server.test.  The best way to use this is to have the two
# entries "com.example.-test" and "com.example.".  This will therefore match
# classes in the main sourcebase first, or in the test suite if the name isn't
# found there.

#privPrefixes = [ "com.example.-test", "com.example.",
#                 "org.apache.log4j", "java.util.", "java.awt.", "java.lang.",
#                 "java." ]
privPrefixes = [ "ra.-test", "ra.", "org.apache.log4j", "java.util.",
                 "java.awt.", "java.lang.",
                 "javax.sql", "java.sql", "java."]


# -- No user serviceable parts below this line --

import fileinput
import getopt
import os
import re
import string
import sys

#
# A dictionary of dictionaries.  The outer dictionary is keyed on either a
# value in privPrefixes or othersString, and the inner dictionary maps the
# string keys to paths.
#
mappings = {}

othersString = "<Others>"


# For Java, throw away class-use, packages, package-use, package-frame, and
# package-tree.  Keep package-summary as the destination for package names.
# For Doxygen, throw away *-members.html files.
filesToIgnoreRE = re.compile(
    r"(class-use|package(s|-(use|frame|tree))|-members\.html$)")

Javadoc = 1
Doxygen = 2


# ## Processing ##

def processDir(dir):
    "Process the documentation tree rooted at the given directory."

    if os.access(dir + "/allclasses-frame.html", os.F_OK):
        type = Javadoc
    elif os.access(dir + "/all-packages.html", os.F_OK):
        type = Javadoc # Gjdoc HtmlDoclet, actually.
    elif os.access(dir + "/doxygen.png", os.F_OK):
        type = Doxygen
    else:
        print >>sys.stderr, "The directory", dir, "does not contain the file allclasses-frame.html, nor doxygen.png; ignoring it."
        return

    if type == Javadoc:
        pathList = os.popen("find " + dir + " -name \*.html")
    else:
        pathList = os.popen("find " + dir +
                            " -name class\*.html -o -name namespace\*.html")

    for path in pathList:
        process(dir, path, type)

    pathList.close()


def process(dir, path, type):
    "Process the given path, where dir is the root of the documentation tree."

    if filesToIgnoreRE.search(path):
        return

    # path should be a fully qualified path to a Javadoc / Doxygen file.
    # name is the fully qualified class name associated, with namespaces
    # separated by full stops, even when processing C++ classes.
    path = path.strip()

    name = path.replace(dir, "").replace("/", ".").replace(".html", "")

    if type == Javadoc:
        name = name.replace("package-summary", "")
    else:
        name = name.replace("_1_1", ".").replace("class", "").replace("namespace", "")


    # Generate keys as all the suffixes in name, and add those keys and values
    # to the mappings dictionary.

    parts = name.split(".")

    n = len(parts)

    key = parts[n - 1]

    if key == "":
        n -= 1
        key = parts[n - 1]

    add(name, key, path)

    for i in range(n - 2, -1, -1):
        part = parts[i].strip()
        key = part + "." + key
        add(name, key, path)


def add(name, key, value):
    """Add the given key and value to the mappings dictionary.

    Select the subdictionary depending upon whether the given name matches one
    of the privileged prefixes.
    """
    
    global othersString
    global privPrefixes
    
    for prefix in privPrefixes:
        parts = prefix.split("-")

        if len(parts) == 1:
            if name.find(prefix) == 0:
                addMapping(prefix, key, value)
                return
        else:
            if name.find(parts[0]) == 0 and name.find(parts[1]) == -1:
                addMapping(prefix, key, value)
                return
        
    addMapping(othersString, key, value)


def addMapping(subdict, key, value):
    "Add the given key and value to the named subdictionary of mappings."

    global mappings

    if not mappings.has_key(subdict):
        mappings[subdict] = {}

    dict = mappings[subdict]
    
    dict[key] = value


# ## Output ##

def outputAll(outputFile):
    "Output everything in mappings to the given file."

    file = open(outputFile, 'w')

    for prefix in privPrefixes:
        outputSubdict(prefix, file)

    outputSubdict(othersString, file)

    file.close()


def outputSubdict(subdict, file):
    "Output everything in the named subdictionary of mappings."

    if not mappings.has_key(subdict):
        return

    map = mappings[subdict]

    for key in map.keys():
        output(key, map[key], file)


def output(key, value, file):
    "Output the given key-value pair."

    print >>file, '"' + key + '" "' + value + '"'


# ## Reloading Javadoc Module ##

def reloadJavadocModule(display):
    """Issue a command to the sawfish instance running on the given display to
    reload the javadoc module."""
    
    os.system("echo ',reload javadoc' | sawfish-client -q --display " +
              display + " - >/dev/null 2>/dev/null")
    

def reloadJavadocModuleEverywhere():
    """Reload the javadoc module on every sawfish instance running on this
    machine."""
    
    pids = readClose(os.popen("pidof sawfish"))

    for pid in pids.split(" "):
        p = pid.strip()

        if p != "":
            env = readClose(os.popen("cat /proc/" + p + "/environ"))

            bits = env.split("\000")

            for bit in bits:
                if bit.find("DISPLAY") == 0:
                    reloadJavadocModule(bit.split("=")[1])
                    break


def readClose(file):
    "Read everything from the given file, and close it."

    def concat(x, y):
        return x + y
    
    result = reduce(concat, file.readlines(), "")
    file.close()
    return result


# ## Entry point ##

def main():
    reload = 0
    outputFilename = None
    
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hro:",
                                   ["help", "reload", "output="])
    except getopt.GetoptError:
        usage()
        sys.exit(2)

    for o, a in opts:
        if o in ("-h", "--help"):
            usage()
            sys.exit(0)
        elif o in ("-r", "--reload"):
            reload = 1
        elif o in ("-o", "--output"):
            outputFilename = a

    if len(args) < 1 or outputFilename == None:
        usage()
        sys.exit(2)

    for dir in args:
        processDir(dir)

    outputAll(outputFilename)

    if reload:
        reloadJavadocModuleEverywhere()


def usage():
    print """
Usage:

javadoc-indexer.py [-h/--help] [-r/--reload] -o <file>/--output=<file>
                   <dir>+
where
      --help          instructs this script to display this message and exit;
      --reload        indicates that all running sawfish instances should have
                        the javadoc module reloaded after reindexing;
      --output=<file> gives the file into which this script should place its
                        output;
      <dir>           is a fully qualified directory at the root of a
                        Javadoc- or Doxygen-generated documentation tree.
                        Javadoc directories contain the file
                        allclasses-frame.html, and are often called "api".
                        Doxygen directories contain the file doxygen.png,
                        and are often called "html".
"""


if __name__ == "__main__":
    main()
