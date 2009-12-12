// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

admin = function () {
}

admin.createCollection = function(path) {
    var collName = admin.prompt('Section name');
    if(collName != null) {
        window.location.href = '/admin/collection' + path + collName + '?action=create';
    }
}

admin.createResource = function(type, path) {
    var resName = admin.prompt('File name');
	var resLang = admin.prompt('Language (2 letters)');
    if(resName != null) {
        window.location.href = '/admin/edit' + path + resName + '.' + resLang + '.' + type;
    }
}

admin.prompt = function(question) {
    return prompt(question);
}
