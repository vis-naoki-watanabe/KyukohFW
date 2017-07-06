# プロジェクト用のログディレクトリを作成する
# mkproject.shを実行した場合は、自動でmklogdir.shも呼ばれる

export WORK_ROOT=$(cd $(dirname $0)/../../../;pwd)
export LOGS_ROOT=${WORK_ROOT}/99_logs
export CACHE_ROOT=${WORK_ROOT}/99_cache
export PROJECT_NAME=$1

export LOGS_PATH=${LOGS_ROOT}/${PROJECT_NAME}
export CACH_PATH=${CACHE_ROOT}/${PROJECT_NAME}

echo "[作成]${LOGS_PATH}"
mkdir -p ${LOGS_PATH}/{apache,misc}
chmod 777 ${LOGS_PATH}/{apache,misc}

echo "[作成]${CACH_PATH}"
mkdir -p ${CACH_PATH}
chmod 777 ${CACH_PATH}