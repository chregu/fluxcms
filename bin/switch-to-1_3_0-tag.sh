BRANCH="tags/1_3_0"
DIRS="webinc inc admin install"
STARTDIR=`pwd`

echo -e "\nUsually this script works without problems, but nevertheles\nPLEASE DO BACKUP BEFORE.\nPress <Enter> to continue or <Ctrl>+<C> to abort."
read

svn propget svn:externals . > .externals.old
echo -n "" > .externals

for i in $DIRS
do
echo $i
cd $i
svn switch https://svn.bitflux.ch/repos/public/fluxcms/$BRANCH/$i
cd $STARTDIR
echo "$i https://svn.bitflux.ch/repos/public/fluxcms/$BRANCH/$i" >> .externals
done
#echo "" >> .externals

#themes

cd themes
DIRS="standard"
STARTDIR2=`pwd`

svn propget svn:externals . > .externals.old
echo -n "" > .externals

for i in $DIRS
do
echo $i
cd $i
svn switch https://svn.bitflux.ch/repos/public/fluxcms/$BRANCH/themes/$i
cd $STARTDIR2
echo "$i https://svn.bitflux.ch/repos/public/fluxcms/$BRANCH/themes/$i" >> .externals
done
#echo "" >> .externals
#comment out the followin, if you're not using fluxcms_demo, but your own 

svn propset svn:externals . -F .externals 
svn switch https://svn.bitflux.ch/repos/public/fluxcms_demo/$BRANCH/themes/

cd $STARTDIR
svn propset svn:externals . -F .externals 
svn switch https://svn.bitflux.ch/repos/public/fluxcms_demo/$BRANCH/
