DIRS="webinc inc admin install"
DIRS_INC="popoon"

STARTDIR=`pwd`
echo -e "\nUsually this script works without problems, but nevertheles\nPLEASE DO BACKUP BEFORE.\nPress <Enter> to continue or <Ctrl>+<C> to abort."
read

#POPOON
cd inc
svn propget svn:externals . > .externals.old
echo -n "" > .externals

for i in $DIRS_INC
do
echo $i
cd $i
svn switch https://svn.bitflux.ch/repos/public/popoon/$POPOON_BRANCH
cd ..
echo "$i https://svn.bitflux.ch/repos/public/popoon/$POPOON_BRANCH" >> .externals
done

svn propset svn:externals . -F .externals 

cd ..

#ROOT
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

#THEMES

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
