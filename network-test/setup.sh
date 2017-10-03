#!/bin/bash

### Parameter initialization
options=()
inputs=()
# default options
options["skipPreload"]=0
echo "${options[@]}"

usage(){
	echo "Usage: bash $0 [-p] [hostname]"
	exit 0
}

optionError(){
	echo "Unknown option: $1"
	exit 1
}
for input in "$@"; do
	echo input : $input
	if [[ "$input" =~ ^-- ]]; then
		option=${input##--}
		case $option in
			help)
				usage
				;;
			*)
				optionError $option
				;;
		esac
		echo > /dev/null
	elif [[ "$input" =~ ^- ]]; then
		optionStr=${input##-}
		echo optionStr : $optionStr
		while [[ -n "$optionStr" ]]; do
			option=${optionStr:0:1}
			echo optionStr : $optionStr
			echo option : $option
			case $option in
				h)
					usage
					;;
				p)
					options["skipPreload"]=1
					echo 'Option : "Skip speedtest config preload" had been set'
					;;
				*)
					optionError "-$option"
					;;
			esac
			optionStr=${optionStr:1}
		done
		echo > /dev/null
	else
		inputs+=("$input")
	fi
done

### Install necesary package
sudo yum install -y git php php-dom php-gd php-sqlite3 php-posix gcc gcc-c++ unzip libaio-devel nc sysbench wget bc httpd

# sysbench is not included in AWS base repo, we need to install the repo as well
which sysbench > /dev/null
if [[ $? = 1 ]]; then
	sudo yum install -y http://www.percona.com/downloads/percona-release/percona-release-0.0-1.x86_64.rpm
	sudo yum install -y sysbench
fi

# Download phoronix test suite from git
git clone https://github.com/phoronix-test-suite/phoronix-test-suite.git
#alias phoronix-test-suite="~/phoronix-test-suite/phoronix-test-suite"
echo 'alias phoronix-test-suite="~/phoronix-test-suite/phoronix-test-suite"' >> ~/.bashrc

# Download speedtest cli from git
git clone https://github.com/sivel/speedtest-cli.git
#alias speedtest="~/speedtest-cli/speedtest.py"
echo 'alias speedtest="~/speedtest-cli/speedtest.py"' >> ~/.bashrc

# Re-run bashrc for alias
#. ~/.bashrc

# Close SELinux
sudo setenforce 0

# Change hostname
if [[ -n "$1" ]]; then
	sudo hostname "$1"
	exec -l bash
fi

#### Speedtest config pre-load
if [[ ! -d ./speedtest-cli ]]; then
	>&2 echo "ERROR: speedtest-cli cannot be installed"
	exit
fi

# Note: no longer used
if [[ 1 = 0 ]]; then
	originPath=$(pwd)
	cd speedtest-cli
	speedtestPath=$(pwd)
	currentUser=$(whoami)

	sudo bash -c "cat <<EOT > /etc/httpd/conf.d/localhost.conf
	<VirtualHost *:80>
		ServerName localhost
		DocumentRoot ${speedtestPath}
	</VirtualHost>
EOT"
	sudo bash -c "sed -ri 's/^User apache\$/User ${currentUser}/g' /etc/httpd/conf/httpd.conf"
	sudo bash -c "sed -ri 's/^Group apache\$/Group ${currentUser}/g' /etc/httpd/conf/httpd.conf"
	sudo service httpd restart
fi

sed -ri "s#://www.speedtest.net/speedtest-config.php#http://localhost/speedtest-config.php#g" speedtest.py


# Download config
wget -t 5 -T 5 -w 30 http://www.speedtest.net/speedtest-config.php -P /var/www/html/

if [[ $? != 0 ]]; then
	>&2 echo "ERROR: Failed to download speedtest config. Please download it manually and place it into /var/www/html/"
fi
cd $originPath