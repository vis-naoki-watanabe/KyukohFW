# execute in Framework/Tools/ Dirctory
# 新規にプロジェクトを作成する

export WORK_ROOT=$(cd $(dirname $0)/../;pwd)
export PROJECT_ROOT=$(cd $(dirname $0)/../../../;pwd)
export FRAMEWORK_REF="../../00_framework/KyukohFW"

# プロジェクトルート: cf. 00_application
PROJECT_ROOT=${PROJECT_ROOT}/$1
# アプリ名やドメイン名
PROJECT_NAME=$2
# プロジェクトパス
PROJECT_PATH=${PROJECT_ROOT}/${PROJECT_NAME}

if [ "${PROJECT_ROOT}" = "" ] || [ "${PROJECT_NAME}" = "" ]; then
    echo usage: mkproject.sh PROJECT_ROOT PROJECT_NAME
    exit;
fi

# ログとキャッシュのルート
export LOGS_REF="../../99_logs/${PROJECT_NAME}"
export CACHE_REF="../../99_cache/${PROJECT_NAME}"

# プロジェクトディレクトリ作成
echo ============================================
echo プロジェクトディレクトリ作成作成
echo ============================================
echo "[作成]${PROJECT_PATH}"
mkdir -p ${PROJECT_PATH}
echo ""

# シンボリックリンク作成
echo ============================================
echo Frameworkシンボリックリンク作成
echo ============================================
dir_list=("Framework" "Library" "Tools")
for dir in ${dir_list[@]}
do
    if [ ! -L ${PROJECT_PATH}/${dir} ]; then
        echo "ln -s ${FRAMEWORK_REF}/${dir} ${PROJECT_PATH}/${dir}"
        ln -s ${FRAMEWORK_REF}/${dir} ${PROJECT_PATH}/${dir}
    else
        echo "ln -nfs ${FRAMEWORK_REF}/${dir} ${PROJECT_PATH}/${dir}"
        ln -nfs ${FRAMEWORK_REF}/${dir} ${PROJECT_PATH}/${dir}
    fi
done
echo ""

# プロジェクト雛形コピー
echo ============================================
echo プロジェクト雛形コピー
echo ============================================
if [ -e ${PROJECT_PATH}/App ]; then
    echo "${PROJECT_PATH}/Appが存在します。"
    echo "雛形をコピーするには、Appを削除してください。"
else
    echo ${PROJECT_PATH}/App
    cp -r ../Sample ${PROJECT_PATH}/App
    mv ${PROJECT_PATH}/App/htdocs ${PROJECT_PATH}/
    mv ${PROJECT_PATH}/App/config ${PROJECT_PATH}/
fi
echo ""

# ログ・キャッシュディレクトリ作成
echo ============================================
echo ログ、キャッシュディレクトリ作成
echo ============================================
./mklogdir.sh ${PROJECT_NAME}
echo ""

# ログ：シンボリックリンク作成
echo ============================================
echo ログ：シンボリックリンク作成
echo ============================================
dir_list=("logs")
for dir in ${dir_list[@]}
do
    if [ ! -L ${PROJECT_PATH}/${dir} ]; then
        echo "ln -s ${LOGS_REF}/${dir} ${PROJECT_PATH}"
        ln -s ${LOGS_REF} ${PROJECT_PATH}/${dir}
    else
        echo "ln -nfs ${LOGS_REF}/${dir} ${PROJECT_PATH}"
        ln -nfs ${LOGS_REF} ${PROJECT_PATH}/${dir}
    fi
done
echo ""

# キャッシュ：シンボリックリンク作成
echo ============================================
echo キャッシュ：シンボリックリンク作成
echo ============================================
dir_list=("cache")
for dir in ${dir_list[@]}
do
    if [ ! -L ${PROJECT_PATH}/${dir} ]; then
        echo "ln -s ${CACHE_REF}/${dir} ${PROJECT_PATH}"
        ln -s ${CACHE_REF} ${PROJECT_PATH}/${dir}
    else
        echo "ln -nfs ${CACHE_REF}/${dir} ${PROJECT_PATH}"
        ln -nfs ${CACHE_REF} ${PROJECT_PATH}/${dir}
    fi
done