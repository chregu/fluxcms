#/bin/bash

if [ -f single.js ]
then
    rm single.js
fi

if [ -f _single ]
then
    rm _single
fi

sourcefiles="common.js
dbforms2.js
fields.js
form.js
formdata.js
groups.js
helpers.js
listview.js
liveselect.js
log.js
statusline.js
toolbar.js
transport.js"

# append all source files
for sourcefile in $sourcefiles
do
    echo "processing $sourcefile..."
    echo "/*" >> _single
    echo "  $sourcefile" >> _single
    echo "*/" >> _single
    cat $sourcefile >> _single
done

# compress it using rhino
if [ -f _single ]
then
    #ls -alh _single
    echo "compressing single.js..."
    java -jar custom_rhino.jar -c _single > single.js
    rm _single
fi
