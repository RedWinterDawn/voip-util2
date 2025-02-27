#!/bin/bash

dbhost=db
dbname=pbxs
dbuser=postgres
basedir=/cluster/sites

pbx=`psql -h ${dbhost} -U ${dbuser} -d ${dbname} -t -A -F% -c "select id, location from resource_group where domain = '$1'"`
old_location=`echo $pbx | cut -f 2 -d %`
pbx=`echo $pbx | cut -f 1 -d %`

# Get the PBX id
if [ $? != 0 ]
then
	printf "PBX $1 was not found\n"
	exit 10
fi

# Check the location
if [ $2 != `psql -h $dbhost -U $dbuser -d $dbname -t -c "select id from nas_location where id = '$2'"` ]
then
	printf "Invalid location $2\n"
	exit 11
fi
if [ $2 == $old_location ]
then
	printf "PBX $1 already in $2\n"
	exit 0 
fi

# Check required location are mounted
if [ ! -d $basedir/$old_location/pbxs/ ]
then
	printf "Required site $old_location isn't mounted\nTry running \"service mount_nas reload\"\n"
	exit 20
fi
if [ ! -d $basedir/$2/pbxs ]
then
	printf "Required site $2 isn't mounted\nTry running \"service mount_nas reload\"\n"
	exit 20
fi

# Get NFS Location
case $2 in
	chicago-legacy)
		case $old_location in
		    pvu)
		        nfs="10.117.61.1" ;;
		    dfw)
		        nfs="10.118.61.1" ;;
		    lax)
		        nfs="10.119.61.1" ;;
		    nyc)
		        nfs="10.120.61.1" ;;
		    ord)
		        nfs="10.121.61.1" ;;
		    atl)
		        nfs="10.122.61.1" ;;
		    geg)
		        nfs="10.123.61.1" ;;
		    lon)
		        nfs="10.124.61.1" ;;
		esac
		;;
	pvu)
		nfs="10.117.61.1" ;;
	dfw)
		nfs="10.118.61.1" ;;
	lax)
		nfs="10.119.61.1" ;;
	nyc)
		nfs="10.120.61.1" ;;
	ord)
		nfs="10.121.61.1" ;;
	atl)
		nfs="10.122.61.1" ;;
	geg)
		nfs="10.123.61.1" ;;
	lon)
		nfs="10.124.61.1" ;;
esac

case $old_location in
    pvu)
        old_nfs="10.117.61.1" ;;
    dfw)
        old_nfs="10.118.61.1" ;;
    lax)
        old_nfs="10.119.61.1" ;;
    nyc)
        old_nfs="10.120.61.1" ;;
    ord)
        old_nfs="10.121.61.1" ;;
    atl)
        old_nfs="10.122.61.1" ;;
    geg)
        old_nfs="10.123.61.1" ;;
    lon)
        old_nfs="10.124.61.1" ;;
esac

	

# Do the move
logger "Copying files from $old_location to $2 for $pbx"

if [ $2 == "chicago-legacy" ]
then
	if [[ -e $basedir/$old_location/pbxs/$pbx ]]
	then
		rsync -ra $nfs::v4/pbxs/$pbx/ $basedir/$2/pbxs/$pbx/ 1>/dev/null 2>&1
		rsync -ra --delete-after $nfs::v4/pbxs/$pbx/voicemail/ $basedir/$2/pbxs/$pbx/voicemail 1>/dev/null 2>&1
	else
		mkdir $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
    	mkdir $basedir/$2/pbxs/$pbx/holdmusic 1>/dev/null 2>&1
    	mkdir $basedir/$2/pbxs/$pbx/soundclips 1>/dev/null 2>&1
    	mkdir $basedir/$2/pbxs/$pbx/voicemail 1>/dev/null 2>&1
    	chown -R root:pbx $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
    	chmod -R 2775 $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
	fi
elif [ $old_location == "chicago-legacy" ]
then
    if [[ -e $basedir/$old_location/pbxs/$pbx ]]
    then
        rsync -ra $basedir/chicago-legacy/pbxs/$pbx/ $nfs::v4/pbxs/$pbx/ 1>/dev/null 2>&1
		rsync -ra --delete-after $basedir/chicago-legacy/pbxs/$pbx/voicemail/ $nfs::v4/pbxs/$pbx/voicemail/ 1>/dev/null 2>&1
    else
        mkdir $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
        mkdir $basedir/$2/pbxs/$pbx/holdmusic 1>/dev/null 2>&1
        mkdir $basedir/$2/pbxs/$pbx/soundclips 1>/dev/null 2>&1
        mkdir $basedir/$2/pbxs/$pbx/voicemail 1>/dev/null 2>&1
        chown -R root:pbx $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
        chmod -R 2775 $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
    fi  
else
	if [[ -e $basedir/$old_location/pbxs/$pbx ]]
	then
		ssh -i /root/.ssh/internal-only -T -o StrictHostKeyChecking=no root@${old_nfs} "rsync -ra /NFSStorage/pbx/pbxs/$pbx/ $nfs::v4/pbxs/$pbx/; rsync -ra --delete-after /NFSStorage/pbx/pbxs/$pbx/voicemail/ $nfs::v4/pbxs/$pbx/voicemail/" 2>&1
	else
		mkdir $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
		mkdir $basedir/$2/pbxs/$pbx/holdmusic 1>/dev/null 2>&1
		mkdir $basedir/$2/pbxs/$pbx/soundclips 1>/dev/null 2>&1
		mkdir $basedir/$2/pbxs/$pbx/voicemail 1>/dev/null 2>&1
		chown -R root:pbx $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
		chmod -R 2775 $basedir/$2/pbxs/$pbx 1>/dev/null 2>&1
	fi
fi
if [[ $? != 0 ]]
then
	echo "rsync failed!"
	exit 25
fi
if [ ! -e $basedir/$2/voicemail/$pbx ]
then
	ln -s ../pbxs/$pbx/voicemail $basedir/$2/voicemail/$pbx 1>/dev/null 2>&1
fi

# Mark the old directory as "migrated" 
rm $basedir/$2/pbxs/$pbx/migrated
echo "Migrated to $2 on $(date)" > $basedir/$old_location/pbxs/$pbx/migrated
