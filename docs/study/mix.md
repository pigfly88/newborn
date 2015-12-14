测试用户网络情况的脚本（bat）
@echo off
echo  Waiting for a moment please...
echo test start at %date:~0,10% %time:~0,-3% >> net.log
echo --------------------Start Ping----------------------- >> net.log
ping -n 10 tcpidtexas01.boyaagame.com >> net.log
ping -n 10 pclpidpk01-static.boyaagame.com >> net.log
ping -n 10 pclpidpk01.boyaagame.com >> net.log
ping -n 10 103.61.193.61 >> net.log
ping -n 10 103.61.193.40 >> net.log
echo --------------------End Ping------------------------- >> net.log

echo ------------------------------------------------------------------------------------------ >> net.log


echo --------------------Start Tracert----------------------- >> net.log
tracert -d tcpidtexas01.boyaagame.com >> net.log
tracert -d 103.61.193.61 >> net.log
tarcert -d pclpidpk01.boyaagame.com >> net.log
tracert -d 103.61.193.40 >> net.log
echo --------------------End Tracert------------------------- >> net.log