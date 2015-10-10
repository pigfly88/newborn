#/bin/bash
capfile=$1
result=$capfile.cmd_length_list.txt
/usr/local/wireshark.1.99.10/bin/tshark -n  -Xlua_script:by9-wireshark.lua -T fields -e by9.cmd -e by9.pklen -r $capfile | grep 0x | awk 'BEGIN{}{list[$1]+=$2} END{for(v in list){print list[v]" "v}}' | sort -n -r >$result; echo "head 10 $result";head $result

