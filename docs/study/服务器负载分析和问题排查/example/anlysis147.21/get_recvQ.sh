#/bin/bash
head -n 2 netstat_ntpa_120.132.147.21.17_44.txt ;grep "^tcp" netstat_ntpa_120.132.147.21.17_44.txt | sort -k 2 -n -r  | head -n 10

