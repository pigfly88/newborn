#/bin/bash
capfile=$1
result=$capfile.cmd_count_list.txt
 /usr/local/wireshark.1.99.10/bin/tshark -n  -Xlua_script:by9-wireshark.lua -T fields -e by9.cmd -r $capfile | sort | uniq -c | grep "0x" | sort -n -r >$result; echo "head 10 $result";head $result;

