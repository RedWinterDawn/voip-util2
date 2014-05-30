#! /bin/bash
# pbx_move_single_customer <domain> <new_pbx_number>

DEST_BUCKET=$(psql -h rodb -U postgres pbxs -c "COPY (SELECT bucket from pbx_node where host = '${DEST_PBX_IP}') TO STDOUT;")
DEFAULT_PBX_IP_BASE='10.199.7.'

if [ -z "$1" ]
then
  #echo "\$1 is null"
  read -p "Domain ? " DOMAIN
else
  #echo "\$1 is NOT null"
  DOMAIN=${1}
fi

echo Currently $(psql -h rodb -U postgres pbxs -c "COPY (SELECT assigned_server,bucket,domain from resource_group where domain = '${DOMAIN}') TO STDOUT;")
#DEST_PBX_IP=""

if [ -z "$2" ]
then
  #echo "\$2 is null"
  read -p "DESTINATION PBX (Last octet or full IP) ? " DEST_PBX
else
  #echo "\$2 is NOT null"
  DEST_PBX=${2}
fi

if [ -z "$3" ]
then
  doFlush="ask"
else 
  #Third argument is set
  doFlush=${3}
fi

if [ -n "$4" ]
then
  location=${4}
fi

if [[ "$DEST_PBX" == *"."* ]]
then
# destination includes "." so is likely a full ip
	DEST_PBX_IP="$DEST_PBX"
else
	DEST_PBX_IP="$DEFAULT_PBX_IP_BASE$DEST_PBX"
fi

DEST_BUCKET=$(psql -h rodb -U postgres pbxs -c "COPY (SELECT bucket from pbx_node where host = '${DEST_PBX_IP}') TO STDOUT;")

if [[ -z "$DEST_BUCKET" ]]
then
	# no bucket
	echo "Bad destination $DEST_PBX_IP"
	exit 1
fi

#echo Currently $(psql -h db -U postgres pbxs -c "COPY (SELECT assigned_server,bucket,domain from resource_group where domain = '${DOMAIN}') TO STDOUT;")
echo "..."
echo Changing ${DOMAIN} to ${DEST_PBX_IP}
if [ $doFlush == "ask" ]
then
	read -p "Are you sure you want to make this change?  (enter to accept, ctrl-c to abort): " YES
fi

shopt -s nocasematch
if [[ "$YES" == "N" ]]
then
	shopt -u nocasematch
	echo "negative on the move, aborting"
	exit 0
fi
shopt -u nocasematch

logger moving $DOMAIN from $(psql -h db -U postgres pbxs -c "COPY (SELECT assigned_server from resource_group where domain = '${DOMAIN}') TO STDOUT;") to $DEST_PBX_IP
if [ -n $location ]
then
	ssh -T -o StrictHostKeyChecking=no root@enc1 "/root/migrate-files.sh $DOMAIN $location" 2>&1
fi

echo $(psql -h db -U postgres pbxs -c "UPDATE resource_group SET assigned_server = '${DEST_PBX_IP}', bucket = '${DEST_BUCKET}', location = '${location}' where domain = '${DOMAIN}'; COMMIT;")

echo Changed to $(psql -h rodb -U postgres pbxs -c "COPY (SELECT assigned_server,bucket,domain from resource_group where domain = '${DOMAIN}') TO STDOUT;")

if [[ "$doFlush" == "ask" ]]
then
	read -p "Flush memcached? (N) " doFlush
fi

shopt -s nocasematch
if [[ "$doFlush" == "Y" ]]
then
    echo "Flushing memcached (trust me, its better this way)"
    /root/flush_memcached
    echo "*** memcache flushed at $(date) ***" >> $LOGFILE
else
    echo "Skipping memcached flush"
fi
shopt -u nocasematch

