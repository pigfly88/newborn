#!/bin/sh
#<?php die();?>
source /etc/profile
umask 002
cd $(cd "$(dirname "$0")";pwd)
readonly path=$(pwd)/;
readonly cli_path=${path}../cli.php;

readonly game=$1;
readonly pNum=$2;
RunFile="${path}../data/plock/${game}.run.${pNum}";
QuitFile="${path}../data/plock/${game}.quit.${pNum}"
Logspath="/data/demo/wwwroot/logs/resultlog/";

function Run(){
	  while true
      do
		#检测quit文件是否存在，存在就先把run文件删除然后删除quit文件
		if [[ -f $QuitFile  ]];then
			rm -f ${RunFile}
			if [ $? -eq 0 ]; then
				rm -f ${QuitFile}
				exit
			fi 
		else
			/usr/local/php/bin/php -f ${cli_path} "m=resultlog&p=start&g=${game}&pflag=${pNum}"
			#/usr/local/php/bin/php ${path}resultlog.php -p ${pNum} >> ${Logspath}${game}.`date +%Y_%m_%d`.log
			#/usr/local/php/bin/php ${path}resultlog.php -p ${pNum} > /dev/null 2>&1 &
			sleep 3
		fi 
      done
}
#判断该进程锁文件是否存在，存在就exit
if [[ ! -f $RunFile  ]];then
	echo $$>$RunFile
	echo "go run....."
    Run   
else
	OLDPID=`cat "$RunFile"`
	FLAG=`ps aux | grep "resultlog.sh.php $1 $2" | grep "$OLDPID" | awk '{print $2}' | tr -s ["\n"]`
	if [[ $FLAG -eq $OLDPID ]];then
		echo "shell is alive"
		exit
	else
		echo "shell down delete run file!"
		ps aux | grep "resultlog.sh.php $1 $2" | grep "$OLDPID" | awk '{print $2}' | xargs --no-run-if-empty kill
		rm -f ${RunFile}
	fi
fi
