##步驟

1. 安裝 openssl openssl-devel

    yum install openssl openssl-devel

2. 安裝libssh2
    
    wget http://www.libssh2.org/download/libssh2-1.4.3.tar.gz
    
    tar zxvf libssh2-1.4.3.tar.gz
    
    cd libssh2-1.4.3
    
    ./configure
    
    make all install
    
3. 編譯SSH2擴展(--with-php-config必須在php-config下,可用 find / -name php-config查詢位置)
    
    wget http://pecl.php.net/get/ssh2-0.12.tgztar 
    
    zxvf ssh2-0.12.tgz 
    
    cd ssh2-0.12phpize 
    
    ./configure --with-ssh2 --with-php-config=/usr/local/php/bin/php-config 
    
    make & make install
    
4. 編輯php.ini, 在extension_dir下加入ssh2.so  
    extension="ssh2.so"
5. 重啟php-fpm
    service php-fpm restart
    
6. 驗證是否成功      
    php -m | grep ssh2
  
  
##常見問題
1. phpize錯誤訊息: Can't find pgp headwrs in /usr/include/php ....

    A:缺少php-devel, 請依PHP版本安裝php-devel

    PHP5.3,PHP5.4 : yum install php-devel

    PHP5.5 : yum install php55w-devel

    PHP5.6 : yum install php56w-devel


2. PHP版本衝突 : Error: php56w-common conflicts with php-common-5.3.3-48.el6_8.x86_64

    A: 移除舊版本(下列範例為移除PHP5.3)

    yum remove php-common-5.3.3-48.el6_8.x86_64 
    
    
    
    
