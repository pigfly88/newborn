#!/bin/sh
#<?php die();?>

#切换到surplus目录
cd $(cd "$(dirname "$0")";pwd)
readonly current_path=$(pwd)/;
readonly cli_path=${current_path}../cli.php;
readonly minute=$(date +%M)
readonly hour=$(date +%H)
readonly day=$(date +%d)
readonly week=$(date +%w)

#修改data目录下文件权限
if [ "0" -eq "$(($minute % 10))" ] ; then
	chmod -R 777 ${current_path}../data/*
fi

#每分钟执行的任务
/usr/local/php/bin/php -f ${cli_path} 'm=ptables&p=index&g=5' > /dev/null 2>&1 &

/usr/local/php/bin/php -f ${cli_path} 'm=async&p=stat&g=5&pflag=1' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=async&p=stat&g=5&pflag=2' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=async&p=stat&g=5&pflag=3' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=async&p=stat&g=5&pflag=4' > /dev/null 2>&1 &
#/usr/local/php/bin/php -f ${cli_path} 'm=match&p=award&g=5' > /dev/null 2>&1 &	#此功能已改为server调用
/usr/local/php/bin/php -f ${cli_path} 'm=vcrontab&p=core&g=5' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=radio&p=send_radio&g=5' > /dev/null 2>&1 &

/usr/local/php/bin/php -f ${cli_path} 'm=storage&p=start&g=5&pflag=1' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=redenvelope&p=log&g=5' > /dev/null 2>&1 &

#每五分钟执行一次
if [ "0" -eq "$(($minute % 5))" ] ; then
#/usr/local/php/bin/php -f ${cli_path} 'm=online&p=stat' > /dev/null 2>&1 & #此功能已改为server调用
/usr/local/php/bin/php -f ${cli_path} 'm=coupons&p=update&g=5' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=roundday&p=day_total_crontab&g=5' > /dev/null 2>&1 &
/usr/local/php/bin/php -f ${cli_path} 'm=dbredis&p=index&g=5' > /dev/null 2>&1 &
fi

#每3分钟执行的任务
if [ "0" -eq "$(($minute % 3))" ] ; then
/bin/bash ${current_path}resultlog.sh.php 5 1 > /dev/null 2>&1 &
#/bin/bash ${current_path}resultlog.sh.php 5 2 > /dev/null 2>&1 &
#/bin/bash ${current_path}resultlog.sh.php 5 3 > /dev/null 2>&1 &
#/bin/bash ${current_path}resultlog.sh.php 5 4 > /dev/null 2>&1 &
#/bin/bash ${current_path}resultlog.sh.php 5 5 > /dev/null 2>&1 &
#/bin/bash ${current_path}resultlog.sh.php 5 6 > /dev/null 2>&1 &
fi

if [ "03" -eq $hour ] && [ "00" -eq $minute ] ; then
	/usr/local/php/bin/php -f ${cli_path} 'm=storage&p=cleanUpData&g=5&pflag=1' > /dev/null 2>&1 &
	/usr/local/php/bin/php -f ${cli_path} 'm=report&p=redis2db&g=5' > /dev/null 2>&1 &
fi

#10分钟执行 飞信积分上报
if [ "0" -eq "$(($minute % 10))" ] ; then
	/usr/local/php/bin/php -f ${cli_path} 'm=fxup&p=up&g=5' > /dev/null 2>&1 &
fi

#每天0点 跑马灯统计
if [ "0" -eq $hour ] && [ "0" -eq $minute ] ; then   
	/usr/local/php/bin/php -f ${cli_path} 'm=marquee&p=marquee&g=5' > /dev/null 2>&1 &  
	
fi

#每天2点 用户牌局基础数据落地 coupons话费券统计  大厅牌局统计  vip统计
if [ "02" -eq $hour ] && [ "0" -eq $minute ] ; then   
	/usr/local/php/bin/php -f ${cli_path} 'm=roundday&p=day_round_to_db&g=5' > /dev/null 2>&1 &  
	/usr/local/php/bin/php -f ${cli_path} 'm=roundday&p=day_total_to_db&g=5' > /dev/null 2>&1 &
	/usr/local/php/bin/php -f ${cli_path} 'm=coupons&p=total_log&g=5' > /dev/null 2>&1 &
	/usr/local/php/bin/php -f ${cli_path} 'm=resultlogstatistic&p=redis2db&g=5' > /dev/null 2>&1 &
	/usr/local/php/bin/php -f ${cli_path} 'm=vipstat&p=redis2db&g=5' > /dev/null 2>&1 &
fi


#每周六03:00执行的任务
if [ "03" -eq $hour ] && [ "00" -eq $minute ] && [ "6" -eq $week ] ; then
	/usr/local/php/bin/php -f ${cli_path} 'm=trumpetlog&p=dellog&g=5' > /dev/null 2>&1 &  
fi