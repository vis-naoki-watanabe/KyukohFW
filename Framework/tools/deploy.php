src_path=/home/VisselKobe/vissel_server/
dest_user=VisselKobe
dest_domain=40.115.189.156


dest_path=/var/www/visselkobe.cloudapp.net/
if [ "$1" != "" ]; then
    if [ "$1" = "dev" ]; then
        dest_path=/var/www/visselkobe-dev.cloudapp.net/
    fi
fi

declare -a dir_list=(
    "config/vissel/config.yml,config/vissel/"
    "sql/Vissel/,sql/Vissel/"
    "data/js/,data/js/"
    "data/tools-vissel.visualize.tokyo/,data/tools-app.vissel-kobe.co.jp/"
    "tools/vissel/cmd_common.php,tools/vissel/"
    "tools/vissel/notification/manager.php,tools/vissel/notification/"
    "tools/vissel/notification/manager_dev.php,tools/vissel/notification/"
    "tools/vissel/notification/NotificationManager.php,tools/vissel/notification/"
    "tools/vissel/notification/direct_send.php,tools/vissel/notification/"
    "tools/vissel/notification/bak/notification.php,tools/vissel/notification/bak"
    "controllers/*.php,controllers/"
    "controllers/Vissel/,controllers/Vissel/"
    "models/App.php,models/"
    "models/common.php,models/"
    "models/App/*.php,models/App/"
    "models/App/Vissel/,models/App/Vissel/"
    "models/Utils/Cache/,models/Utils/Cache/"
    "models/Utils/Zend/,models/Utils/Zend/"
    "models/Utils/Spyc.class.php,models/Utils/"
    "models/Framework/,models/Framework/"
    "views/includes/vissel/,views/includes/vissel/"
    "views/scripts/vissel/,views/scripts/vissel/"
    "views/snippets/vissel/,views/snippets/vissel/"
    "views-tools/includes/vissel/,views-tools/includes/vissel/"
    "views-tools/scripts/vissel/,views-tools/scripts/vissel/"
    "views-tools/snippets/vissel/,views-tools/snippets/vissel/"
)

for e in ${dir_list[@]}; do
	buff=${e}
	IFS=',' read -ra target <<< "$buff"

	opt_src=$src_path${target[0]}
	opt_dest=$dest_path${target[1]}

        if [ ! -e $opt_dest ]; then
            mkdir -p $opt_dest
        fi

	#comm="rsync -auzv --no-o --no-p --delete "$opt_src" "$opt_dest
        comm="rsync -rlcgzopDv --delete -exclude "._*" "$opt_src" "$opt_dest
	echo $comm
        $comm
done

declare -a dir_list2=(
    "data/vissel.visualize.tokyo/,data/app.vissel-kobe.co.jp/"
)
for e in ${dir_list2[@]}; do
	buff=${e}
	IFS=',' read -ra target <<< "$buff"

	opt_src=$src_path${target[0]}
	opt_dest=$dest_path${target[1]}

        if [ ! -e $opt_dest ]; then
            mkdir -p $opt_dest
        fi

	#comm="rsync -auzv --no-o --no-p "$opt_src" "$opt_dest
        comm="rsync -rlcgzopDv -exclude "._*" "$opt_src" "$opt_dest
	echo $comm
        $comm
done

cd /var/www/visselkobe.cloudapp.net/data/tools-app.vissel-kobe.co.jp/images/
ln -nfs /var/www/visselkobe.cloudapp.net/data/app.vissel-kobe.co.jp/images/media media

cd /var/www/visselkobe-dev.cloudapp.net/data/tools-app.vissel-kobe.co.jp/images/
ln -nfs /var/www/visselkobe-dev.cloudapp.net/data/app.vissel-kobe.co.jp/images/media media