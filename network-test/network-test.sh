#!/usr/bin/env bash

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
SKYBLUE='\033[0;36m'
PLAIN='\033[0m'
REPORT_FOLDER="reports"
RAW_REPORT="${REPORT_FOLDER}/raw.log"

# check root
[[ $EUID -ne 0 ]] && echo -e "${RED}Error:${PLAIN} This script must be run as root!" && exit 1
[[ ! -d "${REPORT_FOLDER}" ]] && mkdir "${REPORT_FOLDER}"
rm -f ${RAW_REPORT}

# check python
if  [ ! -e '/usr/bin/python' ]; then
        echo -e
        read -p "${RED}Error:${PLAIN} python is not install. You must be install python command at first.\nDo you want to install? [y/n]" is_install
        if [[ ${is_install} == "y" || ${is_install} == "Y" ]]; then
            if [ "${release}" == "centos" ]; then
                        yum -y install python
                else
                        apt-get -y install python
                fi
        else
            exit
        fi
        
fi

# TODO: Check bc

# install speedtest
if  [ ! -e './speedtest.py' ]; then
    # check wget
    if  [ ! -e '/usr/bin/wget' ]; then
            echo -e
            read -p "${RED}Error:${PLAIN} wget is not install. You must be install wget command at first.\nDo you want to install? [y/n]" is_install
            if [[ ${is_install} == "y" || ${is_install} == "Y" ]]; then
                    if [ "${release}" == "centos" ]; then
                            yum -y install wget
                    else
                            apt-get -y install wget
                    fi
            else
                    exit
            fi
    fi
    #https://raw.github.com/sivel/speedtest-cli/master/speedtest.py
    wget https://raw.githubusercontent.com/sivel/speedtest-cli/master/speedtest.py > /dev/null 2>&1
fi
chmod a+rx speedtest.py


# Handle batch inputs
speed_test(){
    temp=$(python speedtest.py --server $1 --share 2>&1)
    is_down=$(echo "$temp" | grep 'Download') 
    if [[ ${is_down} ]]; then
        local REDownload=$(echo "$temp" | awk -F ':' '/Download/{print $2}')
        local reupload=$(echo "$temp" | awk -F ':' '/Upload/{print $2}')
        local relatency=$(echo "$temp" | awk -F ':' '/Hosted/{print $2}')
        local defaultNodeName=$(echo "$temp" | grep "Hosted by" | sed -r 's/Hosted by (.*) \[.*/\1/')
        local nodeName=${2:-$defaultNodeName}

        if [[ $(bc -l <<< "${relatency% ms} < 10000") = 1 ]]; then
            downloadSum=$(bc -l <<< "scale=2; $downloadSum + ${REDownload% Mbit/s}")
            uploadSum=$(bc -l <<< "scale=2; $uploadSum + ${reupload% Mbit/s}")
            latencySum=$(bc -l <<< "scale=2; $latencySum + ${relatency% ms}")
            testSum=$(($testSum + 1))
        fi

        printf "${YELLOW}%-60s${GREEN}%-18s${RED}%-20s${SKYBLUE}%-12s${PLAIN}\n" "${nodeName}" "${reupload}" "${REDownload}" "${relatency}"
    else
        local cerror="ERROR"
        errorSum=$(($errorSum + 1))
    fi
}
speed_test_report(){
    printf "%-60s%-18s%-20s%-12s\n" "Node Name" "Upload Speed" "Download Speed" "Latency"
    downloadSum=0
    uploadSum=0
    latencySum=0
    testSum=0
    errorSum=0
    ### Do bandwidth test one by one
    start=$(date +%s)
    for i in "$@"; do
        speed_test $i
    done
    end=$(date +%s)  
    ### Print out the test summary
    # Average download, upload & latency
    downloadSum=$(bc -l <<< "scale=2; $downloadSum / $testSum")
    uploadSum=$(bc -l <<< "scale=2; $uploadSum / $testSum")
    latencySum=$(bc -l <<< "scale=2; $latencySum / $testSum")
    echo "---------------------------------------------------------------------------------------------------------"
    printf "${YELLOW}%-60s${GREEN}%-18s${RED}%-20s${SKYBLUE}%-12s${PLAIN}\n" "Average" "${uploadSum} Mbit/s" "${downloadSum} Mbit/s" "${latencySum} ms"
    echo -e "$uploadSum\n$downloadSum\n$latencySum"
    echo -e "\n$uploadSum\n$downloadSum\n$latencySum" >> ${RAW_REPORT}
    # Error
    if (( $errorSum > 0 )); then
        echo ""
        echo Error tests : $errorSum
    fi
    # Summary
    echo ""
    time=$(( $end - $start ))
    if [[ $time -gt 60 ]]; then
        min=$(expr $time / 60)
        sec=$(expr $time % 60)
        echo -ne "Execution Time：${min} m ${sec} s"
    else
        echo -ne "Execution Time：${time} sec"
    fi
    echo -ne "\nCurrent Time: "
    echo $(date +%Y-%m-%d" "%H:%M:%S)
    echo "Testing done！"
}

if [[ $# != 0 ]]; then
    speed_test_report $@
else
    ### Default tests
    echo -e "\n\n\n" >> ${RAW_REPORT}
    echo ""
    echo "######################"
    echo "# China (South)      #"
    echo "######################"
    speed_test_report 1849 6894 10305
    echo ""
    echo "######################"
    echo "# China (Central)    #"
    echo "######################"
    # speed_test_report 12637
    speed_test_report 5097 5131
    echo ""
    echo "######################"
    echo "# China (East)       #"
    echo "######################"
    speed_test_report 3633 5083 4665 6715
    echo ""
    echo "######################"
    echo "# China (SW)         #"
    echo "######################"
    speed_test_report 4863 4575 4624
    echo ""
    echo "######################"
    echo "# China (Noth)       #"
    echo "######################"
    speed_test_report 5475 4690
    echo ""
    echo "######################"
    echo "# Hong Kong          #"
    echo "######################"
    speed_test_report 1536 8170 10267 13538
    echo -e "\n\n\n" >> ${RAW_REPORT}
    echo ""
    echo "######################"
    echo "# Taiwan (Koushiung) #"
    echo "######################"
    speed_test_report 4941 2593 3842 5853 11713 8627 12601 8968
    echo ""
    echo "######################"
    echo "# Taiwan (Taichung)  #"
    echo "######################"
    speed_test_report 4940 2591 3841 5854 11707 12585 12000
    echo ""
    echo "######################"
    echo "# Taiwan (Taipei)    #"
    echo "######################"
    speed_test_report 3967 2327 2133 5334 11703 8623 14036 12583 5008
    echo ""
    echo "######################"
    echo "# Japan              #"
    echo "######################"
    echo ""
    echo "#########################################################"
    echo "# ${RED}Warning: ${PLAIN}The Japan testing nodes are very unstable.   #"
    echo "# So don't be suprised that when you find a bad result. #"
    echo "# Also don't rely on the final 'Average' result         #"
    echo "#                                                       #"
    echo "# Luckily, there are still 2 nodes found to be stable,  #"
    echo "#  - SoftEther Corporation (Tsukuba)                    #"
    echo "#  - Allied Telesis Capital Corporation (Misawa)        #"
    echo "#                                                       #"
    echo "# You can value these 2 more than the rest.             #"
    echo "#########################################################"
    speed_test_report 7139 6405 6766 8832 14277 12511 8348
    echo ""
    echo "######################"
    echo "# US (San Jose)      #"
    echo "######################"
    speed_test_report 10384 5479 11899

fi

exit

clear
echo "#############################################################"
echo "# Description: Test your server's network with Speedtest    #"
echo "# Intro:  https://www.oldking.net/305.html                  #"
echo "# Author: Oldking <oooldking@gmail.com>                     #"
echo "# Github: https://github.com/oooldking                      #"
echo "#############################################################"
echo
echo "测试服务器到"
echo -ne "1.中国电信 2.中国联通 3.中国移动 4.本地默认 5.全面测速"

while :; do echo
        read -p "请输入数字选择： " telecom
        if [[ ! $telecom =~ ^[1-5]$ ]]; then
                echo "输入错误! 请输入正确的数字!"
        else
                break   
        fi
done

if [[ ${telecom} == 1 ]]; then
        telecomName="电信"
        echo -e "\n选择最靠近你的方位"
    echo -ne "1.北方 2.南方"
    while :; do echo
            read -p "请输入数字选择： " pos
            if [[ ! $pos =~ ^[1-2]$ ]]; then
                    echo "输入错误! 请输入正确的数字!"
            else
                    break
            fi
    done
    echo -e "\n选择最靠近你的城市"
    if [[ ${pos} == 1 ]]; then
        echo -ne "1.郑州 2.襄阳"
        while :; do echo
                read -p "请输入数字选择： " city
                if [[ ! $city =~ ^[1-2]$ ]]; then
                        echo "输入错误! 请输入正确的数字!"
                else
                        break
            fi
        done
        if [[ ${city} == 1 ]]; then
                num=4595
                cityName="郑州"
        fi
        if [[ ${city} == 2 ]]; then
                num=12637
                cityName="襄阳"
        fi
    fi
    if [[ ${pos} == 2 ]]; then
        echo -ne "1.上海 2.杭州 3.南宁 4.南昌 5.长沙 6.深圳 7.重庆 8.成都"
        while :; do echo
                read -p "请输入数字选择： " city
                if [[ ! $city =~ ^[1-8]$ ]]; then
                        echo "输入错误! 请输入正确的数字!"
                else
                        break
            fi
        done
        if [[ ${city} == 1 ]]; then
                num=3633
                cityName="上海"
        fi
        if [[ ${city} == 2 ]]; then
                num=7509
                cityName="杭州"
        fi
        if [[ ${city} == 3 ]]; then
                num=10305
                cityName="南宁"
        fi
        if [[ ${city} == 4 ]]; then
                num=7230
                cityName="南昌"
        fi
        if [[ ${city} == 5 ]]; then
                num=6132
                cityName="长沙"
        fi
        if [[ ${city} == 6 ]]; then
                num=5081
                cityName="深圳"
        fi
        if [[ ${city} == 7 ]]; then
                num=6592
                cityName="重庆"
        fi
        if [[ ${city} == 8 ]]; then
                num=4624
                cityName="成都"
        fi
    fi
fi

if [[ ${telecom} == 2 ]]; then
        telecomName="联通"
    echo -ne "\n1.北方 2.南方"
    while :; do echo
            read -p "请输入数字选择： " pos
            if [[ ! $pos =~ ^[1-2]$ ]]; then
                    echo "输入错误! 请输入正确的数字!"
            else
                    break
            fi
    done
    echo -e "\n选择最靠近你的城市"
    if [[ ${pos} == 1 ]]; then
        echo -ne "1.沈阳 2.长春 3.哈尔滨 4.天津 5.济南 6.北京 7.郑州 8.西安 9.太原 10.宁夏 11.兰州 12.西宁"
        while :; do echo
                read -p "请输入数字选择： " city
                if [[ ! $city =~ ^(([1-9])|(1([0-2]{1})))$ ]]; then
                        echo "输入错误! 请输入正确的数字!"
                else
                        break
            fi
        done
        if [[ ${city} == 1 ]]; then
                num=5017
                cityName="沈阳"
        fi
        if [[ ${city} == 2 ]]; then
                num=9484
                cityName="长春"
        fi
        if [[ ${city} == 3 ]]; then
                num=5460
                cityName="哈尔滨"
        fi
        if [[ ${city} == 4 ]]; then
                num=5475
                cityName="天津"
        fi
        if [[ ${city} == 5 ]]; then
                num=5039
                cityName="济南"
        fi
        if [[ ${city} == 6 ]]; then
                num=5145
                cityName="北京"
        fi
        if [[ ${city} == 7 ]]; then
                num=5131
                cityName="郑州"
        fi
        if [[ ${city} == 8 ]]; then
                num= 4863
                cityName="西安"
        fi
        if [[ ${city} == 9 ]]; then
                num=12868
                cityName="太原"
        fi
        if [[ ${city} == 10 ]]; then
                num=5509
                cityName="宁夏"
        fi
        if [[ ${city} == 11 ]]; then
                num=4690
                cityName="兰州"
        fi
        if [[ ${city} == 12 ]]; then
                num=5992
                cityName="西宁"
        fi
    fi
    if [[ ${pos} == 2 ]]; then
        echo -ne "1.上海 2.杭州 3.南宁 4.合肥 5.南昌 6.长沙 7.深圳 8.广州 9.重庆 10.昆明 11.成都"
        while :; do echo
                read -p "请输入数字选择： " city
                if [[ ! $city =~ ^(([1-9])|(1([0-1]{1})))$ ]]; then
                        echo "输入错误! 请输入正确的数字!"
                else
                        break
            fi
        done
        if [[ ${city} == 1 ]]; then
                num=5083
                cityName="上海"
        fi
        if [[ ${city} == 2 ]]; then
                num=5300
                cityName="杭州"
        fi
        if [[ ${city} == 3 ]]; then
                num=5674
                cityName="南宁"
        fi
        if [[ ${city} == 4 ]]; then
                num=5724
                cityName="合肥"
        fi
        if [[ ${city} == 5 ]]; then
                num=5079
                cityName="南昌"
        fi
        if [[ ${city} == 6 ]]; then
                num=4870
                cityName="长沙"
        fi
        if [[ ${city} == 7 ]]; then
                num=10201
                cityName="深圳"
        fi
        if [[ ${city} == 8 ]]; then
                num=3891
                cityName="广州"
        fi
        if [[ ${city} == 9 ]]; then
                num=5726
                cityName="重庆"
        fi
        if [[ ${city} == 10 ]]; then
                num=5103
                cityName="昆明"
        fi
        if [[ ${city} == 11 ]]; then
                num=2461
                cityName="成都"
        fi
    fi
fi

if [[ ${telecom} == 3 ]]; then
        telecomName="移动"
    echo -ne "\n1.北方 2.南方"
    while :; do echo
            read -p "请输入数字选择： " pos
            if [[ ! $pos =~ ^[1-2]$ ]]; then
                    echo "输入错误! 请输入正确的数字!"
            else
                    break
            fi
    done
    echo -e "\n选择最靠近你的城市"
    if [[ ${pos} == 1 ]]; then
        echo -ne "1.西安"
        while :; do echo
                read -p "请输入数字选择： " city
                if [[ ! $city =~ ^[1]$ ]]; then
                        echo "输入错误! 请输入正确的数字!"
                else
                        break
            fi
        done
        if [[ ${city} == 1 ]]; then
                num=5292
        fi
    fi
    if [[ ${pos} == 2 ]]; then
        echo -ne "1.上海 2.宁波 3.无锡 4.杭州 5.合肥 6.成都"
        while :; do echo
                read -p "请输入数字选择： " city
                if [[ ! $city =~ ^[1-6]$ ]]; then
                        echo "输入错误! 请输入正确的数字!"
                else
                        break
            fi
        done
        if [[ ${city} == 1 ]]; then
                num=4665
                cityName="上海"
        fi
        if [[ ${city} == 2 ]]; then
                num=6715
                cityName="宁波"
        fi
        if [[ ${city} == 3 ]]; then
                num=5122
                cityName="无锡"
        fi
        if [[ ${city} == 4 ]]; then
                num=4647
                cityName="杭州"
        fi
        if [[ ${city} == 5 ]]; then
                num=4377 
                cityName="合肥"
        fi
        if [[ ${city} == 6 ]]; then
                num=4575
                cityName="成都"
        fi
    fi
fi

result() {
    download=`cat speed.log | awk -F ':' '/Download/{print $2}'`
    upload=`cat speed.log | awk -F ':' '/Upload/{print $2}'`
    hostby=`cat speed.log | awk -F ':' '/Hosted/{print $1}'`
    latency=`cat speed.log | awk -F ':' '/Hosted/{print $2}'`
    clear
    echo "$hostby"
    echo "延迟  : $latency"
    echo "上传  : $upload"
    echo "下载  : $download"
    echo -ne "\n当前时间: "
    echo $(date +%Y-%m-%d" "%H:%M:%S)
}

speed_test(){
    temp=$(python speedtest.py --server $1 --share 2>&1)
    is_down=$(echo "$temp" | grep 'Download') 
    if [[ ${is_down} ]]; then
        local REDownload=$(echo "$temp" | awk -F ':' '/Download/{print $2}')
        local reupload=$(echo "$temp" | awk -F ':' '/Upload/{print $2}')
        local relatency=$(echo "$temp" | awk -F ':' '/Hosted/{print $2}')
        local nodeName=$2

        printf "${YELLOW}%-17s${GREEN}%-18s${RED}%-20s${SKYBLUE}%-12s${PLAIN}\n" "${nodeName}" "${reupload}" "${REDownload}" "${relatency}"
    else
        local cerror="ERROR"
    fi
}

if [[ ${telecom} =~ ^[1-3]$ ]]; then
    python speedtest.py --server ${num} --share 2>/dev/null | tee speed.log 2>/dev/null
    is_down=$(cat speed.log | grep 'Download')

    if [[ ${is_down} ]]; then
        result
        echo "测试到 ${cityName}${telecomName} 完成！"
        rm -rf speed.log
    else
        echo -e "\n${RED}ERROR:${PLAIN} 当前节点不可用，请更换其他节点，或换个时间段再测试。"
    fi
fi

if [[ ${telecom} == 4 ]]; then
    python speedtest.py | tee speed.log
    result
    echo "本地测试完成！"
    rm -rf speed.log
fi

if [[ ${telecom} == 5 ]]; then
    echo ""
    printf "%-14s%-18s%-20s%-12s\n" "Node Name" "Upload Speed" "Download Speed" "Latency"
    start=$(date +%s) 
    speed_test '1111' '测试节点'
    speed_test '1122' '测试节点'
    speed_test '12637' '襄阳电信'
    speed_test '5081' '深圳电信'
    speed_test '3633' '上海电信'
    speed_test '4624' '成都电信'
    speed_test '5017' '沈阳联通'
    speed_test '4863' '西安联通'
    speed_test '5083' '上海联通'
    speed_test '5726' '重庆联通'
    speed_test '5192' '西安移动'
    speed_test '4665' '上海移动'
    speed_test '6715' '宁波移动'
    speed_test '4575' '成都移动'
    end=$(date +%s)  
    echo ""
    time=$(( $end - $start ))
    if [[ $time -gt 60 ]]; then
        min=$(expr $time / 60)
        sec=$(expr $time % 60)
        echo -ne "花费时间：${min} 分 ${sec} 秒"
    else
        echo -ne "花费时间：${time} 秒"
    fi
    echo -ne "\n当前时间: "
    echo $(date +%Y-%m-%d" "%H:%M:%S)
    echo "全面测试完成！"
fi