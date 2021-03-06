#!/bin/sh
DEBIANSF_CVS_PATH=/home/bayle/local/sourceforge/savannah/src2/sourceforge
SAVANNAH_CVS_PATH=/home/bayle/cvs/savannah/savannah
for savannah_theme_dir in $SAVANNAH_CVS_PATH/www/images/*.theme
do
	theme_name=`basename $savannah_theme_dir '.theme'`
	echo "Converting $theme_name"	
	if [ ! -d $DEBIANSF_CVS_PATH/www/themes/savannah_$theme_name/ ]
	then
		cd $savannah_theme_dir ; find . | grep -v CVS | cpio -pdumvB $DEBIANSF_CVS_PATH/www/themes/savannah_$theme_name/images/ >/dev/null 2>&1
	fi 
	cd $SAVANNAH_CVS_PATH ;
	if [ -f www/css/$theme_name.css ]
	then
		echo "Found $theme_name css"
		if [ ! -f $DEBIANSF_CVS_PATH/www/themes/savannah_$theme_name/debiansf.css ]
		then
			cat $SAVANNAH_CVS_PATH/www/css/$theme_name.css |\
				sed "s:/images/$theme_name.theme/:/themes/savannah_$theme_name/images/:" > $DEBIANSF_CVS_PATH/www/themes/savannah_$theme_name/debiansf.css
		fi
	fi
	cat $DEBIANSF_CVS_PATH/../tools/savannah_std.class |\
			sed "s/THEMENAME/savannah_$theme_name/" > $DEBIANSF_CVS_PATH/www/themes/savannah_$theme_name/Theme.class

done
