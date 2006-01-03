<?php
if (!extension_loaded('SXIPXMLResponse')) {
    $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
    if (!dl($prefix . 'SXIPXMLResponse.' . PHP_SHLIB_SUFFIX)) {
        die("SXIPXMLResponse module was not loaded by PHP\n");
    }
}

# available functions

# void Loginx(char* xmlMessage)
# Returns a reference to a hash
# Keys             Type of Value
# "version"			string
# "responseID"		string
# "homesite"		string
# "membersite"		string
# "instant"			string
# "context"			string
# "explanation"		string
# "p3p"				string
# "messageID"		string
# "method"			string
# "gupi"			string
# "fetch"			reference to hash - see note below

# void Fetchx(char* xmlMessage)
# Returns a reference to a hash
# Keys             Type of Value
# "version"			string
# "responseID"		string
# "homesite"		string
# "membersite"		string
# "instant"			string
# "context"			string
# "explanation"		string
# "p3p"				string
# "messageID"       string
# "fetch"			reference to hash - see note below

# void Storex(char* xmlMessage)
# Returns a reference to a hash
# Keys             Type of Value
# "version"			string
# "responseID"		string
# "homesite"		string
# "membersite"		string
# "instant"			string
# "context"			string
# "explanation"		string
# "messageID"       string
# "store"			reference to hash - see note below

# void verifyLoginx(char* xmlMessage)
# Checks validity of xml and contained digital signature

# void verifyFetchx(char* xmlMessage)
# Checks validity of xml and contained digital signature

# void verifyStorex(char* xmlMessage)
# Checks validity of xml and contained digital signature


# NOTE:
# "fetch" is a reference to a hash that has as its keys the context
# of the XML to which it refers. The values are references to hashes
# which have the XPath keys and value that are references to array
# of values for that key

# "store" is a reference to a hash that has as its keys the context
# of the XML to which it refers. The values are strings that are the
# statuses of the store operations for each context
