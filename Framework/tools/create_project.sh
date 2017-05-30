#!/bin/bash

export ROOT_PATH=~/visapp/app00
export APP_NAME=$1
export APP_PATH=${ROOT_PATH}/${APP_NAME}
export FRAMEWORK_PATH_RELATIVE=../../framework/KyukohFW

# アプリケーションディレクトリ作成
mkdir -p  ${APP_PATH}

# ログディレクトリ作成
mkdir -p  ${ROOT_PATH}/logs/${APP_NAME}/apache
mkdir -p  ${ROOT_PATH}/logs/${APP_NAME}/misc

# キャッシュディレクトリ作成
mkdir -p  ${ROOT_PATH}/cache/${APP_NAME}

# シンボリックリンク作成
cd ${APP_PATH}
ln -s ${FRAMEWORK_PATH_RELATIVE}/Framework Framework
ln -s ${FRAMEWORK_PATH_RELATIVE}/Library Library
ln -s ../logs logs
ln -s ../cache cache

