#/bin/bash
capfile=$1
result=$capfile.port_cmd_count.txt
/usr/local/wireshark.1.99.10/bin/tshark -n  -Xlua_script:by9-wireshark.lua -T fields -e tcp.port -e by9.cmd -r $capfile | grep 0x | sed 's/.*,\([^0]\)/\1/' |  sed 's/0x.*//'| sort | uniq -c | sort -n -r -k 1,2 >$result; echo "head of $result"; head $result;

